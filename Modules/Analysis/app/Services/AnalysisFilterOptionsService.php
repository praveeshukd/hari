<?php

namespace Modules\Analysis\Services;

use Modules\Analysis\Repositories\AnalysisRepositoryInterface;

final class AnalysisFilterOptionsService
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
    ) {}

    /**
     * @return array<string, \Illuminate\Support\Collection<int, string>>
     */
    public function get(): array
    {
        return $this->analyses->distinctFilterOptions();
    }
}
