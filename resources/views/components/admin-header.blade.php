@props(['title' => 'Dashboard', 'searchMode' => 'submit', 'failedToday' => 0])

@php $user = auth()->user(); @endphp

<header class="admin-header">
    <a href="{{ route('dashboard') }}" class="admin-crumb">
        <span class="admin-crumb-icon">@include('partials.icons.nav', ['name' => 'home'])</span>
        <span>{{ $title }}</span>
    </a>

    @if($searchMode === 'focus')
        <div class="admin-search-wrapper">
            <span class="admin-search-icon">@include('partials.icons.nav', ['name' => 'search'])</span>
            <input type="text" id="admin-search" class="admin-search-input" placeholder="Search" readonly
                   onclick="(document.getElementById('search-input') || document.querySelector('.dataTables_filter input'))?.focus()" aria-label="Search">
            <span class="admin-search-kbd">ctrl/cmd + k</span>
        </div>
    @else
        <form class="admin-search-wrapper" action="{{ route('analysis.index') }}" method="GET">
            <span class="admin-search-icon">@include('partials.icons.nav', ['name' => 'search'])</span>
            <input type="text" id="admin-search" name="search" class="admin-search-input" placeholder="Search" aria-label="Search" value="{{ request('search') }}">
            <span class="admin-search-kbd">ctrl/cmd + k</span>
        </form>
    @endif

    <div class="admin-header-actions">
        {{ $slot }}

        <details class="hdr-menu">
            <summary class="hdr-chip">
                Molecules
                <span class="hdr-chevron">@include('partials.icons.nav', ['name' => 'chevron'])</span>
            </summary>
            <div class="hdr-menu-panel">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('landing') }}" target="_blank" rel="noopener">View website</a>
            </div>
        </details>

        <details class="hdr-menu">
            <summary class="hdr-chip">
                English
                <span class="hdr-chevron">@include('partials.icons.nav', ['name' => 'chevron'])</span>
            </summary>
            <div class="hdr-menu-panel">
                <span class="is-current">English</span>
            </div>
        </details>

        <details class="hdr-menu">
            <summary class="hdr-chip">
                QC Lab
                <span class="hdr-chevron">@include('partials.icons.nav', ['name' => 'chevron'])</span>
            </summary>
            <div class="hdr-menu-panel">
                <a href="{{ route('analysis.index') }}">Analysis sheet</a>
                @can('create', Modules\Analysis\Models\Analysis::class)
                    <a href="{{ route('analysis.index', ['import' => 1]) }}">Import records</a>
                @endcan
            </div>
        </details>

        <a href="{{ route('analysis.index') }}" class="hdr-bell" title="QC alerts" aria-label="QC alerts">
            @include('partials.icons.nav', ['name' => 'bell'])
            @if($failedToday > 0)
                <span class="hdr-bell-dot"></span>
            @endif
        </a>

        @if($user)
            <details class="hdr-menu">
                <summary class="hdr-user">
                    <span class="hdr-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="hdr-user-name">{{ $user->name }}</span>
                </summary>
                <div class="hdr-menu-panel">
                    <span class="hdr-user-email">{{ $user->email }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">Sign out</button>
                    </form>
                </div>
            </details>
        @endif
    </div>
</header>

@once
    <script>
        document.addEventListener('keydown', function (event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                const sheet = document.getElementById('search-input');
                const header = document.getElementById('admin-search');
                (sheet || header)?.focus();
            }
        });
        document.addEventListener('click', function (event) {
            document.querySelectorAll('.hdr-menu[open]').forEach(function (menu) {
                if (!menu.contains(event.target)) menu.removeAttribute('open');
            });
        });
    </script>
@endonce
