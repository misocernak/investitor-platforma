<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<title>{{ trim($__env->yieldContent('naslov')) ? trim($__env->yieldContent('naslov')).' · ' : '' }}Temelj Investitori</title>
<script>try{if(localStorage.getItem('meniUzak')==='1')document.documentElement.classList.add('meni-uzak')}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
{{-- Samo ikonice koje aplikacija koristi (spisak u config/ikonice.php) — nekoliko KB umesto više MB --}}
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names={{ implode(',', config('ikonice')) }}&display=block" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: 1 }}"/>
@stack('head')
</head>
<body class="bg-surface font-body-md text-body-md text-on-surface antialiased">
@php
  $nav = [
    ['dashboard', 'Nadzorna tabla', 'grid_view', ['dashboard']],
    ['projects.index', 'Projekti i zgrade', 'apartment', ['projects.*']],
    ['units.index', 'Stanovi', 'door_front', ['units.*']],
    ['checklists.index', 'Checkliste', 'fact_check', ['checklists.*']],
    ['claims.index', 'Reklamacije', 'build_circle', ['claims.*']],
    ['documents.index', 'Dokumentacija', 'folder_open', ['documents.*']],
    ['oglasi.index', 'Oglasi na Temelju', 'campaign', ['oglasi.*']],
    ['upiti.index', 'Upiti kupaca', 'forum', ['upiti.*']],
  ];
  if ($currentUser && $currentUser->jeNadzor()) {
    $nav = [['claims.index', 'Moje reklamacije', 'build_circle', ['claims.*']]];
  }
  if ($currentUser && $currentUser->mozeAdministrirati()) {
    $nav[] = ['recenzije.index', 'Recenzije kupaca', 'reviews', ['recenzije.*']];
    $nav[] = ['users.index', 'Korisnici i uloge', 'group', ['users.*']];
  }
  $jePlatforma = $currentUser?->jePlatforma();
  if ($jePlatforma) {
    $nav = [['platforma.index', 'Firme i zahtevi', 'domain', ['platforma.*']]];
  }
  // Broj otvorenih reklamacija za oznaku u meniju (Nadzor vidi samo svoje dodele)
  $otvoreneUMeniju = $jePlatforma ? 0 : \App\Models\Claim::whereNotIn('status', ['Resena', 'Odbijena'])
    ->when($currentUser?->jeNadzor(), fn ($q) => $q->where('odgovorni_id', $currentUser->id))
    ->count();
  $zahteviNaCekanju = $jePlatforma ? \App\Models\Tenant::where('status', 'na_cekanju')->count() : 0;
  // Novi upiti kupaca sa Temelja (tabela postoji tek posle migracije 2026_09_23)
  $noviUpiti = 0;
  if ($currentUser && !$currentUser->jeNadzor() && !$jePlatforma) {
    try { $noviUpiti = \App\Models\Upit::where('status', 'novo')->count(); } catch (\Throwable $e) { $noviUpiti = 0; }
  }
  $inicijali = collect(explode(' ', $currentUser->ime_prezime ?? '?'))->filter()->take(2)->map(fn ($d) => mb_strtoupper(mb_substr($d, 0, 1)))->implode('');
  $firma = $jePlatforma ? 'Admin platforme' : ($currentTenant->naziv ?? 'Investitor');
@endphp

{{-- Gornja traka — samo na telefonu (na računaru je sve u levom meniju, da sadržaj dobije punu visinu ekrana) --}}
<header class="meni-gornji md:hidden fixed top-0 inset-x-0 h-14 z-40 bg-surface-container-lowest border-b border-outline-variant/40 flex items-center gap-space-sm px-margin-mobile">
  <button type="button" class="w-9 h-9 -ml-1.5 rounded flex items-center justify-center text-on-surface hover:bg-surface-container-low" data-meni-otvori aria-label="Otvori meni">
    <span class="material-symbols-outlined text-[24px]">menu</span>
  </button>
  <span class="w-7 h-7 rounded bg-primary text-on-primary flex items-center justify-center font-bold text-[15px]">T</span>
  <span class="font-label-md text-label-md font-semibold truncate">{{ $firma }}</span>
</header>
<div class="fixed inset-0 z-40 bg-inverse-surface/40 hidden md:hidden" data-meni-pozadina></div>

{{-- Levi meni --}}
<aside class="meni-bocni fixed left-0 top-0 bottom-0 w-60 z-50 bg-surface-container-lowest border-r border-outline-variant/40 flex flex-col -translate-x-full md:translate-x-0 transition-[transform,width] duration-200 select-none" data-meni>
  <div class="h-14 px-space-md border-b border-outline-variant/30 flex items-center gap-space-sm shrink-0">
    <a href="{{ route($nav[0][0]) }}" class="flex items-center gap-space-sm min-w-0 flex-1" title="{{ $firma }}">
      <span class="w-8 h-8 rounded bg-primary text-on-primary flex items-center justify-center shrink-0 font-bold text-[16px]">T</span>
      <span class="meni-tekst flex flex-col min-w-0 leading-tight">
        <span class="font-label-md text-label-md text-on-surface font-semibold truncate">{{ $firma }}</span>
        <span class="text-[10px] font-semibold uppercase tracking-wider text-on-surface-variant truncate">Temelj · Investitori</span>
      </span>
    </a>
  </div>

  <nav class="flex-1 overflow-y-auto px-space-sm py-space-md flex flex-col gap-0.5" aria-label="Glavni meni">
    <span class="meni-tekst px-space-sm pb-space-xs text-[10px] font-semibold uppercase tracking-wider text-on-surface-variant/70">Meni</span>
    @foreach($nav as [$ruta, $labela, $ikonica, $aktivno])
    @php $jeAktivna = request()->routeIs(...$aktivno); @endphp
    <a href="{{ route($ruta) }}" title="{{ $labela }}" @if($jeAktivna) aria-current="page" @endif
       class="meni-stavka relative flex items-center gap-space-sm h-9 px-space-sm rounded transition-colors {{ $jeAktivna ? 'bg-surface-container text-on-surface font-semibold' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
      <span class="material-symbols-outlined text-[20px]">{{ $ikonica }}</span>
      <span class="meni-tekst font-body-md text-body-md flex-1 truncate {{ $jeAktivna ? 'font-semibold' : '' }}">{{ $labela }}</span>
      @if($ruta === 'claims.index' && $otvoreneUMeniju > 0)
      <span class="meni-tekst min-w-[20px] h-5 px-1.5 rounded bg-error-container text-on-error-container text-[11px] font-semibold flex items-center justify-center">{{ $otvoreneUMeniju }}</span>
      @endif
      @if($ruta === 'platforma.index' && $zahteviNaCekanju > 0)
      <span class="meni-tekst min-w-[20px] h-5 px-1.5 rounded bg-amber-500 text-white text-[11px] font-semibold flex items-center justify-center" title="Zahtevi na čekanju">{{ $zahteviNaCekanju }}</span>
      @endif
      @if($ruta === 'upiti.index' && $noviUpiti > 0)
      <span class="meni-tekst min-w-[20px] h-5 px-1.5 rounded bg-emerald-600 text-white text-[11px] font-semibold flex items-center justify-center" title="Novi upiti">{{ $noviUpiti }}</span>
      @endif
    </a>
    @endforeach
  </nav>

  <div class="p-space-sm border-t border-outline-variant/30 flex flex-col gap-0.5 shrink-0">
    <button type="button" class="meni-stavka hidden md:flex items-center gap-space-sm h-9 px-space-sm rounded text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface" data-meni-suzi title="Suzi / proširi meni">
      <span class="material-symbols-outlined meni-okreni text-[20px] transition-transform">left_panel_close</span>
      <span class="meni-tekst font-body-md text-body-md">Suzi meni</span>
    </button>
    <div class="meni-stavka flex items-center gap-space-sm p-1.5 rounded">
      <span class="meni-tekst w-8 h-8 rounded-full bg-surface-container-highest text-on-surface flex items-center justify-center shrink-0 text-[12px] font-semibold" title="{{ $currentUser->ime_prezime ?? '' }}">{{ $inicijali }}</span>
      <a href="{{ route('nalog.lozinka') }}" class="meni-tekst flex flex-col min-w-0 flex-1 leading-tight hover:underline underline-offset-2" title="Moj nalog — promena lozinke">
        <span class="font-label-md text-label-md text-on-surface truncate">{{ $currentUser->ime_prezime ?? '' }}</span>
        <span class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $jePlatforma ? 'Admin platforme' : \App\Support\Prikaz::label($currentUser->uloga ?? '') }}</span>
      </a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded text-on-surface-variant hover:text-error hover:bg-error-container/40 transition-colors" title="Odjava" aria-label="Odjava">
          <span class="material-symbols-outlined text-[18px]">logout</span>
        </button>
      </form>
    </div>
  </div>
</aside>

<div class="meni-sadrzaj md:pl-60 transition-[padding] duration-200">
  <main class="min-h-screen pt-14 md:pt-0">
    <div class="w-full max-w-[1440px] mx-auto px-margin-mobile sm:px-margin py-space-lg flex flex-col gap-space-lg">
      @yield('content')
    </div>
  </main>
</div>

@if(session('uspesno'))
<div class="fixed bottom-5 right-5 left-5 sm:left-auto z-[70] bg-primary text-on-primary px-space-md py-space-sm rounded-lg shadow-xl font-body-md text-body-md flex items-center gap-space-sm transition-opacity duration-300" id="poruka-uspeh" role="status">
  <span class="material-symbols-outlined text-[20px] text-emerald-400">task_alt</span>
  <span>{{ session('uspesno') }}</span>
</div>
<script>setTimeout(()=>{const t=document.getElementById('poruka-uspeh'); if(t){t.style.opacity='0'; setTimeout(()=>t.remove(),400);}},4000);</script>
@endif
@if($errors->any())
<div class="fixed bottom-5 right-5 left-5 sm:left-auto z-[70] bg-error text-on-error px-space-md py-space-sm rounded-lg shadow-xl font-body-md text-body-md max-w-md flex gap-space-sm" role="alert">
  <span class="material-symbols-outlined text-[20px]">error</span>
  <ul class="flex flex-col gap-0.5">@foreach($errors->all() as $greska)<li>{{ $greska }}</li>@endforeach</ul>
  <button type="button" class="ml-auto self-start opacity-80 hover:opacity-100" onclick="this.parentElement.remove()" aria-label="Zatvori"><span class="material-symbols-outlined text-[18px]">close</span></button>
</div>
@endif

<script>
(function(){
  // --- Iskačući prozori (modali) ---
  const prikaziModal = (m, d) => { if(!m) return; m.classList.toggle('hidden', !d); m.classList.toggle('flex', d); document.body.style.overflow = d ? 'hidden' : ''; };
  document.addEventListener('click', e => {
    const otvori = e.target.closest('[data-modal-open]');
    if (otvori) {
      const m = document.getElementById(otvori.dataset.modalOpen);
      // data-postavi='{"tip":"..."}' — unapred popunjava polja u prozoru (npr. tip dokumenta iz checkliste)
      if (m && otvori.dataset.postavi) {
        try { const v = JSON.parse(otvori.dataset.postavi); Object.keys(v).forEach(k => { const p = m.querySelector('[name="'+k+'"]'); if (p) { p.value = v[k]; p.dispatchEvent(new Event('change')); } }); } catch(_) {}
      }
      prikaziModal(m, true);
      setTimeout(() => m?.querySelector('input:not([type=hidden]):not([readonly]), select, textarea')?.focus(), 50);
      return;
    }
    const zatvori = e.target.closest('[data-modal-close]');
    if (zatvori) { prikaziModal(document.getElementById(zatvori.dataset.modalClose), false); return; }
    if (e.target.matches('[data-modal]')) { prikaziModal(e.target, false); return; }

    // --- Klik na red tabele otvara detalj (osim klika na link/dugme/polje u redu) ---
    const red = e.target.closest('tr[data-href]');
    if (red && !e.target.closest('a,button,input,select,textarea,label,form')) {
      if (e.ctrlKey || e.metaKey) window.open(red.dataset.href, '_blank'); else window.location = red.dataset.href;
    }
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('[data-modal]:not(.hidden)').forEach(m => prikaziModal(m, false)); });

  // --- Filteri: promena izbora odmah primenjuje filter ---
  document.querySelectorAll('[data-auto-submit]').forEach(p => p.addEventListener('change', () => p.form.submit()));

  // --- Potvrda pre osetljivih akcija ---
  document.querySelectorAll('form[data-potvrdi]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.potvrdi)) e.preventDefault(); }));

  // --- Levi meni: sklapanje na računaru, izvlačenje na telefonu ---
  document.querySelector('[data-meni-suzi]')?.addEventListener('click', () => {
    const uzak = document.documentElement.classList.toggle('meni-uzak');
    try { localStorage.setItem('meniUzak', uzak ? '1' : '0'); } catch(_) {}
  });
  const meni = document.querySelector('[data-meni]'), poz = document.querySelector('[data-meni-pozadina]');
  const prikaziMeni = d => { meni.classList.toggle('-translate-x-full', !d); poz.classList.toggle('hidden', !d); };
  document.querySelector('[data-meni-otvori]')?.addEventListener('click', () => prikaziMeni(true));
  poz?.addEventListener('click', () => prikaziMeni(false));
})();
</script>
@stack('scripts')
</body>
</html>
