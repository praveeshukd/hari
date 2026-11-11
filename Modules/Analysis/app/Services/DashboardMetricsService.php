<?php

namespace Modules\Analysis\Services;

use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class DashboardMetricsService
{
    private const MAX_RANGE_DAYS = 90;

    public function __construct(
        private readonly AnalysisRepositoryInterface $analyses,
    ) {}

    public function build(?string $from = null, ?string $to = null): array
    {
        [$rangeStart, $rangeEnd] = $this->resolveRange($from, $to);

        $dayCount = $rangeStart->diffInDays($rangeEnd) + 1;
        $prevEnd = $rangeStart->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($dayCount - 1)->startOfDay();

        $start = $rangeStart->toDateString();
        $end = $rangeEnd->toDateString();
        $prevStartDate = $prevStart->toDateString();
        $prevEndDate = $prevEnd->toDateString();
        $today = now()->toDateString();

        $weekSamples = $this->analyses->countBetween($start, $end);
        $prevSamples = $this->analyses->countBetween($prevStartDate, $prevEndDate);
        $weekPassed = $this->analyses->countPassingBetween($start, $end);
        $weekFailed = $this->analyses->countFailingBetween($start, $end);
        $todayFailed = $this->analyses->countFailingBetween($today, $today);
        $weekAvg = $this->analyses->averagePurityBetween($start, $end);
        $prevAvg = $this->analyses->averagePurityBetween($prevStartDate, $prevEndDate);

        $passRate = $weekSamples > 0 ? round(($weekPassed / $weekSamples) * 100, 1) : 0.0;
        $failRate = $weekSamples > 0 ? round(($weekFailed / $weekSamples) * 100, 1) : 0.0;

        $rangeLabel = $this->formatRangeLabel($rangeStart, $rangeEnd);

        $cards = [
            [
                'label' => 'Total Samples',
                'value' => number_format($weekSamples),
                'meta' => $this->trend($weekSamples, $prevSamples, 0, $rangeLabel),
                'color' => 'teal',
                'icon' => 'clipboard',
            ],
            [
                'label' => 'Passed',
                'value' => number_format($weekPassed),
                'meta' => ['text' => 'In range', 'label' => $rangeLabel, 'dir' => 'flat'],
                'color' => 'green',
                'icon' => 'check',
            ],
            [
                'label' => 'Failed Today',
                'value' => number_format($todayFailed),
                'meta' => [
                    'text' => 'Today',
                    'label' => 'Today',
                    'dir' => $todayFailed > 0 ? 'down' : 'flat',
                ],
                'color' => 'red',
                'icon' => 'alert',
            ],
            [
                'label' => 'Avg. Purity',
                'value' => number_format($weekAvg, 2).'%',
                'meta' => $this->trend($weekAvg, $prevAvg, 1, $rangeLabel),
                'color' => 'blue',
                'icon' => 'beaker',
            ],
        ];

        return [
            'cards' => $cards,
            'performance' => [
                'pass_rate' => $passRate,
                'fail_rate' => $failRate,
                'avg_purity' => $weekAvg,
                'samples' => $weekSamples,
            ],
            'chart' => $this->rangeChart($rangeStart, $rangeEnd),
            'rangeLabel' => $rangeLabel,
            'from' => $start,
            'to' => $end,
            'passed' => $this->analyses->latestPassing(5),
            'failed' => $this->analyses->latestFailing(5),
            'failedToday' => $todayFailed,
        ];
    }

    private function formatRangeLabel(Carbon $start, Carbon $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->format('M j, Y');
        }

        if ($start->year === $end->year) {
            return $start->format('M j').' - '.$end->format('M j, Y');
        }

        return $start->format('M j, Y').' - '.$end->format('M j, Y');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(?string $from, ?string $to): array
    {
        $explicit = $from !== null || $to !== null;

        $end = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
        $start = $from
            ? Carbon::parse($from)->startOfDay()
            : $end->copy()->subDays(6)->startOfDay();

        if ($start->gt($end)) {
            throw new InvalidArgumentException('Dashboard from date must be on or before to date.');
        }

        if ($start->diffInDays($end) + 1 > self::MAX_RANGE_DAYS) {
            $start = $end->copy()->subDays(self::MAX_RANGE_DAYS - 1)->startOfDay();
        }

        // Default window with no samples: snap to the latest analysis activity
        // so QC Trends / KPIs are not an empty week when older records exist.
        if (! $explicit && $this->analyses->countBetween($start->toDateString(), $end->toDateString()) === 0) {
            $latest = $this->analyses->latestAnalysisDate();
            if ($latest !== null) {
                $end = Carbon::parse($latest)->endOfDay();
                $start = $end->copy()->subDays(self::MAX_RANGE_DAYS - 1)->startOfDay();
            }
        }

        return [$start, $end];
    }

    /**
     * @return array{labels: list<string>, samples: list<int>, passed: list<int>}
     */
    private function rangeChart(Carbon $start, Carbon $end): array
    {
        $rows = $this->analyses->dailyCounts($start->toDateString(), $end->toDateString())
            ->keyBy(fn ($row) => (string) $row->day);

        $labels = [];
        $samples = [];
        $passed = [];
        $span = $start->diffInDays($end);

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->toDateString();
            $labels[] = $span > 14 ? $day->format('m/d') : $day->format('D');
            $row = $rows->get($key);
            $samples[] = $row ? (int) $row->total : 0;
            $passed[] = $row ? (int) $row->passed : 0;
        }

        return [
            'labels' => $labels,
            'samples' => $samples,
            'passed' => $passed,
        ];
    }

    /**
     * @return array{text: string, dir: string, pct?: string, label: string}
     */
    private function trend(float|int $current, float|int $previous, int $decimals = 0, string $label = 'vs prior period'): array
    {
        $diff = round($current - $previous, $decimals);
        $pct = $previous == 0
            ? ($current == 0 ? 0.0 : 100.0)
            : round((($current - $previous) / abs((float) $previous)) * 100, 1);

        if ($diff > 0) {
            return [
                'text' => number_format($diff, $decimals).' Increase',
                'dir' => 'up',
                'pct' => number_format(abs($pct), 1),
                'label' => $label,
            ];
        }
        if ($diff < 0) {
            return [
                'text' => number_format(abs($diff), $decimals).' Decrease',
                'dir' => 'down',
                'pct' => number_format(abs($pct), 1),
                'label' => $label,
            ];
        }

        return [
            'text' => 'No change',
            'dir' => 'flat',
            'pct' => '0.0',
            'label' => $label,
        ];
    }
}
