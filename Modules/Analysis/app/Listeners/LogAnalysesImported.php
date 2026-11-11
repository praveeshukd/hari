<?php

namespace Modules\Analysis\Listeners;

use Modules\Analysis\Events\AnalysesImported;
use Illuminate\Support\Facades\Log;

class LogAnalysesImported
{
    public function handle(AnalysesImported $event): void
    {
        Log::info('Analyses imported', [
            'imported' => $event->imported,
            'skipped' => $event->skipped,
            'errors' => count($event->errors),
        ]);
    }
}
