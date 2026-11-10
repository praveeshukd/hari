<?php

namespace App\Http\Controllers;

use App\DataTables\AnalysisDataTable;
use App\Models\Analysis;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Show the QC overview in the admin dashboard layout.
     */
    public function index(Request $request, AnalysisDataTable $dataTable)
    {
        if ($request->ajax() && $request->wantsJson()) {
            return $dataTable->ajax();
        }

        $user = auth()->user();

        $rangeEnd = now()->endOfDay();
        $rangeStart = now()->subDays(6)->startOfDay();
        $prevEnd = $rangeStart->copy()->subDay()->endOfDay();
        $prevStart = $rangeStart->copy()->subDays(7)->startOfDay();

        $records = Analysis::query()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'date', 'sample_name', 'batch_no', 'purity', 'result', 'done_by', 'instrument']);

        $isFail = function ($row): bool {
            return str_contains(strtolower((string) $row->result), 'fail');
        };

        $isPass = function ($row) use ($isFail): bool {
            if ($isFail($row)) {
                return false;
            }
            if (str_contains(strtolower((string) $row->result), 'pass')) {
                return true;
            }

            return $row->purity !== null && (float) $row->purity >= 98;
        };

        $between = function (Collection $rows, Carbon $start, Carbon $end): Collection {
            return $rows->filter(function ($row) use ($start, $end) {
                return $row->date && $row->date->between($start, $end);
            })->values();
        };

        $week = $between($records, $rangeStart, $rangeEnd);
        $prev = $between($records, $prevStart, $prevEnd);
        $today = $records->filter(fn ($row) => $row->date && $row->date->isToday())->values();

        $trend = function (float|int $current, float|int $previous, int $decimals = 0): array {
            $diff = round($current - $previous, $decimals);
            if ($diff > 0) {
                return ['text' => $this->formatNumber($diff, $decimals).' Increase', 'dir' => 'up'];
            }
            if ($diff < 0) {
                return ['text' => $this->formatNumber(abs($diff), $decimals).' Decrease', 'dir' => 'down'];
            }

            return ['text' => 'No change', 'dir' => 'muted'];
        };

        $distinct = fn (Collection $rows, string $field) => $rows
            ->pluck($field)
            ->filter(fn ($value) => filled($value))
            ->unique()
            ->count();

        $weekAvg = round((float) ($week->avg('purity') ?? 0), 2);
        $prevAvg = round((float) ($prev->avg('purity') ?? 0), 2);

        $cards = [
            [
                'label' => 'Samples',
                'value' => number_format($week->count()),
                'meta' => $trend($week->count(), $prev->count()),
                'color' => 'red',
                'icon' => 'flask',
            ],
            [
                'label' => 'Passed',
                'value' => number_format($week->filter($isPass)->count()),
                'meta' => ['text' => 'This week', 'dir' => 'up'],
                'color' => 'green',
                'icon' => 'check',
            ],
            [
                'label' => 'Failed',
                'value' => number_format($today->filter($isFail)->count()),
                'meta' => ['text' => 'Today', 'dir' => $today->filter($isFail)->count() > 0 ? 'down' : 'up'],
                'color' => 'red',
                'icon' => 'alert',
            ],
            [
                'label' => 'Average Purity',
                'value' => number_format($weekAvg, 2).'%',
                'meta' => $trend($weekAvg, $prevAvg, 1),
                'color' => 'purple',
                'icon' => 'beaker',
            ],
            [
                'label' => 'Tests',
                'value' => number_format($records->count()),
                'meta' => $trend($week->count(), $prev->count()),
                'color' => 'orange',
                'icon' => 'clipboard',
            ],
            [
                'label' => 'Products',
                'value' => number_format($distinct($records, 'sample_name')),
                'meta' => $trend($distinct($week, 'sample_name'), $distinct($prev, 'sample_name')),
                'color' => 'green',
                'icon' => 'users',
            ],
            [
                'label' => 'Instruments',
                'value' => number_format($distinct($records, 'instrument')),
                'meta' => ['text' => 'Active instruments', 'dir' => 'up'],
                'color' => 'blue',
                'icon' => 'box',
            ],
            [
                'label' => 'Analysts',
                'value' => number_format($distinct($records, 'done_by')),
                'meta' => ['text' => 'Registered analysts', 'dir' => 'up'],
                'color' => 'teal',
                'icon' => 'store',
            ],
        ];

        $year = now()->year;
        $chart = ['labels' => [], 'pass' => [], 'fail' => []];
        for ($month = 1; $month <= 12; $month++) {
            $chart['labels'][] = Carbon::create($year, $month, 1)->format('M');
            $monthRows = $records->filter(fn ($row) => $row->date && (int) $row->date->year === $year && (int) $row->date->month === $month);
            $chart['pass'][] = $monthRows->filter($isPass)->count();
            $chart['fail'][] = $monthRows->filter($isFail)->count();
        }

        $latest = function (Collection $rows) {
            return $rows
                ->sortByDesc(fn ($row) => sprintf('%s-%08d', $row->date?->format('Y-m-d') ?? '0000-00-00', $row->id))
                ->take(5)
                ->values();
        };

        return $dataTable->render('dashboard', [
            'user' => $user,
            'cards' => $cards,
            'chart' => $chart,
            'year' => $year,
            'rangeLabel' => $rangeStart->format('d-m-Y').' to '.$rangeEnd->format('d-m-Y'),
            'passed' => $latest($records->filter($isPass)),
            'failed' => $latest($records->filter($isFail)),
            'failedToday' => $today->filter($isFail)->count(),
        ]);
    }

    private function formatNumber(float|int $value, int $decimals): string
    {
        return number_format($value, $decimals);
    }
}
