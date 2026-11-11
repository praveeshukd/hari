<?php

namespace Modules\Analysis\Repositories;

use Modules\Analysis\DTOs\AnalysisData;
use Modules\Analysis\DTOs\AnalysisFilters;
use App\Repositories\EloquentRepository;
use Modules\Analysis\Models\Analysis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentAnalysisRepository extends EloquentRepository implements AnalysisRepositoryInterface
{
    protected function modelClass(): string
    {
        return Analysis::class;
    }

    public function find(int $id): ?Analysis
    {
        /** @var Analysis|null $model */
        $model = parent::find($id);

        return $model;
    }

    public function findOrFail(int $id): Analysis
    {
        /** @var Analysis $model */
        $model = parent::findOrFail($id);

        return $model;
    }

    public function findWithTrashed(int $id): ?Analysis
    {
        /** @var Analysis|null $model */
        $model = parent::findWithTrashed($id);

        return $model;
    }

    public function create(array $attributes): Analysis
    {
        /** @var Analysis $model */
        $model = parent::create($attributes);

        return $model;
    }

    public function update(Model $model, array $attributes): Analysis
    {
        /** @var Analysis $updated */
        $updated = parent::update($model, $attributes);

        return $updated;
    }

    public function updateField(Model $model, string $field, mixed $value, array $extra = []): Analysis
    {
        /** @var Analysis $updated */
        $updated = parent::updateField($model, $field, $value, $extra);

        return $updated;
    }

    public function createFromData(AnalysisData $data, array $extra = []): Analysis
    {
        return $this->create(array_merge($data->toArray(), $extra));
    }

    public function updateFromData(Analysis $analysis, AnalysisData $data, array $extra = []): Analysis
    {
        return $this->update($analysis, array_merge($data->toArray(), $extra));
    }

    public function query(AnalysisFilters $filters = new AnalysisFilters(), bool $withSearch = true, bool $onlyTrashed = false): Builder
    {
        return $this->newQuery(onlyTrashed: $onlyTrashed)
            ->filtered($filters->toArray(), $withSearch);
    }

    public function findManyByIds(array $ids, bool $withTrashed = false): Collection
    {
        $ids = array_values(array_filter($ids, static fn ($id) => $id !== null && $id !== ''));

        if ($ids === []) {
            return collect();
        }

        return $this->newQuery(withTrashed: $withTrashed)
            ->whereIn('id', $ids)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    public function distinctFilterOptions(): array
    {
        $decryptUnique = function (string $column): Collection {
            return $this->newQuery()
                ->whereNotNull($column)
                ->limit(500)
                ->get([$column])
                ->pluck($column)
                ->filter(fn ($value) => filled($value))
                ->unique()
                ->sort()
                ->values();
        };

        return [
            'sample_names' => $decryptUnique('sample_name'),
            'batch_nos' => $decryptUnique('batch_no'),
            'analyses' => $this->newQuery()->select('analysis')->whereNotNull('analysis')->where('analysis', '!=', '')->distinct()->orderBy('analysis')->pluck('analysis'),
            'instruments' => $this->newQuery()->select('instrument')->whereNotNull('instrument')->where('instrument', '!=', '')->distinct()->orderBy('instrument')->pluck('instrument'),
            'columns' => $this->newQuery()->select('column_name')->whereNotNull('column_name')->where('column_name', '!=', '')->distinct()->orderBy('column_name')->pluck('column_name'),
            'done_bys' => $decryptUnique('done_by'),
            'results' => $this->newQuery()->select('result')->whereNotNull('result')->where('result', '!=', '')->distinct()->orderBy('result')->pluck('result'),
        ];
    }

    public function countBetween(string $startDate, string $endDate): int
    {
        return (int) $this->newQuery()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->count();
    }

    public function averagePurityBetween(string $startDate, string $endDate): float
    {
        return round((float) ($this->newQuery()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->whereNotNull('purity')
            ->avg('purity') ?? 0), 2);
    }

    public function countPassingBetween(string $startDate, string $endDate): int
    {
        return (int) $this->passingQuery()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->count();
    }

    public function countFailingBetween(string $startDate, string $endDate): int
    {
        return (int) $this->failingQuery()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->count();
    }

    public function countDistinctSampleNames(): int
    {
        return (int) $this->newQuery()
            ->whereNotNull('sample_name_hash')
            ->distinct()
            ->count('sample_name_hash');
    }

    public function countDistinctInstruments(): int
    {
        return (int) $this->newQuery()
            ->whereNotNull('instrument')
            ->where('instrument', '!=', '')
            ->distinct()
            ->count('instrument');
    }

    public function countDistinctAnalysts(): int
    {
        return (int) $this->newQuery()
            ->whereNotNull('done_by_hash')
            ->distinct()
            ->count('done_by_hash');
    }

    public function countAll(): int
    {
        return (int) $this->newQuery()->count();
    }

    public function latestAnalysisDate(): ?string
    {
        $value = $this->newQuery()->max('date');

        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->toDateString();
    }

    public function sheetSummaryCounts(): array
    {
        $total = $this->countAll();
        $passed = (int) $this->passingQuery()->count();
        $failed = (int) $this->failingQuery()->count();

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'pending' => max(0, $total - $passed - $failed),
        ];
    }

    public function dailyCounts(string $startDate, string $endDate): Collection
    {
        $driver = DB::connection()->getDriverName();
        $dayExpression = match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d', date)",
            'pgsql' => 'DATE(date)::text',
            default => 'DATE(date)',
        };

        return $this->newQuery()
            ->selectRaw("{$dayExpression} as day")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(result, '')) LIKE '%fail%' THEN 0 WHEN LOWER(COALESCE(result, '')) LIKE '%pass%' OR purity >= 98 THEN 1 ELSE 0 END) as passed")
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->groupBy(DB::raw($dayExpression))
            ->orderBy('day')
            ->get();
    }

    public function latestPassing(int $limit = 5): Collection
    {
        return $this->passingQuery()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'date', 'sample_name', 'batch_no', 'purity', 'result', 'done_by', 'instrument']);
    }

    public function latestFailing(int $limit = 5): Collection
    {
        return $this->failingQuery()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'date', 'sample_name', 'batch_no', 'purity', 'result', 'done_by', 'instrument']);
    }

    private function passingQuery(): Builder
    {
        return $this->newQuery()->where(function (Builder $query) {
            $query->whereRaw("LOWER(COALESCE(result, '')) LIKE '%pass%'")
                ->orWhere(function (Builder $inner) {
                    $inner->whereRaw("LOWER(COALESCE(result, '')) NOT LIKE '%fail%'")
                        ->where('purity', '>=', 98);
                });
        })->whereRaw("LOWER(COALESCE(result, '')) NOT LIKE '%fail%'");
    }

    private function failingQuery(): Builder
    {
        return $this->newQuery()->whereRaw("LOWER(COALESCE(result, '')) LIKE '%fail%'");
    }
}
