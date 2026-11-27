<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Participant;
use App\Services\ExcelService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    protected ExcelService $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Halaman Live Monitoring Quiz.
     * Alur: Pilih quiz dan cek daftar peserta yang ikut quiz, tampilan table motion & animasi menarik.
     */
    public function index(Request $request)
    {
        $quizzes = Quiz::withCount('participants')->latest()->get();
        $selectedQuizId = $request->input('quiz_id', $quizzes->first()?->id);

        $selectedQuiz = null;
        $participants = collect();
        $formattedParticipants = [];
        $stats = [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'avg_score' => 0,
            'highest_score' => 0,
        ];

        if ($selectedQuizId) {
            $selectedQuiz = Quiz::with('questions')->find($selectedQuizId);
            if ($selectedQuiz) {
                $participants = Participant::where('quiz_id', $selectedQuiz->id)
                    ->orderByDesc('score')
                    ->orderBy('finished_at')
                    ->get();

                $formattedParticipants = $participants->map(function ($p, $idx) {
                    $duration = '-';
                    if ($p->started_at && $p->finished_at) {
                        $duration = $p->started_at->diff($p->finished_at)->format('%im %ss');
                    } elseif ($p->started_at) {
                        $duration = $p->started_at->diffForHumans(null, true);
                    }
                    return [
                        'id' => $p->id,
                        'rank' => $idx + 1,
                        'name' => $p->name,
                        'attendance_number' => $p->attendance_number,
                        'class_name' => $p->class_name,
                        'status' => $p->status,
                        'score' => round($p->score, 1),
                        'total_correct' => $p->total_correct,
                        'total_answered' => $p->total_answered,
                        'total_questions' => $p->total_questions,
                        'progress_percent' => $p->total_questions > 0 ? round(($p->total_answered / $p->total_questions) * 100) : 0,
                        'started_at' => $p->started_at ? $p->started_at->format('H:i:s') : '-',
                        'finished_at' => $p->finished_at ? $p->finished_at->format('H:i:s') : '-',
                        'duration' => $duration,
                    ];
                })->values()->all();

                $total = $participants->count();
                $completed = $participants->where('status', 'completed')->count();
                $inProgress = $participants->where('status', 'in_progress')->count();
                $avgScore = $total > 0 ? $participants->avg('score') : 0;
                $highestScore = $total > 0 ? $participants->max('score') : 0;

                $stats = [
                    'total' => $total,
                    'completed' => $completed,
                    'in_progress' => $inProgress,
                    'avg_score' => round($avgScore, 1),
                    'highest_score' => round($highestScore, 1),
                ];
            }
        }

        return view('admin.monitoring.index', compact(
            'quizzes',
            'selectedQuiz',
            'participants',
            'formattedParticipants',
            'stats'
        ));
    }

    /**
     * Polling API endpoint untuk update peserta live tanpa refresh page.
     */
    public function liveData(Quiz $quiz)
    {
        $participants = Participant::where('quiz_id', $quiz->id)
            ->orderByDesc('score')
            ->orderBy('finished_at')
            ->get()
            ->map(function ($p, $index) {
                $duration = '-';
                if ($p->started_at && $p->finished_at) {
                    $duration = $p->started_at->diff($p->finished_at)->format('%im %ss');
                } elseif ($p->started_at) {
                    $duration = $p->started_at->diffForHumans(null, true);
                }

                return [
                    'id' => $p->id,
                    'rank' => $index + 1,
                    'name' => $p->name,
                    'attendance_number' => $p->attendance_number,
                    'class_name' => $p->class_name,
                    'status' => $p->status,
                    'score' => round($p->score, 1),
                    'total_correct' => $p->total_correct,
                    'total_answered' => $p->total_answered,
                    'total_questions' => $p->total_questions,
                    'progress_percent' => $p->total_questions > 0 ? round(($p->total_answered / $p->total_questions) * 100) : 0,
                    'started_at' => $p->started_at ? $p->started_at->format('H:i:s') : '-',
                    'finished_at' => $p->finished_at ? $p->finished_at->format('H:i:s') : '-',
                    'duration' => $duration,
                ];
            });

        $total = $participants->count();
        $completed = $participants->where('status', 'completed')->count();
        $inProgress = $participants->where('status', 'in_progress')->count();
        $avgScore = $total > 0 ? $participants->avg('score') : 0;
        $highestScore = $total > 0 ? $participants->max('score') : 0;

        return response()->json([
            'participants' => $participants,
            'stats' => [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'avg_score' => round($avgScore, 1),
                'highest_score' => round($highestScore, 1),
            ],
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Hapus satu peserta kuis dari pemantauan live / hasil.
     */
    public function destroyParticipant(Participant $participant)
    {
        $quizId = $participant->quiz_id;
        $name = $participant->name;
        $participant->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Peserta "' . $name . '" berhasil dihapus dari kuis.',
            ]);
        }

        return back()->with('success', 'Data peserta "' . $name . '" berhasil dihapus.');
    }

    /**
     * Hapus multiple / batch peserta kuis yang dipilih (centang).
     */
    public function bulkDestroyParticipants(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:participants,id',
        ]);

        $ids = $request->input('ids', []);
        $count = Participant::whereIn('id', $ids)->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $count . ' data peserta terpilih berhasil dihapus.',
                'deleted_count' => $count,
            ]);
        }

        return back()->with('success', $count . ' data peserta terpilih berhasil dihapus.');
    }
}
