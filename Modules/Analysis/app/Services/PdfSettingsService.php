<?php

namespace Modules\Analysis\Services;

use Modules\Analysis\DTOs\PdfSettingsData;
use Illuminate\Http\UploadedFile;

final class PdfSettingsService
{
    public function get(): PdfSettingsData
    {
        $path = $this->path();
        if (! file_exists($path)) {
            return PdfSettingsData::defaults();
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)) {
            return PdfSettingsData::defaults();
        }

        return PdfSettingsData::fromArray($data);
    }

    public function save(array $validated, ?UploadedFile $logo = null): PdfSettingsData
    {
        $current = $this->get();

        $settings = new PdfSettingsData(
            companyName: (string) $validated['company_name'],
            companyTagline: (string) ($validated['company_tagline'] ?? 'Next Gen Ingredients'),
            documentName: (string) $validated['document_name'],
            requestPrefix: (string) $validated['request_prefix'],
            logoUrl: $current->logoUrl,
        );

        if ($logo) {
            $settings = new PdfSettingsData(
                companyName: $settings->companyName,
                companyTagline: $settings->companyTagline,
                documentName: $settings->documentName,
                requestPrefix: $settings->requestPrefix,
                logoUrl: 'data:'.$logo->getMimeType().';base64,'.base64_encode((string) file_get_contents($logo->getRealPath())),
            );
        }

        file_put_contents($this->path(), json_encode($settings->toArray(), JSON_PRETTY_PRINT));

        return $settings;
    }

    private function path(): string
    {
        return storage_path('app/pdf_settings.json');
    }
}
