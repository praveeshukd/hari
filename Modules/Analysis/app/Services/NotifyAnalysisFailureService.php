<?php

namespace Modules\Analysis\Services;

use Modules\Analysis\Models\Analysis;
use App\Models\User;
use Modules\Analysis\Notifications\AnalysisFailedNotification;
use Illuminate\Support\Facades\Notification;

final class NotifyAnalysisFailureService
{
    public function maybeNotify(Analysis $analysis): void
    {
        $isFail = str_contains(strtolower((string) $analysis->result), 'fail');
        $belowThreshold = $analysis->purity !== null
            && (float) $analysis->purity < (float) config('analysis.fail_purity_threshold', 90);

        if (! $isFail && ! $belowThreshold) {
            return;
        }

        $admins = User::query()->where('role', User::ROLE_ADMIN)->get();
        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new AnalysisFailedNotification($analysis));
    }
}
