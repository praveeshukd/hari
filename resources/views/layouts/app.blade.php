<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Molecules BioLabs – A powerful, beautiful web application.')">
    <title>@yield('title', config('app.name', 'Molecules BioLabs')) – @yield('page_title', 'Welcome')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

{{-- Navbar --}}
@hasSection('hide_navbar')
@else
<nav class="navbar" id="main-navbar">
    <a href="{{ route('landing') }}" class="nav-brand" id="nav-brand-link">
        <img src="{{ asset('images/molecules-logo-dark.svg') }}" alt="{{ config('app.name', 'Molecules BioLabs') }}" class="brand-logo brand-logo--nav">
    </a>

    @hasSection('hide_nav_links')
    @else
    <div class="nav-links">
        <a href="{{ route('landing') }}#features">Features</a>
        <a href="{{ route('landing') }}#pricing">Pricing</a>
        <a href="{{ route('landing') }}#testimonials">Reviews</a>
    </div>
    @endif

    <div class="nav-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm" id="nav-dashboard-btn">Dashboard</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm" id="nav-logout-btn">Sign Out</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm" id="nav-login-btn">Sign In</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm" id="nav-register-btn">Get Started</a>
        @endauth
    </div>
</nav>
@endif

{{-- Flash Messages --}}
@if(session('success'))
<div class="alert alert-success" role="alert" id="flash-success" style="position:fixed;top:80px;left:50%;transform:translateX(-50%);z-index:9999;min-width:320px;max-width:560px;">
    <span class="alert-icon">✓</span>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger" role="alert" id="flash-error" style="position:fixed;top:80px;left:50%;transform:translateX(-50%);z-index:9999;min-width:320px;max-width:560px;">
    <span class="alert-icon">⚠</span>
    <span>{{ session('error') }}</span>
</div>
@endif

{{-- Main Content --}}
@yield('content')

{{-- Footer --}}
@hasSection('hide_footer')
@else
<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <div class="nav-brand">
                <img src="{{ asset('images/molecules-logo-dark.svg') }}" alt="{{ config('app.name', 'Molecules BioLabs') }}" class="brand-logo brand-logo--footer">
            </div>
            <p>Building beautiful, modern web applications with Laravel. Fast, secure, and scalable.</p>
        </div>
        <div class="footer-col">
            <h4>Product</h4>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#testimonials">Reviews</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Account</h4>
            <ul>
                <li><a href="{{ route('login') }}">Sign In</a></li>
                <li><a href="{{ route('register') }}">Sign Up</a></li>
                @auth
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                @endauth
            </ul>
        </div>
        <div class="footer-col">
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Cookie Policy</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© {{ date('Y') }} {{ config('app.name', 'Molecules BioLabs') }}. All rights reserved.</span>
        <span>Built with ♥ using Laravel</span>
    </div>
</footer>
@endif

<script>
// Navbar scroll effect
const navbar = document.getElementById('main-navbar');
window.addEventListener('scroll', () => {
    navbar?.classList.toggle('scrolled', window.scrollY > 50);
}, { passive: true });

// Auto-dismiss flash messages
document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => {
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(() => el.remove(), 500);
    }, 4000);
});

// Scroll reveal
const revealEls = document.querySelectorAll('.reveal');
const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
}, { threshold: 0.1 });
revealEls.forEach(el => io.observe(el));
</script>

@yield('scripts')
</body>
</html>
