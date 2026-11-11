<?php

namespace Modules\Analysis\Repositories;

use Modules\Analysis\DTOs\AnalysisData;
use Modules\Analysis\DTOs\AnalysisFilters;
use App\Repositories\RepositoryInterface;
use Modules\Analysis\Models\Analysis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface AnalysisRepositoryInterface extends RepositoryInterface
{
    public function find(int $id): ?Analysis;

    public function findOrFail(int $id): Analysis;

    public function findWithTrashed(int $id): ?Analysis;

    public function create(array $attributes): Analysis;

    public function update(\Illuminate\Database\Eloquent\Model $model, array $attributes): Analysis;

    public function updateField(\Illuminate\Database\Eloquent\Model $model, string $field, mixed $value, array $extra = []): Analysis;


    public function createFromData(AnalysisData $data, array $extra = []): Analysis;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function updateFromData(Analysis $analysis, AnalysisData $data, array $extra = []): Analysis;

    public function query(AnalysisFilters $filters = new AnalysisFilters(), bool $withSearch = true, bool $onlyTrashed = false): Builder;

    /**
     * @param  list<int|string>  $ids
     * @return Collection<int, Analysis>
     */
    public function findManyByIds(array $ids, bool $withTrashed = false): Collection;

    /**
     * @return array<string, Collection<int, string>>
     */
    public function distinctFilterOptions(): array;

    public function countBetween(string $startDate, string $endDate): int;

    public function averagePurityBetween(string $startDate, string $endDate): float;

    public function countPassingBetween(string $startDate, string $endDate): int;

    public function countFailingBetween(string $startDate, string $endDate): int;

    public function countDistinctSampleNames(): int;

    public function countDistinctInstruments(): int;

    public function countDistinctAnalysts(): int;

    public function countAll(): int;

    public function latestAnalysisDate(): ?string;

    /**
     * @return array{total: int, passed: int, failed: int, pending: int}
     */
    public function sheetSummaryCounts(): array;

    /**
     * @return Collection<int, object{day: string, total: int|string, passed: int|string}>
     */
    public function dailyCounts(string $startDate, string $endDate): Collection;

    /**
     * @return Collection<int, Analysis>
     */
    public function latestPassing(int $limit = 5): Collection;

    /**
     * @return Collection<int, Analysis>
     */
    public function latestFailing(int $limit = 5): Collection;
}
