<?php

namespace Modules\Analysis\Actions;

use Modules\Analysis\DTOs\AnalysisData;
use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Modules\Analysis\Services\NotifyAnalysisFailureService;
use Modules\Analysis\Models\Analysis;
use Illuminate\Support\Facades\DB;

final class UpdateAnalysisAction
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly RecordAnalysisActivityAction $activity,
        private readonly NotifyAnalysisFailureService $failureNotifier,
    ) {}

    public function execute(Analysis $analysis, AnalysisData $data): Analysis
    {
        return DB::transaction(function () use ($analysis, $data) {
            $before = $analysis->only(array_keys($data->toArray()));
            $analysis = $this->analyses->updateFromData($analysis, $data, [
                'updated_by' => auth()->id(),
            ]);

            $this->activity->execute($analysis, 'updated', auth()->user(), [
                'before' => $before,
                'after' => $data->toArray(),
            ]);
            $this->failureNotifier->maybeNotify($analysis);

            return $analysis;
        });
    }
}
