<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/privacy-policy', fn () => Inertia::render('Legal/PrivacyPolicy'))->name('privacy');
Route::get('/terms-of-service', fn () => Inertia::render('Legal/TermsOfService'))->name('terms');

Route::get('/pricing', fn () => Inertia::render('Marketing/Pricing'))->name('pricing.public');

Route::get('/dashboard', function () {
    $user = auth()->user();

    $resumes = $user->resumes()
        ->with(['analysisReports' => function ($query) {
            $query->latest();
        }])
        ->latest()
        ->get()
        ->map(function ($resume) {
            $latestReport = $resume->analysisReports->first();

            return [
                'id' => $resume->id,
                'title' => $resume->filename ?? $resume->title,
                'score' => $latestReport ? $latestReport->overall_score : 0,
                'created_at' => $resume->created_at,
            ];
        });

    return Inertia::render('Dashboard', [
        'resumes' => $resumes,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->prefix('api')->group(function () {
    Route::post('/job/match', [\App\Http\Controllers\Api\JobMatchController::class, 'match'])->name('api.job.match');
});

Route::middleware('auth')->group(function () {
    Route::get('/job-matches', [ResumeController::class, 'index'])->name('job-matches.index');
    Route::get('/history', [ResumeController::class, 'history'])->name('history');

    Route::get('/upload', fn () => Inertia::render('Upload'))->name('upload');
    Route::get('/pricing', fn () => Inertia::render('Marketing/Pricing'))->name('pricing');
    Route::get('/linkedin', fn () => Inertia::render('Marketing/LinkedIn'))->name('linkedin');
    Route::get('/how-it-works', fn () => Inertia::render('Marketing/Simple', ['title' => 'How It Works']))->name('how-it-works');

    Route::post('/resumes/upload', [ResumeController::class, 'upload'])->name('resumes.upload');
    Route::get('/resumes/{resume}/report', [ResumeController::class, 'report'])->name('resumes.report');
    Route::get('/resumes/{resume}/target', [ResumeController::class, 'showTargetForm'])->name('resumes.target');
    Route::post('/resumes/{resume}/target', [ResumeController::class, 'processTargetMatch'])->name('resumes.target.process');

    Route::post('/api/ai/rewrite-bullet', [ResumeController::class, 'rewriteBullet'])->name('ai.rewrite-bullet');

    Route::post('/resumes/{resume}/download', [ResumeController::class, 'downloadImprovedPreview'])
        ->name('resumes.download.preview');
    Route::get('/resume/{resume}/download/{template?}', [ResumeController::class, 'downloadImproved'])
        ->name('resumes.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
