@extends('layouts.app')

@section('title', config('app.name', 'Molecules BioLabs'))
@section('page_title', 'Home')
@section('meta_description', 'Molecules BioLabs – A beautiful, modern web platform built with Laravel. Get started for free today.')

@section('content')

{{-- ===== HERO ===== --}}
<section class="hero" id="hero">
    <div class="hero-glow"></div>
    <div style="position:relative;z-index:1;max-width:820px;margin:0 auto;">

      

        <h1>
             Molecules Biolabs<br>
          
        </h1>

        <p>
            Next Gen Ingredients.
        </p>

        <div class="hero-cta">
         
            <a href="{{ route('login') }}" class="btn btn-outline btn-lg" id="hero-cta-login">
                Sign In
            </a>
        </div>

        {{-- Floating stats strip --}}
        <div style="margin-top:4rem;display:flex;align-items:center;justify-content:center;gap:2.5rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:var(--clr-text-muted);">
                <span style="color:var(--clr-success);font-size:1.1rem;">✓</span> No credit card required
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:var(--clr-text-muted);">
                <span style="color:var(--clr-success);font-size:1.1rem;">✓</span> Free forever plan
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:var(--clr-text-muted);">
                <span style="color:var(--clr-success);font-size:1.1rem;">✓</span> Production ready
            </div>
        </div>

    </div>
</section>



@endsection

@section('scripts')
<script>
// Parallax on hero glow
document.addEventListener('mousemove', (e) => {
    const glow = document.querySelector('.hero-glow');
    if (!glow) return;
    const x = (e.clientX / window.innerWidth - 0.5) * 40;
    const y = (e.clientY / window.innerHeight - 0.5) * 40;
    glow.style.transform = `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`;
});
</script>
@endsection
