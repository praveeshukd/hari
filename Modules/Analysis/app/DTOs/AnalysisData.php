<?php

namespace Modules\Analysis\DTOs;

use Modules\Analysis\Support\PressureNormalizer;

final readonly class AnalysisData
{
    public function __construct(
        public string $date,
        public string $sampleName,
        public string $batchNo,
        public string $analysis,
        public ?float $pressure,
        public string $columnName,
        public string $instrument,
        public ?float $stdCon,
        public ?float $stdArea,
        public ?float $sampleCon,
        public ?float $sampleArea,
        public ?float $standardPurity,
        public ?string $requestFormNo,
        public ?string $result,
        public ?string $doneBy,
        public ?string $remarks,
        public ?string $specification,
        public ?string $sampleSubmittedBy,
    ) {}

    public static function fromValidated(array $data, ?PressureNormalizer $normalizer = null): self
    {
        $normalizer ??= new PressureNormalizer();

        return new self(
            date: (string) $data['date'],
            sampleName: (string) $data['sample_name'],
            batchNo: (string) $data['batch_no'],
            analysis: (string) $data['analysis'],
            pressure: $normalizer->normalize($data['pressure'] ?? null),
            columnName: (string) ($data['column_name'] ?? ''),
            instrument: (string) ($data['instrument'] ?? ''),
            stdCon: self::nullableFloat($data['std_con'] ?? null),
            stdArea: self::nullableFloat($data['std_area'] ?? null),
            sampleCon: self::nullableFloat($data['sample_con'] ?? null),
            sampleArea: self::nullableFloat($data['sample_area'] ?? null),
            standardPurity: self::nullableFloat($data['standard_purity'] ?? null),
            requestFormNo: self::nullableString($data['request_form_no'] ?? null),
            result: self::nullableString($data['result'] ?? null),
            doneBy: self::nullableString($data['done_by'] ?? null),
            remarks: self::nullableString($data['remarks'] ?? null),
            specification: self::nullableString($data['specification'] ?? null),
            sampleSubmittedBy: self::nullableString($data['sample_submitted_by'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'sample_name' => $this->sampleName,
            'batch_no' => $this->batchNo,
            'analysis' => $this->analysis,
            'pressure' => $this->pressure,
            'column_name' => $this->columnName,
            'instrument' => $this->instrument,
            'std_con' => $this->stdCon,
            'std_area' => $this->stdArea,
            'sample_con' => $this->sampleCon,
            'sample_area' => $this->sampleArea,
            'standard_purity' => $this->standardPurity,
            'request_form_no' => $this->requestFormNo,
            'result' => $this->result,
            'done_by' => $this->doneBy,
            'remarks' => $this->remarks,
            'specification' => $this->specification,
            'sample_submitted_by' => $this->sampleSubmittedBy,
        ];
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
