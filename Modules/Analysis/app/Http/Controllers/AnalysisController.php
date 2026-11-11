<?php

namespace Modules\Analysis\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Modules\Analysis\Actions\BulkDeleteAnalysesAction;
use Modules\Analysis\Actions\BulkUpdateResultAction;
use Modules\Analysis\Actions\CreateAnalysisAction;
use Modules\Analysis\Actions\DeleteAnalysisAction;
use Modules\Analysis\Actions\QuickUpdateAnalysisAction;
use Modules\Analysis\Actions\RestoreAnalysisAction;
use Modules\Analysis\Actions\UpdateAnalysisAction;
use Modules\Analysis\DataTables\AnalysisDataTable;
use Modules\Analysis\DTOs\AnalysisData;
use Modules\Analysis\DTOs\AnalysisFilters;
use Modules\Analysis\DTOs\QuickUpdateData;
use Modules\Analysis\Http\Requests\Analysis\BulkDeleteAnalysisRequest;
use Modules\Analysis\Http\Requests\Analysis\BulkUpdateResultRequest;
use Modules\Analysis\Http\Requests\Analysis\ImportAnalysisRequest;
use Modules\Analysis\Http\Requests\Analysis\QuickUpdateAnalysisRequest;
use Modules\Analysis\Http\Requests\Analysis\SavePdfSettingsRequest;
use Modules\Analysis\Http\Requests\Analysis\StoreAnalysisRequest;
use Modules\Analysis\Http\Requests\Analysis\UpdateAnalysisRequest;
use Modules\Analysis\Jobs\ImportAnalysesJob;
use Modules\Analysis\Models\Analysis;
use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Modules\Analysis\Services\AnalysisFilterOptionsService;
use Modules\Analysis\Services\ExportAnalysesService;
use Modules\Analysis\Services\ExportAnalysisPdfService;
use Modules\Analysis\Services\PdfSettingsService;

class AnalysisController extends Controller
{
    public function index(
        Request $request,
        AnalysisDataTable $dataTable,
        AnalysisFilterOptionsService $filterOptions,
        PdfSettingsService $pdfSettings,
        AnalysisRepositoryInterface $analyses,
    ) {
        $this->authorize('viewAny', Analysis::class);

        if ($request->ajax() && $request->wantsJson()) {
            return $dataTable->ajax();
        }

        return $dataTable->render('analysis.index', [
            'filterOptions' => $filterOptions->get(),
            'settings' => $pdfSettings->get()->toArray(),
            'isTrash' => $request->boolean('trash'),
            'sheetStats' => $analyses->sheetSummaryCounts(),
        ]);
    }

    public function show(Analysis $analysis)
    {
        $this->authorize('view', $analysis);

        $analysis->load(['creator:id,name', 'updater:id,name']);

        $payload = $analysis->toArray();
        $payload['created_by_name'] = $analysis->creator?->name;
        $payload['updated_by_name'] = $analysis->updater?->name;

        $payload['activity_logs'] = $analysis->activityLogs()
            ->with('user:id,name')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($log) => [
                'action' => $log->action,
                'user' => $log->user?->name,
                'at' => $log->created_at?->toDateTimeString(),
                'changes' => $log->changes,
            ])
            ->values();

        return response()->json($payload);
    }

    public function create()
    {
        $this->authorize('create', Analysis::class);

        return view('analysis.create');
    }

    public function store(StoreAnalysisRequest $request, CreateAnalysisAction $action)
    {
        $analysis = $action->execute(AnalysisData::fromValidated($request->validated()));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Record added successfully.',
                'record' => [
                    'id' => $analysis->id,
                    'purity' => $analysis->purity,
                ],
            ]);
        }

        return redirect()->route('analysis.index')
            ->with('success', 'Record added successfully.');
    }

    public function edit(Analysis $analysis)
    {
        $this->authorize('update', $analysis);

        return view('analysis.edit', compact('analysis'));
    }

    public function update(UpdateAnalysisRequest $request, Analysis $analysis, UpdateAnalysisAction $action)
    {
        abort_if($analysis->trashed(), 404);

        $analysis = $action->execute($analysis, AnalysisData::fromValidated($request->validated()));

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

    public function destroy(Analysis $analysis, DeleteAnalysisAction $action)
    {
        abort_if($analysis->trashed(), 404);
        $this->authorize('delete', $analysis);
        $action->execute($analysis);

        return redirect()->route('analysis.index')
            ->with('success', 'Record deleted successfully.');
    }

    public function restore(int $id, RestoreAnalysisAction $action)
    {
        $analysis = Analysis::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $analysis);
        $action->execute($analysis);

        return redirect()->route('analysis.index', ['trash' => 1])
            ->with('success', 'Record restored successfully.');
    }

    public function quickUpdate(
        QuickUpdateAnalysisRequest $request,
        Analysis $analysis,
        QuickUpdateAnalysisAction $action,
    ) {
        abort_if($analysis->trashed(), 404);

        try {
            $analysis = $action->execute(
                $analysis,
                QuickUpdateData::fromValidated($request->validated())
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'purity' => $analysis->purity,
        ]);
    }

    public function bulkDelete(BulkDeleteAnalysisRequest $request, BulkDeleteAnalysesAction $action)
    {
        $count = $action->execute($request->validated('ids'));

        return back()->with('success', "Deleted {$count} record(s).");
    }

    public function bulkResult(BulkUpdateResultRequest $request, BulkUpdateResultAction $action)
    {
        $count = $action->execute(
            $request->validated('ids'),
            $request->validated('result')
        );

        return back()->with('success', "Updated result on {$count} record(s).");
    }

    public function bulkExport(Request $request, ExportAnalysesService $exports)
    {
        $this->authorize('viewAny', Analysis::class);

        return $exports->downloadExcelByIds($this->normalizeIds($request->input('ids', [])));
    }

    public function export(Request $request, ExportAnalysesService $exports)
    {
        $this->authorize('viewAny', Analysis::class);

        return $exports->downloadExcel(AnalysisFilters::fromRequest($request->all()));
    }

    public function importView()
    {
        $this->authorize('create', Analysis::class);

        return redirect()->route('analysis.index', ['import' => 1]);
    }

    public function import(ImportAnalysisRequest $request)
    {
        $file = $request->file('file');
        $storedPath = $file->storeAs(
            'imports',
            uniqid('import_', true).'_'.$file->getClientOriginalName(),
            'local'
        );

        ImportAnalysesJob::dispatch(
            $storedPath,
            $file->getClientOriginalName(),
            $request->user()?->id
        );

        return redirect()->route('analysis.index')
            ->with('success', 'Import queued. Run `php artisan queue:work` if workers are not already running.');
    }

    public function exportPdf(Request $request, ExportAnalysisPdfService $exports)
    {
        $this->authorize('viewAny', Analysis::class);

        $ids = $request->input('ids');

        return $exports->export(
            $ids === null ? null : $this->normalizeIds($ids),
            AnalysisFilters::fromRequest($request->all())
        );
    }

    public function saveSettings(SavePdfSettingsRequest $request, PdfSettingsService $settings)
    {
        $settings->save($request->validated(), $request->file('logo'));

        return redirect()->back()->with('success', 'PDF Logo & Header Settings updated successfully!');
    }

    public function template(ExportAnalysesService $exports)
    {
        $this->authorize('viewAny', Analysis::class);

        return $exports->downloadTemplate();
    }

    /**
     * @param  array<int|string>|string|null  $ids
     * @return list<int|string>
     */
    private function normalizeIds(array|string|null $ids): array
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_filter($ids, static fn ($id) => $id !== null && $id !== ''));
    }
}
