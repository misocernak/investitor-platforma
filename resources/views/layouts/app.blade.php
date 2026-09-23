<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<title>{{ trim($__env->yieldContent('naslov')) ? trim($__env->yieldContent('naslov')).' · ' : '' }}Temelj Investitori</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet"/>
{{-- Samo ikonice koje aplikacija koristi (spisak u config/ikonice.php) — nekoliko KB umesto više MB --}}
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names={{ implode(',', config('ikonice')) }}&display=block" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: 1 }}"/>
@stack('head')
</head>
<body class="bg-surface font-body-md text-body-md text-on-surface antialiased">
@php
  $nav = [
    ['dashboard', 'Nadzorna tabla', 'dashboard', ['dashboard']],
    ['projects.index', 'Projekti & Zgrade', 'domain', ['projects.*']],
    ['units.index', 'Stanovi / Jedinice', 'apartment', ['units.*']],
    ['checklists.index', 'Checkliste', 'fact_check', ['checklists.*']],
    ['claims.index', 'Reklamacije & Garancije', 'assignment_late', ['claims.*']],
  ];
  if ($currentUser && $currentUser->jeNadzor()) {
    $nav = [['claims.index', 'Reklamacije & Garancije', 'assignment_late', ['claims.*']]];
  }
  if ($currentUser && $currentUser->mozeAdministrirati()) {
    $nav[] = ['users.index', 'Korisnici', 'group', ['users.*']];
  }
  $inicijali = collect(explode(' ', $currentUser->ime_prezime ?? '?'))->filter()->take(2)->map(fn ($d) => mb_substr($d, 0, 1))->implode('');
@endphp

<header class="fixed top-0 left-0 right-0 h-14 bg-primary text-on-primary z-50 shadow-[0_1px_8px_rgba(0,0,0,0.08)]">
  <div class="h-14 w-full px-space-lg flex items-center justify-between gap-space-md">
    <div class="flex items-center gap-space-md min-w-0">
      <button type="button" class="md:hidden p-1.5 -ml-1.5 rounded-lg text-on-primary hover:bg-primary-container" data-meni-otvori aria-label="Otvori meni">
        <span class="material-symbols-outlined text-[24px]">menu</span>
      </button>
      <a href="{{ route($nav[0][0]) }}" class="flex items-center gap-space-sm shrink-0">
        <span class="w-8 h-8 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary">
          <span class="material-symbols-outlined text-[20px]">foundation</span>
        </span>
        <span class="font-headline-sm text-headline-sm tracking-tight font-semibold hidden sm:inline">Temelj <span class="text-on-primary-container font-medium">Investitori</span></span>
      </a>
      <div class="hidden lg:flex items-center gap-space-sm pl-space-md border-l border-white/10 min-w-0">
        <span class="font-body-md text-body-md font-medium truncate">{{ $currentTenant->naziv ?? 'Investitor' }}</span>
      </div>
    </div>
    <div class="flex items-center gap-space-sm">
      <div class="flex items-center gap-space-sm">
        <div class="text-right hidden sm:block">
          <div class="font-body-sm text-body-sm font-semibold leading-tight">{{ $currentUser->ime_prezime ?? '' }}</div>
          <div class="font-label-xs text-label-xs text-on-primary-container leading-tight">{{ \App\Support\Prikaz::label($currentUser->uloga ?? '') }}</div>
        </div>
        <span class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary font-semibold text-sm">{{ $inicijali }}</span>
      </div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="p-1.5 rounded-lg text-on-primary-container hover:bg-primary-container hover:text-on-primary transition-colors flex items-center justify-center" title="Odjava" aria-label="Odjava">
          <span class="material-symbols-outlined text-[20px]">logout</span>
        </button>
      </form>
    </div>
  </div>
</header>

{{-- Pozadina ispod menija na telefonu --}}
<div class="fixed inset-0 top-14 z-30 bg-primary/40 hidden md:hidden" data-meni-pozadina></div>

<aside class="fixed left-0 top-14 bottom-0 w-64 bg-primary-container text-on-primary z-40 flex flex-col justify-between py-space-md -translate-x-full md:translate-x-0 transition-transform duration-200" data-meni>
  <div class="flex flex-col gap-space-xs">
    <div class="px-space-md py-space-xs"><span class="font-label-xs text-label-xs uppercase tracking-wider text-on-primary-container">Glavni meni</span></div>
    <nav class="flex flex-col gap-1 px-space-sm">
      @foreach($nav as [$ruta, $labela, $ikonica, $aktivno])
      @php $jeAktivna = request()->routeIs(...$aktivno); @endphp
      <a href="{{ route($ruta) }}" class="flex items-center gap-space-sm px-space-md py-2 rounded-lg transition-colors {{ $jeAktivna ? 'bg-surface-container-lowest text-on-surface font-semibold shadow-[0_1px_4px_rgba(0,0,0,0.06)]' : 'text-on-primary-container hover:bg-white/10 hover:text-on-primary' }}" @if($jeAktivna) aria-current="page" @endif>
        <span class="material-symbols-outlined text-[20px]">{{ $ikonica }}</span>
        <span class="font-body-md text-body-md">{{ $labela }}</span>
      </a>
      @endforeach
    </nav>
  </div>
  <div class="px-space-md pt-space-md">
    <div class="p-space-sm rounded-lg bg-primary flex flex-col gap-1">
      <div class="flex items-center justify-between">
        <span class="font-label-xs text-label-xs uppercase text-on-primary-container">Firma</span>
        <span class="font-label-xs text-label-xs text-tertiary-fixed-dim">Aktivna</span>
      </div>
      <div class="font-body-sm text-body-sm font-medium truncate">{{ $currentTenant->naziv ?? '' }}</div>
      @if(!empty($currentTenant->pib))<div class="font-label-xs text-label-xs text-on-primary-container">PIB {{ $currentTenant->pib }}</div>@endif
    </div>
  </div>
</aside>

<div class="md:pl-64">
<main class="w-full pt-14 px-space-md sm:px-space-lg pb-margin-lg bg-surface min-h-screen">
<div class="pt-space-lg">
@yield('content')
</div>
</main>
</div>

@if(session('uspesno'))
<div class="fixed bottom-6 right-6 left-6 sm:left-auto z-50 bg-primary text-on-primary px-space-md py-space-sm rounded-lg shadow-lg font-body-sm text-body-sm flex items-center gap-space-xs transition-opacity duration-300" id="action-toast">
  <span class="material-symbols-outlined text-[18px] text-tertiary-fixed-dim">check_circle</span>
  <span>{{ session('uspesno') }}</span>
</div>
<script>setTimeout(()=>{const t=document.getElementById('action-toast'); if(t){t.style.opacity='0'; setTimeout(()=>t.remove(),400);}},3500);</script>
@endif
@if($errors->any())
<div class="fixed bottom-6 right-6 left-6 sm:left-auto z-50 bg-error text-on-error px-space-md py-space-sm rounded-lg shadow-lg font-body-sm text-body-sm max-w-md" role="alert">
  <ul class="list-disc pl-4">@foreach($errors->all() as $greska)<li>{{ $greska }}</li>@endforeach</ul>
</div>
@endif
<script>
function toggleModal(id, show){ const m=document.getElementById(id); if(!m) return; m.classList.toggle('hidden', !show); }
document.querySelectorAll('[data-modal-open]').forEach(b=>b.addEventListener('click',()=>toggleModal(b.getAttribute('data-modal-open'), true)));
document.querySelectorAll('[data-modal-close]').forEach(b=>b.addEventListener('click',()=>toggleModal(b.getAttribute('data-modal-close'), false)));
// Modal se zatvara klikom na pozadinu i tasterom Esc
document.querySelectorAll('.fixed.inset-0[id]').forEach(m=>m.addEventListener('click',e=>{ if(e.target===m) m.classList.add('hidden'); }));
document.addEventListener('keydown',e=>{ if(e.key==='Escape') document.querySelectorAll('.fixed.inset-0[id]').forEach(m=>m.classList.add('hidden')); });
// Meni na telefonu
(function(){
  const meni=document.querySelector('[data-meni]'), poz=document.querySelector('[data-meni-pozadina]');
  const prikazi=d=>{ meni.classList.toggle('-translate-x-full', !d); poz.classList.toggle('hidden', !d); };
  document.querySelector('[data-meni-otvori]')?.addEventListener('click',()=>prikazi(meni.classList.contains('-translate-x-full')));
  poz?.addEventListener('click',()=>prikazi(false));
})();
</script>
@stack('scripts')
</body>
</html>
