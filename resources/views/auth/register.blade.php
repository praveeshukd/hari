@extends('layouts.app')

@section('title', config('app.name', 'Molecules BioLabs'))
@section('page_title', 'Create Account')
@section('meta_description', 'Create your free Molecules BioLabs account and start building today.')
@section('hide_footer', '1')
@section('hide_nav_links', '1')

@section('content')

<div class="auth-page" id="register-page">
    <div class="auth-bg-orb auth-bg-orb-1"></div>
    <div class="auth-bg-orb auth-bg-orb-2"></div>

    <div class="auth-card" id="register-card" style="max-width:520px;">

        {{-- Logo --}}
        <div class="auth-logo">
            <img src="{{ asset('images/molecules-logo-dark.svg') }}" alt="{{ config('app.name', 'Molecules BioLabs') }}" class="brand-logo brand-logo--auth">
        </div>

        {{-- Heading --}}
        <div class="auth-heading">
            <h1>Create your account</h1>
            <p>Join thousands of developers building with Molecules BioLabs</p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert" id="register-error-alert">
                <span class="alert-icon">⚠</span>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Register Form --}}
        <form method="POST" action="{{ route('register') }}" id="register-form" novalidate>
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="John Doe"
                        required
                        autocomplete="name"
                        autofocus
                    >
                </div>
                @error('name')
                    <div class="invalid-feedback" id="name-error">⚠ {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">✉</span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        required
                        autocomplete="email"
                    >
                </div>
                @error('email')
                    <div class="invalid-feedback" id="email-error">⚠ {{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Min. 8 characters"
                            required
                            autocomplete="new-password"
                            oninput="checkStrength(this.value)"
                        >
                    </div>
                    @error('password')
                        <div class="invalid-feedback" id="password-error">⚠ {{ $message }}</div>
                    @enderror
                    {{-- Strength meter --}}
                    <div class="password-strength" id="strength-meter" style="display:none;">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <div class="strength-text" id="strength-text"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Repeat password"
                            required
                            autocomplete="new-password"
                        >
                    </div>
                </div>
            </div>

            <div style="margin-bottom:1.5rem;">
                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms" style="font-size:0.83rem;color:var(--clr-text-muted);">
                        I agree to the <a href="#" class="link-primary">Terms of Service</a> and <a href="#" class="link-primary">Privacy Policy</a>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" id="register-submit-btn">
                🚀 Create Free Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="{{ route('login') }}" id="go-to-login-link">Sign in instead</a>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
function checkStrength(val) {
    const meter  = document.getElementById('strength-meter');
    const fill   = document.getElementById('strength-fill');
    const text   = document.getElementById('strength-text');

    if (!val.length) { meter.style.display = 'none'; return; }
    meter.style.display = 'block';

    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { pct: '20%', color: '#f43f5e', label: '😟 Very weak' },
        { pct: '40%', color: '#f97316', label: '😟 Weak' },
        { pct: '60%', color: '#f59e0b', label: '😐 Fair' },
        { pct: '80%', color: '#22c55e', label: '😊 Strong' },
        { pct: '100%', color: '#10b981', label: '🔥 Very strong' },
    ];

    const lvl = levels[Math.min(score, 4)];
    fill.style.width = lvl.pct;
    fill.style.background = lvl.color;
    text.textContent = lvl.label;
    text.style.color = lvl.color;
}

// Loading state
document.getElementById('register-form')?.addEventListener('submit', function(e) {
    const terms = document.getElementById('terms');
    if (!terms.checked) {
        e.preventDefault();
        alert('Please agree to the Terms of Service to continue.');
        return;
    }
    const btn = document.getElementById('register-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Creating account...';
});
</script>
@endsection
