<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Participant;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResultController extends Controller
{
    protected ExcelService $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Halaman Hasil Quiz.
     * Alur: Pilih quiz, export excel, tampilan table.
     */
    public function index(Request $request)
    {
        $quizzes = Quiz::withCount('participants')->latest()->get();
        $selectedQuizId = $request->input('quiz_id', $quizzes->first()?->id);

        $selectedQuiz = null;
        $participants = collect();
        $stats = [
            'total' => 0,
            'completed' => 0,
            'avg_score' => 0,
            'pass_count' => 0, // skor >= 70
        ];

        if ($selectedQuizId) {
            $selectedQuiz = Quiz::with('questions')->find($selectedQuizId);
            if ($selectedQuiz) {
                $query = Participant::where('quiz_id', $selectedQuiz->id);

                if ($search = $request->input('search')) {
                    $query->where('name', 'like', "%{$search}%");
                }

                if ($status = $request->input('status')) {
                    $query->where('status', $status);
                }

                $participants = $query->orderByDesc('score')
                    ->orderBy('finished_at')
                    ->paginate(15)
                    ->withQueryString();

                $allParticipants = Participant::where('quiz_id', $selectedQuiz->id)->get();
                $stats = [
                    'total' => $allParticipants->count(),
                    'completed' => $allParticipants->where('status', 'completed')->count(),
                    'avg_score' => round($allParticipants->avg('score') ?? 0, 1),
                    'pass_count' => $allParticipants->where('score', '>=', 70)->count(),
                ];
            }
        }

        return view('admin.results.index', compact(
            'quizzes',
            'selectedQuiz',
            'participants',
            'stats'
        ));
    }

    /**
     * Export hasil kuis terpilih ke format Excel.
     */
    public function export(Quiz $quiz)
    {
        $quiz->load('questions');
        $participants = Participant::where('quiz_id', $quiz->id)
            ->orderByDesc('score')
            ->orderBy('finished_at')
            ->get();

        $path = $this->excelService->exportQuizResults($quiz, $participants);
        $fileName = 'Hasil_Kuis_' . Str::slug($quiz->title) . '_' . date('Ymd_His') . '.xlsx';

        return response()->download($path, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Detail pengerjaan satu peserta (analisis jawaban benar/salah).
     */
    public function participantDetail(Quiz $quiz, Participant $participant)
    {
        $participant->load(['answers.question']);
        return view('admin.results.participant-detail', compact('quiz', 'participant'));
    }

    /**
     * Halaman formulasi nilai gabungan multi-quiz.
     */
    public function formulation(Request $request)
    {
        $allQuizzes = Quiz::withCount(['participants' => function ($q) {
            $q->where('status', 'completed');
        }])->latest()->get();

        $selectedQuizIds = array_filter((array) $request->input('quiz_ids', []));
        $weights = (array) $request->input('weights', []);

        // Default jika pertama kali buka dan ada minimal 2 kuis
        if (empty($selectedQuizIds) && $allQuizzes->count() >= 2) {
            $firstTwo = $allQuizzes->take(2);
            $selectedQuizIds = $firstTwo->pluck('id')->toArray();
            $weights = [
                $selectedQuizIds[0] => 50,
                $selectedQuizIds[1] => 50,
            ];
        }

        $selectedQuizzes = Quiz::with(['participants' => function ($q) {
            $q->orderByDesc('score');
        }])->whereIn('id', $selectedQuizIds)->get();

        // Peserta per kuis
        $quizParticipantsMap = [];
        foreach ($selectedQuizzes as $q) {
            $quizParticipantsMap[$q->id] = $q->participants->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => trim($p->name),
                    'score' => (float) $p->score,
                    'status' => $p->status,
                ];
            })->values()->toArray();
        }

        return view('admin.results.formulation', compact(
            'allQuizzes',
            'selectedQuizIds',
            'weights',
            'selectedQuizzes',
            'quizParticipantsMap'
        ));
    }

    /**
     * Export formulasi hasil akhir ke Excel.
     */
    public function exportFormulation(Request $request)
    {
        $validated = $request->validate([
            'formula_title' => 'nullable|string|max:150',
            'data' => 'required|array',
            'quizzes' => 'required|array',
        ]);

        $path = $this->excelService->exportCombinedResults(
            $validated['formula_title'] ?? 'Formulasi Hasil Akhir Kuis',
            $validated['quizzes'],
            $validated['data']
        );

        $fileName = 'Formulasi_Hasil_' . date('Ymd_His') . '.xlsx';
        return response()->download($path, $fileName)->deleteFileAfterSend(true);
    }
}
