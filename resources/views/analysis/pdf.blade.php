<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $settings['document_name'] ?? 'Sample Test Report' }}</title>
<style>
    @page { margin: 12mm 10mm 16mm 10mm; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10pt;
        color: #000;
        background: #fff;
        margin: 0;
        padding: 0;
    }

    table.sheet {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
    }

    table.sheet td {
        border: 1px solid #000;
        vertical-align: middle;
        font-size: 10pt;
        padding: 5px 8px;
    }

    .header-table { margin-bottom: 8pt; }

    .logo-cell {
        text-align: center;
        padding: 8px 6px;
    }
    .logo-img { width: 140px; height: 45px; }

    table.sheet td.company-cell {
        text-align: center;
        font-size: 16pt;
        font-weight: bold;
        line-height: 1.35;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        padding: 12px 8px;
    }

    .nest { padding: 0; }

    table.inner {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
    }
    table.inner td {
        border: none;
        border-left: 1px solid #000;
        vertical-align: middle;
        font-size: 10pt;
        padding: 6px 8px;
    }
    table.inner td.first { border-left: none; }

    table.inner td.doc-label { line-height: 1.3; padding: 8px; }
    table.inner td.doc-value {
        font-size: 12pt;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 8px;
    }

    table.inner td.ref-lbl { text-align: center; font-weight: bold; }
    table.inner td.ref-val { text-align: center; font-weight: bold; font-size: 11pt; }

    table.sheet td.col-head { font-weight: bold; text-align: left; padding: 6px 8px; }
    table.sheet td.val { text-align: center; padding: 11px 6px; }

    .remarks {
        margin-top: 20pt;
        padding-left: 28pt;
        font-size: 10pt;
    }
    .remarks-lbl { font-weight: bold; }
    .remarks-txt { margin-top: 4pt; }

    .sig-table {
        width: 100%;
        margin-top: 125pt;
        border-collapse: collapse;
    }
    .sig-table td {
        border: none;
        font-size: 10pt;
        padding: 0;
    }
    .sig-left { text-align: left; padding-left: 28pt; }
    .sig-right { text-align: right; padding-right: 42pt; }

    .page-break { page-break-after: always; }
</style>
</head>
<body>
@php
    $company = trim($settings['company_name'] ?? "MOLECULES BIOLABS\nPRIVATE LIMITED");
    if (!str_contains($company, "\n")) {
        $company = preg_replace('/\s+(PRIVATE\s+LIMITED)\s*$/iu', "\n$1", $company) ?: $company;
    }

    $logoSrc = $settings['logo_url'] ?? null;
    if (empty($logoSrc)) {
        $png = public_path('images/molecules-logo-dark.png');
        if (is_file($png)) {
            $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($png));
        }
    }
@endphp

@foreach($records as $row)
@php
    $formNo = filled($row->request_form_no)
        ? $row->request_form_no
        : (($settings['request_prefix'] ?? 'STR')
            . ($row->date?->format('y') ?? '')
            . '/'
            . ($row->batch_no ?? str_pad((string) $row->id, 3, '0', STR_PAD_LEFT)));
@endphp
<div class="page {{ !$loop->last ? 'page-break' : '' }}">

    <table class="sheet header-table" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="logo-cell" width="28%" rowspan="2">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Molecules" class="logo-img" width="140" height="45">
                @else
                    <div style="font-size:14pt;font-weight:bold;color:#12324F;">Molecules</div>
                    <div style="font-size:7pt;color:#12324F;">{{ $settings['company_tagline'] ?? 'Next Gen Ingredients' }}</div>
                @endif
            </td>
            <td class="company-cell" width="72%">{!! nl2br(e($company)) !!}</td>
        </tr>
        <tr>
            <td class="nest">
                <table class="inner" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="first doc-label" width="24%">Document<br>Name</td>
                        <td class="doc-value" width="76%">{{ $settings['document_name'] ?? 'SAMPLE TEST REPORT' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="nest" colspan="2">
                <table class="inner" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="first" width="28%">Sample Received Date</td>
                        <td width="17%">{{ $row->date?->format('d/m/Y') }}</td>
                        <td class="ref-lbl" width="31%">Request Form No</td>
                        <td class="ref-val" width="24%">{{ $formNo }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="sheet" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="col-head" width="28%">Sample Name</td>
            <td class="col-head" width="26%">Batch No/QC No</td>
            <td class="col-head" width="22%">Result</td>
            <td class="col-head" width="24%">Specification</td>
        </tr>
        <tr>
            <td class="val">{{ strtoupper($row->sample_name) }}</td>
            <td class="val">{{ $row->batch_no }}</td>
            <td class="val">
                @if($row->purity)
                    {{ number_format($row->purity, 2) }}%
                @else
                    {{ $row->result ?? '—' }}
                @endif
            </td>
            <td class="val">{{ $row->specification ?? '0.80%' }}</td>
        </tr>
        <tr>
            <td>Sample Submitted by</td>
            <td colspan="3">{{ $row->sample_submitted_by ?? ($row->done_by ?: 'Ann') }}</td>
        </tr>
        <tr>
            <td>Analysis</td>
            <td colspan="3">{{ $row->analysis }}</td>
        </tr>
        <tr>
            <td>Completed date</td>
            <td colspan="3">{{ $row->date?->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td>Done by</td>
            <td colspan="3">{{ $row->done_by }}</td>
        </tr>
    </table>

    <div class="remarks">
        <span class="remarks-lbl">Remarks :</span>
        @if(filled($row->remarks))
            <div class="remarks-txt">{{ $row->remarks }}</div>
        @endif
    </div>

    <table class="sig-table" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="sig-left" width="50%">Prepared by:</td>
            <td class="sig-right" width="50%">Approved by:</td>
        </tr>
    </table>

</div>
@endforeach
</body>
</html>
