@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', Prikaz::label($checklist->tip_checkliste).' · '.$building->naziv)

@section('content')
@php
  $ukupno = $checklist->items->count();
  $reseno = $checklist->items->where('zavrseno', true)->count();
  $procenat = $ukupno > 0 ? round($reseno / $ukupno * 100) : 0;
  $kompletno = $ukupno > 0 && $reseno === $ukupno;
  $mozeMenjati = $currentUser->mozeUredjivati();
  $putanja = ['Checkliste' => route('checklists.index')];
  if ($building->project) { $putanja[$building->project->naziv] = route('projects.show', ['project' => $building->project, 'zgrada' => $building->id, 'tab' => 'checkliste']); }
  $putanja[$building->naziv] = null;
@endphp

<x-zaglavlje :naslov="Prikaz::label($checklist->tip_checkliste)" :putanja="$putanja" :opis="'Zgrada '.$building->naziv.' — dokumenta potrebna za predaju zahteva.'">
  <x-slot:uzNaslov>
    <span class="cip {{ $kompletno ? 'bg-emerald-50 text-emerald-800' : 'bg-tertiary-fixed text-on-tertiary-fixed' }}">{{ $kompletno ? 'Spremno za predaju' : 'U toku' }}</span>
  </x-slot:uzNaslov>
  <button type="button" onclick="window.print()" class="dugme-sekundarno ne-stampaj"><span class="material-symbols-outlined text-[18px]">print</span>Štampaj</button>
</x-zaglavlje>

{{-- Izbor jedne od 3 fiksne checkliste zgrade --}}
<nav class="flex flex-wrap items-center gap-1 p-1 rounded-lg bg-surface-container-low self-start ne-stampaj" aria-label="Checkliste zgrade">
  @foreach($tipovi as $t)
  @php $c = $sveCheckliste->firstWhere('tip_checkliste', $t); $aktivna = $tip === $t; @endphp
  <a href="{{ route('checklists.show', ['building' => $building, 'tip' => $t]) }}" @if($aktivna) aria-current="page" @endif
     class="inline-flex items-center gap-1.5 h-8 px-space-md rounded font-label-md text-label-md transition-colors {{ $aktivna ? 'bg-surface-container-lowest text-on-surface font-semibold shadow-sm' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}">
    {{ Prikaz::label($t) }}
    <span class="cip {{ $c && $c->ukupno_stavki && $c->reseno_stavki === $c->ukupno_stavki ? 'bg-emerald-50 text-emerald-800' : 'bg-surface-container text-on-surface-variant' }}">{{ $c->reseno_stavki ?? 0 }}/{{ $c->ukupno_stavki ?? 0 }}</span>
  </a>
  @endforeach
</nav>

<section class="kartica overflow-hidden">
  {{-- Napredak (dozvoljena automatika: zbir stavki, PRD 9.4) --}}
  <div class="px-space-lg py-space-md flex flex-col gap-space-sm border-b border-surface-container">
    <div class="flex items-center justify-between font-body-md text-body-md">
      <span class="text-on-surface-variant">Napredak</span>
      <span class="font-semibold font-mono-num">{{ $reseno }} / {{ $ukupno }} stavki rešeno ({{ $procenat }}%)</span>
    </div>
    <div class="h-2 rounded-full bg-surface-container overflow-hidden"><div class="h-full rounded-full {{ $kompletno ? 'bg-emerald-500' : 'bg-primary' }}" style="width: {{ $procenat }}%"></div></div>
  </div>

  <ol class="flex flex-col">
    @foreach($checklist->items as $i => $stavka)
    @php
      $dok = $stavka->povezani_tip_dokumenta ? ($dokumentiPoTipu[$stavka->povezani_tip_dokumenta] ?? null) : null;
    @endphp
    <li class="px-space-lg py-space-md flex flex-col md:flex-row md:items-center justify-between gap-space-md {{ $i > 0 ? 'border-t border-surface-container' : '' }} hover:bg-surface-container-low/40 transition-colors">
      <div class="flex items-start gap-space-md min-w-0">
        {{-- Ručno štikliranje (PRD 6.6 / 2.3) — nezavisno od postojanja dokumenta --}}
        <form method="POST" action="{{ route('checklists.toggle', $stavka) }}" class="pt-0.5">
          @csrf @method('PATCH')
          <button type="submit" @disabled(!$mozeMenjati) title="{{ $stavka->zavrseno ? 'Vrati u nezavršene' : 'Označi kao završeno' }}"
                  class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors {{ $stavka->zavrseno ? 'bg-primary border-primary text-on-primary' : 'border-outline bg-surface-container-lowest hover:border-primary' }} disabled:opacity-50">
            @if($stavka->zavrseno)<span class="material-symbols-outlined text-[16px]">check</span>@endif
          </button>
        </form>
        <div class="flex flex-col gap-1 min-w-0">
          <div class="flex flex-wrap items-center gap-space-sm">
            <span class="font-label-md text-label-md font-semibold">{{ $i + 1 }}. {{ Prikaz::stavka($stavka->naziv_stavke) }}</span>
            @if($stavka->zavrseno)
              <span class="cip bg-emerald-50 text-emerald-800"><span class="material-symbols-outlined text-[14px]">check_circle</span>Završeno</span>
            @else
              <span class="cip bg-error-container/60 text-on-error-container"><span class="material-symbols-outlined text-[14px]">cancel</span>Nedostaje</span>
            @endif
          </div>
          @if($dok)
            <div class="flex flex-wrap items-center gap-x-space-sm gap-y-0.5 font-body-sm text-body-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-[16px]">description</span>
              <span class="font-medium text-on-surface">{{ $dok->naziv }}</span>
              <span class="cip bg-surface-container text-on-surface">v{{ $dok->verzija }}</span>
              @if($dok->datum_izdavanja)<span>· izdat {{ $dok->datum_izdavanja->format('d.m.Y.') }}</span>@endif
              @unless($stavka->zavrseno)<span class="text-secondary font-medium">· dokument postoji — potvrdite kvačicom</span>@endunless
            </div>
          @elseif(!$stavka->zavrseno)
            <div class="flex items-center gap-1.5 font-body-sm text-body-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-[16px] text-error">warning</span>
              Nije priložen dokument tipa „{{ Prikaz::label($stavka->povezani_tip_dokumenta) }}“. Priložite ga ili stavku potvrdite ručno.
            </div>
          @endif
        </div>
      </div>
      <div class="flex items-center gap-space-sm shrink-0 self-end md:self-center ne-stampaj">
        @if($dok && $dok->imaFajl())
          <a href="{{ route('documents.download', $dok) }}" class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">download</span>Preuzmi</a>
        @endif
        @if(!$dok && $stavka->povezani_tip_dokumenta)
          <button type="button" class="dugme-primarno dugme-malo" data-modal-open="modal-dokument" data-postavi='@json(['tip' => $stavka->povezani_tip_dokumenta])'>
            <span class="material-symbols-outlined text-[16px]">add</span>Dodaj dokument
          </button>
        @endif
      </div>
    </li>
    @endforeach
  </ol>
</section>

<div class="p-space-md rounded-lg bg-surface-container-low flex items-start gap-space-md">
  <span class="material-symbols-outlined text-[20px] text-on-surface-variant">info</span>
  <p class="font-body-md text-body-md text-on-surface-variant"><strong class="text-on-surface font-semibold">Ručna potvrda.</strong> Štikliranje stavke je administrativna potvrda vašeg tima. Sistem ne proverava pravnu ispravnost dokumenata niti rokove.</p>
</div>

@include('documents._modal', ['zgrada' => $building])
@endsection
