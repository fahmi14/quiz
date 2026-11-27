<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Participant;
use App\Models\ParticipantAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentQuizController extends Controller
{
    /**
     * Halaman Masuk Peserta: Masukkan Kode Kuis.
     */
    public function enterCode()
    {
        return view('student.enter-code');
    }

    /**
     * Verifikasi kode kuis & redirect ke input nama jika valid.
     */
    public function checkCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'Silakan masukkan kode kuis.',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $quiz = Quiz::where('code', $code)->first();

        if (!$quiz) {
            return back()->withInput()->with('error', 'Kode kuis "' . $code . '" tidak ditemukan. Mohon periksa kembali.');
        }

        if (!$quiz->is_active) {
            return back()->withInput()->with('error', 'Kuis ini saat ini sedang dinonaktifkan oleh penyelenggara.');
        }

        if ($quiz->isUpcoming()) {
            return back()->withInput()->with('error', 'Kuis ini belum dimulai. Dimulai pada: ' . $quiz->starts_at->format('d M Y H:i') . ' WIB');
        }

        if ($quiz->isExpired()) {
            return back()->withInput()->with('error', 'Masa aktif kuis telah berakhir pada: ' . $quiz->expires_at->format('d M Y H:i') . ' WIB');
        }

        return redirect()->route('student.join', ['code' => $quiz->code]);
    }

    /**
     * Halaman Peserta: Input Nama & Informasi Sebelum Mulai.
     */
    public function join(Request $request, string $code)
    {
        $quiz = Quiz::withCount('questions')->where('code', strtoupper($code))->firstOrFail();

        if ($quiz->isExpired() || !$quiz->is_active) {
            return redirect()->route('student.enter-code')->with('error', 'Kuis sudah tidak aktif atau masa aktifnya habis.');
        }

        return view('student.join', compact('quiz'));
    }

    /**
     * Mulai Kuis: Simpan data Peserta & generate urutan soal/opsi teracak jika diaktifkan.
     */
    public function start(Request $request, string $code)
    {
        $quiz = Quiz::with('questions')->where('code', strtoupper($code))->firstOrFail();

        $request->validate([
            'name' => 'required|string|min:2|max:100',
            'attendance_number' => 'nullable|string|max:50',
            'class_name' => 'nullable|string|max:100',
            'identifier' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Nama lengkap peserta wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
        ]);

        $questions = $quiz->questions;
        if ($questions->isEmpty()) {
            return back()->with('error', 'Kuis ini belum memiliki soal. Silakan hubungi admin.');
        }

        // Susun urutan ID soal
        $qIds = $questions->pluck('id')->toArray();
        if ($quiz->shuffle_questions) {
            shuffle($qIds);
        }

        // Susun pemetaan opsi acak per soal jika opsi shuffle aktif
        $optionOrders = [];
        if ($quiz->shuffle_options) {
            foreach ($questions as $q) {
                // Kumpulkan opsi yang tersedia (mendukung dinamis 2-20 opsi)
                $availableOpts = array_keys($q->getAllOptions());
                $shuffled = $availableOpts;
                shuffle($shuffled);
                $optionOrders[$q->id] = $shuffled;
            }
        }

        $sessionToken = Str::random(40);
        $participant = Participant::create([
            'quiz_id' => $quiz->id,
            'session_token' => $sessionToken,
            'name' => $request->input('name'),
            'attendance_number' => $request->input('attendance_number'),
            'class_name' => $request->input('class_name'),
            'identifier' => $request->input('identifier'),
            'started_at' => now(),
            'status' => 'in_progress',
            'score' => 0,
            'total_correct' => 0,
            'total_answered' => 0,
            'total_questions' => count($qIds),
            'question_order' => $qIds,
            'option_orders' => $optionOrders,
        ]);

        // Simpan token di session
        session(['quiz_participant_token' => $sessionToken]);

        return redirect()->route('student.play', ['token' => $sessionToken]);
    }

    /**
     * Halaman Kerjakan Kuis (Interaktif, Pastel, Timer, Motion Antislop).
     */
    public function play(string $token)
    {
        $participant = Participant::with(['quiz', 'answers'])->where('session_token', $token)->firstOrFail();
        $quiz = $participant->quiz;

        if ($participant->status === 'completed') {
            return redirect()->route('student.result', ['token' => $token]);
        }

        // Cek durasi waktu pengerjaan
        $endTime = $participant->started_at->copy()->addMinutes($quiz->duration_minutes);
        if (now()->gt($endTime)) {
            // Waktu habis otomatis kalkulasi dan selesaikan
            $this->finalizeQuiz($participant);
            return redirect()->route('student.result', ['token' => $token])
                ->with('info', 'Waktu pengerjaan telah selesai.');
        }

        $remainingSeconds = max(0, (int) now()->diffInSeconds($endTime, false));

        // Ambil soal berdasarkan urutan question_order peserta
        $qIds = $participant->question_order ?: $quiz->questions()->pluck('id')->toArray();
        $allQuestions = Question::whereIn('id', $qIds)->get()->keyBy('id');

        $orderedQuestions = [];
        $optionOrders = $participant->option_orders ?? [];

        foreach ($qIds as $idx => $id) {
            if (isset($allQuestions[$id])) {
                $q = $allQuestions[$id];
                $existingAnswer = $participant->answers->firstWhere('question_id', $id);

                // Siapkan urutan opsi untuk peserta (mendukung dinamis 2-20 opsi)
                $displayOptions = [];
                $qAllOptions = $q->getAllOptions();
                $allKeys = array_keys($qAllOptions);
                $customOrder = $optionOrders[$id] ?? $allKeys;

                // Buat mapping tombol (A, B, C, ...) ke isi opsi asli
                $posLetters = range('A', 'T');
                $btnIdx = 0;
                foreach ($customOrder as $origKey) {
                    if (isset($qAllOptions[$origKey])) {
                        $displayOptions[] = [
                            'display_label' => $posLetters[$btnIdx] ?? $origKey,
                            'original_key' => $origKey,
                            'text' => $qAllOptions[$origKey],
                        ];
                        $btnIdx++;
                    }
                }

                $orderedQuestions[] = [
                    'index' => $idx + 1,
                    'id' => $q->id,
                    'text' => $q->question_text,
                    'points' => $q->points,
                    'options' => $displayOptions,
                    'saved_answer' => $existingAnswer?->selected_option,
                ];
            }
        }

        return view('student.play', compact(
            'participant',
            'quiz',
            'orderedQuestions',
            'remainingSeconds',
            'token'
        ));
    }

    /**
     * Ajax submit satu jawaban (autosave saat memilih opsi).
     */
    public function saveAnswer(Request $request, string $token)
    {
        $participant = Participant::where('session_token', $token)->firstOrFail();

        if ($participant->status === 'completed') {
            return response()->json(['error' => 'Kuis sudah diselesaikan.'], 400);
        }

        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'selected_option' => 'required|string|max:10',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        $selected = strtoupper(trim($request->input('selected_option')));
        $isCorrect = ($selected === strtoupper($question->correct_answer));

        ParticipantAnswer::updateOrCreate(
            [
                'participant_id' => $participant->id,
                'question_id' => $question->id,
            ],
            [
                'selected_option' => $selected,
                'is_correct' => $isCorrect,
            ]
        );

        // Update total terjawab & skor berjalan
        $answers = ParticipantAnswer::where('participant_id', $participant->id)->get();
        $totalAnswered = $answers->count();
        $totalCorrect = $answers->where('is_correct', true)->count();
        
        $totalScore = 0;
        foreach ($answers as $ans) {
            if ($ans->is_correct) {
                $totalScore += $ans->question->points;
            }
        }

        $participant->update([
            'total_answered' => $totalAnswered,
            'total_correct' => $totalCorrect,
            'score' => $totalScore,
        ]);

        return response()->json([
            'success' => true,
            'total_answered' => $totalAnswered,
            'total_correct' => $totalCorrect,
            'score' => $totalScore,
        ]);
    }

    /**
     * Submit akhir kuis dan kalkulasi nilai final.
     */
    public function finish(Request $request, string $token)
    {
        $participant = Participant::with(['quiz.questions', 'answers.question'])
            ->where('session_token', $token)
            ->firstOrFail();

        // Jika peserta mengirimkan array answers sebagai fallback backup
        if ($request->has('answers_backup')) {
            $backupData = json_decode($request->input('answers_backup'), true);
            if (is_array($backupData)) {
                foreach ($backupData as $qId => $selectedOpt) {
                    if (!empty($selectedOpt)) {
                        $q = Question::find($qId);
                        if ($q && $q->quiz_id === $participant->quiz_id) {
                            $isCorrect = (strtoupper($selectedOpt) === strtoupper($q->correct_answer));
                            ParticipantAnswer::updateOrCreate(
                                [
                                    'participant_id' => $participant->id,
                                    'question_id' => $q->id,
                                ],
                                [
                                    'selected_option' => strtoupper($selectedOpt),
                                    'is_correct' => $isCorrect,
                                ]
                            );
                        }
                    }
                }
            }
        }

        if ($participant->status !== 'completed') {
            $this->finalizeQuiz($participant);
        }

        return redirect()->route('student.result', ['token' => $token])
            ->with('success', 'Selamat! Kuis berhasil diselesaikan.');
    }

    /**
     * Halaman Nilai / Ringkasan Hasil Peserta.
     */
    public function result(string $token)
    {
        $participant = Participant::with(['quiz.questions', 'answers.question'])
            ->where('session_token', $token)
            ->firstOrFail();

        $quiz = $participant->quiz;
        $maxScore = $quiz->questions->sum('points') ?: 100;
        $percentage = $maxScore > 0 ? round(($participant->score / $maxScore) * 100) : 0;

        return view('student.result', compact('participant', 'quiz', 'maxScore', 'percentage'));
    }

    /**
     * Helper perhitungan akhir kuis.
     */
    private function finalizeQuiz(Participant $participant): void
    {
        $answers = ParticipantAnswer::where('participant_id', $participant->id)->with('question')->get();
        
        $totalScore = 0;
        $totalCorrect = 0;

        foreach ($answers as $ans) {
            if ($ans->selected_option && $ans->question && strtoupper($ans->selected_option) === strtoupper($ans->question->correct_answer)) {
                $ans->update(['is_correct' => true]);
                $totalCorrect++;
                $totalScore += $ans->question->points;
            } else {
                $ans->update(['is_correct' => false]);
            }
        }

        $participant->update([
            'finished_at' => now(),
            'status' => 'completed',
            'score' => $totalScore,
            'total_correct' => $totalCorrect,
            'total_answered' => $answers->count(),
        ]);
    }
}
