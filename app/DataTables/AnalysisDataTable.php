<?php

namespace App\DataTables;

use App\Models\Analysis;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AnalysisDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $table = new EloquentDataTable($query);

        if (! $this->isSheet()) {
            $table->addIndexColumn();
        }

        return $table
            ->addColumn('checkbox', function (Analysis $row) {
                return '<input type="checkbox" class="row-checkbox" value="'.$row->id.'" aria-label="Select record '.$row->id.'">';
            })
            ->editColumn('date', fn (Analysis $row) => $this->editable($row, 'date', $row->date?->format('Y-m-d')))
            ->editColumn('sample_name', fn (Analysis $row) => $this->editable($row, 'sample_name', $row->sample_name, 'cell-strong'))
            ->editColumn('batch_no', fn (Analysis $row) => $this->editable($row, 'batch_no', $row->batch_no))
            ->editColumn('analysis', fn (Analysis $row) => $this->editable($row, 'analysis', $row->analysis))
            ->editColumn('purity', fn (Analysis $row) => $this->purityMarkup($row))
            ->editColumn('result', function (Analysis $row) {
                if ($this->isSheet()) {
                    return $this->sheetResultMarkup($row);
                }

                return $this->resultBadge($row);
            })
            ->addColumn('action', function (Analysis $row) {
                $url = route('analysis.destroy', $row->id);
                $token = csrf_token();

                return '<div class="dt-actions">'
                    .'<button type="button" class="dt-icon dt-icon-view" onclick="openViewModal('.$row->id.')" title="View">View</button>'
                    .'<button type="button" class="dt-icon dt-icon-edit" onclick="openEditModal('.$row->id.')" title="Edit">Edit</button>'
                    .'<form action="'.$url.'" method="POST" onsubmit="return confirm(\'Delete this record?\')">'
                    .'<input type="hidden" name="_token" value="'.$token.'">'
                    .'<input type="hidden" name="_method" value="DELETE">'
                    .'<button type="submit" class="dt-icon dt-icon-delete" title="Delete">Delete</button>'
                    .'</form></div>';
            })
            ->orderColumn('checkbox', 'analyses.id $1')
            ->orderColumn('action', 'analyses.id $1')
            ->orderColumn('DT_RowIndex', 'analyses.id $1')
            ->rawColumns([
                'checkbox', 'date', 'sample_name', 'batch_no', 'analysis',
                'purity', 'result', 'action',
            ])
            ->setRowId(fn (Analysis $row) => 'row-'.$row->id);
    }

    public function query(Analysis $model): QueryBuilder
    {
        return $model->newQuery()->filtered(
            request()->only(Analysis::FILTER_KEYS),
            false
        );
    }

    public function html(): HtmlBuilder
    {
        $sheet = $this->isSheet();

        $builder = $this->builder()
            ->setTableId('analyses-table')
            ->columns($this->getColumns())
            ->minifiedAjax(
                $sheet ? route('analysis.index', [], false) : route('dashboard', [], false),
                $sheet ? $this->filterScript() : null
            )
            ->orderBy(1, 'desc')
            ->parameters([
                'scrollX' => $sheet,
                'autoWidth' => false,
                'pageLength' => $sheet ? 25 : 10,
                'searchDelay' => 350,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
                'dom' => '<"dt-toolbar"lf>rt<"dt-footer"ip>',
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => 'Search records...',
                    'lengthMenu' => 'Show _MENU_',
                    'info' => 'Showing _START_–_END_ of _TOTAL_',
                    'infoEmpty' => 'No records',
                    'infoFiltered' => '(filtered from _MAX_)',
                    'processing' => 'Loading records...',
                    'emptyTable' => 'No HPLC records yet.',
                    'zeroRecords' => 'No records match the current filters.',
                    'paginate' => [
                        'previous' => 'Prev',
                        'next' => 'Next',
                    ],
                ],
                'search' => ['search' => (string) request('search', '')],
                'drawCallback' => 'function () { if (window.onAnalysisTableDraw) window.onAnalysisTableDraw(); }',
            ]);

        return $builder;
    }

    public function getColumns(): array
    {
        if (! $this->isSheet()) {
            return [
                Column::make('DT_RowIndex')->title('#')->orderable(false)->searchable(false)->width(48),
                Column::make('date')->title('Date'),
                Column::make('sample_name')->title('Sample'),
                Column::make('batch_no')->title('Batch'),
                Column::make('purity')->title('Purity'),
                Column::make('result')->title('Result')->searchable(false),
                Column::computed('action')->title('')->exportable(false)->printable(false)->width(210)->addClass('dt-actions-col'),
            ];
        }

        return [
            Column::computed('checkbox')
                ->title('<input type="checkbox" class="select-all" aria-label="Select all on this page">')
                ->titleAttr('Select')
                ->exportable(false)
                ->printable(false)
                ->width(36)
                ->addClass('dt-check'),
            Column::make('date')->title('Date'),
            Column::make('sample_name')->title('Sample Name'),
            Column::make('batch_no')->title('Batch No'),
            Column::make('analysis')->title('Analysis'),
            Column::make('result')->title('Result')->searchable(false),
            Column::computed('action')
                ->title('Actions')
                ->exportable(false)
                ->printable(false)
                ->width(210)
                ->addClass('dt-actions-col'),
        ];
    }

    protected function filename(): string
    {
        return 'QC_Analysis_'.date('YmdHis');
    }

    protected function isSheet(): bool
    {
        return ! request()->routeIs('dashboard');
    }

    protected function filterScript(): string
    {
        return <<<'JS'
var form = document.getElementById('filter-form');
if (form) {
    new FormData(form).forEach(function (value, key) {
        data[key] = value;
    });
}
JS;
    }

    protected function purityMarkup(Analysis $row): string
    {
        if ($row->purity === null) {
            return '<span class="cell-purity" id="purity-'.$row->id.'">—</span>';
        }

        $class = match (true) {
            $row->purity >= 98 => 'purity-high',
            $row->purity >= 90 => 'purity-mid',
            default => 'purity-low',
        };

        return '<span class="cell-purity '.$class.'" id="purity-'.$row->id.'">'
            .number_format($row->purity, 2).'%</span>';
    }

    protected function resultBadge(Analysis $row): string
    {
        $result = strtoupper(trim((string) $row->result));

        return match ($result) {
            'PASS' => '<span class="badge badge-pass">Pass</span>',
            'FAIL' => '<span class="badge badge-fail">Fail</span>',
            '' => '<span class="muted-dash">—</span>',
            default => '<span class="badge badge-pending">'.e($row->result).'</span>',
        };
    }

    protected function sheetResultMarkup(Analysis $row): string
    {
        $purity = $row->purity === null ? '' : $this->purityMarkup($row);
        $status = trim((string) $row->result) === '' ? '' : $this->resultBadge($row);

        if ($purity === '' && $status === '') {
            return '<span class="muted-dash">—</span>';
        }

        return '<div class="result-stack">'.$purity.$status.'</div>';
    }

    protected function editable(Analysis $row, string $field, mixed $value, string $class = ''): string
    {
        $text = ($value === null || $value === '') ? '' : e((string) $value);
        $classes = trim('cell '.$class);

        return '<span class="'.$classes.'" data-field="'.e($field).'" data-id="'.$row->id.'" title="'.$text.'" ondblclick="editCell(this)">'.$text.'</span>';
    }
}
