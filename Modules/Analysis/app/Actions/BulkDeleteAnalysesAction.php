<?php

namespace Modules\Analysis\Actions;

use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class BulkDeleteAnalysesAction
{
    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
        private readonly RecordAnalysisActivityAction $activity,
    ) {}

    /**
     * @param  list<int|string>  $ids
     */
    public function execute(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $records = $this->analyses->findManyByIds($ids);
            foreach ($records as $record) {
                $this->activity->execute($record, 'bulk_deleted', auth()->user(), ['id' => $record->id]);
                $this->analyses->delete($record);
            }

            return $records->count();
        });
    }
}
