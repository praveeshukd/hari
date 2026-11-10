@extends('layouts.app')

@section('title', config('app.name', 'Molecules BioLabs') . ' – Admin Dashboard')
@section('page_title', 'Dashboard')
@section('meta_description', 'HPLC QC Analysis Management System Dashboard.')
@section('hide_footer', '1')
@section('hide_nav_links', '1')
@section('hide_navbar', '1')

@push('styles')
<link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="{{ asset('css/datatable.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="admin-wrapper" id="dashboard-wrapper">
    @include('partials.admin-sidebar', ['active' => 'dashboard'])

    <x-admin-header title="Dashboard" :failed-today="$failedToday" />

    <main class="admin-main" id="dashboard-main">
        <div class="spark-pagehead">
            <h1>Dashboard</h1>
            <div class="spark-range">
                <span class="spark-range-icon">@include('partials.icons.nav', ['name' => 'clipboard'])</span>
                From {{ $rangeLabel }}
            </div>
        </div>

        <div class="spark-grid">
            @foreach($cards as $card)
                <article class="spark-card">
                    <div class="spark-ico spark-ico-{{ $card['color'] }}">
                        @include('partials.icons.nav', ['name' => $card['icon']])
                    </div>
                    <div class="spark-copy">
                        <div class="spark-label">{{ $card['label'] }}</div>
                        <div class="spark-value">{{ $card['value'] }}</div>
                        <div class="spark-meta {{ $card['meta']['dir'] }}">{{ $card['meta']['text'] }}</div>
                    </div>
                </article>
            @endforeach
        </div>

        <section class="spark-panel">
            <div class="spark-panel-head">
                <h2>Pass vs Fail ({{ $year }})</h2>
                <div class="spark-legend">
                    <span><i class="swatch swatch-pass"></i> Pass</span>
                    <span><i class="swatch swatch-fail"></i> Fail</span>
                </div>
            </div>
            <div class="spark-chart">
                <canvas id="pass-fail-chart"></canvas>
            </div>
        </section>

        <div class="spark-split">
            <section class="spark-panel">
                <div class="spark-panel-head">
                    <h2>Last 5 Passed</h2>
                </div>
                @include('partials.dashboard-records', ['rows' => $passed, 'empty' => 'No passed records yet.'])
            </section>
            <section class="spark-panel">
                <div class="spark-panel-head">
                    <h2>Last 5 Failed</h2>
                </div>
                @include('partials.dashboard-records', ['rows' => $failed, 'empty' => 'No failed records yet.'])
            </section>
        </div>

        <section class="sheet-card" id="datasheet">
            <div class="sheet-toolbar">
                <div class="sheet-toolbar-top">
                    <div class="sheet-title">
                        <h2>QC Records</h2>
                        <p>Server-side table. Open the analysis sheet for every column, filters, and PDF export.</p>
                    </div>
                    <div class="sheet-toolbar-actions">
                        <a href="{{ route('analysis.index') }}" class="btn-dt-create">Open analysis sheet</a>
                    </div>
                </div>
            </div>
            <div class="dt-table-wrap">
                {!! $dataTable->table(['class' => 'qc-datatable display nowrap', 'style' => 'width:100%']) !!}
            </div>
        </section>
    </main>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
{!! $dataTable->scripts() !!}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chart);
    const ctx = document.getElementById('pass-fail-chart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Pass',
                        data: chartData.pass,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.08)',
                        fill: false,
                        tension: 0.35,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        borderWidth: 2
                    },
                    {
                        label: 'Fail',
                        data: chartData.fail,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.08)',
                        fill: false,
                        tension: 0.35,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#94a3b8', precision: 0, font: { size: 11 } },
                        grid: { color: '#eef2f6' },
                        border: { display: false }
                    },
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 11 } },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }
</script>
@endsection
