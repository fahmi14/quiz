<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Participant;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    protected ExcelService $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Dashboard ringkasan kuis.
     */
    public function index()
    {
        $quizzes = Quiz::withCount(['questions', 'participants'])
            ->with(['participants' => function ($q) {
                $q->latest()->limit(5);
            }])
            ->latest()
            ->paginate(10);

        $totalQuizzes = Quiz::count();
        $totalParticipants = Participant::count();
        $activeQuizzes = Quiz::where('is_active', true)->count();
        $completedParticipants = Participant::where('status', 'completed')->count();

        return view('admin.quizzes.index', compact(
            'quizzes',
            'totalQuizzes',
            'totalParticipants',
            'activeQuizzes',
            'completedParticipants'
        ));
    }

    /**
     * Form pembuatan kuis (Nama quiz, masa aktif, kode, import template).
     */
    public function create()
    {
        // Generate random unique code
        $suggestedCode = strtoupper(Str::random(6));
        return view('admin.quizzes.create', compact('suggestedCode'));
    }

    /**
     * Step 1: Upload file excel & preview kolom mapping.
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('excel_file');
        $fileName = 'quiz_import_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('temp_imports', $fileName);
        $fullPath = Storage::path($filePath);

        try {
            $preview = $this->excelService->getPreview($fullPath);
            return response()->json([
                'success' => true,
                'file_path' => $filePath,
                'headers' => $preview['headers'],
                'preview_rows' => $preview['preview_rows'],
                'total_rows' => $preview['total_rows'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Unduh template excel untuk quiz, menyesuaikan jumlah opsi pilihan ganda (2 s/d 20).
     */
    public function downloadTemplate(Request $request)
    {
        $optionsCount = (int) $request->input('options_count', 5);
        $optionsCount = max(2, min(20, $optionsCount));
        $path = $this->excelService->generateTemplate($optionsCount);
        $fileName = "Template_Soal_Kuis_{$optionsCount}_Pilihan.xlsx";
        return response()->download($path, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Simpan kuis baru beserta soal dari mapping excel atau manual.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:quizzes,code',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'duration_minutes' => 'required|integer|min:1|max:360',
            'options_count' => 'required|integer|min:2|max:20',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            
            // File & Mapping (jika import Excel)
            'import_file_path' => 'nullable|string',
            'col_question' => 'nullable|string',
            'col_key' => 'nullable|string',
            'col_points' => 'nullable|string',

            // Soal manual (jika ada)
            'manual_questions' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $quiz = Quiz::create([
                'title' => $validated['title'],
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'] ?? null,
                'starts_at' => $validated['starts_at'] ?? null,
                'expires_at' => $validated['expires_at'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'options_count' => (int) $validated['options_count'],
                'shuffle_questions' => $request->boolean('shuffle_questions'),
                'shuffle_options' => $request->boolean('shuffle_options'),
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
                'created_by' => auth()->id(),
            ]);

            // Jika ada file import & mapping kolom
            if (!empty($validated['import_file_path']) && !empty($validated['col_question'])) {
                $fullPath = Storage::path($validated['import_file_path']);
                if (file_exists($fullPath)) {
                    $mapping = [
                        'question_col' => $validated['col_question'],
                        'key_col' => $request->input('col_key', 'G'),
                        'points_col' => $request->input('col_points', null),
                    ];

                    // Masukkan mapping dinamis untuk opsi A s/d T
                    $letters = range('A', 'T');
                    for ($i = 0; $i < $quiz->options_count; $i++) {
                        $letter = $letters[$i];
                        $colInput = $request->input('col_opt_' . strtolower($letter));
                        if ($colInput) {
                            $mapping['opt_' . strtolower($letter) . '_col'] = $colInput;
                        }
                    }

                    $parsed = $this->excelService->parseQuestionsWithMapping($fullPath, $mapping);
                    foreach ($parsed as $idx => $qData) {
                        Question::create(array_merge($qData, [
                            'quiz_id' => $quiz->id,
                            'order_num' => $idx + 1,
                        ]));
                    }

                    // Hapus temp file
                    @unlink($fullPath);
                }
            }

            // Jika ada manual questions
            if (!empty($validated['manual_questions']) && is_array($validated['manual_questions'])) {
                $startOrder = $quiz->questions()->count() + 1;
                foreach ($validated['manual_questions'] as $mQ) {
                    if (!empty($mQ['question_text'])) {
                        $optsData = [];
                        $letters = range('A', 'T');
                        for ($i = 0; $i < $quiz->options_count; $i++) {
                            $let = $letters[$i];
                            $keyName = 'option_' . strtolower($let);
                            if (isset($mQ[$keyName]) && trim($mQ[$keyName]) !== '') {
                                $optsData[$let] = $mQ[$keyName];
                            }
                        }

                        Question::create([
                            'quiz_id' => $quiz->id,
                            'order_num' => $startOrder++,
                            'question_text' => $mQ['question_text'],
                            'option_a' => $optsData['A'] ?? null,
                            'option_b' => $optsData['B'] ?? null,
                            'option_c' => $optsData['C'] ?? null,
                            'option_d' => $optsData['D'] ?? null,
                            'option_e' => $optsData['E'] ?? null,
                            'options_data' => $optsData,
                            'correct_answer' => strtoupper($mQ['correct_answer'] ?? 'A'),
                            'points' => (int) ($mQ['points'] ?? 10),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('quizzes.show', $quiz)
                ->with('success', 'Kuis "' . $quiz->title . '" berhasil dibuat dengan ' . $quiz->questions()->count() . ' soal!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat kuis: ' . $e->getMessage());
        }
    }
        }
    }

    /**
     * Detail kuis & daftar soal.
     */
    public function show(Quiz $quiz)
    {
        $quiz->load(['questions', 'participants' => function ($q) {
            $q->latest()->limit(10);
        }]);

        return view('admin.quizzes.show', compact('quiz'));
    }

    /**
     * Edit data kuis.
     */
    public function edit(Quiz $quiz)
    {
        $quiz->load('questions');
        return view('admin.quizzes.edit', compact('quiz'));
    }

    /**
     * Update kuis.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:quizzes,code,' . $quiz->id,
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'duration_minutes' => 'required|integer|min:1|max:360',
            'options_count' => 'required|integer|min:2|max:20',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $quiz->update([
            'title' => $validated['title'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'options_count' => (int) $validated['options_count'],
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'shuffle_options' => $request->boolean('shuffle_options'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Pengaturan kuis berhasil diperbarui.');
    }

    /**
     * Tambah soal manual ke kuis yang sudah ada (dukung 2-20 opsi).
     */
    public function addQuestion(Request $request, Quiz $quiz)
    {
        $optsCount = $quiz->options_count ?: 5;
        $letters = range('A', 'T');
        $validKeys = array_slice($letters, 0, $optsCount);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'correct_answer' => 'required|in:' . implode(',', $validKeys),
            'points' => 'required|integer|min:1',
        ]);

        $optsData = [];
        for ($i = 0; $i < $optsCount; $i++) {
            $letter = $letters[$i];
            $val = $request->input('option_' . strtolower($letter));
            if ($i < 2 && empty($val)) {
                return back()->withInput()->with('error', "Pilihan $letter wajib diisi minimal 2 pilihan teratas.");
            }
            if ($val !== null && trim($val) !== '') {
                $optsData[$letter] = $val;
            }
        }

        $nextOrder = $quiz->questions()->max('order_num') + 1;

        $quiz->questions()->create([
            'order_num' => $nextOrder,
            'question_text' => $validated['question_text'],
            'option_a' => $optsData['A'] ?? null,
            'option_b' => $optsData['B'] ?? null,
            'option_c' => $optsData['C'] ?? null,
            'option_d' => $optsData['D'] ?? null,
            'option_e' => $optsData['E'] ?? null,
            'options_data' => $optsData,
            'correct_answer' => strtoupper($validated['correct_answer']),
            'points' => (int)$validated['points'],
        ]);

        return back()->with('success', 'Soal baru berhasil ditambahkan.');
    }

    /**
     * Update butir soal.
     */
    public function updateQuestion(Request $request, Quiz $quiz, Question $question)
    {
        $optsCount = $quiz->options_count ?: 5;
        $letters = range('A', 'T');
        $validKeys = array_slice($letters, 0, $optsCount);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'correct_answer' => 'required|in:' . implode(',', $validKeys),
            'points' => 'required|integer|min:1',
        ]);

        $optsData = [];
        for ($i = 0; $i < $optsCount; $i++) {
            $letter = $letters[$i];
            $val = $request->input('option_' . strtolower($letter));
            if ($i < 2 && empty($val)) {
                return back()->withInput()->with('error', "Pilihan $letter wajib diisi minimal 2 pilihan teratas.");
            }
            if ($val !== null && trim($val) !== '') {
                $optsData[$letter] = $val;
            }
        }

        $question->update([
            'question_text' => $validated['question_text'],
            'option_a' => $optsData['A'] ?? null,
            'option_b' => $optsData['B'] ?? null,
            'option_c' => $optsData['C'] ?? null,
            'option_d' => $optsData['D'] ?? null,
            'option_e' => $optsData['E'] ?? null,
            'options_data' => $optsData,
            'correct_answer' => strtoupper($validated['correct_answer']),
            'points' => (int)$validated['points'],
        ]);

        return back()->with('success', 'Butir soal berhasil diperbarui.');
    }

        return back()->with('success', 'Butir soal berhasil diperbarui.');
    }

    /**
     * Hapus soal.
     */
    public function destroyQuestion(Quiz $quiz, Question $question)
    {
        $question->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Hapus seluruh kuis.
     */
    public function destroy(Quiz $quiz)
    {
        $title = $quiz->title;
        $quiz->delete();
        return redirect()->route('quizzes.index')
            ->with('success', 'Kuis "' . $title . '" berhasil dihapus.');
    }
}
