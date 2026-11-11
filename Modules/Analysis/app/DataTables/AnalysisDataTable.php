<?php

namespace Modules\Analysis\DataTables;

use Modules\Analysis\DTOs\AnalysisFilters;
use Modules\Analysis\Models\Analysis;
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
            ->editColumn('request_form_no', fn (Analysis $row) => $this->editable($row, 'request_form_no', $row->request_form_no))
            ->editColumn('analysis', fn (Analysis $row) => $this->editable($row, 'analysis', $row->analysis))
            ->editColumn('standard_purity', fn (Analysis $row) => $this->editable(
                $row,
                'standard_purity',
                $row->standard_purity === null ? '' : number_format((float) $row->standard_purity, 2)
            ))
            ->editColumn('purity', fn (Analysis $row) => $this->purityMarkup($row))
            ->editColumn('result', function (Analysis $row) {
                if ($this->isSheet()) {
                    return $this->sheetResultMarkup($row);
                }

                return $this->resultBadge($row);
            })
            ->addColumn('action', function (Analysis $row) {
                $user = auth()->user();
                $token = csrf_token();
                $actions = '<div class="dt-actions">'
                    .'<button type="button" class="dt-btn-view" onclick="openViewModal('.$row->id.')" title="View">View</button>';

                if ($this->isTrashView()) {
                    if ($user?->can('restore', $row)) {
                        $actions .= '<form action="'.route('analysis.restore', $row->id).'" method="POST">'
                            .'<input type="hidden" name="_token" value="'.$token.'">'
                            .'<button type="submit" class="dt-btn-ghost" title="Restore">Restore</button>'
                            .'</form>';
                    }
                } else {
                    if ($user?->can('update', $row)) {
                        $actions .= '<button type="button" class="dt-btn-ghost" onclick="openEditModal('.$row->id.')" title="Edit">Edit</button>';
                    }
                    if ($user?->can('delete', $row)) {
                        $actions .= '<form action="'.route('analysis.destroy', $row->id).'" method="POST" onsubmit="return confirm(\'Delete this record?\')">'
                            .'<input type="hidden" name="_token" value="'.$token.'">'
                            .'<input type="hidden" name="_method" value="DELETE">'
                            .'<button type="submit" class="dt-btn-ghost dt-btn-danger" title="Delete">Delete</button>'
                            .'</form>';
                    }
                }

                return $actions.'</div>';
            })
            ->orderColumn('checkbox', 'analyses.id $1')
            ->orderColumn('action', 'analyses.id $1')
            ->orderColumn('DT_RowIndex', 'analyses.id $1')
            ->rawColumns([
                'checkbox', 'date', 'sample_name', 'batch_no', 'request_form_no', 'analysis',
                'standard_purity', 'purity', 'result', 'action',
            ])
            ->setRowId(fn (Analysis $row) => 'row-'.$row->id);
    }

    public function query(Analysis $model): QueryBuilder
    {
        $filters = AnalysisFilters::fromRequest(request()->all());

        $columns = $this->isSheet()
            ? [
                'id', 'date', 'sample_name', 'batch_no', 'request_form_no', 'analysis',
                'purity', 'result', 'pressure', 'column_name', 'instrument',
                'std_con', 'std_area', 'sample_con', 'sample_area', 'standard_purity',
                'done_by', 'remarks', 'specification', 'sample_submitted_by',
                'deleted_at',
            ]
            : ['id', 'date', 'sample_name', 'batch_no', 'purity', 'result'];

        $query = $this->isTrashView()
            ? $model->newQuery()->onlyTrashed()
            : $model->newQuery();

        return $query
            ->select($columns)
            ->filtered($filters->toArray(), false);
    }

    public function html(): HtmlBuilder
    {
        $sheet = $this->isSheet();

        return $this->builder()
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
            Column::make('request_form_no')->title('Request Form No'),
            Column::make('analysis')->title('Analysis'),
            Column::make('standard_purity')->title('Std Purity'),
            Column::make('result')->title('Status')->searchable(false),
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

    protected function isTrashView(): bool
    {
        return $this->isSheet() && request()->boolean('trash');
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
var trashInput = document.getElementById('trash-flag');
if (trashInput && trashInput.value) {
    data.trash = trashInput.value;
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
        [$tone, $label] = match (true) {
            $result === 'PASS' || str_contains($result, 'PASS') => ['pass', 'Pass'],
            $result === 'FAIL' || str_contains($result, 'FAIL') => ['fail', 'Fail'],
            $result === '' => ['empty', '—'],
            default => ['pending', e($row->result ?: 'Pending')],
        };

        if ($tone === 'empty') {
            return '<span class="muted-dash">—</span>';
        }

        return '<span class="sheet-status sheet-status--'.$tone.'"><span class="sheet-status-dot" aria-hidden="true"></span>'.$label.'</span>';
    }

    protected function sheetResultMarkup(Analysis $row): string
    {
        $status = $this->resultBadge($row);
        $purity = $row->purity === null
            ? ''
            : '<span class="sheet-status-meta">'.number_format((float) $row->purity, 2).'%</span>';

        if ($status === '<span class="muted-dash">—</span>' && $purity === '') {
            return '<span class="muted-dash">—</span>';
        }

        return '<div class="sheet-status-cell">'.$status.$purity.'</div>';
    }

    protected function editable(Analysis $row, string $field, mixed $value, string $class = ''): string
    {
        $text = ($value === null || $value === '') ? '' : e((string) $value);
        $classes = trim('cell '.$class);

        return '<span class="'.$classes.'" data-field="'.e($field).'" data-id="'.$row->id.'" title="'.$text.'" ondblclick="editCell(this)">'.$text.'</span>';
    }
}
