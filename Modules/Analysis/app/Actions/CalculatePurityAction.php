<?php

namespace Modules\Analysis\Actions;

final class CalculatePurityAction
{
    /**
     * Formula: (sample_area / std_area) * (std_con / sample_con) * standard_purity
     * When standard_purity is null/empty, defaults to 100.
     */
    public function execute(
        ?float $sampleArea,
        ?float $stdArea,
        ?float $stdCon,
        ?float $sampleCon,
        ?float $standardPurity = null,
    ): ?float {
        if (! $sampleArea || ! $stdArea || ! $stdCon || ! $sampleCon) {
            return null;
        }

        if ($stdArea == 0.0 || $sampleCon == 0.0) {
            return null;
        }

        $factor = ($standardPurity !== null && $standardPurity > 0.0)
            ? $standardPurity
            : 100.0;

        return round(($sampleArea / $stdArea) * ($stdCon / $sampleCon) * $factor, 4);
    }
}
