@php
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'sheet' => '<path d="M8 3h7l5 5v13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M15 3v5h5"/><path d="M9 13h6M9 17h6"/>',
        'chart' => '<path d="M4 19h16"/><path d="M7 16v-5"/><path d="M12 16V8"/><path d="M17 16v-8"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 3v2.2M12 18.8V21M4.9 6.3l1.6 1.6M17.5 16.1l1.6 1.6M3 12h2.2M18.8 12H21M4.9 17.7l1.6-1.6M17.5 7.9l1.6-1.6"/>',
        'flask' => '<path d="M9 3h6M10 3v6L5.5 19a2 2 0 0 0 1.8 3h9.4a2 2 0 0 0 1.8-3L14 9V3"/>',
        'check' => '<circle cx="12" cy="12" r="8"/><path d="M8.5 12.5l2.2 2.2 4.8-5"/>',
        'alert' => '<path d="M12 8v5"/><path d="M12 16.5h.01"/><path d="M10.3 4.8 3.6 17a2 2 0 0 0 1.7 3h13.4a2 2 0 0 0 1.7-3L13.7 4.8a2 2 0 0 0-3.4 0z"/>',
        'beaker' => '<path d="M9 3h6M10 3v5.5L6 19a2 2 0 0 0 1.8 3h8.4A2 2 0 0 0 18 19l-4-10.5V3"/><path d="M8.5 14h7"/>',
        'clipboard' => '<rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 4.5h6v2H9z"/><path d="M9 11h6M9 15h4"/>',
        'users' => '<circle cx="9" cy="9" r="3"/><circle cx="16" cy="10" r="2.2"/><path d="M4.5 18a4.5 4.5 0 0 1 9 0"/><path d="M14 18a3.5 3.5 0 0 1 6 0"/>',
        'box' => '<path d="M3 8l9-4 9 4-9 4-9-4z"/><path d="M3 8v8l9 4 9-4V8"/><path d="M12 12v8"/>',
        'store' => '<path d="M4 10h16l-1.2 9H5.2L4 10z"/><path d="M6 10V7a6 6 0 0 1 12 0v3"/><path d="M9 19v-4h6v4"/>',
        'home' => '<path d="M4 11.5 12 4l8 7.5"/><path d="M7 10.5V20h10v-9.5"/>',
        'search' => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-3.5-3.5"/>',
        'bell' => '<path d="M6 16V11a6 6 0 1 1 12 0v5l1.5 2H4.5L6 16z"/><path d="M10 19a2 2 0 0 0 4 0"/>',
        'chevron' => '<path d="M6 9l6 6 6-6"/>',
    ];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$name] ?? '' !!}</svg>
