<?php

namespace Modules\Analysis\Actions;

use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Modules\Analysis\Models\Analysis;
use Illuminate\Support\Facades\DB;

final class RestoreAnalysisAction
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly RecordAnalysisActivityAction $activity,
    ) {}

    public function execute(Analysis $analysis): Analysis
    {
        return DB::transaction(function () use ($analysis) {
            $this->analyses->restore($analysis);
            $analysis->refresh();
            $this->activity->execute($analysis, 'restored', auth()->user());

            return $analysis;
        });
    }
}
