<style>
.mini-nav{
    position:fixed;top:0;left:0;right:0;z-index:100;
    display:flex;align-items:center;justify-content:space-between;
    padding:.7rem 1.5rem;
    background:rgba(5,9,20,.85);
    backdrop-filter:blur(16px);
    border-bottom:1px solid rgba(255,255,255,.07);
}
.mini-nav-brand{display:flex;align-items:center;gap:.6rem;font-family:'Outfit',sans-serif;font-size:1rem;font-weight:700;}
.mini-nav-brand .ico{width:28px;height:28px;border-radius:7px;background:linear-gradient(135deg,#6366f1,#a78bfa,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:900;color:#fff;}
.mini-nav-brand span{background:linear-gradient(135deg,#6366f1,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
</style>
<nav class="mini-nav">
    <div class="mini-nav-brand">
        <div class="ico">Q</div>
        <span>QC Analysis</span>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;">
        <a href="{{ route('analysis.index') }}" class="btn btn-outline btn-sm">📊 Sheet</a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">Dashboard</a>
        @auth
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button class="btn btn-ghost btn-sm" style="color:rgba(255,255,255,.5);">Sign Out</button>
        </form>
        @endauth
    </div>
</nav>
