<?php

namespace Modules\Analysis\Http\Requests\Analysis;

use Modules\Analysis\Http\Requests\Analysis\Concerns\ValidatesAnalysisFields;
use Modules\Analysis\Models\Analysis;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnalysisRequest extends FormRequest
{
    use ValidatesAnalysisFields;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Analysis::class) ?? false;
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
