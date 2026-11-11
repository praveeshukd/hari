<?php

namespace Modules\Analysis\Http\Requests\Analysis;

use Modules\Analysis\Models\Analysis;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('bulkDelete', Analysis::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ];
    }
}
