@extends('layouts.app')
@section('naslov', 'Stanovi')

@section('content')
@php
  $putanja = ['Stanovi' => null];
  if ($zgrada) {
    $putanja = ['Projekti i zgrade' => route('projects.index')];
    if ($zgrada->project) { $putanja[$zgrada->project->naziv] = route('projects.show', ['project' => $zgrada->project, 'zgrada' => $zgrada->id]); }
    $putanja[$zgrada->naziv] = null;
  }
  $grupa = request('grupa');
  $link = fn ($g) => route('units.index', array_filter(['zgrada' => $zgrada?->id, 'grupa' => $grupa === $g ? null : $g, 'q' => request('q')]));
@endphp

<x-zaglavlje naslov="Stanovi" :putanja="$putanja">
  @if($zgrade->count() > 1)
  <form method="GET" action="{{ route('units.index') }}">
    <select name="zgrada" data-auto-submit class="polje w-auto min-w-[220px]" aria-label="Zgrada">
      @foreach($zgrade as $z)<option value="{{ $z->id }}" @selected($zgrada && $zgrada->id === $z->id)>{{ $z->naziv }}{{ $z->project ? ' — '.$z->project->naziv : '' }}</option>@endforeach
    </select>
  </form>
  @endif
  @if($zgrada)
  <button type="button" data-modal-open="modal-novi-stan" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Dodaj stan</button>
  @endif
</x-zaglavlje>

@if(!$zgrada)
  <section class="kartica">
    <x-prazno ikonica="apartment" naslov="Još nema zgrada" tekst="Stanovi se vode po zgradi. Kreirajte projekat i dodajte mu zgradu.">
      <a href="{{ route('projects.index') }}" class="dugme-primarno">Idi na projekte</a>
    </x-prazno>
  </section>
@else
  {{-- Klik na karticu filtrira tabelu (ponovni klik skida filter) --}}
  <section class="grid grid-cols-2 lg:grid-cols-4 gap-gutter" aria-label="Pregled jedinica">
    <x-pokazatelj labela="Prodato" :vrednost="$statistika['prodato']" :opis="'od '.$statistika['ukupno'].' jedinica'" :href="$link('prodato')" :aktivno="$grupa === 'prodato'" />
    <x-pokazatelj labela="Za prodaju" :vrednost="$statistika['za_prodaju']" opis="slobodno" :href="$link('za_prodaju')" :aktivno="$grupa === 'za_prodaju'" />
    <x-pokazatelj labela="Rezervisano" :vrednost="$statistika['rezervisan']" opis="u pregovorima" :href="$link('rezervisan')" :aktivno="$grupa === 'rezervisan'" />
    <x-pokazatelj labela="Otvorene reklamacije" :vrednost="$statistika['otvorene_reklamacije']" ton="greska" opis="na stanovima" :href="$link('reklamacije')" :aktivno="$grupa === 'reklamacije'" />
  </section>

  <section class="kartica overflow-hidden">
    <form method="GET" action="{{ route('units.index') }}" class="px-space-lg py-space-md flex flex-wrap items-center gap-space-sm">
      <input type="hidden" name="zgrada" value="{{ $zgrada->id }}"/>
      @if($grupa)<input type="hidden" name="grupa" value="{{ $grupa }}"/>@endif
      <div class="relative flex-1 min-w-[220px] max-w-sm">
        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant pointer-events-none">search</span>
        <input name="q" value="{{ request('q') }}" placeholder="Pretraži po oznaci ili kupcu…" class="polje pl-9" aria-label="Pretraga"/>
      </div>
      <button class="dugme-sekundarno">Traži</button>
      @if(request('q') || $grupa)
      <a href="{{ route('units.index', ['zgrada' => $zgrada->id]) }}" class="dugme-tiho"><span class="material-symbols-outlined text-[16px]">filter_alt_off</span>Poništi filtere</a>
      @endif
      <span class="ml-auto font-body-sm text-body-sm text-on-surface-variant">Prikazano {{ $stanovi->count() }} od {{ $statistika['ukupno'] }}</span>
    </form>
    @if($stanovi->isEmpty())
      @if($statistika['ukupno'] === 0)
        <x-prazno ikonica="door_front" naslov="Nema evidentiranih stanova" :tekst="'Dodajte stanove i lokale zgrade '.$zgrada->naziv.' — uz svaki vodite kupca, dokumente i reklamacije.'">
          <button type="button" data-modal-open="modal-novi-stan" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Dodaj stan</button>
        </x-prazno>
      @else
        <x-prazno ikonica="search_off" naslov="Nema rezultata" tekst="Nijedan stan ne odgovara pretrazi ili filteru." />
      @endif
    @else
      @include('units._tabela')
    @endif
  </section>

  @include('units._modal_novi')
@endif
@endsection
