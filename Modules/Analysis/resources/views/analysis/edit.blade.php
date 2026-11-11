<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Analysis – {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        .edit-page { min-height:100vh; background:var(--clr-bg); padding-top:5rem; }
        .edit-card {
            max-width:780px; margin:0 auto; padding:2rem;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);
            border-radius:18px;
        }
        .edit-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; }
        .edit-title  { font-family:'Outfit',sans-serif; font-size:1.35rem; font-weight:800; }
        .edit-subtitle { color:var(--clr-text-muted); font-size:.85rem; margin-top:.25rem; }
        .form-grid  { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .form-full  { grid-column:1/-1; }
        .fld label  { display:block; font-size:.78rem; font-weight:600; color:var(--clr-text-muted); margin-bottom:.35rem; text-transform:uppercase; letter-spacing:.04em; }
        .fld input, .fld select, .fld textarea {
            width:100%; background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08); border-radius:8px;
            color:var(--clr-text); font-family:'Inter',sans-serif;
            font-size:.9rem; padding:.6rem .85rem; outline:none; transition:.2s;
        }
        .fld input:focus,.fld select:focus,.fld textarea:focus{
            border-color:var(--clr-primary); box-shadow:0 0 0 3px rgba(99,102,241,.15);
        }
        .fld textarea { resize:vertical; min-height:70px; }
        .purity-box {
            background:rgba(6,182,212,.08); border:1px solid rgba(6,182,212,.25);
            border-radius:8px; padding:.6rem .85rem; color:#06b6d4;
            font-size:1.1rem; font-weight:700; min-height:42px;
            display:flex; align-items:center;
        }
        .section-divider { grid-column:1/-1; border-top:1px solid rgba(255,255,255,.06); margin:.5rem 0; padding-top:.5rem; font-size:.72rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:rgba(255,255,255,.25); }
        .edit-footer { display:flex; justify-content:space-between; align-items:center; margin-top:1.5rem; flex-wrap:wrap; gap:.75rem; }
        .invalid-feedback { color:#f43f5e; font-size:.76rem; margin-top:.3rem; }
        input[type="date"]::-webkit-calendar-picker-indicator { filter:invert(1); opacity:.5; }
        select option { background:#1c2128; }
    </style>
</head>
<body>
@include('layouts._nav_mini')

<div class="edit-page">
<div style="max-width:780px;margin:0 auto;padding:0 1.5rem;">

    <div class="edit-card">
        <div class="edit-header">
            <div>
                <div class="edit-title">✏️ Edit Analysis Record</div>
                <div class="edit-subtitle">ID #{{ $analysis->id }} &nbsp;·&nbsp; {{ $analysis->sample_name }} &nbsp;·&nbsp; {{ $analysis->batch_no }}</div>
            </div>
            <a href="{{ route('analysis.index') }}" class="btn btn-outline btn-sm">← Back to Sheet</a>
        </div>

        @if($errors->any())
            <div style="background:rgba(244,63,94,.1);border:1px solid rgba(244,63,94,.25);border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.83rem;color:#fda4af;">
                @foreach($errors->all() as $e) <div>⚠ {{ $e }}</div> @endforeach
            </div>
        @endif

        <form action="{{ route('analysis.update', $analysis->id) }}" method="POST" id="edit-form">
        @csrf @method('PUT')
        <div class="form-grid">

            {{-- Basic Info --}}
            <div class="section-divider">📋 Sample Information</div>

            <div class="fld">
                <label>Date *</label>
                <input type="date" name="date" value="{{ old('date', $analysis->date?->format('Y-m-d')) }}" required>
                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="fld">
                <label>Sample Name *</label>
                <input type="text" name="sample_name" value="{{ old('sample_name', $analysis->sample_name) }}" required>
                @error('sample_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="fld">
                <label>Batch No *</label>
                <input type="text" name="batch_no" value="{{ old('batch_no', $analysis->batch_no) }}" required>
                @error('batch_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="fld">
                <label>Request Form No</label>
                <input type="text" name="request_form_no" value="{{ old('request_form_no', $analysis->request_form_no) }}" placeholder="e.g. STR26/B2024-001">
                @error('request_form_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="fld">
                <label>Analysis *</label>
                <input type="text" name="analysis" value="{{ old('analysis', $analysis->analysis) }}" required>
                @error('analysis') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Instrument --}}
            <div class="section-divider">🔬 Instrument Details</div>

            <div class="fld">
                <label>Pressure</label>
                <input type="number" step="any" name="pressure" value="{{ old('pressure', $analysis->pressure) }}" placeholder="psi / bar">
            </div>
            <div class="fld">
                <label>Column *</label>
                <input type="text" name="column_name" value="{{ old('column_name', $analysis->column_name) }}" required>
                @error('column_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="fld form-full">
                <label>Instrument *</label>
                <input type="text" name="instrument" value="{{ old('instrument', $analysis->instrument) }}" required>
                @error('instrument') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Calculations --}}
            <div class="section-divider">🧮 Calculation Data</div>

            <div class="fld">
                <label>Standard Concentration</label>
                <input type="number" step="any" name="std_con" id="e-std-con" value="{{ old('std_con', $analysis->std_con) }}" oninput="calcPurity()">
            </div>
            <div class="fld">
                <label>Standard Area</label>
                <input type="number" step="any" name="std_area" id="e-std-area" value="{{ old('std_area', $analysis->std_area) }}" oninput="calcPurity()">
            </div>
            <div class="fld">
                <label>Sample Concentration</label>
                <input type="number" step="any" name="sample_con" id="e-sample-con" value="{{ old('sample_con', $analysis->sample_con) }}" oninput="calcPurity()">
            </div>
            <div class="fld">
                <label>Sample Area</label>
                <input type="number" step="any" name="sample_area" id="e-sample-area" value="{{ old('sample_area', $analysis->sample_area) }}" oninput="calcPurity()">
            </div>
            <div class="fld">
                <label>Standard Purity</label>
                <input type="number" step="any" name="standard_purity" id="e-standard-purity" value="{{ old('standard_purity', $analysis->standard_purity) }}" placeholder="e.g. 99 or 98" oninput="calcPurity()">
                @error('standard_purity') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="fld form-full">
                <label>Purity % (Auto-Calculated)</label>
                <div class="purity-box" id="e-purity-preview">
                    {{ $analysis->purity ? number_format($analysis->purity,4).'%' : '—' }}
                </div>
            </div>

            {{-- Result --}}
            <div class="section-divider">✅ Result & Sign-off</div>

            <div class="fld">
                <label>Result</label>
                <select name="result">
                    <option value="">— Select —</option>
                    <option value="Pass"    {{ old('result', $analysis->result) === 'Pass'    ? 'selected' : '' }}>Pass</option>
                    <option value="Fail"    {{ old('result', $analysis->result) === 'Fail'    ? 'selected' : '' }}>Fail</option>
                    <option value="Pending" {{ old('result', $analysis->result) === 'Pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="fld">
                <label>Done By / Reviewed By</label>
                <input type="text" name="done_by" value="{{ old('done_by', $analysis->done_by) }}">
            </div>
            <div class="fld form-full">
                <label>Remarks</label>
                <textarea name="remarks">{{ old('remarks', $analysis->remarks) }}</textarea>
            </div>

        </div>{{-- /form-grid --}}

        <div class="edit-footer">
            <form action="{{ route('analysis.destroy', $analysis->id) }}" method="POST"
                  onsubmit="return confirm('Permanently delete this record?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">🗑 Delete Record</button>
            </form>
            <div style="display:flex;gap:.75rem;">
                <a href="{{ route('analysis.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">💾 Update Record</button>
            </div>
        </div>

        </form>
    </div>

</div>
</div>

<script>
function calcPurity(){
    const sc = parseFloat(document.getElementById('e-std-con').value);
    const sa = parseFloat(document.getElementById('e-std-area').value);
    const pc = parseFloat(document.getElementById('e-sample-con').value);
    const pa = parseFloat(document.getElementById('e-sample-area').value);
    const typedStd = parseFloat(document.getElementById('e-standard-purity').value);
    const factor = (typedStd > 0) ? typedStd : 100;
    const el = document.getElementById('e-purity-preview');
    if(sc && sa && pc && pa){
        const p = (pa/sa)*(sc/pc)*factor;
        el.textContent = p.toFixed(4)+'%';
        el.style.color = p>=99?'#4ade80':p>=95?'#fbbf24':'#fb7185';
    } else {
        el.textContent='—';
        el.style.color='#06b6d4';
    }
}
calcPurity(); // init on page load
</script>
</body>
</html>
