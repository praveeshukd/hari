<?php

namespace Modules\Analysis\Http\Requests\Analysis;

use Modules\Analysis\Http\Requests\Analysis\Concerns\ValidatesAnalysisFields;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnalysisRequest extends FormRequest
{
    use ValidatesAnalysisFields;

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('analysis')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->preparePressureForValidation();
    }

    public function rules(): array
    {
        return $this->analysisRules();
    }
}
