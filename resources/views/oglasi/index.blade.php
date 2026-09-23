@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', 'Oglasi na Temelju')

@section('content')
@php
  $aktivni = $oglasi->filter(fn ($o) => $o->status === 'aktivan')->count();
  $noviUkupno = $upitiPoStanu->sum('novih');
@endphp

<x-zaglavlje naslov="Oglasi na Temelju" opis="Stanovi u prodaji koje kupci vide na Temelj.rs — uz ocene vaše firme. Upiti stižu direktno ovde." />

@include('oglasi._veza')

<section class="grid grid-cols-2 lg:grid-cols-4 gap-gutter" aria-label="Pregled oglasa">
  <x-pokazatelj labela="Vidljivi na Temelju" :vrednost="$aktivni" ikonica="campaign" opis="aktivnih oglasa" />
  <x-pokazatelj labela="Novi upiti" :vrednost="$noviUkupno" ikonica="forum" opis="čekaju odgovor" :href="route('upiti.index', ['status' => 'novo'])" />
  <x-pokazatelj labela="Spremno za oglas" :vrednost="$spremni->count()" ikonica="add_home" opis="stanova bez oglasa" />
  <x-pokazatelj labela="Nisu stigli" :vrednost="$oglasi->filter(fn ($o) => $o->greska_sinhronizacije)->count()" ikonica="sync_problem" ton="greska" opis="treba ponoviti slanje" />
</section>

<section class="kartica overflow-hidden">
  <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
    <h2 class="font-headline-sm text-headline-sm">Vaši oglasi</h2>
  </div>
  @if($oglasi->isEmpty())
    <x-prazno ikonica="campaign" naslov="Još nemate nijedan oglas" tekst="Izaberite stan iz liste „Spremno za oglas“ ispod, dodajte fotografije i cenu — to je sve. Oglas se sam skida kad stan prodate." />
  @else
  <div class="overflow-x-auto">
    <table class="tabela">
      <thead>
        <tr>
          <th>Stan</th>
          <th>Cena</th>
          <th>Stanje</th>
          <th class="text-center">Upiti</th>
          <th class="text-right">Akcije</th>
        </tr>
      </thead>
      <tbody>
        @foreach($oglasi as $oglas)
        @php
          $stan = $oglas->unit;
          $naslovna = $oglas->slike->firstWhere('tip', 'slika');
          $up = $upitiPoStanu[$stan->id] ?? null;
        @endphp
        <tr data-href="{{ route('oglasi.forma', $stan) }}">
          <td>
            <div class="flex items-center gap-space-sm min-w-[220px]">
              @if($naslovna)
                <img src="{{ $naslovna->urlMala() }}" alt="" class="w-14 h-10 rounded object-cover bg-surface-container shrink-0" loading="lazy">
              @else
                <span class="w-14 h-10 rounded bg-surface-container flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-[18px] text-on-surface-variant">image</span></span>
              @endif
              <div class="min-w-0">
                <a href="{{ route('units.show', $stan) }}" class="font-semibold hover:underline underline-offset-2">Stan {{ $stan->oznaka }}</a>
                <div class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $stan->building->project->naziv ?? '' }} · {{ $stan->building->naziv ?? '' }}</div>
              </div>
            </div>
          </td>
          <td class="font-mono-num whitespace-nowrap">{{ $oglas->cena_na_upit || !$stan->cena ? 'Na upit' : number_format($stan->cena, 0, ',', '.').' €' }}</td>
          <td>
            <x-oglas-stanje :oglas="$oglas" :tenant="$tenant" />
            @if($stan->status === 'Rezervisan' && $oglas->status !== 'skinut')<div class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">prikazuje se kao „Rezervisano“</div>@endif
          </td>
          <td class="text-center">
            @if($up)
              <a href="{{ route('upiti.index') }}" class="inline-flex items-center gap-1 {{ $up->novih ? 'font-semibold text-emerald-700' : 'text-on-surface-variant' }}">{{ $up->ukupno }}@if($up->novih)<span class="cip bg-emerald-50 text-emerald-800">{{ $up->novih }} novo</span>@endif</a>
            @else
              <span class="text-on-surface-variant">0</span>
            @endif
          </td>
          <td class="text-right whitespace-nowrap">
            <div class="inline-flex items-center gap-1">
              @if($oglas->greska_sinhronizacije && $tenant->povezanSaTemeljem())
              <form method="POST" action="{{ route('oglasi.ponovi', $oglas) }}">@csrf<button class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">sync</span>Pošalji ponovo</button></form>
              @endif
              @if($oglas->temelj_url && $oglas->status === 'aktivan' && $oglas->sinhronizovan_at)
              <a href="{{ $oglas->temelj_url }}" target="_blank" rel="noopener" class="dugme-tiho dugme-malo" title="Pogledaj oglas na Temelju"><span class="material-symbols-outlined text-[16px]">open_in_new</span></a>
              @endif
              <a href="{{ route('oglasi.forma', $stan) }}" class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">edit</span>Izmeni</a>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>

<section class="kartica overflow-hidden">
  <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
    <div class="flex items-center gap-space-sm">
      <h2 class="font-headline-sm text-headline-sm">Spremno za oglas</h2>
      <span class="cip bg-surface-container text-on-surface-variant">{{ $spremni->count() }}</span>
    </div>
    <span class="font-body-sm text-body-sm text-on-surface-variant hidden sm:inline">Stanovi „Za prodaju“ i „Rezervisani“ koji još nisu na Temelju</span>
  </div>
  @if($spremni->isEmpty())
    <x-prazno ikonica="task_alt" naslov="Svi stanovi u prodaji su oglašeni" tekst="Kad stanu promenite status u „Za prodaju“, pojaviće se ovde." />
  @else
  <div class="overflow-x-auto">
    <table class="tabela">
      <thead><tr><th>Stan</th><th>Zgrada</th><th class="text-right">Kvadratura</th><th class="text-right">Cena</th><th>Status</th><th class="text-right"></th></tr></thead>
      <tbody>
        @foreach($spremni as $stan)
        <tr data-href="{{ route('oglasi.forma', $stan) }}">
          <td class="font-semibold">Stan {{ $stan->oznaka }}</td>
          <td class="text-on-surface-variant">{{ $stan->building->project->naziv ?? '' }} · {{ $stan->building->naziv ?? '' }}</td>
          <td class="text-right font-mono-num whitespace-nowrap">{{ $stan->kvadratura ? number_format($stan->kvadratura, 2, ',', '.').' m²' : '—' }}</td>
          <td class="text-right font-mono-num whitespace-nowrap">{{ $stan->cena ? number_format($stan->cena, 0, ',', '.').' €' : '—' }}</td>
          <td><x-status :v="$stan->status" /></td>
          <td class="text-right"><a href="{{ route('oglasi.forma', $stan) }}" class="dugme-primarno dugme-malo"><span class="material-symbols-outlined text-[16px]">campaign</span>Oglasi</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>
@endsection
