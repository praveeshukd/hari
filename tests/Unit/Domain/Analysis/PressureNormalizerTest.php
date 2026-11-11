<?php

namespace Tests\Unit\Domain\Analysis;

use App\Domain\Analysis\Support\PressureNormalizer;
use PHPUnit\Framework\TestCase;

class PressureNormalizerTest extends TestCase
{
    public function test_it_extracts_the_first_number_from_free_text(): void
    {
        $normalizer = new PressureNormalizer();

        $this->assertSame(145.0, $normalizer->normalize('145 bar'));
        $this->assertSame(12.5, $normalizer->normalize('12.5 psi'));
        $this->assertNull($normalizer->normalize(''));
    }
}
