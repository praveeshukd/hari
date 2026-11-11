@php
    $active = $active ?? 'dashboard';
    $user = auth()->user();
    $canCreate = $user && $user->can('create', Modules\Analysis\Models\Analysis::class);
@endphp

<nav class="admin-mobile-nav" aria-label="Primary">
    <a href="{{ route('dashboard') }}" class="admin-mobile-nav-item {{ $active === 'dashboard' ? 'is-active' : '' }}">
        <span class="admin-mobile-nav-ico" aria-hidden="true">@include('partials.icons.nav', ['name' => 'dashboard'])</span>
        <span class="admin-mobile-nav-label">Dashboard</span>
    </a>

    <a href="{{ route('analysis.index') }}" class="admin-mobile-nav-item {{ $active === 'analysis' && ! request('trash') ? 'is-active' : '' }}">
        <span class="admin-mobile-nav-ico" aria-hidden="true">@include('partials.icons.nav', ['name' => 'sheet'])</span>
        <span class="admin-mobile-nav-label">Analysis</span>
    </a>

    @if($user?->isAdmin())
        <a href="{{ route('users.index') }}" class="admin-mobile-nav-item {{ $active === 'users' ? 'is-active' : '' }}">
            <span class="admin-mobile-nav-ico" aria-hidden="true">@include('partials.icons.nav', ['name' => 'users'])</span>
            <span class="admin-mobile-nav-label">Users</span>
        </a>
    @else
        <a href="{{ route('analysis.import') }}" class="admin-mobile-nav-item {{ request()->routeIs('analysis.import') ? 'is-active' : '' }}">
            <span class="admin-mobile-nav-ico" aria-hidden="true">@include('partials.icons.nav', ['name' => 'box'])</span>
            <span class="admin-mobile-nav-label">Import</span>
        </a>
    @endif

    @if($canCreate)
        <a href="{{ route('analysis.index') }}#settings" class="admin-mobile-nav-item" onclick="if (typeof openSettingsModal === 'function') { event.preventDefault(); openSettingsModal(); }">
            <span class="admin-mobile-nav-ico" aria-hidden="true">@include('partials.icons.nav', ['name' => 'settings'])</span>
            <span class="admin-mobile-nav-label">Settings</span>
        </a>
    @else
        <details class="admin-mobile-nav-more">
            <summary class="admin-mobile-nav-item">
                <span class="admin-mobile-nav-ico" aria-hidden="true">@include('partials.icons.nav', ['name' => 'settings'])</span>
                <span class="admin-mobile-nav-label">More</span>
            </summary>
            <div class="admin-mobile-nav-panel">
                @if($user)
                    <span class="admin-mobile-nav-email">{{ $user->email }}</span>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </div>
        </details>
    @endif
</nav>
