<?php

namespace Tests\Unit;

use Modules\Analysis\Actions\CalculatePurityAction;
use PHPUnit\Framework\TestCase;

class CalculatePurityActionTest extends TestCase
{
    private CalculatePurityAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CalculatePurityAction();
    }

    public function test_uses_100_when_standard_purity_is_null(): void
    {
        $purity = $this->action->execute(1000.0, 1000.0, 1.0, 1.0, null);

        $this->assertSame(100.0, $purity);
    }

    public function test_uses_typed_standard_purity_of_99(): void
    {
        $purity = $this->action->execute(1000.0, 1000.0, 1.0, 1.0, 99.0);

        $this->assertSame(99.0, $purity);
    }

    public function test_uses_typed_standard_purity_of_98(): void
    {
        $purity = $this->action->execute(1000.0, 1000.0, 1.0, 1.0, 98.0);

        $this->assertSame(98.0, $purity);
    }

    public function test_defaults_to_100_when_standard_purity_is_zero(): void
    {
        $purity = $this->action->execute(1000.0, 1000.0, 1.0, 1.0, 0.0);

        $this->assertSame(100.0, $purity);
    }

    public function test_returns_null_when_inputs_incomplete(): void
    {
        $this->assertNull($this->action->execute(null, 1000.0, 1.0, 1.0, 99.0));
        $this->assertNull($this->action->execute(1000.0, 0.0, 1.0, 1.0, 99.0));
    }
}
