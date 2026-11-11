<?php

namespace Modules\Analysis\Actions;

final class CalculatePurityAction
{
    /**
     * Formula: (sample_area / std_area) * (std_con / sample_con) * 100
     */
    public function execute(
        ?float $sampleArea,
        ?float $stdArea,
        ?float $stdCon,
        ?float $sampleCon,
    ): ?float {
        if (! $sampleArea || ! $stdArea || ! $stdCon || ! $sampleCon) {
            return null;
        }

        if ($stdArea == 0.0 || $sampleCon == 0.0) {
            return null;
        }

        return round(($sampleArea / $stdArea) * ($stdCon / $sampleCon) * 100, 4);
    }
}
