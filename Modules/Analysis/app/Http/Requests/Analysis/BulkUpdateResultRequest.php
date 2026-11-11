<?php

namespace Modules\Analysis\Http\Requests\Analysis;

use Modules\Analysis\Models\Analysis;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('bulkUpdateResult', Analysis::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
            'result' => ['required', 'string', Rule::in(['PASS', 'FAIL'])],
        ];
    }
}
