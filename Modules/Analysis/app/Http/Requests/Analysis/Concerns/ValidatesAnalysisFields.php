<?php

namespace Modules\Analysis\Http\Requests\Analysis\Concerns;

use Modules\Analysis\Support\PressureNormalizer;

trait ValidatesAnalysisFields
{
    protected function analysisRules(): array
    {
        return [
            'date' => ['required', 'date'],
            'sample_name' => ['required', 'string', 'max:255'],
            'batch_no' => ['required', 'string', 'max:100'],
            'analysis' => ['required', 'string', 'max:255'],
            'pressure' => ['nullable', 'numeric'],
            'column_name' => ['nullable', 'string', 'max:255'],
            'instrument' => ['nullable', 'string', 'max:255'],
            'std_con' => ['nullable', 'numeric'],
            'std_area' => ['nullable', 'numeric'],
            'sample_con' => ['nullable', 'numeric'],
            'sample_area' => ['nullable', 'numeric'],
            'result' => ['nullable', 'string', 'max:100'],
            'done_by' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'specification' => ['nullable', 'string', 'max:255'],
            'sample_submitted_by' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function preparePressureForValidation(): void
    {
        $normalized = app(PressureNormalizer::class)->normalize($this->input('pressure'));
        $this->merge(['pressure' => $normalized]);
    }
}
