<?php

namespace Modules\Analysis\Actions;

use Modules\Analysis\DTOs\AnalysisData;
use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Modules\Analysis\Services\NotifyAnalysisFailureService;
use Modules\Analysis\Models\Analysis;
use Illuminate\Support\Facades\DB;

final class CreateAnalysisAction
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly RecordAnalysisActivityAction $activity,
        private readonly NotifyAnalysisFailureService $failureNotifier,
    ) {}

    public function execute(AnalysisData $data): Analysis
    {
        return DB::transaction(function () use ($data) {
            $userId = auth()->id();
            $analysis = $this->analyses->createFromData($data, [
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->activity->execute($analysis, 'created', auth()->user(), $data->toArray());
            $this->failureNotifier->maybeNotify($analysis);

            return $analysis;
        });
    }
}
