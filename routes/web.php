<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Auth Routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected Routes (authenticated only)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Analysis Sheet (QC)
    // "analysis" singularizes to "analysi", which does not match Analysis $analysis.
    Route::resource('analysis', AnalysisController::class)->parameters([
        'analysis' => 'analysis',
    ]);
    Route::patch('/analysis/{analysis}/quick-update', [AnalysisController::class, 'quickUpdate'])
         ->name('analysis.quick-update');

    // Export / Import / PDF / Settings
    Route::get('/analysis-export',          [AnalysisController::class, 'export'])     ->name('analysis.export');
    Route::get('/analysis-export-pdf',      [AnalysisController::class, 'exportPdf'])  ->name('analysis.export-pdf');
    Route::post('/analysis-export-pdf',     [AnalysisController::class, 'exportPdf'])  ->name('analysis.export-pdf.post');
    Route::post('/analysis-settings',       [AnalysisController::class, 'saveSettings'])->name('analysis.save-settings');
    Route::get('/analysis-import',          [AnalysisController::class, 'importView']) ->name('analysis.import');
    Route::post('/analysis-import',         [AnalysisController::class, 'import'])     ->name('analysis.import.store');
    Route::get('/analysis-template',        [AnalysisController::class, 'template'])   ->name('analysis.template');
});

