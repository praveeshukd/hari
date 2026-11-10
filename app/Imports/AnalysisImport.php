<?php

namespace App\Imports;

use App\Models\Analysis;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AnalysisImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;
    public int $skipped  = 0;
    public array $errors = [];

    /**
     * Flexible heading normaliser — supports both the export template
     * and freeform column names the user might type.
     */
    private function norm(string $key): string
    {
        return strtolower(trim(preg_replace('/[^a-z0-9]/i', '_', $key)));
    }

    /**
     * Resolve a value from a row by trying multiple possible keys.
     */
    private function resolve(array $row, array $keys): mixed
    {
        foreach ($keys as $k) {
            $nk = $this->norm($k);
            foreach ($row as $rk => $rv) {
                if ($this->norm((string)$rk) === $nk) return $rv;
            }
        }
        return null;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowArr = $row->toArray();

            // Map flexible column names → model fields
            $date       = $this->resolve($rowArr, ['date', 'Date']);
            $sampleName = $this->resolve($rowArr, ['sample_name', 'Sample Name', 'sample name', 'samplename']);
            $batchNo    = $this->resolve($rowArr, ['batch_no', 'Batch No', 'batch no', 'batchno', 'batch_number']);
            $analysis   = $this->resolve($rowArr, ['analysis', 'Analysis']);
            $pressure   = $this->resolve($rowArr, ['pressure', 'Pressure']);
            $column     = $this->resolve($rowArr, ['column', 'Column', 'column_name']);
            $instrument = $this->resolve($rowArr, ['instrument', 'Instrument']);
            $stdCon     = $this->resolve($rowArr, ['std_con', 'Std Con', 'std concentration', 'std_concentration']);
            $stdArea    = $this->resolve($rowArr, ['std_area', 'Std Area', 'std area']);
            $sampleCon  = $this->resolve($rowArr, ['sample_con', 'Sample Con', 'sample concentration', 'sample_concentration']);
            $sampleArea = $this->resolve($rowArr, ['sample_area', 'Sample Area', 'sample area']);
            $result     = $this->resolve($rowArr, ['result', 'Result']);
            $doneBy     = $this->resolve($rowArr, ['done_by', 'Done By', 'done by', 'doneby', 'reviewed_by']);
            $remarks    = $this->resolve($rowArr, ['remarks', 'Remarks', 'remark', 'notes']);

            // Skip rows without minimum required data
            if (empty($sampleName) || empty($batchNo)) {
                $this->skipped++;
                continue;
            }

            // Parse date
            if ($date && !is_string($date)) {
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)
                        ->format('Y-m-d');
                } catch (\Throwable $e) {
                    $date = date('Y-m-d');
                }
            }

            try {
                Analysis::create([
                    'date'        => $date ?: date('Y-m-d'),
                    'sample_name' => $sampleName,
                    'batch_no'    => $batchNo,
                    'analysis'    => $analysis ?? '',
                    'pressure'    => is_numeric($pressure)   ? (float)$pressure   : null,
                    'column_name' => $column ?? '',
                    'instrument'  => $instrument ?? '',
                    'std_con'     => is_numeric($stdCon)     ? (float)$stdCon     : null,
                    'std_area'    => is_numeric($stdArea)    ? (float)$stdArea    : null,
                    'sample_con'  => is_numeric($sampleCon)  ? (float)$sampleCon  : null,
                    'sample_area' => is_numeric($sampleArea) ? (float)$sampleArea : null,
                    'result'      => $result,
                    'done_by'     => $doneBy,
                    'remarks'     => $remarks,
                ]);
                $this->imported++;
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }
}
