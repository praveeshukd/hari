<?php

namespace Modules\Analysis\Support;

final class SensitiveValueHasher
{
    public function hash(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = mb_strtolower(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        return hash_hmac('sha256', $normalized, (string) config('app.key'));
    }
}
