@extends('layouts.app')

@section('title', 'QC Analysis – ' . config('app.name'))
@section('page_title', 'Analysis Sheet')
@section('hide_footer', '1')
@section('hide_nav_links', '1')
@section('hide_navbar', '1')

@push('styles')
<link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="{{ asset('css/datatable.css') }}" rel="stylesheet">
@endpush

@section('content')
@php
    $sheetStats = $sheetStats ?? ['total' => 0, 'passed' => 0, 'failed' => 0, 'pending' => 0];
@endphp
<div class="admin-wrapper">
    @include('partials.admin-sidebar', ['active' => 'analysis'])
    <x-admin-header title="Analysis Sheet" search-mode="focus" />
    <main class="admin-main inv-sheet">
        <div class="inv-sheet-head">
            <nav class="inv-sheet-crumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span aria-hidden="true">/</span>
                <span>{{ !empty($isTrash) ? 'Trash' : 'Analysis' }}</span>
            </nav>
            @if(empty($isTrash))
                @can('create', Modules\Analysis\Models\Analysis::class)
                    <button type="button" onclick="openAddModal()" class="inv-sheet-create" id="add-new-btn">+ Create</button>
                @endcan
            @else
                <a href="{{ route('analysis.index') }}" class="inv-sheet-create">Back to sheet</a>
            @endif
        </div>

        @if(empty($isTrash))
        <section class="inv-kpi-grid" aria-label="Analysis summary">
            <article class="inv-kpi-card inv-kpi-card--total">
                <div class="inv-kpi-icon" aria-hidden="true">@include('partials.icons.nav', ['name' => 'clipboard'])</div>
                <div class="inv-kpi-body">
                    <span class="inv-kpi-label">Total Samples</span>
                    <span class="inv-kpi-value">{{ number_format($sheetStats['total']) }}</span>
                </div>
                <span class="inv-kpi-accent" aria-hidden="true"></span>
            </article>
            <article class="inv-kpi-card inv-kpi-card--paid">
                <div class="inv-kpi-icon" aria-hidden="true">@include('partials.icons.nav', ['name' => 'check'])</div>
                <div class="inv-kpi-body">
                    <span class="inv-kpi-label">Passed</span>
                    <span class="inv-kpi-value">{{ number_format($sheetStats['passed']) }}</span>
                </div>
                <span class="inv-kpi-accent" aria-hidden="true"></span>
            </article>
            <article class="inv-kpi-card inv-kpi-card--partial">
                <div class="inv-kpi-icon" aria-hidden="true">@include('partials.icons.nav', ['name' => 'alert'])</div>
                <div class="inv-kpi-body">
                    <span class="inv-kpi-label">Pending</span>
                    <span class="inv-kpi-value">{{ number_format($sheetStats['pending']) }}</span>
                </div>
                <span class="inv-kpi-accent" aria-hidden="true"></span>
            </article>
            <article class="inv-kpi-card inv-kpi-card--canceled">
                <div class="inv-kpi-icon" aria-hidden="true">@include('partials.icons.nav', ['name' => 'alert'])</div>
                <div class="inv-kpi-body">
                    <span class="inv-kpi-label">Failed</span>
                    <span class="inv-kpi-value">{{ number_format($sheetStats['failed']) }}</span>
                </div>
                <span class="inv-kpi-accent" aria-hidden="true"></span>
            </article>
        </section>
        @endif

<div class="sheet-card inv-main-card" id="datasheet">
<form id="filter-form" autocomplete="off" onsubmit="return false;">
    @if(!empty($isTrash))
        <input type="hidden" name="trash" id="trash-flag" value="1">
    @endif

    <div class="inv-grid-toolbar">
        <div class="inv-grid-toolbar-left">
            <label class="inv-status-select" for="filter-result">
                <span class="inv-status-dot" aria-hidden="true"></span>
                <select name="filter_result" id="filter-result" class="inv-toolbar-select" onchange="applyAnalysisFilters()">
                    <option value="">All Samples</option>
                    <option value="PASS" {{ strtoupper((string) request('filter_result')) === 'PASS' ? 'selected' : '' }}>Passed</option>
                    <option value="FAIL" {{ strtoupper((string) request('filter_result')) === 'FAIL' ? 'selected' : '' }}>Failed</option>
                    <option value="Pending" {{ strtoupper((string) request('filter_result')) === 'PENDING' ? 'selected' : '' }}>Pending</option>
                </select>
            </label>
            <div class="inv-date-range">
                <input type="date" name="date_from" id="date-from" class="inv-toolbar-input" value="{{ request('date_from') }}" aria-label="Start date" onchange="applyAnalysisFilters()">
                <span aria-hidden="true">–</span>
                <input type="date" name="date_to" id="date-to" class="inv-toolbar-input" value="{{ request('date_to') }}" aria-label="End date" onchange="applyAnalysisFilters()">
            </div>
            <button type="button" class="inv-tool-btn" id="open-filter-btn" onclick="openFilterModal()" title="More filters">
                Filter
                <span class="filter-count" id="filter-count" hidden>0</span>
            </button>
            <button type="button" class="inv-tool-icon" onclick="reloadAnalysisTable()" title="Refresh" aria-label="Refresh">↻</button>
        </div>
        <div class="inv-grid-toolbar-right">
            @if(empty($isTrash))
                <div class="bulk-actions" id="bulk-actions">
                    <select id="bulk-action-select" class="inv-toolbar-select" aria-label="Bulk action">
                        <option value="">Bulk actions…</option>
                        @can('bulkUpdateResult', Modules\Analysis\Models\Analysis::class)
                            <option value="pass">Mark PASS</option>
                            <option value="fail">Mark FAIL</option>
                        @endcan
                        <option value="export">Export selected</option>
                        @can('bulkDelete', Modules\Analysis\Models\Analysis::class)
                            <option value="delete">Delete selected</option>
                        @endcan
                    </select>
                    <button type="button" class="inv-tool-btn" onclick="runBulkAction()">Apply</button>
                </div>
                <div class="inv-export-group">
                    <button type="button" class="inv-tool-btn" id="export-btn" onclick="exportExcel()">Export</button>
                    <button type="button" class="inv-tool-btn inv-tool-btn-pdf" id="export-pdf-btn" onclick="submitPdfExport()">
                        PDF (<span id="selected-count">0</span>)
                    </button>
                    @can('create', Modules\Analysis\Models\Analysis::class)
                        <a href="{{ route('analysis.import') }}" class="inv-tool-btn" id="import-btn">Import</a>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    <div class="modal-overlay" id="filter-modal" onclick="if (event.target === this) closeFilterModal()">
        <div class="modal" style="max-width:640px;">
            <div class="modal-header">
                <h3 class="modal-title">Search records</h3>
                <button type="button" class="modal-close" onclick="closeFilterModal()" aria-label="Close">✕</button>
            </div>
            <div class="form-grid">
                <div class="fld">
                    <label for="filter-sample">Sample</label>
                    <select name="filter_sample" id="filter-sample" class="f-select">
                        <option value="">All samples</option>
                        @foreach($filterOptions['sample_names'] as $v)
                            <option value="{{ $v }}" {{ request('filter_sample') == $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fld">
                    <label for="filter-batch">Batch</label>
                    <select name="filter_batch" id="filter-batch" class="f-select">
                        <option value="">All batches</option>
                        @foreach($filterOptions['batch_nos'] as $v)
                            <option value="{{ $v }}" {{ request('filter_batch') == $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fld">
                    <label for="filter-analysis">Analysis</label>
                    <select name="filter_analysis" id="filter-analysis" class="f-select">
                        <option value="">All analyses</option>
                        @foreach($filterOptions['analyses'] as $v)
                            <option value="{{ $v }}" {{ request('filter_analysis') == $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fld">
                    <label for="filter-instrument">Instrument</label>
                    <select name="filter_instrument" id="filter-instrument" class="f-select">
                        <option value="">All instruments</option>
                        @foreach($filterOptions['instruments'] as $v)
                            <option value="{{ $v }}" {{ request('filter_instrument') == $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fld">
                    <label for="filter-column">Column</label>
                    <select name="filter_column" id="filter-column" class="f-select">
                        <option value="">All columns</option>
                        @foreach($filterOptions['columns'] as $v)
                            <option value="{{ $v }}" {{ request('filter_column') == $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fld form-full">
                    <label for="filter-done-by">Analyst</label>
                    <select name="filter_done_by" id="filter-done-by" class="f-select">
                        <option value="">All analysts</option>
                        @foreach($filterOptions['done_bys'] as $v)
                            <option value="{{ $v }}" {{ request('filter_done_by') == $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="sheet-btn" onclick="clearAnalysisFilters()">Clear</button>
                <button type="button" class="sheet-btn sheet-btn-primary" onclick="applyAnalysisFilters()">Search</button>
            </div>
        </div>
    </div>
</form>

<form id="pdf-export-form" action="{{ route('analysis.export-pdf') }}" method="GET" target="_blank" style="display:none;">
    <input type="hidden" name="ids" id="pdf-export-ids">
</form>
<form id="bulk-delete-form" method="POST" action="{{ route('analysis.bulk-delete') }}" hidden>
    @csrf
</form>
<form id="bulk-result-form" method="POST" action="{{ route('analysis.bulk-result') }}" hidden>
    @csrf
    <input type="hidden" name="result" id="bulk-result-value" value="">
</form>

@if(session('success'))
    <div class="alert alert-success" id="flash-msg"><span>Saved.</span> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" id="flash-msg"><span>Error.</span> {{ session('error') }}</div>
@endif

<div class="dt-table-wrap">
    {!! $dataTable->table(['class' => 'qc-datatable display nowrap', 'style' => 'width:100%']) !!}
</div>
</div>

{{-- ADD / EDIT MODAL --}}
<div class="modal-overlay" id="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add HPLC Analysis Record</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form id="analysis-form" method="POST" action="{{ route('analysis.store') }}">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="id" id="form-id">
            <div id="analysis-form-errors" class="form-errors" role="alert"></div>

            <div class="form-grid">
                <div class="fld">
                    <label>Date *</label>
                    <input type="date" name="date" id="f-date" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="fld">
                    <label>Sample Name *</label>
                    <input type="text" name="sample_name" id="f-sample_name" required placeholder="e.g. Paracetamol Raw Material">
                </div>
                <div class="fld">
                    <label>Batch No *</label>
                    <input type="text" name="batch_no" id="f-batch_no" required placeholder="e.g. B2024-001">
                </div>
                <div class="fld">
                    <label>Analysis *</label>
                    <input type="text" name="analysis" id="f-analysis" required placeholder="e.g. Assay by HPLC">
                </div>
                <div class="fld">
                    <label>Pressure (bar / psi)</label>
                    <input type="text" name="pressure" id="f-pressure" placeholder="e.g. 145 bar">
                </div>
                <div class="fld">
                    <label>Column Name</label>
                    <input type="text" name="column_name" id="f-column_name" placeholder="e.g. C18 250x4.6mm 5µm">
                </div>
                <div class="fld">
                    <label>Instrument Name</label>
                    <input type="text" name="instrument" id="f-instrument" placeholder="e.g. HPLC-01 (Agilent 1260)">
                </div>
                <div class="fld">
                    <label>Done By / Analyst</label>
                    <input type="text" name="done_by" id="f-done_by" placeholder="e.g. John Doe">
                </div>
                <div class="fld">
                    <label>Std Concentration</label>
                    <input type="number" step="any" name="std_con" id="f-std_con" placeholder="0.00" oninput="calcPurityPreview()">
                </div>
                <div class="fld">
                    <label>Std Peak Area</label>
                    <input type="number" step="any" name="std_area" id="f-std_area" placeholder="0.00" oninput="calcPurityPreview()">
                </div>
                <div class="fld">
                    <label>Sample Concentration</label>
                    <input type="number" step="any" name="sample_con" id="f-sample_con" placeholder="0.00" oninput="calcPurityPreview()">
                </div>
                <div class="fld">
                    <label>Sample Peak Area</label>
                    <input type="number" step="any" name="sample_area" id="f-sample_area" placeholder="0.00" oninput="calcPurityPreview()">
                </div>
                <div class="fld">
                    <label>Calculated Purity %</label>
                    <div class="purity-preview" id="purity-preview-val">—</div>
                </div>
                <div class="fld">
                    <label>Result Status</label>
                    <select name="result" id="f-result">
                        <option value="PASS">PASS</option>
                        <option value="FAIL">FAIL</option>
                    </select>
                </div>
                <div class="fld form-full">
                    <label>Specification Target</label>
                    <input type="text" name="specification" id="f-specification" placeholder="e.g. 0.80%">
                </div>
                <div class="fld form-full">
                    <label>Sample Submitted By</label>
                    <input type="text" name="sample_submitted_by" id="f-sample_submitted_by" placeholder="e.g. Ann">
                </div>
                <div class="fld form-full">
                    <label>Remarks</label>
                    <textarea name="remarks" id="f-remarks" placeholder="Optional notes..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Analysis Record</button>
            </div>
        </form>
    </div>
</div>

{{-- READ-ONLY RECORD --}}
<div class="modal-overlay" id="view-modal-overlay">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="view-modal-title">
        <div class="modal-header">
            <h3 class="modal-title" id="view-modal-title">HPLC Analysis Record</h3>
            <button type="button" class="modal-close" onclick="closeViewModal()" aria-label="Close">✕</button>
        </div>
        <div class="form-grid">
            <div class="fld">
                <label>Date</label>
                <div class="view-value" id="v-date">—</div>
            </div>
            <div class="fld">
                <label>Sample Name</label>
                <div class="view-value" id="v-sample_name">—</div>
            </div>
            <div class="fld">
                <label>Batch No</label>
                <div class="view-value" id="v-batch_no">—</div>
            </div>
            <div class="fld">
                <label>Analysis</label>
                <div class="view-value" id="v-analysis">—</div>
            </div>
            <div class="fld">
                <label>Pressure</label>
                <div class="view-value" id="v-pressure">—</div>
            </div>
            <div class="fld">
                <label>Column</label>
                <div class="view-value" id="v-column_name">—</div>
            </div>
            <div class="fld">
                <label>Instrument</label>
                <div class="view-value" id="v-instrument">—</div>
            </div>
            <div class="fld">
                <label>Done By</label>
                <div class="view-value" id="v-done_by">—</div>
            </div>
            <div class="fld">
                <label>Std Concentration</label>
                <div class="view-value" id="v-std_con">—</div>
            </div>
            <div class="fld">
                <label>Std Area</label>
                <div class="view-value" id="v-std_area">—</div>
            </div>
            <div class="fld">
                <label>Sample Concentration</label>
                <div class="view-value" id="v-sample_con">—</div>
            </div>
            <div class="fld">
                <label>Sample Area</label>
                <div class="view-value" id="v-sample_area">—</div>
            </div>
            <div class="fld">
                <label>Purity</label>
                <div class="view-value" id="v-purity">—</div>
            </div>
            <div class="fld">
                <label>Result</label>
                <div class="view-value" id="v-result">—</div>
            </div>
            <div class="fld form-full">
                <label>Specification</label>
                <div class="view-value" id="v-specification">—</div>
            </div>
            <div class="fld form-full">
                <label>Sample Submitted By</label>
                <div class="view-value" id="v-sample_submitted_by">—</div>
            </div>
            <div class="fld form-full">
                <label>Remarks</label>
                <div class="view-value" id="v-remarks">—</div>
            </div>
            <div class="fld">
                <label>Created by</label>
                <div class="view-value" id="v-created_by">—</div>
            </div>
            <div class="fld">
                <label>Updated by</label>
                <div class="view-value" id="v-updated_by">—</div>
            </div>
            @if(auth()->user()?->isAdmin())
            <div class="fld form-full">
                <label>Activity</label>
                <div class="view-value" id="v-activity">—</div>
            </div>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

{{-- PDF SETUP / BRANDING SETTINGS MODAL --}}
<div class="modal-overlay" id="settings-modal-overlay">
    <div class="modal" style="max-width:550px;">
        <div class="modal-header">
            <h3 class="modal-title">⚙️ PDF Report Branding & Settings</h3>
            <button class="modal-close" onclick="closeSettingsModal()">✕</button>
        </div>
        <form action="{{ route('analysis.save-settings') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-grid" style="grid-template-columns:1fr;">
                <div class="fld">
                    <label>Company Name (Header)</label>
                    <textarea name="company_name" rows="2" required placeholder="MOLECULES BIOLABS&#10;PRIVATE LIMITED">{{ $settings['company_name'] ?? "MOLECULES BIOLABS\nPRIVATE LIMITED" }}</textarea>
                </div>
                <div class="fld">
                    <label>Company Tagline</label>
                    <input type="text" name="company_tagline" value="{{ $settings['company_tagline'] ?? 'Next Gen Ingredients' }}" placeholder="Next Gen Ingredients">
                </div>
                <div class="fld">
                    <label>Logo Image</label>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/jpg,image/svg+xml">
                </div>
                <div class="fld">
                    <label>Document Title</label>
                    <input type="text" name="document_name" value="{{ $settings['document_name'] ?? 'SAMPLE TEST REPORT' }}" placeholder="SAMPLE TEST REPORT">
                </div>
                <div class="fld">
                    <label>Request Form Prefix</label>
                    <input type="text" name="request_prefix" value="{{ $settings['request_prefix'] ?? 'STR' }}" placeholder="STR">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeSettingsModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save PDF Branding</button>
            </div>
        </form>
    </div>
</div>
    </main>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
{!! $dataTable->scripts() !!}
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const selectedIds = new Set();
const CAN_QUICK_EDIT = @json(auth()->user()?->canMutateAnalyses() ?? false);
const IS_TRASH = @json(!empty($isTrash));

window.onAnalysisTableDraw = function () {
    const boxes = document.querySelectorAll('#analyses-table_wrapper .row-checkbox');
    boxes.forEach(cb => {
        cb.checked = selectedIds.has(String(cb.value));
        cb.closest('tr')?.classList.toggle('selected-row', cb.checked);
    });
    const allOn = boxes.length > 0 && Array.from(boxes).every(cb => cb.checked);
    document.querySelectorAll('.select-all').forEach(el => { el.checked = allOn; });
    const countSpan = document.getElementById('selected-count');
    if (countSpan) countSpan.textContent = String(selectedIds.size);
    const dtSearch = document.querySelector('#analyses-table_wrapper .dataTables_filter input');
    if (dtSearch && !dtSearch.id) dtSearch.id = 'search-input';
};

function analysisTable() {
    return window.LaravelDataTables['analyses-table'];
}

function reloadAnalysisTable() {
    analysisTable()?.ajax.reload(null, false);
}

function openFilterModal() {
    document.getElementById('filter-modal')?.classList.add('open');
}

function closeFilterModal() {
    document.getElementById('filter-modal')?.classList.remove('open');
}

function updateFilterBadge() {
    const form = document.getElementById('filter-form');
    const badge = document.getElementById('filter-count');
    if (!form || !badge) return;
    let count = 0;
    form.querySelectorAll('input[name], select[name]').forEach(el => {
        if (el.value) count++;
    });
    badge.hidden = count === 0;
    badge.textContent = String(count);
}

function applyAnalysisFilters() {
    updateFilterBadge();
    reloadAnalysisTable();
    closeFilterModal();
}

function clearAnalysisFilters() {
    document.querySelectorAll('#filter-form input, #filter-form select').forEach(el => { el.value = ''; });
    updateFilterBadge();
    reloadAnalysisTable();
    closeFilterModal();
}

document.addEventListener('DOMContentLoaded', updateFilterBadge);

function exportExcel() {
    const params = new URLSearchParams(new FormData(document.getElementById('filter-form')));
    const term = analysisTable()?.search() || '';
    if (term) params.set('search', term);
    window.location = "{{ route('analysis.export') }}?" + params.toString();
}

function runBulkAction() {
    const action = document.getElementById('bulk-action-select')?.value;
    const ids = Array.from(selectedIds);
    if (!action) return;
    if (ids.length === 0) {
        alert('Select at least one record.');
        return;
    }

    if (action === 'export') {
        const params = new URLSearchParams();
        ids.forEach(id => params.append('ids[]', id));
        window.location = "{{ route('analysis.bulk-export') }}?" + params.toString();
        return;
    }

    if (action === 'delete') {
        if (!confirm('Delete ' + ids.length + ' selected record(s)?')) return;
        const form = document.getElementById('bulk-delete-form');
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
        form.submit();
        return;
    }

    if (action === 'pass' || action === 'fail') {
        const form = document.getElementById('bulk-result-form');
        document.getElementById('bulk-result-value').value = action.toUpperCase();
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
        form.submit();
    }
}

document.addEventListener('change', function (event) {
    const target = event.target;
    if (!(target instanceof HTMLInputElement)) return;

    if (target.classList.contains('select-all')) {
        document.querySelectorAll('.select-all').forEach(el => { el.checked = target.checked; });
        document.querySelectorAll('#analyses-table_wrapper .row-checkbox').forEach(cb => {
            cb.checked = target.checked;
            if (target.checked) selectedIds.add(String(cb.value));
            else selectedIds.delete(String(cb.value));
            cb.closest('tr')?.classList.toggle('selected-row', target.checked);
        });
    }

    if (target.classList.contains('row-checkbox')) {
        if (target.checked) selectedIds.add(String(target.value));
        else selectedIds.delete(String(target.value));
        target.closest('tr')?.classList.toggle('selected-row', target.checked);
    }

    const countSpan = document.getElementById('selected-count');
    if (countSpan) countSpan.textContent = String(selectedIds.size);
});
/* Toggle Analytics Visibility */
function submitPdfExport() {
    const ids = Array.from(selectedIds);

    if (ids.length === 0) {
        if (confirm('No records selected. Export every record that matches the current search and filters?')) {
            const params = new URLSearchParams(new FormData(document.getElementById('filter-form')));
            const term = analysisTable()?.search() || '';
            if (term) params.set('search', term);
            window.open("{{ route('analysis.export-pdf') }}?" + params.toString(), '_blank');
        }
        return;
    }

    document.getElementById('pdf-export-ids').value = ids.join(',');
    document.getElementById('pdf-export-form').submit();
}

function fieldValue(value) {
    return value === null || value === undefined ? '' : value;
}

function clearFormErrors() {
    const box = document.getElementById('analysis-form-errors');
    if (!box) return;
    box.replaceChildren();
    box.classList.remove('show');
}

function showFormErrors(messages) {
    const box = document.getElementById('analysis-form-errors');
    box.replaceChildren();
    messages.forEach(message => {
        const line = document.createElement('div');
        line.textContent = message;
        box.appendChild(line);
    });
    box.classList.add('show');
    box.scrollIntoView({ block: 'nearest' });
}

function showSheetFlash(kind, text) {
    document.getElementById('flash-msg')?.remove();
    const el = document.createElement('div');
    el.id = 'flash-msg';
    el.className = 'alert ' + (kind === 'success' ? 'alert-success' : 'alert-danger');
    const label = document.createElement('span');
    label.textContent = kind === 'success' ? 'Saved.' : 'Error.';
    el.append(label, document.createTextNode(' ' + text));
    document.querySelector('.dt-table-wrap')?.before(el);
    setTimeout(() => el.remove(), 3500);
}

document.getElementById('analysis-form').addEventListener('submit', async function (event) {
    event.preventDefault();
    const form = event.currentTarget;
    const submitBtn = form.querySelector('[type="submit"]');
    clearFormErrors();
    if (submitBtn) submitBtn.disabled = true;

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });

        let data = {};
        try { data = await res.json(); } catch (err) { data = {}; }

        if (res.status === 422) {
            const messages = Object.values(data.errors || {}).flat();
            showFormErrors(messages.length ? messages : [data.message || 'Could not save this record.']);
            return;
        }

        if (!res.ok || data.success === false) {
            showFormErrors([data.message || 'Could not save this record.']);
            return;
        }

        closeModal();
        reloadAnalysisTable();
        showSheetFlash('success', data.message || 'Record added successfully.');
    } catch (err) {
        showFormErrors(['Could not save this record. Check your connection and try again.']);
    } finally {
        if (submitBtn) submitBtn.disabled = false;
    }
});

/* Modal management */
function openAddModal() {
    clearFormErrors();
    document.getElementById('modal-title').textContent = 'Add HPLC Analysis Record';
    document.getElementById('analysis-form').action = "{{ route('analysis.store') }}";
    document.getElementById('form-method').value = 'POST';
    document.getElementById('form-id').value = '';
    document.getElementById('f-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('f-sample_name').value = '';
    document.getElementById('f-batch_no').value = '';
    document.getElementById('f-analysis').value = '';
    document.getElementById('f-pressure').value = '';
    document.getElementById('f-column_name').value = '';
    document.getElementById('f-instrument').value = '';
    document.getElementById('f-done_by').value = '';
    document.getElementById('f-std_con').value = '';
    document.getElementById('f-std_area').value = '';
    document.getElementById('f-sample_con').value = '';
    document.getElementById('f-sample_area').value = '';
    document.getElementById('purity-preview-val').textContent = '—';
    document.getElementById('f-result').value = 'PASS';
    document.getElementById('f-specification').value = '0.80%';
    document.getElementById('f-sample_submitted_by').value = 'Ann';
    document.getElementById('f-remarks').value = '';
    document.getElementById('modal-overlay').classList.add('open');
}

async function openEditModal(id) {
    try {
        const res = await fetch(`/analysis/${id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        if (!res.ok || !data || !data.id) {
            alert('Could not load record data.');
            return;
        }
        clearFormErrors();
        document.getElementById('modal-title').textContent = `Edit HPLC Record #${id}`;
        document.getElementById('analysis-form').action = `/analysis/${id}`;
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('form-id').value = id;

        document.getElementById('f-date').value = data.date ? String(data.date).slice(0, 10) : '';
        document.getElementById('f-sample_name').value = fieldValue(data.sample_name);
        document.getElementById('f-batch_no').value = fieldValue(data.batch_no);
        document.getElementById('f-analysis').value = fieldValue(data.analysis);
        document.getElementById('f-pressure').value = fieldValue(data.pressure);
        document.getElementById('f-column_name').value = fieldValue(data.column_name);
        document.getElementById('f-instrument').value = fieldValue(data.instrument);
        document.getElementById('f-done_by').value = fieldValue(data.done_by);
        document.getElementById('f-std_con').value = fieldValue(data.std_con);
        document.getElementById('f-std_area').value = fieldValue(data.std_area);
        document.getElementById('f-sample_con').value = fieldValue(data.sample_con);
        document.getElementById('f-sample_area').value = fieldValue(data.sample_area);
        document.getElementById('f-result').value = data.result || 'PASS';
        document.getElementById('f-specification').value = data.specification || '0.80%';
        document.getElementById('f-sample_submitted_by').value = fieldValue(data.sample_submitted_by);
        document.getElementById('f-remarks').value = fieldValue(data.remarks);

        calcPurityPreview();
        document.getElementById('modal-overlay').classList.add('open');
    } catch(err) {
        alert('Could not load record data.');
    }
}

function closeModal() {
    document.getElementById('modal-overlay').classList.remove('open');
}

function viewText(value) {
    if (value === null || value === undefined || value === '') return '—';
    return String(value);
}

async function openViewModal(id) {
    const overlay = document.getElementById('view-modal-overlay');
    document.getElementById('view-modal-title').textContent = 'HPLC Analysis Record';
    ['date','sample_name','batch_no','analysis','pressure','column_name','instrument','done_by','std_con','std_area','sample_con','sample_area','purity','result','specification','sample_submitted_by','remarks','created_by','updated_by','activity'].forEach(key => {
        const el = document.getElementById('v-' + key);
        if (el) el.textContent = '—';
    });
    overlay.classList.add('open');

    try {
        const res = await fetch(`/analysis/${id}`, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Could not load record');
        const data = await res.json();
        const titleName = data.sample_name ? ` · ${data.sample_name}` : '';
        document.getElementById('view-modal-title').textContent = `HPLC Analysis Record${titleName}`;
        document.getElementById('v-date').textContent = data.date ? String(data.date).slice(0, 10) : '—';
        document.getElementById('v-sample_name').textContent = viewText(data.sample_name);
        document.getElementById('v-batch_no').textContent = viewText(data.batch_no);
        document.getElementById('v-analysis').textContent = viewText(data.analysis);
        document.getElementById('v-pressure').textContent = viewText(data.pressure);
        document.getElementById('v-column_name').textContent = viewText(data.column_name);
        document.getElementById('v-instrument').textContent = viewText(data.instrument);
        document.getElementById('v-done_by').textContent = viewText(data.done_by);
        document.getElementById('v-std_con').textContent = viewText(data.std_con);
        document.getElementById('v-std_area').textContent = viewText(data.std_area);
        document.getElementById('v-sample_con').textContent = viewText(data.sample_con);
        document.getElementById('v-sample_area').textContent = viewText(data.sample_area);
        document.getElementById('v-purity').textContent = (data.purity === null || data.purity === undefined || data.purity === '')
            ? '—'
            : Number(data.purity).toFixed(2) + '%';
        document.getElementById('v-result').textContent = viewText(data.result);
        document.getElementById('v-specification').textContent = viewText(data.specification);
        document.getElementById('v-sample_submitted_by').textContent = viewText(data.sample_submitted_by);
        document.getElementById('v-remarks').textContent = viewText(data.remarks);
        document.getElementById('v-created_by').textContent = viewText(data.created_by_name);
        document.getElementById('v-updated_by').textContent = viewText(data.updated_by_name);
        const activityEl = document.getElementById('v-activity');
        if (activityEl) {
            if (Array.isArray(data.activity_logs) && data.activity_logs.length) {
                activityEl.innerHTML = data.activity_logs.map(log =>
                    `<div>${viewText(log.at)} · ${viewText(log.action)} · ${viewText(log.user)}</div>`
                ).join('');
            } else {
                activityEl.textContent = '—';
            }
        }
    } catch (err) {
        closeViewModal();
        alert('Could not load record data.');
    }
}

function closeViewModal() {
    document.getElementById('view-modal-overlay').classList.remove('open');
}

function openSettingsModal() {
    document.getElementById('settings-modal-overlay').classList.add('open');
}
if (location.hash === '#settings') {
    openSettingsModal();
}
function closeSettingsModal() {
    document.getElementById('settings-modal-overlay').classList.remove('open');
}

function calcPurityPreview() {
    const sc = parseFloat(document.getElementById('f-sample_con').value);
    const sa = parseFloat(document.getElementById('f-sample_area').value);
    const stc = parseFloat(document.getElementById('f-std_con').value);
    const sta = parseFloat(document.getElementById('f-std_area').value);
    const prevEl = document.getElementById('purity-preview-val');

    if (sc > 0 && sa > 0 && stc > 0 && sta > 0) {
        const purity = (sa / sta) * (stc / sc) * 100;
        prevEl.textContent = purity.toFixed(2) + '%';
    } else {
        prevEl.textContent = '—';
    }
}

/* Inline cell editing */
let activeCell = null;
function editCell(el) {
    if (!CAN_QUICK_EDIT || IS_TRASH) return;
    if (activeCell) commitCell(activeCell);
    activeCell = el;
    el.setAttribute('contenteditable', 'true');
    el.focus();
    const r = document.createRange(); r.selectNodeContents(el); r.collapse(false);
    const s = window.getSelection(); s.removeAllRanges(); s.addRange(r);
    el.addEventListener('blur', () => commitCell(el), { once: true });
    el.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); commitCell(el); }
        if (e.key === 'Escape') { el.removeAttribute('contenteditable'); activeCell = null; }
    });
}

async function commitCell(el) {
    el.removeAttribute('contenteditable'); activeCell = null;
    const field = el.dataset.field, id = el.dataset.id, value = el.innerText.trim();
    try {
        const res = await fetch(`/analysis/${id}/quick-update`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ field, value })
        });
        const data = await res.json();
        if (data.success && data.purity != null) {
            const pEl = document.getElementById('purity-' + id);
            if (pEl) pEl.textContent = Number(data.purity).toFixed(2) + '%';
        }
        const row = document.getElementById('row-' + id);
        if (row) {
            row.style.background = 'rgba(99,102,241,.18)';
            setTimeout(() => row.style.background = '', 700);
        }
    } catch(err) {
        console.error(err);
    }
}

/* Flash dismiss */
setTimeout(() => document.getElementById('flash-msg')?.remove(), 3500);

/* Ctrl+N = new row */
document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault(); openAddModal();
    }
});

</script>
@endsection
