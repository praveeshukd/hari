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
                <div class="inv-kpi-body">
                    <span class="inv-kpi-label">Total Samples</span>
                    <span class="inv-kpi-value">{{ number_format($sheetStats['total']) }}</span>
                </div>
                <span class="inv-kpi-accent" aria-hidden="true"></span>
            </article>
            <article class="inv-kpi-card inv-kpi-card--paid">
                <div class="inv-kpi-body">
                    <span class="inv-kpi-label">Passed</span>
                    <span class="inv-kpi-value">{{ number_format($sheetStats['passed']) }}</span>
                </div>
                <span class="inv-kpi-accent" aria-hidden="true"></span>
            </article>
            <article class="inv-kpi-card inv-kpi-card--partial">
                <div class="inv-kpi-body">
                    <span class="inv-kpi-label">Pending</span>
                    <span class="inv-kpi-value">{{ number_format($sheetStats['pending']) }}</span>
                </div>
                <span class="inv-kpi-accent" aria-hidden="true"></span>
            </article>
            <article class="inv-kpi-card inv-kpi-card--canceled">
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
            <button type="button" class="inv-tool-btn" id="open-filter-btn" onclick="openFilterModal()" title="More filters">
                Filter
                <span class="filter-count" id="filter-count" hidden>0</span>
            </button>
        </div>
        <div class="inv-grid-toolbar-right">
            @if(empty($isTrash))
                <div class="inv-export-group">
                    <button type="button" class="inv-tool-btn" id="export-btn" onclick="exportExcel()">Export</button>
                    <button type="button" class="inv-tool-btn inv-tool-btn-pdf" id="export-pdf-btn" onclick="submitPdfExport()">Export PDF</button>
                    @can('create', Modules\Analysis\Models\Analysis::class)
                        <button type="button" class="inv-tool-btn" id="import-btn" onclick="openImportModal()">Import</button>
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
                <div class="fld">
                    <label for="filter-done-by">Analyst</label>
                    <select name="filter_done_by" id="filter-done-by" class="f-select">
                        <option value="">All analysts</option>
                        @foreach($filterOptions['done_bys'] as $v)
                            <option value="{{ $v }}" {{ request('filter_done_by') == $v ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fld">
                    <label for="date-from">From</label>
                    <input type="date" name="date_from" id="date-from" value="{{ request('date_from') }}">
                </div>
                <div class="fld">
                    <label for="date-to">To</label>
                    <input type="date" name="date_to" id="date-to" value="{{ request('date_to') }}">
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
    <div class="mobile-sheet-list" id="mobile-analysis-list" aria-live="polite"></div>
</div>
</div>

@if(empty($isTrash))
<div class="bulk-bar" id="bulk-bar" hidden>
    <div class="bulk-bar-inner">
        <div class="bulk-bar-count">
            <strong id="selected-count">0</strong>
            <span>selected</span>
            <button type="button" class="bulk-bar-clear" onclick="clearSelection()">Clear</button>
        </div>
        <div class="bulk-bar-actions">
            @can('bulkUpdateResult', Modules\Analysis\Models\Analysis::class)
                <button type="button" class="bulk-bar-btn bulk-bar-btn--pass" onclick="runBulkAction('pass')">Mark PASS</button>
                <button type="button" class="bulk-bar-btn bulk-bar-btn--fail" onclick="runBulkAction('fail')">Mark FAIL</button>
            @endcan
            <button type="button" class="bulk-bar-btn" onclick="runBulkAction('export')">Export Excel</button>
            <button type="button" class="bulk-bar-btn bulk-bar-btn--pdf" onclick="submitPdfExport()">Export PDF</button>
            @can('bulkDelete', Modules\Analysis\Models\Analysis::class)
                <button type="button" class="bulk-bar-btn bulk-bar-btn--danger" onclick="runBulkAction('delete')">Delete</button>
            @endcan
        </div>
    </div>
</div>
@endif

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
                    <label>Request Form No</label>
                    <input type="text" name="request_form_no" id="f-request_form_no" placeholder="e.g. STR26/B2024-001">
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
                    <label>Standard Purity</label>
                    <input type="number" step="any" name="standard_purity" id="f-standard_purity" placeholder="e.g. 99 or 98" oninput="calcPurityPreview()">
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
                <label>Request Form No</label>
                <div class="view-value" id="v-request_form_no">—</div>
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
                <label>Standard Purity</label>
                <div class="view-value" id="v-standard_purity">—</div>
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
            <div class="fld form-full">
                <label>Activity</label>
                <div class="view-value" id="v-activity">—</div>
            </div>
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

{{-- IMPORT EXCEL / CSV MODAL --}}
@can('create', Modules\Analysis\Models\Analysis::class)
<div class="modal-overlay" id="import-modal-overlay" onclick="if (event.target === this) closeImportModal()">
    <div class="modal" style="max-width:560px;" role="dialog" aria-modal="true" aria-labelledby="import-modal-title">
        <div class="modal-header">
            <h3 class="modal-title" id="import-modal-title">Import Excel / CSV</h3>
            <button type="button" class="modal-close" onclick="closeImportModal()" aria-label="Close">✕</button>
        </div>
        <form action="{{ route('analysis.import.store') }}" method="POST" enctype="multipart/form-data" id="import-modal-form">
            @csrf
            <p class="import-modal-lead">Upload HPLC analysis data. Purity is calculated on import. Max 10MB.</p>

            <div class="import-modal-template">
                <div>
                    <strong>Need a template?</strong>
                    <span>Download the Excel file with the correct headers.</span>
                </div>
                <a href="{{ route('analysis.template') }}" class="sheet-btn">Download template</a>
            </div>

            <label class="import-dropzone" id="import-dropzone" for="import-file-input">
                <input type="file" name="file" id="import-file-input" accept=".xlsx,.xls,.csv" required>
                <span class="import-dropzone-title" id="import-drop-title">Drop file here or click to browse</span>
                <span class="import-dropzone-sub" id="import-drop-sub">XLSX, XLS, or CSV</span>
            </label>

            <div class="import-file-chip" id="import-file-chip" hidden>
                <span id="import-file-name"></span>
                <button type="button" class="modal-close" onclick="clearImportFile()" aria-label="Remove file">✕</button>
            </div>

            @error('file')
                <div class="form-errors show" style="margin-top:.75rem;">{{ $message }}</div>
            @enderror

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeImportModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="import-submit-btn" disabled>Upload &amp; import</button>
            </div>
        </form>
    </div>
</div>
@endcan
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

function syncBulkBar() {
    const bar = document.getElementById('bulk-bar');
    const countSpan = document.getElementById('selected-count');
    const count = selectedIds.size;
    if (countSpan) countSpan.textContent = String(count);
    if (bar) bar.hidden = count === 0;
    document.body.classList.toggle('has-bulk-selection', count > 0);
}

function clearSelection() {
    selectedIds.clear();
    document.querySelectorAll('#analyses-table_wrapper .row-checkbox, .select-all').forEach(el => {
        el.checked = false;
        el.closest('tr')?.classList.remove('selected-row');
    });
    syncBulkBar();
}

function cellText(tr, field) {
    const el = tr.querySelector('[data-field="' + field + '"]');
    return (el?.textContent || '').trim();
}

function escHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatMobileDate(iso) {
    if (!iso) return { main: '—', sub: '' };
    const d = new Date(iso + 'T00:00:00');
    if (Number.isNaN(d.getTime())) return { main: iso, sub: '' };
    const sub = d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
    return { main: iso, sub };
}

function renderMobileAnalysisList() {
    const list = document.getElementById('mobile-analysis-list');
    if (!list) return;

    const rows = document.querySelectorAll('#analyses-table tbody tr');
    if (!rows.length || (rows.length === 1 && rows[0].querySelector('td.dataTables_empty'))) {
        list.innerHTML = '<div class="mobile-sheet-empty">No HPLC records yet.</div>';
        return;
    }

    const canEdit = CAN_QUICK_EDIT && !IS_TRASH;
    const frag = document.createDocumentFragment();

    rows.forEach((tr) => {
        const id = tr.querySelector('.row-checkbox')?.value;
        if (!id) return;

        const date = formatMobileDate(cellText(tr, 'date'));
        const sample = cellText(tr, 'sample_name') || 'Untitled sample';
        const batch = cellText(tr, 'batch_no') || '—';
        const analysis = cellText(tr, 'analysis') || 'Analysis';
        const checked = selectedIds.has(String(id));

        const card = document.createElement('article');
        card.className = 'mobile-sheet-card' + (checked ? ' is-selected' : '');
        card.dataset.id = id;
        card.innerHTML =
            '<label class="mobile-sheet-check">' +
                '<input type="checkbox" class="row-checkbox mobile-row-checkbox" value="' + id + '"' + (checked ? ' checked' : '') + ' aria-label="Select record ' + id + '">' +
            '</label>' +
            '<div class="mobile-sheet-date">' +
                '<span class="mobile-sheet-date-ico" aria-hidden="true">📅</span>' +
                '<strong>' + date.main + '</strong>' +
                (date.sub ? '<span>' + date.sub + '</span>' : '') +
            '</div>' +
            '<div class="mobile-sheet-main">' +
                '<div class="mobile-sheet-title-row">' +
                    '<div>' +
                        '<button type="button" class="mobile-sheet-name" data-view="' + id + '">' + escHtml(sample) + '</button>' +
                        '<div class="mobile-sheet-sub">' + escHtml(analysis) + '</div>' +
                    '</div>' +
                '</div>' +
                '<span class="mobile-sheet-batch">Batch: ' + escHtml(batch) + '</span>' +
            '</div>' +
            '<div class="mobile-sheet-actions">' +
                '<button type="button" class="mobile-sheet-icon-btn" data-view="' + id + '" title="View" aria-label="View">👁</button>' +
                (canEdit
                    ? '<details class="mobile-sheet-more">' +
                        '<summary class="mobile-sheet-icon-btn" title="More" aria-label="More">⋮</summary>' +
                        '<div class="mobile-sheet-menu">' +
                            '<button type="button" data-edit="' + id + '">Edit</button>' +
                            '<button type="button" data-view="' + id + '">View</button>' +
                        '</div>' +
                      '</details>'
                    : '<button type="button" class="mobile-sheet-icon-btn" data-view="' + id + '" title="View" aria-label="View details">⋮</button>') +
            '</div>';

        frag.appendChild(card);
    });

    list.innerHTML = '';
    list.appendChild(frag);
}

window.onAnalysisTableDraw = function () {
    const boxes = document.querySelectorAll('#analyses-table_wrapper .row-checkbox');
    boxes.forEach(cb => {
        cb.checked = selectedIds.has(String(cb.value));
        cb.closest('tr')?.classList.toggle('selected-row', cb.checked);
    });
    const allOn = boxes.length > 0 && Array.from(boxes).every(cb => cb.checked);
    document.querySelectorAll('.select-all').forEach(el => { el.checked = allOn; });
    syncBulkBar();
    renderMobileAnalysisList();
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

function runBulkAction(action) {
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
        if (!confirm('Mark ' + ids.length + ' record(s) as ' + action.toUpperCase() + '?')) return;
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
        document.querySelectorAll('#analyses-table_wrapper .row-checkbox, #mobile-analysis-list .row-checkbox').forEach(cb => {
            cb.checked = target.checked;
            if (target.checked) selectedIds.add(String(cb.value));
            else selectedIds.delete(String(cb.value));
            cb.closest('tr')?.classList.toggle('selected-row', target.checked);
            cb.closest('.mobile-sheet-card')?.classList.toggle('is-selected', target.checked);
        });
    }

    if (target.classList.contains('row-checkbox')) {
        if (target.checked) selectedIds.add(String(target.value));
        else selectedIds.delete(String(target.value));
        target.closest('tr')?.classList.toggle('selected-row', target.checked);
        target.closest('.mobile-sheet-card')?.classList.toggle('is-selected', target.checked);
        document.querySelectorAll('#analyses-table_wrapper .row-checkbox[value="' + target.value + '"], #mobile-analysis-list .row-checkbox[value="' + target.value + '"]').forEach(cb => {
            cb.checked = target.checked;
        });
    }

    syncBulkBar();
});

document.getElementById('mobile-analysis-list')?.addEventListener('click', function (event) {
    const editBtn = event.target.closest('[data-edit]');
    if (editBtn) {
        event.preventDefault();
        openEditModal(editBtn.getAttribute('data-edit'));
        return;
    }
    const viewBtn = event.target.closest('[data-view]');
    if (viewBtn) {
        event.preventDefault();
        openViewModal(viewBtn.getAttribute('data-view'));
    }
});

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
    document.getElementById('f-request_form_no').value = '';
    document.getElementById('f-analysis').value = '';
    document.getElementById('f-pressure').value = '';
    document.getElementById('f-column_name').value = '';
    document.getElementById('f-instrument').value = '';
    document.getElementById('f-done_by').value = '';
    document.getElementById('f-std_con').value = '';
    document.getElementById('f-std_area').value = '';
    document.getElementById('f-sample_con').value = '';
    document.getElementById('f-sample_area').value = '';
    document.getElementById('f-standard_purity').value = '';
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
        document.getElementById('f-request_form_no').value = fieldValue(data.request_form_no);
        document.getElementById('f-analysis').value = fieldValue(data.analysis);
        document.getElementById('f-pressure').value = fieldValue(data.pressure);
        document.getElementById('f-column_name').value = fieldValue(data.column_name);
        document.getElementById('f-instrument').value = fieldValue(data.instrument);
        document.getElementById('f-done_by').value = fieldValue(data.done_by);
        document.getElementById('f-std_con').value = fieldValue(data.std_con);
        document.getElementById('f-std_area').value = fieldValue(data.std_area);
        document.getElementById('f-sample_con').value = fieldValue(data.sample_con);
        document.getElementById('f-sample_area').value = fieldValue(data.sample_area);
        document.getElementById('f-standard_purity').value = fieldValue(data.standard_purity);
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
    ['date','sample_name','batch_no','request_form_no','analysis','pressure','column_name','instrument','done_by','std_con','std_area','sample_con','sample_area','standard_purity','purity','result','specification','sample_submitted_by','remarks','created_by','updated_by','activity'].forEach(key => {
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
        document.getElementById('v-request_form_no').textContent = viewText(data.request_form_no);
        document.getElementById('v-analysis').textContent = viewText(data.analysis);
        document.getElementById('v-pressure').textContent = viewText(data.pressure);
        document.getElementById('v-column_name').textContent = viewText(data.column_name);
        document.getElementById('v-instrument').textContent = viewText(data.instrument);
        document.getElementById('v-done_by').textContent = viewText(data.done_by);
        document.getElementById('v-std_con').textContent = viewText(data.std_con);
        document.getElementById('v-std_area').textContent = viewText(data.std_area);
        document.getElementById('v-sample_con').textContent = viewText(data.sample_con);
        document.getElementById('v-sample_area').textContent = viewText(data.sample_area);
        document.getElementById('v-standard_purity').textContent = (data.standard_purity === null || data.standard_purity === undefined || data.standard_purity === '')
            ? '—'
            : Number(data.standard_purity).toFixed(2);
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

function openImportModal() {
    document.getElementById('import-modal-overlay')?.classList.add('open');
}

function closeImportModal() {
    document.getElementById('import-modal-overlay')?.classList.remove('open');
}

function clearImportFile() {
    const input = document.getElementById('import-file-input');
    const chip = document.getElementById('import-file-chip');
    const title = document.getElementById('import-drop-title');
    const sub = document.getElementById('import-drop-sub');
    const submit = document.getElementById('import-submit-btn');
    if (input) input.value = '';
    if (chip) chip.hidden = true;
    if (title) title.textContent = 'Drop file here or click to browse';
    if (sub) sub.textContent = 'XLSX, XLS, or CSV';
    if (submit) submit.disabled = true;
}

function syncImportFile(file) {
    const chip = document.getElementById('import-file-chip');
    const name = document.getElementById('import-file-name');
    const title = document.getElementById('import-drop-title');
    const sub = document.getElementById('import-drop-sub');
    const submit = document.getElementById('import-submit-btn');
    if (!file) {
        clearImportFile();
        return;
    }
    if (name) name.textContent = file.name + (file.size ? ` · ${(file.size / 1024).toFixed(1)} KB` : '');
    if (chip) chip.hidden = false;
    if (title) title.textContent = 'File selected';
    if (sub) sub.textContent = 'Ready to upload';
    if (submit) submit.disabled = false;
}

document.getElementById('import-file-input')?.addEventListener('change', function () {
    syncImportFile(this.files?.[0] || null);
});

(function () {
    const zone = document.getElementById('import-dropzone');
    const input = document.getElementById('import-file-input');
    if (!zone || !input) return;

    ['dragenter', 'dragover'].forEach((evt) => {
        zone.addEventListener(evt, (e) => {
            e.preventDefault();
            zone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach((evt) => {
        zone.addEventListener(evt, (e) => {
            e.preventDefault();
            zone.classList.remove('is-dragover');
        });
    });
    zone.addEventListener('drop', (e) => {
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        syncImportFile(file);
    });
})();

@error('file')
openImportModal();
@enderror

if (location.hash === '#settings') {
    openSettingsModal();
}
if (location.hash === '#import' || new URLSearchParams(location.search).get('import') === '1') {
    openImportModal();
}
function closeSettingsModal() {
    document.getElementById('settings-modal-overlay').classList.remove('open');
}

function calcPurityPreview() {
    const sc = parseFloat(document.getElementById('f-sample_con').value);
    const sa = parseFloat(document.getElementById('f-sample_area').value);
    const stc = parseFloat(document.getElementById('f-std_con').value);
    const sta = parseFloat(document.getElementById('f-std_area').value);
    const typedStd = parseFloat(document.getElementById('f-standard_purity').value);
    const factor = (typedStd > 0) ? typedStd : 100;
    const prevEl = document.getElementById('purity-preview-val');

    if (sc > 0 && sa > 0 && stc > 0 && sta > 0) {
        const purity = (sa / sta) * (stc / sc) * factor;
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
