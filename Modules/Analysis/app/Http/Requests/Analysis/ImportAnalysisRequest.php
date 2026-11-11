<?php

namespace Modules\Analysis\Http\Requests\Analysis;

use Modules\Analysis\Models\Analysis;
use Illuminate\Foundation\Http\FormRequest;

class ImportAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Analysis::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ];
    }
}
