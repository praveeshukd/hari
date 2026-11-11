@php
    $active = $active ?? 'dashboard';
    $onSheet = request()->routeIs('analysis.index');
    $user = auth()->user();
@endphp

<aside class="admin-sidebar" id="main-sidebar">
    <a href="{{ route('dashboard') }}" class="admin-sidebar-brand">
        <img src="{{ asset('images/molecules-logo-dark.svg') }}" alt="{{ config('app.name', 'Molecules BioLabs') }}" class="admin-brand-logo">
    </a>

    <nav class="admin-sidebar-nav">
        <div class="admin-nav-section-label">Overview</div>
        <a href="{{ route('dashboard') }}" class="admin-nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
            <span class="nav-icon">@include('partials.icons.nav', ['name' => 'dashboard'])</span>
            <span>Dashboard</span>
        </a>

        <div class="admin-nav-section-label">QC Lab</div>
        <a href="{{ route('analysis.index') }}" class="admin-nav-item {{ $active === 'analysis' ? 'active' : '' }}">
            <span class="nav-icon">@include('partials.icons.nav', ['name' => 'sheet'])</span>
            <span>Analysis Sheet</span>
        </a>
        @can('create', Modules\Analysis\Models\Analysis::class)
            @if($onSheet)
            <a href="javascript:void(0)" onclick="openSettingsModal()" class="admin-nav-item">
                <span class="nav-icon">@include('partials.icons.nav', ['name' => 'settings'])</span>
                <span>PDF Setup</span>
            </a>
            @else
            <a href="{{ route('analysis.index') }}#settings" class="admin-nav-item">
                <span class="nav-icon">@include('partials.icons.nav', ['name' => 'settings'])</span>
                <span>PDF Setup</span>
            </a>
            @endif
        @endcan

        @if($user?->isAdmin())
            <div class="admin-nav-section-label">Admin</div>
            <a href="{{ route('users.index') }}" class="admin-nav-item {{ $active === 'users' ? 'active' : '' }}">
                <span class="nav-icon">@include('partials.icons.nav', ['name' => 'users'])</span>
                <span>Users</span>
            </a>
            <a href="{{ route('analysis.index', ['trash' => 1]) }}" class="admin-nav-item {{ request('trash') ? 'active' : '' }}">
                <span class="nav-icon">@include('partials.icons.nav', ['name' => 'alert'])</span>
                <span>Trash</span>
            </a>
        @endif
    </nav>
</aside>

@include('partials.admin-mobile-nav', ['active' => $active])
