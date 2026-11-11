<?php

namespace Modules\Analysis\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Analysis\DataTables\AnalysisDataTable;
use Modules\Analysis\Services\DashboardMetricsService;
use Modules\Analysis\Models\Analysis;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        AnalysisDataTable $dataTable,
        DashboardMetricsService $metrics,
    ) {
        $this->authorize('viewAny', Analysis::class);

        if ($request->ajax() && $request->wantsJson()) {
            return $dataTable->ajax();
        }

        try {
            $overview = $metrics->build(
                $request->query('from'),
                $request->query('to'),
            );
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('dashboard')
                ->with('error', $exception->getMessage());
        }

        return $dataTable->render('dashboard', [
            'user' => $request->user(),
            'cards' => $overview['cards'],
            'performance' => $overview['performance'],
            'chart' => $overview['chart'],
            'rangeLabel' => $overview['rangeLabel'],
            'from' => $overview['from'],
            'to' => $overview['to'],
            'passed' => $overview['passed'],
            'failed' => $overview['failed'],
            'failedToday' => $overview['failedToday'],
        ]);
    }
}
