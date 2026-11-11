@extends('layouts.app')

@section('title', config('app.name', 'Molecules BioLabs') . ' – Admin Dashboard')
@section('page_title', 'Dashboard')
@section('meta_description', 'HPLC QC Analysis Management System Dashboard.')
@section('hide_footer', '1')
@section('hide_nav_links', '1')
@section('hide_navbar', '1')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="{{ asset('css/datatable.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="admin-wrapper" id="dashboard-wrapper">
    @include('partials.admin-sidebar', ['active' => 'dashboard'])

    <x-admin-header title="Dashboard" :failed-today="$failedToday" />

    <main class="admin-main mdash" id="dashboard-main">
        <div class="mdash-stack">
            <header class="mdash-titlebar" aria-label="Dashboard title">
                <div class="mdash-title-copy">
                    <h1 class="mdash-title">Dashboard</h1>
                    <p class="mdash-subtitle">Monitor HPLC QC samples, purity, and instrument activity in one place</p>
                </div>
                <div class="mdash-date" data-mdash-date>
                    <button type="button" class="mdash-date-btn" aria-expanded="false" aria-controls="mdash-date-menu" data-mdash-date-toggle>
                        <svg class="mdash-date-ico" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <span>{{ $rangeLabel }}</span>
                        <svg class="mdash-chevron" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="mdash-date-menu" id="mdash-date-menu" hidden>
                        <form method="GET" action="{{ route('dashboard') }}" class="mdash-date-form">
                            <label class="mdash-field-label" for="dash-from">Start Date</label>
                            <input type="date" id="dash-from" name="from" class="mdash-field" value="{{ $from }}" max="{{ $to }}">
                            <label class="mdash-field-label" for="dash-to">End Date</label>
                            <input type="date" id="dash-to" name="to" class="mdash-field" value="{{ $to }}" min="{{ $from }}">
                            <div class="mdash-date-actions">
                                <button type="submit" class="mdash-date-apply">Filter</button>
                                <a href="{{ route('dashboard') }}" class="mdash-date-reset">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </header>

            <section class="mdash-kpis" aria-label="QC totals">
                @foreach($cards as $card)
                    <article class="mdash-kpi mdash-kpi--{{ $card['color'] }}">
                        <span class="mdash-kpi-accent" aria-hidden="true"></span>
                        <div class="mdash-kpi-body">
                            <span class="mdash-kpi-label">{{ $card['label'] }}</span>
                            <span class="mdash-kpi-value">{{ $card['value'] }}</span>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="mdash-overview" aria-label="QC overview">
                <article class="mdash-card mdash-chart-card">
                    <div class="mdash-card-head">
                        <div class="mdash-card-heading">
                            <h3>QC Trends</h3>
                            <div class="mdash-legend">
                                <span><i class="mdash-dot mdash-dot--pass"></i>Passed</span>
                                <span><i class="mdash-dot mdash-dot--samples"></i>Samples</span>
                            </div>
                        </div>
                        <a href="{{ route('analysis.index') }}" class="mdash-link">View Report <span aria-hidden="true">›</span></a>
                    </div>
                    <div class="mdash-chart-area">
                        <canvas id="qc-trend-chart" aria-label="QC trends chart"></canvas>
                    </div>
                </article>

                <article class="mdash-card mdash-side" aria-label="Lab performance">
                    <div class="mdash-card-head">
                        <h3>Lab Performance</h3>
                        <span class="mdash-status-pill">Active</span>
                    </div>
                    <ul class="mdash-perf-list">
                        <li>
                            <span>Pass Rate</span>
                            <strong>{{ number_format($performance['pass_rate'], 1) }}%</strong>
                        </li>
                        <li>
                            <span>Avg. Purity</span>
                            <strong>{{ number_format($performance['avg_purity'], 2) }}%</strong>
                        </li>
                        <li>
                            <span>Fail Rate</span>
                            <strong>{{ number_format($performance['fail_rate'], 1) }}%</strong>
                        </li>
                        <li>
                            <span>Samples (range)</span>
                            <strong>{{ number_format($performance['samples']) }}</strong>
                        </li>
                    </ul>
                </article>
            </section>

            <section class="mdash-lists" aria-label="Recent results">
                <article class="mdash-card">
                    <div class="mdash-card-head">
                        <div>
                            <h3>Recent Passed</h3>
                            <p class="mdash-card-sub">Latest samples that met purity criteria</p>
                        </div>
                        <a href="{{ route('analysis.index', ['filter_result' => 'Pass']) }}" class="mdash-link">View All <span aria-hidden="true">›</span></a>
                    </div>
                    @include('partials.dashboard-records', ['rows' => $passed, 'empty' => 'No passed records yet.', 'tone' => 'pass'])
                </article>
                <article class="mdash-card">
                    <div class="mdash-card-head">
                        <div>
                            <h3>Recent Failed</h3>
                            <p class="mdash-card-sub">Latest samples requiring review</p>
                        </div>
                        <a href="{{ route('analysis.index', ['filter_result' => 'Fail']) }}" class="mdash-link">View All <span aria-hidden="true">›</span></a>
                    </div>
                    @include('partials.dashboard-records', ['rows' => $failed, 'empty' => 'No failed records yet.', 'tone' => 'fail'])
                </article>
            </section>

            <section class="mdash-card mdash-table-card" id="datasheet">
                <div class="mdash-card-head mdash-table-head">
                    <div>
                        <h3>QC Records</h3>
                        <p class="mdash-card-sub">Server-side table. Open the analysis sheet for filters, import, and PDF export.</p>
                    </div>
                    <a href="{{ route('analysis.index') }}" class="mdash-btn-primary">Open analysis sheet</a>
                </div>
                <div class="dt-table-wrap">
                    {!! $dataTable->table(['class' => 'qc-datatable display nowrap', 'style' => 'width:100%']) !!}
                </div>
            </section>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
{!! $dataTable->scripts() !!}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const root = document.querySelector('[data-mdash-date]');
        if (!root) return;
        const toggle = root.querySelector('[data-mdash-date-toggle]');
        const menu = root.querySelector('.mdash-date-menu');
        if (!toggle || !menu) return;

        const close = () => {
            menu.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            root.classList.remove('is-open');
        };

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = menu.hidden;
            menu.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            root.classList.toggle('is-open', open);
        });

        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });
    })();

    const chartData = @json($chart);
    const ctx = document.getElementById('qc-trend-chart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Passed',
                        data: chartData.passed,
                        borderColor: '#E1005E',
                        backgroundColor: 'rgba(225, 0, 94, 0.12)',
                        tension: 0.35,
                        yAxisID: 'y',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointBackgroundColor: '#E1005E',
                    },
                    {
                        type: 'bar',
                        label: 'Samples',
                        data: chartData.samples,
                        backgroundColor: 'rgba(15, 23, 42, 0.08)',
                        borderRadius: 6,
                        yAxisID: 'y',
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#94a3b8', precision: 0, font: { size: 11, family: 'Plus Jakarta Sans' } },
                        grid: { color: '#eef2f6' },
                        border: { display: false }
                    },
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 11, family: 'Plus Jakarta Sans' } },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }
</script>
@endsection
