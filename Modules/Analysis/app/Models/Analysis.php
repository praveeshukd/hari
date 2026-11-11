<?php

namespace Modules\Analysis\Models;

use App\Models\User;
use Modules\Analysis\Actions\CalculatePurityAction;
use Modules\Analysis\Casts\NullableEncryptedString;
use Modules\Analysis\Support\SensitiveValueHasher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Analysis extends Model
{
    /** @use HasFactory<\Modules\Analysis\Database\Factories\AnalysisFactory> */
    use HasFactory;
    use SoftDeletes;

    protected static function newFactory()
    {
        return \Modules\Analysis\Database\Factories\AnalysisFactory::new();
    }

    protected $fillable = [
        'date',
        'sample_name',
        'sample_name_hash',
        'batch_no',
        'batch_no_hash',
        'analysis',
        'pressure',
        'column_name',
        'instrument',
        'std_con',
        'std_area',
        'sample_con',
        'sample_area',
        'purity',
        'specification',
        'sample_submitted_by',
        'result',
        'done_by',
        'done_by_hash',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'pressure' => 'float',
        'std_con' => 'float',
        'std_area' => 'float',
        'sample_con' => 'float',
        'sample_area' => 'float',
        'purity' => 'float',
        'sample_name' => NullableEncryptedString::class,
        'batch_no' => NullableEncryptedString::class,
        'remarks' => NullableEncryptedString::class,
        'sample_submitted_by' => NullableEncryptedString::class,
        'done_by' => NullableEncryptedString::class,
    ];

    public const FILTER_KEYS = [
        'search',
        'filter_sample',
        'filter_batch',
        'filter_analysis',
        'filter_instrument',
        'filter_column',
        'filter_result',
        'filter_done_by',
        'date_from',
        'date_to',
        'date',
        'trash',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(AnalysisActivityLog::class);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return static::withTrashed()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }

    /**
     * Shared filters for the data sheet, Excel export, and PDF export.
     * Encrypted fields use hash columns for exact match; free-text search
     * only covers non-encrypted columns.
     */
    public function scopeFiltered(Builder $query, array $filters = [], bool $withSearch = true): Builder
    {
        $hasher = app(SensitiveValueHasher::class);

        if ($withSearch && ($search = self::filterValue($filters, 'search'))) {
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('analysis', 'like', "%{$search}%")
                    ->orWhere('instrument', 'like', "%{$search}%")
                    ->orWhere('column_name', 'like', "%{$search}%")
                    ->orWhere('result', 'like', "%{$search}%")
                    ->orWhere('specification', 'like', "%{$search}%");
            });
        }

        if ($sample = self::filterValue($filters, 'filter_sample')) {
            $query->where('sample_name_hash', $hasher->hash($sample));
        }
        if ($batch = self::filterValue($filters, 'filter_batch')) {
            $query->where('batch_no_hash', $hasher->hash($batch));
        }
        if ($doneBy = self::filterValue($filters, 'filter_done_by')) {
            $query->where('done_by_hash', $hasher->hash($doneBy));
        }

        $exact = [
            'filter_analysis' => 'analysis',
            'filter_instrument' => 'instrument',
            'filter_column' => 'column_name',
            'filter_result' => 'result',
        ];

        foreach ($exact as $key => $column) {
            if ($value = self::filterValue($filters, $key)) {
                $query->where($column, $value);
            }
        }

        if ($from = self::filterValue($filters, 'date_from')) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to = self::filterValue($filters, 'date_to')) {
            $query->whereDate('date', '<=', $to);
        }
        if ($date = self::filterValue($filters, 'date')) {
            $query->whereDate('date', $date);
        }

        return $query;
    }

    private static function filterValue(array $filters, string $key): ?string
    {
        $value = $filters[$key] ?? null;
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected static function booted(): void
    {
        static::saving(function (Analysis $model) {
            $hasher = app(SensitiveValueHasher::class);
            $model->sample_name_hash = $hasher->hash($model->sample_name);
            $model->batch_no_hash = $hasher->hash($model->batch_no);
            $model->done_by_hash = $hasher->hash($model->done_by);

            $model->purity = app(CalculatePurityAction::class)->execute(
                $model->sample_area !== null ? (float) $model->sample_area : null,
                $model->std_area !== null ? (float) $model->std_area : null,
                $model->std_con !== null ? (float) $model->std_con : null,
                $model->sample_con !== null ? (float) $model->sample_con : null,
            );
        });
    }
}
