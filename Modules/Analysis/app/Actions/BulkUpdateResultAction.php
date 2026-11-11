<?php

namespace Modules\Analysis\Actions;

use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Modules\Analysis\Services\NotifyAnalysisFailureService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class BulkUpdateResultAction
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly RecordAnalysisActivityAction $activity,
        private readonly NotifyAnalysisFailureService $failureNotifier,
    ) {}

    /**
     * @param  list<int|string>  $ids
     */
    public function execute(array $ids, string $result): int
    {
        $normalizedResult = strtoupper(trim($result));
        if (! in_array($normalizedResult, ['PASS', 'FAIL'], true)) {
            throw new InvalidArgumentException('Result must be PASS or FAIL.');
        }

        return DB::transaction(function () use ($ids, $normalizedResult) {
            $records = $this->analyses->findManyByIds($ids);
            foreach ($records as $record) {
                $before = $record->result;
                $updated = $this->analyses->updateField($record, 'result', $normalizedResult, [
                    'updated_by' => auth()->id(),
                ]);
                $this->activity->execute($updated, 'bulk_result', auth()->user(), [
                    'before' => $before,
                    'after' => $normalizedResult,
                ]);
                $this->failureNotifier->maybeNotify($updated);
            }

            return $records->count();
        });
    }
}
