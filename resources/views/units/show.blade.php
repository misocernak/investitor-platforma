@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', 'Stan '.$stan->oznaka)

@section('content')
@php
  $zgrada = $stan->building;
  $projekat = $zgrada?->project;
  $putanja = ['Projekti i zgrade' => route('projects.index')];
  if ($projekat) { $putanja[$projekat->naziv] = route('projects.show', ['project' => $projekat, 'zgrada' => $zgrada->id]); }
  if ($zgrada) { $putanja[$zgrada->naziv] = route('units.index', ['zgrada' => $zgrada->id]); }
  $putanja['Stan '.$stan->oznaka] = null;
  $otvorene = $stan->claims->whereNotIn('status', ['Resena', 'Odbijena'])->count();
  $stan->claims->each->setRelation('unit', $stan);
  $stan->setRelation('building', $zgrada);
@endphp

<x-zaglavlje :naslov="'Stan '.$stan->oznaka" :putanja="$putanja"
  :opis="collect([$zgrada?->naziv, $stan->sprat ? 'sprat '.$stan->sprat : null, $stan->kvadratura ? number_format($stan->kvadratura, 2, ',', '.').' m²' : null])->filter()->implode(' · ')">
  <x-slot:uzNaslov><x-status :v="$stan->status" /></x-slot:uzNaslov>
  <button type="button" data-modal-open="modal-dokument" class="dugme-sekundarno"><span class="material-symbols-outlined text-[18px]">upload_file</span>Dodaj dokument</button>
  <button type="button" data-modal-open="modal-nova-reklamacija" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Nova reklamacija</button>
</x-zaglavlje>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
  {{-- Osnovni podaci i kupac (PRD 9.3, sekcija 1) — izmena na licu mesta --}}
  <section class="lg:col-span-5 kartica p-space-lg">
    <form method="POST" action="{{ route('units.update', $stan) }}" class="flex flex-col gap-space-md">
      @csrf @method('PATCH')
      <h2 class="font-headline-sm text-headline-sm">Osnovni podaci</h2>
      <div class="grid grid-cols-2 gap-space-md">
        <x-polje labela="Oznaka *" za="st-oznaka"><input class="polje" id="st-oznaka" name="oznaka" value="{{ $stan->oznaka }}" required/></x-polje>
        <x-polje labela="Sprat" za="st-sprat"><input class="polje" id="st-sprat" name="sprat" value="{{ $stan->sprat }}"/></x-polje>
        <x-polje labela="Kvadratura (m²)" za="st-kv"><input class="polje" id="st-kv" name="kvadratura" type="number" step="0.01" min="0" value="{{ $stan->kvadratura }}"/></x-polje>
        <x-polje labela="Broj soba" za="st-sobe"><input class="polje" id="st-sobe" name="broj_soba" type="number" min="0" max="10" value="{{ $stan->broj_soba }}"/></x-polje>
        <x-polje labela="Cena (€, interno)" za="st-cena" :pomoc="$stan->cena && $stan->kvadratura > 0 ? number_format($stan->cena / $stan->kvadratura, 0, ',', '.').' €/m²' : null">
          <input class="polje" id="st-cena" name="cena" type="number" step="0.01" min="0" value="{{ $stan->cena }}"/>
        </x-polje>
        <x-polje labela="Status *" za="st-status">
          <select class="polje" id="st-status" name="status" required>
            @foreach($statusiStana as $st)<option value="{{ $st }}" @selected($stan->status === $st)>{{ Prikaz::label($st) }}</option>@endforeach
          </select>
        </x-polje>
      </div>

      <div class="pt-space-md border-t border-surface-container flex flex-col gap-space-md">
        <h3 class="oznaka">Kupac</h3>
        <x-polje labela="Ime i prezime" za="st-kupac"><input class="polje" id="st-kupac" name="kupac_ime" value="{{ $stan->customer->ime_prezime ?? '' }}" placeholder="Nema kupca"/></x-polje>
        <div class="grid grid-cols-2 gap-space-md">
          <x-polje labela="Email" za="st-email"><input class="polje" id="st-email" name="kupac_email" type="email" value="{{ $stan->customer->email ?? '' }}"/></x-polje>
          <x-polje labela="Telefon" za="st-tel"><input class="polje" id="st-tel" name="kupac_telefon" value="{{ $stan->customer->telefon ?? '' }}"/></x-polje>
        </div>
        @if($stan->customer && ($stan->customer->telefon || $stan->customer->email))
        <div class="flex flex-wrap gap-space-sm">
          @if($stan->customer->telefon)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $stan->customer->telefon) }}" class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">call</span>Pozovi</a>@endif
          @if($stan->customer->email)<a href="mailto:{{ $stan->customer->email }}" class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">mail</span>Pošalji email</a>@endif
        </div>
        @endif
      </div>

      <div class="flex items-center justify-between gap-space-sm pt-space-xs">
        <span class="font-body-sm text-body-sm text-on-surface-variant">Izmenjeno {{ $stan->updated_at?->format('d.m.Y. H:i') }}</span>
        <button class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">save</span>Sačuvaj izmene</button>
      </div>
    </form>
  </section>

  <div class="lg:col-span-7 flex flex-col gap-space-lg">
    {{-- Reklamacije stana (sekcija 3) --}}
    <section class="kartica overflow-hidden">
      <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
        <div class="flex items-center gap-space-sm">
          <h2 class="font-headline-sm text-headline-sm">Reklamacije</h2>
          @if($otvorene)<span class="cip bg-error-container text-on-error-container">{{ $otvorene }} otvorene</span>@endif
        </div>
        <button type="button" data-modal-open="modal-nova-reklamacija" class="dugme-tiho dugme-malo"><span class="material-symbols-outlined text-[16px]">add</span>Nova</button>
      </div>
      @if($stan->claims->isEmpty())
        <x-prazno ikonica="build_circle" naslov="Nema reklamacija" tekst="Za ovaj stan nije prijavljena nijedna reklamacija." />
      @else
        @php $reklamacije = $stan->claims->sortByDesc('datum_prijave'); @endphp
        @include('claims._tabela')
      @endif
    </section>

    {{-- Dokumenti stana (sekcija 2) --}}
    <section class="kartica overflow-hidden">
      <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
        <div class="flex items-center gap-space-sm">
          <h2 class="font-headline-sm text-headline-sm">Dokumenti</h2>
          <span class="cip bg-surface-container text-on-surface-variant">{{ $stan->documents->where('aktivna_verzija', true)->count() }}</span>
        </div>
        <button type="button" data-modal-open="modal-dokument" class="dugme-tiho dugme-malo"><span class="material-symbols-outlined text-[16px]">add</span>Dodaj</button>
      </div>
      <div class="px-space-sm pb-space-sm flex flex-col gap-1">
        @forelse($stan->documents->where('aktivna_verzija', true)->sortByDesc('created_at') as $dok)
        <div class="flex items-center justify-between gap-space-sm px-space-md py-2.5 rounded bg-surface-container-low">
          <div class="flex items-center gap-space-sm min-w-0">
            <span class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-[18px]">description</span></span>
            <div class="min-w-0">
              <div class="font-label-md text-label-md truncate">{{ $dok->naziv }}</div>
              <div class="font-body-sm text-body-sm text-on-surface-variant">{{ Prikaz::label($dok->tip) }} · v{{ $dok->verzija }}{{ $dok->datum_izdavanja ? ' · '.$dok->datum_izdavanja->format('d.m.Y.') : '' }}</div>
            </div>
          </div>
          @if($dok->imaFajl())
          <a href="{{ route('documents.download', $dok) }}" class="dugme-sekundarno dugme-malo bg-surface-container-lowest shrink-0"><span class="material-symbols-outlined text-[16px]">download</span>Preuzmi</a>
          @endif
        </div>
        @empty
        <x-prazno ikonica="folder_open" naslov="Nema dokumenata" tekst="Ugovor sa kupcem, zapisnik o primopredaji i ostala dokumenta stana." />
        @endforelse
      </div>
    </section>
  </div>
</div>

@include('documents._modal', ['stan' => $stan])
@include('claims._modal_nova', ['stanovi' => collect([$stan]), 'izabraniStan' => $stan->id])
@endsection
