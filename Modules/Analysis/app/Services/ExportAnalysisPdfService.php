<?php

namespace Modules\Analysis\Services;

use Modules\Analysis\DTOs\AnalysisFilters;
use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class ExportAnalysisPdfService
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly PdfSettingsService $settings,
    ) {}

    /**
     * @param  list<int|string>|null  $ids
     */
    public function export(?array $ids, AnalysisFilters $filters): Response|View|\Illuminate\Http\RedirectResponse
    {
        $records = $this->resolveRecords($ids, $filters);

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'No records selected for PDF export.');
        }

        $settings = $this->settings->get()->toArray();

        if (class_exists(Pdf::class)) {
            return Pdf::loadView('analysis.pdf', [
                'records' => $records,
                'settings' => $settings,
            ])
                ->setPaper('a4', 'portrait')
                ->download('Sample_Test_Report_'.now()->format('Y-m-d').'.pdf');
        }

        return view('analysis.pdf', [
            'records' => $records,
            'settings' => $settings,
        ]);
    }

    /**
     * @param  list<int|string>|null  $ids
     */
    private function resolveRecords(?array $ids, AnalysisFilters $filters): Collection
    {
        if (! empty($ids)) {
            return $this->analyses->findManyByIds($ids);
        }

        return $this->analyses->query($filters)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }
}
