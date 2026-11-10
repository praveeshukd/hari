<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel – QC Analysis</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:      #0d1117;
            --bg2:     #161b22;
            --bg3:     #1c2128;
            --border:  #30363d;
            --text:    #e6edf3;
            --muted:   #8b949e;
            --faint:   #484f58;
            --primary: #6366f1;
            --success: #22c55e;
            --danger:  #f43f5e;
            --warning: #f59e0b;
            --cyan:    #06b6d4;
            --grad:    linear-gradient(135deg,#6366f1,#a78bfa,#06b6d4);
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

        /* NAV */
        .topbar{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.5rem;background:var(--bg2);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;}
        .brand{display:flex;align-items:center;gap:.6rem;font-family:'Outfit',sans-serif;font-size:1rem;font-weight:700;}
        .brand-icon{width:30px;height:30px;border-radius:8px;background:var(--grad);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.85rem;color:#fff;}
        .brand span{background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;border-radius:6px;font-size:.82rem;font-weight:600;border:none;cursor:pointer;transition:all .2s;text-decoration:none;white-space:nowrap;}
        .btn-primary{background:var(--grad);color:#fff;box-shadow:0 2px 12px rgba(99,102,241,.3);}
        .btn-primary:hover{filter:brightness(1.1);transform:translateY(-1px);}
        .btn-outline{background:transparent;color:var(--text);border:1px solid var(--border);}
        .btn-outline:hover{background:var(--bg3);}
        .btn-success{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;}
        .btn-success:hover{filter:brightness(1.1);}
        .btn-sm{padding:.3rem .7rem;font-size:.78rem;}

        /* LAYOUT */
        .page{max-width:780px;margin:0 auto;padding:2.5rem 1.5rem;}

        /* HEADER */
        .page-header{margin-bottom:2rem;}
        .page-header h1{font-family:'Outfit',sans-serif;font-size:1.6rem;font-weight:800;margin-bottom:.4rem;}
        .page-header p{color:var(--muted);font-size:.9rem;}
        .breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:var(--faint);margin-bottom:.75rem;}
        .breadcrumb a{color:var(--muted);transition:.2s;}
        .breadcrumb a:hover{color:var(--text);}

        /* CARDS */
        .card{background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:1.75rem;margin-bottom:1.25rem;}
        .card-title{font-family:'Outfit',sans-serif;font-size:.95rem;font-weight:700;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}

        /* DROP ZONE */
        .dropzone{
            border:2px dashed var(--border);border-radius:12px;
            padding:2.5rem;text-align:center;cursor:pointer;
            transition:all .3s ease;position:relative;
            background:rgba(255,255,255,.02);
        }
        .dropzone:hover,.dropzone.dragover{
            border-color:var(--primary);
            background:rgba(99,102,241,.06);
        }
        .dropzone input[type="file"]{
            position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;
        }
        .dz-icon{font-size:3rem;margin-bottom:1rem;filter:grayscale(.5);}
        .dz-title{font-size:1rem;font-weight:600;margin-bottom:.4rem;}
        .dz-sub{color:var(--muted);font-size:.82rem;margin-bottom:1rem;}
        .dz-types{display:flex;justify-content:center;gap:.5rem;flex-wrap:wrap;}
        .dz-badge{padding:.15rem .6rem;border-radius:4px;font-size:.72rem;font-weight:700;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.25);color:var(--primary);}

        /* SELECTED FILE */
        .selected-file{
            display:none;align-items:center;gap:.75rem;
            padding:.75rem 1rem;border-radius:8px;
            background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);
            margin-top:1rem;
        }
        .selected-file.show{display:flex;}
        .file-icon{font-size:1.4rem;}
        .file-name{font-size:.88rem;font-weight:600;word-break:break-all;}
        .file-size{font-size:.75rem;color:var(--muted);}

        /* ALERTS */
        .alert{padding:.75rem 1rem;border-radius:8px;font-size:.83rem;margin-bottom:1rem;display:flex;align-items:flex-start;gap:.5rem;}
        .alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#86efac;}
        .alert-danger{background:rgba(244,63,94,.1);border:1px solid rgba(244,63,94,.25);color:#fda4af;}
        .alert-warning{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#fde68a;}

        /* STEPS */
        .steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
        .step{text-align:center;padding:1.25rem;background:var(--bg3);border:1px solid var(--border);border-radius:10px;}
        .step-num{width:36px;height:36px;border-radius:50%;background:var(--grad);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-family:'Outfit',sans-serif;font-weight:900;font-size:.95rem;color:#fff;box-shadow:0 0 16px rgba(99,102,241,.35);}
        .step h4{font-size:.85rem;font-weight:700;margin-bottom:.3rem;}
        .step p{font-size:.75rem;color:var(--muted);line-height:1.6;}

        /* COLUMN MAP */
        .col-map{display:grid;grid-template-columns:1fr 1fr;gap:.5rem .75rem;font-size:.78rem;}
        .col-row{display:contents;}
        .col-key{font-weight:700;padding:.3rem .6rem;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);border-radius:4px;color:var(--primary);}
        .col-val{color:var(--muted);padding:.3rem .4rem;border-bottom:1px dashed var(--border2);}

        /* PROGRESS */
        .upload-progress{display:none;margin-top:1rem;}
        .upload-progress.show{display:block;}
        .progress-bar{height:6px;background:var(--border);border-radius:999px;overflow:hidden;margin-bottom:.5rem;}
        .progress-fill{height:100%;border-radius:999px;background:var(--grad);width:0%;transition:width .3s ease;}
        .progress-text{font-size:.78rem;color:var(--muted);text-align:center;}
    </style>
</head>
<body>

<nav class="topbar">
    <div class="brand">
        <div class="brand-icon">Q</div>
        <span>QC Analysis</span>
    </div>
    <div style="display:flex;gap:.6rem;">
        <a href="{{ route('analysis.index') }}" class="btn btn-outline btn-sm">← Back to Sheet</a>
        <a href="{{ route('analysis.export') }}" class="btn btn-outline btn-sm">📥 Export</a>
    </div>
</nav>

<div class="page">

    {{-- Breadcrumb --}}
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a> /
        <a href="{{ route('analysis.index') }}">QC Analysis</a> /
        <span>Import</span>
    </div>

    {{-- Header --}}
    <div class="page-header">
        <h1>📤 Import Excel / CSV</h1>
        <p>Upload your HPLC analysis data from an Excel (.xlsx) or CSV (.csv) file. Purity will be auto-calculated on import.</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger">
        <div>@foreach($errors->all() as $e) <div>⚠ {{ $e }}</div> @endforeach</div>
    </div>
    @endif

    {{-- Steps --}}
    <div class="steps">
        <div class="step">
            <div class="step-num">1</div>
            <h4>Download Template</h4>
            <p>Get the official template with correct column headers pre-filled.</p>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <h4>Fill Your Data</h4>
            <p>Add your HPLC data rows under the headers. Do not change column names.</p>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <h4>Upload & Import</h4>
            <p>Drag & drop or browse your file below. Purity is auto-calculated.</p>
        </div>
    </div>

    {{-- Template Download --}}
    <div class="card">
        <div class="card-title">📋 Step 1: Download Template</div>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <div style="font-size:.88rem;margin-bottom:.3rem;font-weight:600;">QC_Analysis_Template.xlsx</div>
                <div style="font-size:.78rem;color:var(--muted);">Pre-formatted Excel template with all 15 column headers. Fill and re-upload.</div>
            </div>
            <a href="{{ route('analysis.template') }}" class="btn btn-outline" id="template-download-btn">
                ⬇️ Download Template
            </a>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="card">
        <div class="card-title">📤 Step 3: Upload File</div>

        <form action="{{ route('analysis.import.store') }}" method="POST" enctype="multipart/form-data" id="import-form">
        @csrf

        <div class="dropzone" id="dropzone">
            <input type="file" name="file" id="file-input" accept=".xlsx,.xls,.csv" required>
            <div class="dz-icon" id="dz-icon">📂</div>
            <div class="dz-title" id="dz-title">Drop your file here, or click to browse</div>
            <div class="dz-sub" id="dz-sub">Supports Excel (.xlsx, .xls) and CSV (.csv) — max 10MB</div>
            <div class="dz-types">
                <span class="dz-badge">XLSX</span>
                <span class="dz-badge">XLS</span>
                <span class="dz-badge">CSV</span>
            </div>
        </div>

        <div class="selected-file" id="selected-file">
            <span class="file-icon" id="file-icon">📊</span>
            <div>
                <div class="file-name" id="file-name"></div>
                <div class="file-size" id="file-size"></div>
            </div>
            <button type="button" onclick="clearFile()" style="margin-left:auto;background:none;border:none;color:var(--muted);cursor:pointer;font-size:1rem;">✕</button>
        </div>

        <div class="upload-progress" id="upload-progress">
            <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
            <div class="progress-text" id="progress-text">Uploading…</div>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;">
            <a href="{{ route('analysis.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-success" id="import-btn" disabled>
                📤 Import Data
            </button>
        </div>
        </form>
    </div>

    {{-- Column Mapping Reference --}}
    <div class="card">
        <div class="card-title">🗺️ Column Name Reference</div>
        <p style="font-size:.8rem;color:var(--muted);margin-bottom:1rem;">
            Your file's column headers are matched flexibly. Here are all accepted header names:
        </p>
        <div class="col-map">
            <div class="col-key">Date</div>
            <div class="col-val">date, Date</div>
            <div class="col-key">Sample Name</div>
            <div class="col-val">sample_name, Sample Name</div>
            <div class="col-key">Batch No</div>
            <div class="col-val">batch_no, Batch No, batchno</div>
            <div class="col-key">Analysis</div>
            <div class="col-val">analysis, Analysis</div>
            <div class="col-key">Pressure</div>
            <div class="col-val">pressure, Pressure</div>
            <div class="col-key">Column</div>
            <div class="col-val">column, Column, column_name</div>
            <div class="col-key">Instrument</div>
            <div class="col-val">instrument, Instrument</div>
            <div class="col-key">Std Concentration</div>
            <div class="col-val">std_con, Std Con, Std Concentration</div>
            <div class="col-key">Std Area</div>
            <div class="col-val">std_area, Std Area</div>
            <div class="col-key">Sample Concentration</div>
            <div class="col-val">sample_con, Sample Con, Sample Concentration</div>
            <div class="col-key">Sample Area</div>
            <div class="col-val">sample_area, Sample Area</div>
            <div class="col-key">Result</div>
            <div class="col-val">result, Result</div>
            <div class="col-key">Done By</div>
            <div class="col-val">done_by, Done By, reviewed_by</div>
            <div class="col-key">Remarks</div>
            <div class="col-val">remarks, Remarks, notes</div>
        </div>
        <div class="alert alert-warning" style="margin-top:1rem;margin-bottom:0;">
            ⚠ <strong>Purity %</strong> is automatically calculated on import — you do not need a Purity column in your file.
        </div>
    </div>

</div>

<script>
const fileInput    = document.getElementById('file-input');
const dropzone     = document.getElementById('dropzone');
const selectedFile = document.getElementById('selected-file');
const importBtn    = document.getElementById('import-btn');

fileInput.addEventListener('change', () => showFile(fileInput.files[0]));

// Drag and drop
dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('dragover'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if(file) { fileInput.files = e.dataTransfer.files; showFile(file); }
});

function showFile(file) {
    if(!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    const icons = {xlsx:'📊', xls:'📊', csv:'📋'};
    document.getElementById('file-icon').textContent = icons[ext] || '📄';
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = (file.size/1024).toFixed(1) + ' KB';
    selectedFile.classList.add('show');
    importBtn.disabled = false;
    document.getElementById('dz-icon').textContent = '✅';
    document.getElementById('dz-title').textContent = 'File selected';
    document.getElementById('dz-sub').textContent = file.name;
}

function clearFile() {
    fileInput.value = '';
    selectedFile.classList.remove('show');
    importBtn.disabled = true;
    document.getElementById('dz-icon').textContent = '📂';
    document.getElementById('dz-title').textContent = 'Drop your file here, or click to browse';
    document.getElementById('dz-sub').textContent = 'Supports Excel (.xlsx, .xls) and CSV (.csv) — max 10MB';
}

// Progress simulation on submit
document.getElementById('import-form').addEventListener('submit', function() {
    importBtn.disabled = true;
    importBtn.textContent = 'Importing…';
    const progress = document.getElementById('upload-progress');
    const fill     = document.getElementById('progress-fill');
    const text     = document.getElementById('progress-text');
    progress.classList.add('show');

    let pct = 0;
    const interval = setInterval(() => {
        pct = Math.min(pct + Math.random() * 15, 90);
        fill.style.width = pct + '%';
        text.textContent = 'Processing rows… ' + Math.round(pct) + '%';
    }, 300);
});
</script>
</body>
</html>
