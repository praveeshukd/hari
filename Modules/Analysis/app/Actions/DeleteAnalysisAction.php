<?php

namespace Modules\Analysis\Actions;

use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Modules\Analysis\Models\Analysis;
use Illuminate\Support\Facades\DB;

final class DeleteAnalysisAction
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly RecordAnalysisActivityAction $activity,
    ) {}

    public function execute(Analysis $analysis): void
    {
        DB::transaction(function () use ($analysis) {
            $this->activity->execute($analysis, 'deleted', auth()->user(), [
                'id' => $analysis->id,
                'sample_name' => $analysis->sample_name,
            ]);
            $this->analyses->delete($analysis);
        });
    }
}
