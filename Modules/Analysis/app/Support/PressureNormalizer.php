<?php

namespace Modules\Analysis\Support;

final class PressureNormalizer
{
    /**
     * Free-text pressure ("145 bar") → first numeric value, or null.
     */
    public function normalize(mixed $pressure): ?float
    {
        if ($pressure === null) {
            return null;
        }

        if (is_numeric($pressure)) {
            return (float) $pressure;
        }

        if (! is_string($pressure)) {
            return null;
        }

        $pressure = trim($pressure);
        if ($pressure === '') {
            return null;
        }

        if (is_numeric($pressure)) {
            return (float) $pressure;
        }

        if (preg_match('/-?\d+(?:\.\d+)?/', str_replace(',', '', $pressure), $match)) {
            return (float) $match[0];
        }

        return null;
    }
}
