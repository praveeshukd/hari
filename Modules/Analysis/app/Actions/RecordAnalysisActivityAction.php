<?php

namespace Modules\Analysis\Actions;

use Modules\Analysis\Models\Analysis;
use Modules\Analysis\Models\AnalysisActivityLog;
use App\Models\User;

final class RecordAnalysisActivityAction
{
    public function execute(?Analysis $analysis, string $action, ?User $user = null, array $changes = []): AnalysisActivityLog
    {
        return AnalysisActivityLog::query()->create([
            'analysis_id' => $analysis?->id,
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'changes' => $changes === [] ? null : $changes,
        ]);
    }
}
