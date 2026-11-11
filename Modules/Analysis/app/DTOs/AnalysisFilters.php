<?php

namespace Modules\Analysis\DTOs;

use Modules\Analysis\Models\Analysis;

final readonly class AnalysisFilters
{
    public function __construct(
        public ?string $search = null,
        public ?string $filterSample = null,
        public ?string $filterBatch = null,
        public ?string $filterAnalysis = null,
        public ?string $filterInstrument = null,
        public ?string $filterColumn = null,
        public ?string $filterResult = null,
        public ?string $filterDoneBy = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $date = null,
        public bool $trash = false,
    ) {}

    public static function fromArray(array $input): self
    {
        $pick = static function (array $input, string $key): ?string {
            $value = $input[$key] ?? null;
            if (is_array($value)) {
                $value = $value[0] ?? null;
            }
            if ($value === null) {
                return null;
            }
            $value = trim((string) $value);

            return $value === '' ? null : $value;
        };

        $trashRaw = $input['trash'] ?? false;

        return new self(
            search: $pick($input, 'search'),
            filterSample: $pick($input, 'filter_sample'),
            filterBatch: $pick($input, 'filter_batch'),
            filterAnalysis: $pick($input, 'filter_analysis'),
            filterInstrument: $pick($input, 'filter_instrument'),
            filterColumn: $pick($input, 'filter_column'),
            filterResult: $pick($input, 'filter_result'),
            filterDoneBy: $pick($input, 'filter_done_by'),
            dateFrom: $pick($input, 'date_from'),
            dateTo: $pick($input, 'date_to'),
            date: $pick($input, 'date'),
            trash: filter_var($trashRaw, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public static function fromRequest(array $input): self
    {
        return self::fromArray(array_intersect_key($input, array_flip(Analysis::FILTER_KEYS)));
    }

    public function toArray(): array
    {
        return array_filter([
            'search' => $this->search,
            'filter_sample' => $this->filterSample,
            'filter_batch' => $this->filterBatch,
            'filter_analysis' => $this->filterAnalysis,
            'filter_instrument' => $this->filterInstrument,
            'filter_column' => $this->filterColumn,
            'filter_result' => $this->filterResult,
            'filter_done_by' => $this->filterDoneBy,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'date' => $this->date,
            'trash' => $this->trash ? '1' : null,
        ], static fn ($value) => $value !== null && $value !== '');
    }
}
