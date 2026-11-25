<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\StudentQuizController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Pastel Quiz App
|--------------------------------------------------------------------------
*/

// Public / Peserta Routes (Sesuai Diagram Alur)
Route::get('/', [StudentQuizController::class, 'enterCode'])->name('home');
Route::get('/masuk', [StudentQuizController::class, 'enterCode'])->name('student.enter-code');
Route::post('/masuk/check', [StudentQuizController::class, 'checkCode'])->name('student.check-code');
Route::get('/kuis/{code}', [StudentQuizController::class, 'join'])->name('student.join');
Route::post('/kuis/{code}/mulai', [StudentQuizController::class, 'start'])->name('student.start');
Route::get('/pengerjaan/{token}', [StudentQuizController::class, 'play'])->name('student.play');
Route::post('/pengerjaan/{token}/jawaban', [StudentQuizController::class, 'saveAnswer'])->name('student.save-answer');
Route::post('/pengerjaan/{token}/selesai', [StudentQuizController::class, 'finish'])->name('student.finish');
Route::get('/pengerjaan/{token}/hasil', [StudentQuizController::class, 'result'])->name('student.result');

// Guest / Authentication Admin Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

// Admin Protected Routes
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard Quiz Management
    Route::get('/', [QuizController::class, 'index'])->name('dashboard');
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes/preview-import', [QuizController::class, 'previewImport'])->name('quizzes.preview-import');
    Route::get('/quizzes/template-excel', [QuizController::class, 'downloadTemplate'])->name('quizzes.download-template');
    Route::post('/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');
    Route::post('/quizzes/{quiz}/clone', [QuizController::class, 'clone'])->name('quizzes.clone');

    // Manajemen Soal Quiz
    Route::post('/quizzes/{quiz}/questions', [QuizController::class, 'addQuestion'])->name('quizzes.questions.store');
    Route::put('/quizzes/{quiz}/questions/{question}', [QuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
    Route::delete('/quizzes/{quiz}/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');

    // Monitor Live Quiz
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/{quiz}/live-data', [MonitoringController::class, 'liveData'])->name('monitoring.live-data');
    Route::delete('/monitoring/participants/{participant}', [MonitoringController::class, 'destroyParticipant'])->name('monitoring.participants.destroy');
    Route::post('/monitoring/participants/bulk-destroy', [MonitoringController::class, 'bulkDestroyParticipants'])->name('monitoring.participants.bulk-destroy');

    // Hasil Quiz & Export Excel
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/formulation', [ResultController::class, 'formulation'])->name('results.formulation');
    Route::post('/results/formulation/export', [ResultController::class, 'exportFormulation'])->name('results.formulation.export');
    Route::get('/results/{quiz}/export', [ResultController::class, 'export'])->name('results.export');
    Route::get('/results/{quiz}/participants/{participant}', [ResultController::class, 'participantDetail'])->name('results.participant');
    Route::post('/results/participants/bulk-destroy', [MonitoringController::class, 'bulkDestroyParticipants'])->name('results.participants.bulk-destroy');
});
