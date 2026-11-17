<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Participant;
use App\Services\ExcelService;
use Illuminate\Http\Request;

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
}
