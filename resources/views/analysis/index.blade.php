<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QC Analysis Dashboard – {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        :root {
            --bg:        #f8fafc;
            --bg2:       #ffffff;
            --bg3:       #f1f5f9;
            --border:    #e2e8f0;
            --border2:   #f1f5f9;
            --text:      #0f172a;
            --muted:     #64748b;
            --faint:     #94a3b8;
            --primary:   #0f766e;
            --primary-h: #0d9488;
            --success:   #10b981;
            --danger:    #ef4444;
            --warning:   #f59e0b;
            --cyan:      #0284c7;
            --grad:      linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #0284c7 100%);
            --grad-card: #ffffff;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;font-size:13px;}
        a{color:inherit;text-decoration:none;}

        /* ---- TOP NAV ---- */
        .topbar{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.5rem;background:#ffffff;border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;box-shadow:0 1px 3px rgba(0,0,0,0.04);}
        .brand{display:flex;align-items:center;gap:.65rem;font-family:'Outfit',sans-serif;font-size:1.05rem;font-weight:700;}
        .brand-icon{width:32px;height:32px;border-radius:8px;background:var(--grad);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.9rem;color:#fff;box-shadow:0 2px 8px rgba(15,118,110,.25);}
        .brand span{color:#0f172a;}

        /* ---- BUTTONS ---- */
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.42rem .95rem;border-radius:7px;font-size:.78rem;font-weight:600;border:none;cursor:pointer;transition:all .2s ease;white-space:nowrap;text-decoration:none;}
        .btn-primary{background:var(--grad);color:#fff;box-shadow:0 3px 10px rgba(15,118,110,.25);}
        .btn-primary:hover{filter:brightness(1.1);transform:translateY(-1px);}
        .btn-pdf{background:linear-gradient(135deg,#e11d48,#be123c);color:#fff;box-shadow:0 3px 10px rgba(225,29,72,.25);}
        .btn-pdf:hover{filter:brightness(1.1);transform:translateY(-1px);}
        .btn-outline{background:#ffffff;color:var(--text);border:1px solid var(--border);}
        .btn-outline:hover{background:var(--bg3);border-color:var(--primary);color:var(--primary);}
        .btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;}
        .btn-sm{padding:.32rem .75rem;font-size:.74rem;}
        .btn-xs{padding:.22rem .55rem;font-size:.68rem;}

        /* ====================================================
           FILTER PANEL
        ==================================================== */
        .filter-panel{
            background:var(--bg2);
            border-bottom:1px solid var(--border);
            padding:.6rem 1.5rem .7rem;
        }
        .filter-row{
            display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;
        }
        .filter-row + .filter-row{margin-top:.5rem;}

        .panel-title{
            font-family:'Outfit',sans-serif;font-size:.95rem;font-weight:700;
            white-space:nowrap;margin-right:.25rem;display:flex;align-items:center;gap:.35rem;
        }
        .f-sep{width:1px;height:18px;background:var(--border);flex-shrink:0;margin:0 .2rem;}

        /* Select dropdowns */
        .f-select{
            background:#ffffff;border:1px solid #cbd5e1;
            border-radius:7px;color:var(--text);font-size:.73rem;
            padding:.32rem .6rem;outline:none;cursor:pointer;
            transition:border-color .2s,background .2s;
            max-width:145px;
        }
        .f-select:focus{border-color:var(--primary);}
        .f-select.active{border-color:var(--primary);background:#f0fdfa;color:var(--primary);font-weight:600;}

        /* Text / date inputs */
        .f-input{
            background:#ffffff;border:1px solid #cbd5e1;
            border-radius:7px;color:var(--text);font-size:.73rem;
            padding:.32rem .6rem;outline:none;transition:.2s;font-family:'Inter',sans-serif;
        }
        .f-input:focus{border-color:var(--primary);}
        .f-input.active{border-color:var(--primary);background:#f0fdfa;}

        /* Search box */
        .search-wrap{
            display:flex;align-items:center;gap:.35rem;
            background:#ffffff;border:1px solid #cbd5e1;
            border-radius:7px;padding:.28rem .65rem;
        }
        .search-wrap.active{border-color:var(--primary);}
        .search-wrap input{background:none;border:none;outline:none;color:var(--text);font-size:.73rem;width:160px;font-family:'Inter',sans-serif;}
        .search-wrap input::placeholder{color:var(--faint);}

        /* Active chips */
        .chips-bar{
            display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;
            padding:.4rem 1.5rem;
            background:#f1f5f9;border-bottom:1px solid var(--border);
        }
        .chip{
            display:inline-flex;align-items:center;gap:.3rem;
            background:#f0fdfa;border:1px solid #99f6e4;
            color:var(--primary);border-radius:999px;
            font-size:.68rem;font-weight:600;padding:.15rem .6rem;
        }
        .chip-remove{color:var(--primary);opacity:.7;font-size:.72rem;cursor:pointer;}
        .chip-remove:hover{opacity:1;}

        /* badge + alert */
        .badge{display:inline-flex;align-items:center;padding:.18rem .55rem;border-radius:999px;font-size:.68rem;font-weight:700;}
        .badge-pass{background:rgba(16,185,129,.12);color:#047857;border:1px solid rgba(16,185,129,.35);}
        .badge-fail{background:rgba(239,68,68,.12);color:#b91c1c;border:1px solid rgba(239,68,68,.35);}
        .badge-pending{background:rgba(245,158,11,.12);color:#b45309;border:1px solid rgba(245,158,11,.35);}

        .alert{padding:.6rem 1.1rem;border-radius:8px;font-size:.8rem;margin:.5rem 1.5rem;display:flex;align-items:center;gap:.5rem;}
        .alert-success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#047857;}
        .alert-danger{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#b91c1c;}
        .form-errors{display:none;margin:0 0 .9rem;padding:.65rem .8rem;border-radius:8px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.35);color:#b91c1c;font-size:.78rem;}
        .form-errors.show{display:block;}
        .form-errors div+div{margin-top:.2rem;}

        /* ====================================================
           EXCEL TABLE
        ==================================================== */
        .sheet-wrap{overflow-x:auto;overflow-y:auto;max-height:calc(100vh - 200px);}
        .sheet{border-collapse:collapse;width:max-content;min-width:100%;font-size:12px;}

        .sheet th.chk-col, .sheet td.chk-col{width:36px;min-width:36px;text-align:center;background:#f8fafc;position:sticky;left:0;z-index:4;border-right:1px solid var(--border)!important;}
        .sheet th.rn,.sheet td.rn{width:40px;min-width:40px;background:#f8fafc;color:var(--muted);font-weight:500;text-align:center;border-right:2px solid var(--border)!important;position:sticky;left:36px;z-index:4;font-size:10.5px;}

        .sheet thead th{background:#f1f5f9;color:#334155;font-weight:600;padding:.5rem .65rem;border:1px solid #cbd5e1;white-space:nowrap;position:sticky;top:0;z-index:3;letter-spacing:.04em;font-size:10.5px;text-transform:uppercase;user-select:none;}
        .sheet thead th.col-date{min-width:100px;}
        .sheet thead th.col-name{min-width:130px;}
        .sheet thead th.col-batch{min-width:95px;}
        .sheet thead th.col-analysis{min-width:120px;}
        .sheet thead th.col-pres{min-width:75px;}
        .sheet thead th.col-col{min-width:105px;}
        .sheet thead th.col-instr{min-width:100px;}
        .sheet thead th.col-num{min-width:85px;}
        .sheet thead th.col-purity{min-width:85px;}
        .sheet thead th.col-result{min-width:85px;}
        .sheet thead th.col-done{min-width:110px;}
        .sheet thead th.col-rem{min-width:130px;}
        .sheet thead th.col-actions{min-width:105px;position:sticky;right:0;z-index:5;background:#f8fafc;}

        .sheet tbody tr{transition:background .12s;}
        .sheet tbody tr:hover{background:rgba(15,118,110,.05);}
        .sheet tbody tr.selected-row{background:rgba(225,29,72,.08)!important;}
        .sheet tbody td{padding:0;border:1px solid var(--border);vertical-align:middle;}

        .row-checkbox{accent-color:var(--danger);cursor:pointer;width:15px;height:15px;}

        .cell{display:block;padding:.38rem .6rem;min-height:28px;outline:none;cursor:default;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;}
        .cell[contenteditable="true"]{cursor:text;background:rgba(15,118,110,.08);border:2px solid var(--primary)!important;box-shadow:0 0 0 3px rgba(15,118,110,.15);white-space:normal;z-index:10;position:relative;}
        .cell-num{text-align:right;font-variant-numeric:tabular-nums;}
        .cell-purity{font-weight:700;color:var(--primary);text-align:right;font-variant-numeric:tabular-nums;}

        td.col-actions{position:sticky;right:0;background:#ffffff;border-left:2px solid var(--border)!important;z-index:2;}
        .action-btns{display:flex;gap:.28rem;padding:.22rem .35rem;justify-content:center;}

        .empty-row td{text-align:center;padding:3.5rem;color:var(--faint);}
        .empty-icon{font-size:2.5rem;margin-bottom:.75rem;}

        .purity-high{color:#047857;}
        .purity-mid {color:#b45309;}
        .purity-low {color:#b91c1c;}

        /* pagination */
        .pagination-wrap{display:flex;align-items:center;gap:.4rem;padding:.65rem 1.5rem;background:#ffffff;border-top:1px solid var(--border);font-size:.75rem;color:var(--muted);}
        .pg-btn{padding:.28rem .6rem;border-radius:5px;background:#ffffff;border:1px solid #cbd5e1;color:var(--text);cursor:pointer;font-size:.72rem;}
        .pg-btn:hover{background:#f1f5f9;border-color:var(--primary);color:var(--primary);}
        .pg-btn.active{background:var(--primary);color:#fff;border-color:var(--primary);}
        .pg-btn:disabled{opacity:.4;cursor:default;}

        /* modal */
        .modal-overlay{display:none;position:fixed;inset:0;z-index:999;background:rgba(15,23,42,.45);backdrop-filter:blur(4px);align-items:center;justify-content:center;}
        .modal-overlay.open{display:flex;}
        .modal{background:#ffffff;border:1px solid var(--border);border-radius:14px;padding:1.65rem;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 45px rgba(0,0,0,.15);animation:fadeUp .22s ease;}
        @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
        .modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;}
        .modal-title{font-family:'Outfit',sans-serif;font-size:1rem;font-weight:700;color:var(--text);}
        .modal-close{background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.1rem;line-height:1;}
        .modal-close:hover{color:var(--text);}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
        .form-full{grid-column:1/-1;}
        .fld label{display:block;font-size:.7rem;font-weight:600;color:var(--muted);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em;}
        .fld input,.fld select,.fld textarea{width:100%;background:#ffffff;border:1px solid #cbd5e1;border-radius:7px;color:var(--text);font-family:'Inter',sans-serif;font-size:.82rem;padding:.48rem .7rem;outline:none;transition:.2s;}
        .fld input:focus,.fld select:focus,.fld textarea:focus{border-color:var(--primary);box-shadow:0 0 0 2px rgba(15,118,110,.15);}
        .fld textarea{resize:vertical;min-height:55px;}
        .purity-preview{background:#f0fdfa;border:1px solid #99f6e4;border-radius:7px;padding:.48rem .7rem;font-size:.85rem;color:var(--primary);font-weight:700;min-height:32px;display:flex;align-items:center;}
        .view-value{background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;color:var(--text);font-size:.82rem;padding:.48rem .7rem;min-height:34px;white-space:pre-wrap;word-break:break-word;}
        .modal-footer{display:flex;justify-content:flex-end;gap:.65rem;margin-top:1.1rem;}

        ::-webkit-scrollbar{width:6px;height:6px;}
        ::-webkit-scrollbar-track{background:var(--bg);}
        ::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}
    </style>
    <link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="{{ asset('css/datatable.css') }}" rel="stylesheet">
</head>
<body>

<div class="admin-wrapper">

    @include('partials.admin-sidebar', ['active' => 'analysis'])

    <x-admin-header title="Analysis Sheet" search-mode="focus" />

    <main class="admin-main">

{{-- ============================================================
     YAJRA DATA SHEET
============================================================ --}}
<div class="sheet-card" id="datasheet">
<form id="filter-form" autocomplete="off" onsubmit="return false;">
    <div class="sheet-toolbar">
        <div class="sheet-toolbar-top">
            <div class="sheet-title">
                <h2>Data Sheet</h2>
                <p>Sort, search, and page on the server. Double-click a cell to edit it, or use View for the full record.</p>
            </div>
            <div class="sheet-toolbar-actions">
                <button type="button" class="sheet-btn sheet-btn-pdf" id="export-pdf-btn" onclick="submitPdfExport()">
                    Export PDF (<span id="selected-count">0</span>)
                </button>
                <button type="button" class="sheet-btn" id="export-btn" onclick="exportExcel()">Export Excel</button>
                <a href="{{ route('analysis.import') }}" class="sheet-btn" id="import-btn">Import</a>
                <button type="button" onclick="openAddModal()" class="sheet-btn sheet-btn-primary" id="add-new-btn">Add Row</button>
            </div>
        </div>

        <div class="sheet-filters">
            <button type="button" class="sheet-btn" id="open-filter-btn" onclick="openFilterModal()">
                Search
                <span class="filter-count" id="filter-count" hidden>0</span>
            </button>
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
                    <label for="date-from">From date</label>
                    <input type="date" name="date_from" id="date-from" class="f-input" value="{{ request('date_from') }}">
                </div>
                <div class="fld">
                    <label for="date-to">To date</label>
                    <input type="date" name="date_to" id="date-to" class="f-input" value="{{ request('date_to') }}">
                </div>
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
                    <label for="filter-result">Result</label>
                    <select name="filter_result" id="filter-result" class="f-select">
                        <option value="">All results</option>
                        @foreach(collect($filterOptions['results'] ?? [])->merge(['PASS', 'FAIL', 'Pending'])->filter()->unique(fn ($value) => strtoupper((string) $value))->sort() as $v)
                            <option value="{{ $v }}" {{ request('filter_result') == $v ? 'selected' : '' }}>{{ $v }}</option>
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
        <form action="{{ route('analysis.save-settings') }}" method="POST">
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
                    <label>Logo Image URL / Path</label>
                    <input type="text" name="logo_url" value="{{ $settings['logo_url'] ?? '' }}" placeholder="https://example.com/logo.png or empty for default text logo">
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
{!! $dataTable->scripts() !!}
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const selectedIds = new Set();

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
    ['date','sample_name','batch_no','analysis','pressure','column_name','instrument','done_by','std_con','std_area','sample_con','sample_area','purity','result','specification','sample_submitted_by','remarks'].forEach(key => {
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
</main>
</div>
</body>
</html>
