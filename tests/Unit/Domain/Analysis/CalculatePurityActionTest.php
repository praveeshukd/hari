<?php

namespace Tests\Unit\Domain\Analysis;

use App\Domain\Analysis\Actions\CalculatePurityAction;
use PHPUnit\Framework\TestCase;

class CalculatePurityActionTest extends TestCase
{
    public function test_it_calculates_purity_with_the_qc_formula(): void
    {
        $action = new CalculatePurityAction();

        $purity = $action->execute(
            sampleArea: 980.0,
            stdArea: 1000.0,
            stdCon: 1.0,
            sampleCon: 1.0,
        );

        $this->assertSame(98.0, $purity);
    }

    public function test_it_returns_null_when_inputs_are_incomplete(): void
    {
        $action = new CalculatePurityAction();

        $this->assertNull($action->execute(980.0, 1000.0, 1.0, null));
    }
}
