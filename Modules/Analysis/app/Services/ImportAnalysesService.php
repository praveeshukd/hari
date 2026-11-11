<?php

namespace Modules\Analysis\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Analysis\Events\AnalysesImported;
use Modules\Analysis\Imports\AnalysisImport;

final class ImportAnalysesService
{
    /**
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function execute(UploadedFile $file): array
    {
        $importer = new AnalysisImport;

        DB::transaction(function () use ($importer, $file) {
            Excel::import($importer, $file);
        });

        AnalysesImported::dispatch(
            $importer->imported,
            $importer->skipped,
            $importer->errors,
        );

        return [
            'imported' => $importer->imported,
            'skipped' => $importer->skipped,
            'errors' => $importer->errors,
        ];
    }
}
