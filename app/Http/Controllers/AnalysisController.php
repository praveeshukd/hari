<?php

namespace App\Http\Controllers;

use App\DataTables\AnalysisDataTable;
use App\Models\Analysis;
use App\Exports\AnalysisExport;
use App\Imports\AnalysisImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AnalysisController extends Controller
{
    /* ─────────────────────────────── INDEX ────────────────────────────── */
    public function index(Request $request, AnalysisDataTable $dataTable)
    {
        if ($request->ajax() && $request->wantsJson()) {
            return $dataTable->ajax();
        }

        $filterOptions = [
            'sample_names' => Analysis::select('sample_name')->distinct()->orderBy('sample_name')->pluck('sample_name'),
            'batch_nos'    => Analysis::select('batch_no')->distinct()->orderBy('batch_no')->pluck('batch_no'),
            'analyses'     => Analysis::select('analysis')->distinct()->orderBy('analysis')->pluck('analysis'),
            'instruments'  => Analysis::select('instrument')->distinct()->orderBy('instrument')->pluck('instrument'),
            'columns'      => Analysis::select('column_name')->distinct()->orderBy('column_name')->pluck('column_name'),
            'done_bys'     => Analysis::select('done_by')->whereNotNull('done_by')->distinct()->orderBy('done_by')->pluck('done_by'),
            'results'      => Analysis::select('result')->whereNotNull('result')->where('result', '!=', '')->distinct()->orderBy('result')->pluck('result'),
        ];

        $settings = $this->getSettings();

        return $dataTable->render('analysis.index', [
            'filterOptions' => $filterOptions,
            'settings' => $settings,
        ]);
    }

    public function show(Analysis $analysis)
    {
        return response()->json($analysis);
    }



    /* ─────────────────────────── CREATE / STORE ────────────────────────── */
    public function create()
    {
        return view('analysis.create');
    }

    public function store(Request $request)
    {
        $record = Analysis::create($this->validated($request));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Record added successfully.',
                'record' => [
                    'id' => $record->id,
                    'purity' => $record->purity,
                ],
            ]);
        }

        return redirect()->route('analysis.index')
            ->with('success', 'Record added successfully.');
    }

    /* ─────────────────────────── EDIT / UPDATE ─────────────────────────── */
    public function edit(Analysis $analysis)
    {
        return view('analysis.edit', compact('analysis'));
    }

    public function update(Request $request, Analysis $analysis)
    {
        $analysis->update($this->validated($request));
        $analysis->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Record updated successfully.',
                'record' => [
                    'id' => $analysis->id,
                    'purity' => $analysis->purity,
                ],
            ]);
        }

        return redirect()->route('analysis.index')
            ->with('success', 'Record updated successfully.');
    }

    /* ───────────────────────────── DELETE ──────────────────────────────── */
    public function destroy(Analysis $analysis)
    {
        $analysis->delete();

        return redirect()->route('analysis.index')
            ->with('success', 'Record deleted successfully.');
    }

    /* ─────────────────────── QUICK INLINE UPDATE (AJAX) ────────────────── */
    public function quickUpdate(Request $request, Analysis $analysis)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $allowed = [
            'date','sample_name','batch_no','analysis','pressure',
            'column_name','instrument','std_con','std_area',
            'sample_con','sample_area','result','done_by','remarks',
            'specification','sample_submitted_by'
        ];

        if (!in_array($field, $allowed)) {
            return response()->json(['error' => 'Invalid field'], 422);
        }

        $analysis->$field = $value;
        $analysis->save();

        return response()->json([
            'success' => true,
            'purity'  => $analysis->purity,
        ]);
    }

    /* ─────────────────────────── EXPORT EXCEL ──────────────────────────── */
    public function export(Request $request)
    {
        $filename = 'QC_Analysis_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new AnalysisExport($request->only(Analysis::FILTER_KEYS)),
            $filename
        );
    }

    /* ─────────────────────────── IMPORT VIEW ───────────────────────────── */
    public function importView()
    {
        return view('analysis.import');
    }

    /* ────────────────────────── IMPORT PROCESS ─────────────────────────── */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $importer = new AnalysisImport();
        Excel::import($importer, $request->file('file'));

        $msg = "Import complete — {$importer->imported} records imported.";
        if ($importer->skipped) {
            $msg .= " {$importer->skipped} rows skipped.";
        }

        return redirect()->route('analysis.index')
            ->with('success', $msg)
            ->with('import_errors', $importer->errors);
    }

    /* ─────────────────────────── EXPORT PDF ──────────────────────────── */
    public function exportPdf(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }

        $query = Analysis::query();

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            $query->filtered($request->only(Analysis::FILTER_KEYS));
        }

        $records = $query->orderByDesc('date')->orderByDesc('id')->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'No records selected for PDF export.');
        }

        $settings = $this->getSettings();

        // Check if dompdf is available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analysis.pdf', compact('records', 'settings'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('Sample_Test_Report_' . now()->format('Y-m-d') . '.pdf');
        }

        // Printable HTML fallback if dompdf extension not loaded in CLI
        return view('analysis.pdf', compact('records', 'settings'));
    }

    /* ─────────────────────── SAVE REPORT SETTINGS ───────────────────── */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'company_name'    => 'required|string|max:255',
            'company_tagline' => 'nullable|string|max:255',
            'document_name'   => 'required|string|max:255',
            'request_prefix'  => 'required|string|max:50',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
        ]);

        $settings = $this->getSettings();
        $settings['company_name']    = $request->company_name;
        $settings['company_tagline'] = $request->company_tagline ?? 'Next Gen Ingredients';
        $settings['document_name']   = $request->document_name;
        $settings['request_prefix']  = $request->request_prefix;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $logoData = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            $settings['logo_url'] = $logoData;
        }

        $path = storage_path('app/pdf_settings.json');
        file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT));

        return redirect()->back()->with('success', 'PDF Logo & Header Settings updated successfully!');
    }

    public function getSettings(): array
    {
        $path = storage_path('app/pdf_settings.json');
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) return $data;
        }

        return [
            'company_name'    => 'MOLECULES BIOLABS PRIVATE LIMITED',
            'company_tagline' => 'Next Gen Ingredients',
            'document_name'   => 'SAMPLE TEST REPORT',
            'request_prefix'  => 'STR',
            'logo_url'        => null,
        ];
    }

    /* ─────────────────────── DOWNLOAD BLANK TEMPLATE ───────────────────── */
    public function template()
    {
        return Excel::download(new AnalysisExport(), 'QC_Analysis_Template.xlsx');
    }

    /* ─────────────────────── SHARED VALIDATION RULES ───────────────────── */
    private function validated(Request $request): array
    {
        $this->normalizePressure($request);

        $data = $request->validate([
            'date'        => 'required|date',
            'sample_name' => 'required|string|max:255',
            'batch_no'    => 'required|string|max:100',
            'analysis'    => 'required|string|max:255',
            'pressure'    => 'nullable|numeric',
            'column_name' => 'nullable|string|max:255',
            'instrument'  => 'nullable|string|max:255',
            'std_con'     => 'nullable|numeric',
            'std_area'    => 'nullable|numeric',
            'sample_con'  => 'nullable|numeric',
            'sample_area' => 'nullable|numeric',
            'result'      => 'nullable|string|max:100',
            'done_by'     => 'nullable|string|max:255',
            'remarks'     => 'nullable|string',
            'specification'       => 'nullable|string|max:255',
            'sample_submitted_by' => 'nullable|string|max:255',
        ]);

        // These columns are NOT NULL. The add form treats them as optional.
        $data['column_name'] = $data['column_name'] ?? '';
        $data['instrument'] = $data['instrument'] ?? '';

        return $data;
    }

    /**
     * The pressure field is free text ("145 bar"). Keep the numeric column
     * by storing the first number in the value.
     */
    private function normalizePressure(Request $request): void
    {
        $pressure = $request->input('pressure');
        if (! is_string($pressure)) {
            return;
        }

        $pressure = trim($pressure);
        if ($pressure === '') {
            $request->merge(['pressure' => null]);

            return;
        }

        if (is_numeric($pressure)) {
            return;
        }

        if (preg_match('/-?\d+(?:\.\d+)?/', str_replace(',', '', $pressure), $match)) {
            $request->merge(['pressure' => $match[0]]);
        }
    }
}

