<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    protected $fillable = [
        'date',
        'sample_name',
        'batch_no',
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
        'remarks',
    ];

    protected $casts = [
        'date'       => 'date',
        'pressure'   => 'float',
        'std_con'    => 'float',
        'std_area'   => 'float',
        'sample_con' => 'float',
        'sample_area'=> 'float',
        'purity'     => 'float',
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
    ];

    /**
     * Shared filters for the data sheet, Excel export, and PDF export.
     */
    public function scopeFiltered(Builder $query, array $filters = [], bool $withSearch = true): Builder
    {
        if ($withSearch && ($search = self::filterValue($filters, 'search'))) {
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('sample_name', 'like', "%{$search}%")
                    ->orWhere('batch_no', 'like', "%{$search}%")
                    ->orWhere('analysis', 'like', "%{$search}%")
                    ->orWhere('instrument', 'like', "%{$search}%")
                    ->orWhere('column_name', 'like', "%{$search}%")
                    ->orWhere('done_by', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        $exact = [
            'filter_sample' => 'sample_name',
            'filter_batch' => 'batch_no',
            'filter_analysis' => 'analysis',
            'filter_instrument' => 'instrument',
            'filter_column' => 'column_name',
            'filter_result' => 'result',
            'filter_done_by' => 'done_by',
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

    /**
     * Auto-calculate purity before saving.
     * Formula: (sample_area / std_area) * (std_con / sample_con) * 100
     */
    public static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->std_area && $model->std_con && $model->sample_area && $model->sample_con) {
                $model->purity = round(
                    ($model->sample_area / $model->std_area) * ($model->std_con / $model->sample_con) * 100,
                    4
                );
            }
        });
    }
}
