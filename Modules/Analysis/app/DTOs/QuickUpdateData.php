<?php

namespace Modules\Analysis\DTOs;

final readonly class QuickUpdateData
{
    public const ALLOWED_FIELDS = [
        'date',
        'sample_name',
        'batch_no',
        'request_form_no',
        'analysis',
        'pressure',
        'column_name',
        'instrument',
        'std_con',
        'std_area',
        'sample_con',
        'sample_area',
        'standard_purity',
        'result',
        'done_by',
        'remarks',
        'specification',
        'sample_submitted_by',
    ];

    public function __construct(
        public string $field,
        public mixed $value,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            field: (string) $data['field'],
            value: $data['value'] ?? null,
        );
    }
}
