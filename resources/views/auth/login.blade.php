@extends('layouts.app')

@section('title', config('app.name', 'Molecules BioLabs'))
@section('page_title', 'Sign In')
@section('meta_description', 'Sign in to your Molecules BioLabs account to access your dashboard.')
@section('hide_footer', '1')
@section('hide_nav_links', '1')

@section('content')

<div class="auth-page" id="login-page">
    <div class="auth-bg-orb auth-bg-orb-1"></div>
    <div class="auth-bg-orb auth-bg-orb-2"></div>

    <div class="auth-card" id="login-card">

        {{-- Logo --}}
        <div class="auth-logo">
            <img src="{{ asset('images/molecules-logo-dark.svg') }}" alt="{{ config('app.name', 'Molecules BioLabs') }}" class="brand-logo brand-logo--auth">
        </div>

        {{-- Heading --}}
        <div class="auth-heading">
            <h1>Welcome back</h1>
            <p>Sign in to your account to continue</p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert" id="login-error-alert">
                <span class="alert-icon">⚠</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
            @csrf

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
                        autofocus
                    >
                </div>
                @error('email')
                    <div class="invalid-feedback" id="email-error">⚠ {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper" style="position:relative;">
                    <span class="input-icon">🔒</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Your password"
                        required
                        autocomplete="current-password"
                        style="padding-right:3rem;"
                    >
                    <button type="button" id="toggle-password" onclick="togglePassword()"
                        style="position:absolute;right:0.9rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--clr-text-faint);font-size:1rem;padding:0;line-height:1;"
                        aria-label="Toggle password visibility">
                        👁
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback" id="password-error">⚠ {{ $message }}</div>
                @enderror
            </div>

            <div class="form-meta">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Remember me</label>
                </div>
                <a href="#" class="link-primary" id="forgot-password-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-full" id="login-submit-btn">
                Sign In →
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account?
            <a href="{{ route('register') }}" id="go-to-register-link">Create one free</a>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('password');
    const btn   = document.getElementById('toggle-password');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁';
    }
}

// Button loading state
document.getElementById('login-form')?.addEventListener('submit', function() {
    const btn = document.getElementById('login-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Signing in...';
});
</script>
@endsection
