<?php

namespace Modules\Analysis\Services;

use Modules\Analysis\DTOs\AnalysisFilters;
use Modules\Analysis\Exports\AnalysisExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportAnalysesService
{
    public function downloadExcel(AnalysisFilters $filters, ?string $filename = null): BinaryFileResponse
    {
        $filename ??= 'QC_Analysis_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new AnalysisExport($filters->toArray()), $filename);
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function downloadExcelByIds(array $ids, ?string $filename = null): BinaryFileResponse
    {
        $filename ??= 'QC_Analysis_Selected_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new AnalysisExport([], $ids), $filename);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new AnalysisExport(), 'QC_Analysis_Template.xlsx');
    }
}
