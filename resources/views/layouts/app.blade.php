<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,300..800;1,300..800&family=JetBrains+Mono:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet"/>
<style>html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}::-webkit-scrollbar{display:none;}</style>
<script src="https://cdn.tailwindcss.com"></script>
<script id="tailwind-config">tailwind.config={darkMode:"class",theme:{extend:{colors:{"on-surface-variant":"#45474c","on-primary-fixed-variant":"#3c475a","on-surface":"#0b1c30","on-secondary-container":"#fefcff","tertiary-fixed-dim":"#6bd8cb","inverse-on-surface":"#eaf1ff","on-tertiary":"#ffffff","secondary-fixed":"#dbe1ff","surface-container":"#e5eeff","surface-tint":"#545f73","error-container":"#ffdad6","surface-container-highest":"#d3e4fe","tertiary-container":"#002f2a","surface-container-high":"#dce9ff","error":"#ba1a1a","outline-variant":"#c5c6cd","on-primary":"#ffffff","on-error-container":"#93000a","on-secondary-fixed":"#00174b","surface-container-low":"#eff4ff","tertiary-fixed":"#89f5e7","on-background":"#0b1c30","on-primary-container":"#8590a6","on-secondary-fixed-variant":"#003ea8","on-primary-fixed":"#111c2d","inverse-primary":"#bcc7de","surface-dim":"#cbdbf5","on-error":"#ffffff","primary":"#091426","on-tertiary-container":"#28a094","primary-container":"#1e293b","primary-fixed":"#d8e3fb","surface":"#f8f9ff","background":"#f8f9ff","surface-variant":"#d3e4fe","primary-fixed-dim":"#bcc7de","inverse-surface":"#213145","secondary-fixed-dim":"#b4c5ff","on-secondary":"#ffffff","on-tertiary-fixed-variant":"#005049","surface-container-lowest":"#ffffff","outline":"#75777d","secondary-container":"#316bf3","tertiary":"#001815","on-tertiary-fixed":"#00201d","surface-bright":"#f8f9ff","secondary":"#0051d5"},borderRadius:{DEFAULT:"0.125rem",lg:"0.25rem",xl:"0.5rem",full:"0.75rem"},spacing:{"space-lg":"1rem","space-md":"0.75rem","space-xl":"1.5rem",margin:"1rem",gutter:"1rem","space-xs":"0.25rem","margin-md":"1.5rem","space-sm":"0.5rem","gutter-lg":"1.5rem","margin-lg":"2rem"},fontFamily:{"body-lg":["Hanken Grotesk"],"headline-lg-mobile":["Hanken Grotesk"],"headline-xl":["Hanken Grotesk"],"label-md":["JetBrains Mono"],"headline-lg":["Hanken Grotesk"],"label-sm":["JetBrains Mono"],"body-sm":["Hanken Grotesk"],"headline-md":["Hanken Grotesk"],"label-xs":["JetBrains Mono"],"body-md":["Hanken Grotesk"],"headline-xl-mobile":["Hanken Grotesk"],"headline-sm":["Hanken Grotesk"]},fontSize:{"body-lg":["0.9375rem",{lineHeight:"1.5rem",letterSpacing:"0em",fontWeight:"400"}],"headline-lg-mobile":["1.25rem",{lineHeight:"1.75rem",letterSpacing:"-0.01em",fontWeight:"600"}],"headline-xl":["2rem",{lineHeight:"2.5rem",letterSpacing:"-0.03em",fontWeight:"700"}],"label-md":["0.8125rem",{lineHeight:"1.125rem",letterSpacing:"-0.01em",fontWeight:"500"}],"headline-lg":["1.5rem",{lineHeight:"2rem",letterSpacing:"-0.02em",fontWeight:"600"}],"label-sm":["0.6875rem",{lineHeight:"0.875rem",letterSpacing:"0.02em",fontWeight:"500"}],"body-sm":["0.75rem",{lineHeight:"1.125rem",letterSpacing:"0.01em",fontWeight:"400"}],"headline-md":["1.125rem",{lineHeight:"1.5rem",letterSpacing:"-0.01em",fontWeight:"600"}],"label-xs":["0.625rem",{lineHeight:"0.75rem",letterSpacing:"0.04em",fontWeight:"600"}],"body-md":["0.84375rem",{lineHeight:"1.25rem",letterSpacing:"0em",fontWeight:"400"}],"headline-xl-mobile":["1.5rem",{lineHeight:"2rem",letterSpacing:"-0.02em",fontWeight:"700"}],"headline-sm":["0.9375rem",{lineHeight:"1.375rem",letterSpacing:"0em",fontWeight:"600"}]}}}};</script>
@stack('head')
</head>
<body class="bg-surface font-body-md text-body-md text-on-surface antialiased">
<header class="fixed top-0 left-0 right-0 h-14 bg-primary text-on-primary z-50 shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
  <div class="h-14 w-full px-space-lg flex items-center justify-between">
    <div class="flex items-center gap-space-lg">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-space-sm">
        <span class="w-8 h-8 rounded bg-surface-container-high flex items-center justify-center font-headline-sm font-bold text-primary">S</span>
        <span class="font-headline-sm text-headline-sm tracking-tight text-on-primary font-semibold">StructureOps</span>
      </a>
      <div class="hidden md:flex items-center gap-space-sm pl-space-md">
        <span class="font-body-md text-body-md text-on-primary font-medium">{{ $currentTenant->naziv ?? 'Investitor' }}</span>
        <span class="font-label-xs text-label-xs uppercase px-space-xs py-0.5 rounded bg-surface-container-high text-on-surface font-medium">{{ $currentUser->uloga ?? '' }}</span>
      </div>
    </div>
    <div class="flex items-center gap-space-md">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="p-1.5 rounded-lg text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors flex items-center justify-center" title="Odjava">
          <span class="material-symbols-outlined text-[20px]">logout</span>
        </button>
      </form>
      <div class="flex items-center gap-space-sm pl-space-sm">
        <div class="text-right hidden sm:block">
          <div class="font-body-sm text-body-sm font-semibold text-on-primary leading-tight">{{ $currentUser->ime_prezime ?? '' }}</div>
          <div class="font-label-xs text-label-xs text-on-primary-container leading-tight">{{ $currentUser->uloga ?? '' }}</div>
        </div>
        <span class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary font-semibold text-sm ring-1 ring-outline-variant">{{ substr($currentUser->ime_prezime ?? '?', 0, 1) }}</span>
      </div>
    </div>
  </div>
</header>
<aside class="fixed left-0 top-14 bottom-0 w-64 bg-primary-container text-on-primary z-40 hidden md:flex flex-col justify-between py-space-md">
  <div class="flex flex-col gap-space-xs">
    <div class="px-space-md py-space-xs"><span class="font-label-xs text-label-xs uppercase tracking-wider text-on-primary-container font-medium">Glavni Meni</span></div>
    <nav class="flex flex-col gap-1 px-space-sm">
      @php $nav = [
        ['nadzorna-tabla', 'dashboard', 'Nadzorna tabla', 'dashboard'],
        ['dosije-zgrade', 'projects.index', 'Projekti & Zgrade', 'domain'],
        ['stanovi-jedinice', 'units.index', 'Stanovi / Jedinice', 'apartment'],
        ['reklamacije-i-garancije', 'claims.index', 'Reklamacije & Garancije', 'assignment_late'],
      ];
      if($currentUser && $currentUser->mozeAdministrirati()) { $nav[] = ['korisnici', 'users.index', 'Korisnici', 'group']; }
      @endphp
      @foreach($nav as [$path, $route, $label, $icon])
      <a href="{{ route($route) }}" class="flex items-center gap-space-sm px-space-md py-2 rounded-lg transition-colors {{ request()->routeIs($route) ? 'bg-surface-container-lowest text-on-surface font-semibold shadow-[0_1px_4px_rgba(0,0,0,0.06)]' : 'text-on-primary-container hover:bg-surface-container-high hover:text-on-surface' }}">
        <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
        <span class="font-body-md text-body-md">{{ $label }}</span>
      </a>
      @endforeach
    </nav>
  </div>
  <div class="px-space-md pt-space-md">
    <div class="p-space-sm rounded-lg bg-primary flex flex-col gap-1">
      <div class="flex items-center justify-between">
        <span class="font-label-xs text-label-xs uppercase text-on-primary-container">Arhiva Dosijea</span>
        <span class="font-label-xs text-label-xs text-tertiary-fixed-dim font-medium">Aktivna</span>
      </div>
      <div class="font-body-sm text-body-sm text-on-primary font-medium truncate">{{ $currentTenant->naziv ?? '' }}</div>
      <div class="font-label-xs text-label-xs text-on-primary-container">Dosije Engine v1.0</div>
    </div>
  </div>
</aside>
<div class="md:pl-64">
<main class="w-full pt-14 px-space-lg pb-margin-lg bg-surface min-h-screen">
@yield('content')
</main>
</div>
@if(session('uspesno'))
<div class="fixed bottom-6 right-6 z-50 bg-primary text-on-primary px-space-md py-space-sm rounded-lg shadow-lg font-body-sm text-body-sm flex items-center gap-space-xs" id="action-toast">
  <span class="material-symbols-outlined text-[18px] text-tertiary-fixed-dim">check_circle</span>
  <span>{{ session('uspesno') }}</span>
</div>
<script>setTimeout(()=>{const t=document.getElementById('action-toast'); if(t){t.style.opacity='0'; t.style.transition='opacity .3s';}},3500);</script>
@endif
@if($errors->any())
<div class="fixed bottom-6 right-6 z-50 bg-error text-on-primary px-space-md py-space-sm rounded-lg shadow-lg font-body-sm text-body-sm max-w-md">
  <ul class="list-disc pl-4">@foreach($errors->all() as $greska)<li>{{ $greska }}</li>@endforeach</ul>
</div>
@endif
<script>
function toggleModal(id, show){ const m=document.getElementById(id); if(!m) return; m.classList.toggle('hidden', !show); }
document.querySelectorAll('[data-modal-open]').forEach(b=>b.addEventListener('click',()=>toggleModal(b.getAttribute('data-modal-open'), true)));
document.querySelectorAll('[data-modal-close]').forEach(b=>b.addEventListener('click',()=>toggleModal(b.getAttribute('data-modal-close'), false)));
</script>
@stack('scripts')
</body>
</html>
