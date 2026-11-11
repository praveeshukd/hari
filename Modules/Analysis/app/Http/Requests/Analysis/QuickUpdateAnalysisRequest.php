<?php

namespace Modules\Analysis\Http\Requests\Analysis;

use Modules\Analysis\DTOs\QuickUpdateData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickUpdateAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('analysis')) ?? false;
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', Rule::in(QuickUpdateData::ALLOWED_FIELDS)],
            'value' => ['nullable'],
        ];
    }
}
