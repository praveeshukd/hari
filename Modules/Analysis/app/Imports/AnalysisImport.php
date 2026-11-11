<?php

namespace Modules\Analysis\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Analysis\DTOs\AnalysisData;
use Modules\Analysis\Repositories\AnalysisRepositoryInterface;

class AnalysisImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;

    public int $skipped = 0;

    /** @var list<string> */
    public array $errors = [];

    private function norm(string $key): string
    {
        return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $key)));
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    private function resolve(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            $normalized = $this->norm($key);
            foreach ($row as $rowKey => $value) {
                if ($this->norm((string) $rowKey) === $normalized) {
                    return $value;
                }
            }
        }

        return null;
    }

    public function collection(Collection $rows): void
    {
        $analyses = app(AnalysisRepositoryInterface::class);

        foreach ($rows as $index => $row) {
            $rowArr = $row->toArray();

            $sampleName = $this->resolve($rowArr, ['sample_name', 'Sample Name', 'sample name', 'samplename']);
            $batchNo = $this->resolve($rowArr, ['batch_no', 'Batch No', 'batch no', 'batchno', 'batch_number']);

            if (empty($sampleName) || empty($batchNo)) {
                $this->skipped++;

                continue;
            }

            $date = $this->resolve($rowArr, ['date', 'Date']);
            if ($date && ! is_string($date)) {
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)
                        ->format('Y-m-d');
                } catch (\Throwable) {
                    $date = date('Y-m-d');
                }
            }

            try {
                $data = AnalysisData::fromValidated([
                    'date' => $date ?: date('Y-m-d'),
                    'sample_name' => $sampleName,
                    'batch_no' => $batchNo,
                    'request_form_no' => $this->resolve($rowArr, ['request_form_no', 'Request Form No', 'request form no', 'form_no']),
                    'analysis' => $this->resolve($rowArr, ['analysis', 'Analysis']) ?? '',
                    'pressure' => $this->resolve($rowArr, ['pressure', 'Pressure']),
                    'column_name' => $this->resolve($rowArr, ['column', 'Column', 'column_name']) ?? '',
                    'instrument' => $this->resolve($rowArr, ['instrument', 'Instrument']) ?? '',
                    'std_con' => $this->resolve($rowArr, ['std_con', 'Std Con', 'std concentration', 'std_concentration']),
                    'std_area' => $this->resolve($rowArr, ['std_area', 'Std Area', 'std area']),
                    'sample_con' => $this->resolve($rowArr, ['sample_con', 'Sample Con', 'sample concentration', 'sample_concentration']),
                    'sample_area' => $this->resolve($rowArr, ['sample_area', 'Sample Area', 'sample area']),
                    'standard_purity' => $this->resolve($rowArr, ['standard_purity', 'Standard Purity', 'std purity', 'std_purity']),
                    'result' => $this->resolve($rowArr, ['result', 'Result']),
                    'done_by' => $this->resolve($rowArr, ['done_by', 'Done By', 'done by', 'doneby', 'reviewed_by']),
                    'remarks' => $this->resolve($rowArr, ['remarks', 'Remarks', 'remark', 'notes']),
                ]);

                $analyses->createFromData($data);
                $this->imported++;
            } catch (\Throwable $exception) {
                $this->skipped++;
                $this->errors[] = 'Row '.($index + 2).': '.$exception->getMessage();
            }
        }
    }
}
