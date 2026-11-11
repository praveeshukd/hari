<?php

namespace Modules\Analysis\DTOs;

final readonly class PdfSettingsData
{
    public function __construct(
        public string $companyName,
        public string $companyTagline,
        public string $documentName,
        public string $requestPrefix,
        public ?string $logoUrl = null,
    ) {}

    public static function defaults(): self
    {
        return new self(
            companyName: 'MOLECULES BIOLABS PRIVATE LIMITED',
            companyTagline: 'Next Gen Ingredients',
            documentName: 'SAMPLE TEST REPORT',
            requestPrefix: 'STR',
            logoUrl: null,
        );
    }

    public static function fromArray(array $data): self
    {
        $defaults = self::defaults();

        return new self(
            companyName: (string) ($data['company_name'] ?? $defaults->companyName),
            companyTagline: (string) ($data['company_tagline'] ?? $defaults->companyTagline),
            documentName: (string) ($data['document_name'] ?? $defaults->documentName),
            requestPrefix: (string) ($data['request_prefix'] ?? $defaults->requestPrefix),
            logoUrl: $data['logo_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'company_name' => $this->companyName,
            'company_tagline' => $this->companyTagline,
            'document_name' => $this->documentName,
            'request_prefix' => $this->requestPrefix,
            'logo_url' => $this->logoUrl,
        ];
    }
}
