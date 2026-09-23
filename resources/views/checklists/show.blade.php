@extends('layouts.app')

@section('content')
@php $procenat = $checklist->ukupno > 0 ? round($checklist->reseno / $checklist->ukupno * 100) : 0; @endphp
<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md">
    <div class="flex items-center gap-space-md">
      <a href="{{ route('projects.show', ['project' => $building->projekat_id, 'tab' => 'checkliste']) }}" class="p-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">arrow_back</span></a>
      <div>
        <div class="flex items-center gap-space-sm">
          <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-medium">Dosije Objekta</span>
          <span class="font-label-xs text-label-xs uppercase px-1.5 py-0.5 rounded bg-surface-container-high text-secondary font-medium">Interni Registar</span>
        </div>
        <h1 class="font-headline-lg text-headline-lg text-on-surface font-semibold tracking-tight">{{ $building->naziv }} — {{ str_replace('_', ' ', $checklist->tip_checkliste) }}</h1>
      </div>
    </div>
  </div>

  <!-- Proces selektor (3 fiksne checkliste) -->
  <div class="flex items-center gap-space-xs p-1 rounded-xl bg-surface-container-low max-w-fit overflow-x-auto">
    @foreach($tipovi as $tipC)
    <a href="{{ route('checklists.show', ['building' => $building, 'tip' => $tipC]) }}" class="flex items-center gap-space-sm px-space-md h-8 rounded-lg font-body-sm text-body-sm transition-all whitespace-nowrap {{ $tip === $tipC ? 'bg-surface-container-lowest text-on-surface font-semibold shadow-sm' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container font-medium' }}">
      <span class="material-symbols-outlined text-[18px] {{ $tip === $tipC ? 'text-secondary' : '' }}">{{ $tipC === 'Upotrebna_dozvola' ? 'verified' : ($tipC === 'Uknjizba' ? 'account_balance' : 'real_estate_agent') }}</span>
      <span>{{ str_replace('_', ' ', $tipC) }}</span>
      <span class="font-label-xs text-label-xs px-1.5 py-0.2 rounded {{ $tip === $tipC ? 'bg-surface-container-high text-on-surface-variant' : 'bg-surface-container text-on-surface-variant' }}">{{ $sveCheckliste->firstWhere('tip_checkliste', $tipC)?->reseno ?? 0 }}/{{ $sveCheckliste->firstWhere('tip_checkliste', $tipC)?->ukupno ?? 0 }}</span>
    </a>
    @endforeach
  </div>

  <!-- Napomena o strogo rucnoj overi (PRD 2) -->
  <div class="flex items-center justify-between p-space-md rounded-xl bg-surface-container-low text-on-surface shadow-sm gap-space-md">
    <div class="flex items-center gap-space-md">
      <div class="p-2 rounded-lg bg-surface-container text-secondary"><span class="material-symbols-outlined text-[20px]">info</span></div>
      <div class="flex flex-col">
        <span class="font-body-md text-body-md font-semibold">Protokol ručne overe administratora (Strogo interno)</span>
        <span class="font-body-sm text-body-sm text-on-surface-variant">Ovaj modul ne vrši automatsku validaciju niti je integrisan sa CEOP/opštinom. Status zavisi isključivo od neposredne revizije ovlašćenog rukovodioca.</span>
      </div>
    </div>
  </div>

  <!-- Progress (dozvoljena automatika: zbir stavki, PRD 9.4) -->
  <div class="p-space-lg rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-space-md">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-xs">
      <div class="flex items-baseline gap-space-sm">
        <span class="font-label-md text-label-md font-semibold text-on-surface">{{ $checklist->reseno }} / {{ $checklist->ukupno }} stavki rešeno</span>
        <span class="font-label-sm text-label-sm text-on-surface-variant">({{ $procenat }}%)</span>
      </div>
      <div class="flex items-center gap-space-md">
        <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant">Spremno za predaju zahteva:</span>
        @if($checklist->reseno === $checklist->ukupno)
        <span class="font-label-sm text-label-sm font-semibold text-on-tertiary-container flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-on-tertiary-container"></span>SPREMNO ZA PREDAJU</span>
        @else
        <span class="font-label-sm text-label-sm font-semibold text-error flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-error"></span>NEKOMPLETNO</span>
        @endif
      </div>
    </div>
    <div class="w-full h-2 rounded-full bg-surface-container overflow-hidden">
      <div class="h-full rounded-full transition-all duration-300 {{ $checklist->reseno === $checklist->ukupno ? 'bg-on-tertiary-container' : 'bg-secondary' }}" style="width: {{ $procenat }}%;"></div>
    </div>
  </div>

  <!-- Stavke checkliste -->
  <div class="p-space-lg rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-space-md">
    <div class="flex items-center justify-between pb-space-sm">
      <div>
        <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Obavezna dokumentacija — {{ str_replace('_', ' ', $checklist->tip_checkliste) }}</h2>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Dokumenti potrebni za formiranje tehničkog dosijea zgrade.</p>
      </div>
      <span class="font-label-xs text-label-xs text-on-surface-variant">Ručna overa: {{ $currentUser->ime_prezime }}</span>
    </div>
    <div class="flex flex-col gap-space-sm">
      @foreach($checklist->items as $index => $stavka)
      <div class="p-space-md rounded-xl transition-colors flex flex-col md:flex-row md:items-center justify-between gap-space-md {{ $stavka->zavrseno ? 'bg-surface-container-low' : 'bg-surface-container-lowest shadow-sm' }}">
        <div class="flex items-start md:items-center gap-space-md flex-1 min-w-0">
          <form method="POST" action="{{ route('checklists.toggle', $stavka) }}" class="mt-0.5 md:mt-0">
            @csrf @method('PATCH')
            <button type="submit" title="Označi kao završeno (ručno)" class="w-5 h-5 rounded flex items-center justify-center transition-all {{ $stavka->zavrseno ? 'bg-primary text-on-primary' : 'bg-surface-container hover:bg-surface-container-high' }}">
              <span class="material-symbols-outlined text-[16px]">{{ $stavka->zavrseno ? 'check' : '' }}</span>
            </button>
          </form>
          <div class="flex items-center justify-center shrink-0">
            @if($stavka->zavrseno)
            <span class="w-7 h-7 rounded-full bg-surface-container-high flex items-center justify-center text-on-tertiary-container" title="Završeno i overeno"><span class="material-symbols-outlined text-[18px]">check_circle</span></span>
            @else
            <span class="w-7 h-7 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">radio_button_unchecked</span></span>
            @endif
          </div>
          <div class="flex flex-col min-w-0">
            <div class="flex items-center gap-space-sm flex-wrap">
              <span class="font-body-md text-body-md font-semibold text-on-surface">Stavka {{ $index + 1 }}: {{ $stavka->naziv_stavke }}</span>
              @if($stavka->zavrseno)
              <span class="font-label-xs text-label-xs uppercase px-1.5 py-0.5 rounded bg-surface-container text-on-tertiary-container font-semibold">Završeno</span>
              @else
              <span class="font-label-xs text-label-xs uppercase px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface-variant font-medium">Nezavršeno</span>
              @endif
            </div>
            @if(!$stavka->zavrseno)
            <span class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
              @if($stavka->predlog_zavrseno)
              <span class="text-on-tertiary-container font-medium">Vizuelni predlog:</span> postoji dokument tipa [{{ str_replace('_', ' ', $stavka->povezani_tip_dokumenta) }}] — potvrdite kvačicom ili priložite.
              @else
              Nedostaje fajl tipa [{{ str_replace('_', ' ', $stavka->povezani_tip_dokumenta) }}]. Potrebno je ručno priložiti dokument ili štiklirati stavku.
              @endif
            </span>
            @endif
          </div>
        </div>
        <div class="flex items-center gap-space-sm shrink-0 self-end md:self-center pl-10 md:pl-0">
          @if(!$stavka->zavrseno)
          <a href="{{ route('projects.show', ['project' => $building->projekat_id, 'tab' => 'dokumentacija']) }}" class="px-space-md h-8 rounded-lg bg-surface-container-lowest hover:bg-surface-container text-secondary font-body-sm text-body-sm font-semibold shadow-sm transition-colors flex items-center gap-space-xs">
            <span class="material-symbols-outlined text-[16px]">add</span><span>+ Dodaj dokument</span>
          </a>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <!-- Kontekst objekta -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant">Objekat</span><span class="font-body-md text-body-md font-semibold text-on-surface">{{ $building->naziv }}</span><span class="font-body-sm text-body-sm text-on-surface-variant">{{ $building->project->naziv ?? '' }}</span></div>
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant">Status zgrade</span><span class="font-body-md text-body-md font-mono font-semibold text-on-surface">{{ $building->status }}</span></div>
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant">Broj jedinica</span><span class="font-body-md text-body-md font-semibold text-on-surface">{{ $building->units()->count() }}</span></div>
  </div>
</div>
@endsection
