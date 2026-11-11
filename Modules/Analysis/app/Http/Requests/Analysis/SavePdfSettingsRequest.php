<?php

namespace Modules\Analysis\Http\Requests\Analysis;

use Modules\Analysis\Models\Analysis;
use Illuminate\Foundation\Http\FormRequest;

class SavePdfSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageSettings', Analysis::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'document_name' => ['required', 'string', 'max:255'],
            'request_prefix' => ['required', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:4096'],
        ];
    }
}
