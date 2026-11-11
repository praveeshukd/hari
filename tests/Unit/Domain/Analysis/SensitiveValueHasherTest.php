<?php

namespace Tests\Unit\Domain\Analysis;

use App\Domain\Analysis\Support\SensitiveValueHasher;
use Tests\TestCase;

class SensitiveValueHasherTest extends TestCase
{
    public function test_hash_is_case_insensitive_and_trimmed(): void
    {
        $hasher = new SensitiveValueHasher;

        $this->assertSame(
            $hasher->hash('  Alpha '),
            $hasher->hash('alpha')
        );
        $this->assertNull($hasher->hash(''));
        $this->assertNull($hasher->hash(null));
    }
}
