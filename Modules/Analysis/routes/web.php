<?php

use Illuminate\Support\Facades\Route;
use Modules\Analysis\Http\Controllers\AnalysisController;
use Modules\Analysis\Http\Controllers\DashboardController;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/analysis/bulk-delete', [AnalysisController::class, 'bulkDelete'])->name('analysis.bulk-delete');
    Route::post('/analysis/bulk-result', [AnalysisController::class, 'bulkResult'])->name('analysis.bulk-result');
    Route::get('/analysis/bulk-export', [AnalysisController::class, 'bulkExport'])->name('analysis.bulk-export');
    Route::post('/analysis/{id}/restore', [AnalysisController::class, 'restore'])
        ->whereNumber('id')
        ->name('analysis.restore');

    Route::resource('analysis', AnalysisController::class)->parameters([
        'analysis' => 'analysis',
    ]);
    Route::patch('/analysis/{analysis}/quick-update', [AnalysisController::class, 'quickUpdate'])
        ->name('analysis.quick-update');

    Route::get('/analysis-export', [AnalysisController::class, 'export'])->name('analysis.export');
    Route::get('/analysis-export-pdf', [AnalysisController::class, 'exportPdf'])->name('analysis.export-pdf');
    Route::post('/analysis-export-pdf', [AnalysisController::class, 'exportPdf'])->name('analysis.export-pdf.post');
    Route::post('/analysis-settings', [AnalysisController::class, 'saveSettings'])->name('analysis.save-settings');
    Route::get('/analysis-import', [AnalysisController::class, 'importView'])->name('analysis.import');
    Route::post('/analysis-import', [AnalysisController::class, 'import'])->name('analysis.import.store');
    Route::get('/analysis-template', [AnalysisController::class, 'template'])->name('analysis.template');
});
