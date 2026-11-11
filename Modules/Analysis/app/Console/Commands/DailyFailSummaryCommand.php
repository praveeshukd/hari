<?php

namespace Modules\Analysis\Console\Commands;

use Modules\Analysis\Models\Analysis;
use App\Models\User;
use Modules\Analysis\Notifications\DailyFailSummaryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class DailyFailSummaryCommand extends Command
{
    protected $signature = 'analysis:daily-fail-summary {--date=}';

    protected $description = 'Email admins a summary of failed/low-purity analyses for a day';

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->subDay()->toDateString();
        $threshold = (float) config('analysis.fail_purity_threshold', 90);

        $rows = Analysis::query()
            ->whereDate('date', $date)
            ->where(function ($query) use ($threshold) {
                $query->whereRaw("LOWER(COALESCE(result, '')) LIKE '%fail%'")
                    ->orWhere(function ($inner) use ($threshold) {
                        $inner->whereNotNull('purity')->where('purity', '<', $threshold);
                    });
            })
            ->orderBy('id')
            ->get(['id', 'sample_name', 'batch_no', 'purity', 'result'])
            ->map(fn (Analysis $row) => [
                'id' => $row->id,
                'sample_name' => $row->sample_name,
                'batch_no' => $row->batch_no,
                'purity' => $row->purity,
                'result' => $row->result,
            ])
            ->all();

        if ($rows === []) {
            $this->info("No fail/low-purity rows for {$date}.");

            return self::SUCCESS;
        }

        $admins = User::query()->where('role', User::ROLE_ADMIN)->get();
        Notification::send($admins, new DailyFailSummaryNotification($date, $rows));
        $this->info('Sent daily fail summary for '.$date.' ('.count($rows).' rows) to '.$admins->count().' admin(s).');

        return self::SUCCESS;
    }
}
