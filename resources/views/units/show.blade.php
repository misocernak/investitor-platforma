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
  <x-slot:uzNaslov>
    <x-status :v="$stan->status" />
  </x-slot:uzNaslov>
  @include('units._brzi_status')
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
        <x-polje labela="Broj soba" za="st-sobe"><input class="polje" id="st-sobe" name="broj_soba" type="number" min="0" max="20" step="0.5" value="{{ $stan->broj_soba !== null ? (float) $stan->broj_soba : '' }}"/></x-polje>
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
        @php
          $naTemelju = $currentTenant?->povezanSaTemeljem();
          $prodatStan = ! in_array($stan->status, \App\Services\KupciNaTemelju::NEPRODAT, true);
        @endphp
        @if($naTemelju)
        <div class="rounded bg-surface-container-low px-space-md py-space-sm flex flex-col gap-space-xs">
          <div class="flex items-center gap-space-sm font-body-md text-body-md">
            <span class="material-symbols-outlined text-[18px] {{ $stan->temelj_kupac_status === 'potvrdjen' ? 'text-emerald-700' : 'text-on-surface-variant' }}">{{ $stan->temelj_kupac_status === 'potvrdjen' ? 'verified_user' : ($stan->temelj_kupac_status === 'poslat' ? 'mail' : 'info') }}</span>
            @if($stan->temelj_kupac_status === 'potvrdjen')
              <span><strong>Kupac je potvrdio stan na Temelju</strong>@if($stan->temelj_kupac_at) · {{ $stan->temelj_kupac_at->format('d.m.Y.') }}@endif</span>
            @elseif($stan->temelj_kupac_status === 'poslat')
              <span><strong>Poziv je poslat kupcu</strong> na {{ $stan->temelj_kupac_email }}@if($stan->temelj_kupac_at) · {{ $stan->temelj_kupac_at->format('d.m.Y.') }}@endif — čeka potvrdu</span>
            @else
              <span class="text-on-surface-variant">Kad je stan prodat i upisan je email kupca, Temelj mu šalje poziv da stan potvrdi u svom profilu.</span>
            @endif
          </div>
          @if($stan->temelj_kupac_status)
          <div class="flex flex-wrap gap-space-sm">
            @if($stan->temelj_kupac_status === 'poslat' && $prodatStan)
            <button type="submit" form="kupac-ponovo" class="dugme-tiho dugme-malo"><span class="material-symbols-outlined text-[16px]">send</span>Pošalji poziv ponovo</button>
            @endif
            <button type="submit" form="kupac-ukloni" class="dugme-tiho dugme-malo text-error"><span class="material-symbols-outlined text-[16px]">link_off</span>Ukloni pristup kupcu</button>
          </div>
          @endif
          @error('kupac')<p class="font-body-sm text-body-sm text-error">{{ $message }}</p>@enderror
        </div>
        @endif
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
    {{-- Forme za poziv kupcu (dugmad su u formi iznad, povezana preko form="...") --}}
    <form id="kupac-ponovo" method="POST" action="{{ route('units.kupac.ponovo', $stan) }}" class="hidden">@csrf</form>
    <form id="kupac-ukloni" method="POST" action="{{ route('units.kupac.ukloni', $stan) }}" class="hidden" data-potvrdi="Ukloniti kupcu pristup ovom stanu na Temelju?">@csrf</form>
  </section>

  <div class="lg:col-span-7 flex flex-col gap-space-lg">
    {{-- Oglas na Temelju i upiti kupaca --}}
    @php
      $oglas = $stan->oglas;
      $uProdaji = in_array($stan->status, \App\Models\Oglas::STATUSI_U_PRODAJI, true);
      $noviUpitiStana = $stan->upiti->where('status', 'novo')->count();
    @endphp
    @if($oglas || $uProdaji)
    <section class="kartica overflow-hidden">
      <div class="px-space-lg py-space-md flex flex-wrap items-center justify-between gap-space-sm">
        <div class="flex items-center gap-space-sm">
          <span class="material-symbols-outlined text-[22px] text-on-surface-variant">campaign</span>
          <h2 class="font-headline-sm text-headline-sm">Oglas na Temelju</h2>
          @if($oglas)<x-oglas-stanje :oglas="$oglas" :tenant="$currentTenant" />@endif
        </div>
        <div class="flex items-center gap-space-sm">
          @if($oglas?->temelj_url && $oglas->status === 'aktivan' && $oglas->sinhronizovan_at)
          <a href="{{ $oglas->temelj_url }}" target="_blank" rel="noopener" class="dugme-tiho dugme-malo">Pogledaj<span class="material-symbols-outlined text-[16px]">open_in_new</span></a>
          @endif
          @if($uProdaji)
          <a href="{{ route('oglasi.forma', $stan) }}" class="{{ $oglas ? 'dugme-sekundarno' : 'dugme-primarno' }} dugme-malo"><span class="material-symbols-outlined text-[16px]">{{ $oglas ? 'edit' : 'campaign' }}</span>{{ $oglas ? 'Izmeni oglas' : 'Oglasi na Temelju' }}</a>
          @endif
        </div>
      </div>
      @if(!$oglas)
        <p class="px-space-lg pb-space-md font-body-md text-body-md text-on-surface-variant">Ovaj stan je u prodaji, a nije na Temelju. Dodajte fotografije i cenu — kupci ga vide uz ocene vaše firme i šalju upit direktno vama.</p>
      @else
        <p class="px-space-lg pb-space-md font-body-md text-body-md text-on-surface-variant">{{ \App\Support\Prikaz::oglasStanje($oglas, $currentTenant)[2] }}</p>
      @endif
      @if($stan->upiti->isNotEmpty())
      <div class="border-t border-surface-container">
        <div class="px-space-lg pt-space-md pb-space-xs flex items-center justify-between">
          <span class="oznaka">Upiti kupaca ({{ $stan->upiti->count() }})</span>
          @if($noviUpitiStana)<span class="cip bg-emerald-50 text-emerald-800">{{ $noviUpitiStana }} novo</span>@endif
        </div>
        <div class="px-space-sm pb-space-sm flex flex-col gap-1">
          @foreach($stan->upiti->take(4) as $u)
          <a href="{{ route('upiti.show', $u) }}" class="flex items-center justify-between gap-space-md px-space-md py-2 rounded {{ $u->status === 'novo' ? 'bg-emerald-50' : 'bg-surface-container-low' }} hover:bg-surface-container">
            <span class="min-w-0">
              <span class="block font-label-md text-label-md truncate">{{ $u->ime }}</span>
              <span class="block font-body-sm text-body-sm text-on-surface-variant truncate">{{ \Illuminate\Support\Str::limit($u->poruka, 80) }}</span>
            </span>
            <span class="font-body-sm text-body-sm whitespace-nowrap {{ $u->status === 'novo' ? 'text-emerald-800 font-semibold' : 'text-on-surface-variant' }}">{{ $u->status === 'novo' ? 'Novo' : $u->primljeno_at->format('d.m.') }}</span>
          </a>
          @endforeach
        </div>
      </div>
      @endif
    </section>
    @endif

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
          <div class="flex items-center gap-1 shrink-0">
            @if($currentTenant?->povezanSaTemeljem())
            <form method="POST" action="{{ route('documents.kupcu', $dok) }}">
              @csrf
              <button class="dugme-tiho dugme-malo {{ $dok->vidljivo_kupcu ? 'text-emerald-700' : '' }}" title="{{ $dok->vidljivo_kupcu ? 'Kupac vidi dokument na Temelju — klik da sakrijete' : 'Kupac ne vidi dokument — klik da ga prikažete na Temelju' }}"><span class="material-symbols-outlined text-[16px]">{{ $dok->vidljivo_kupcu ? 'visibility' : 'visibility_off' }}</span></button>
            </form>
            @endif
            @if($dok->imaFajl())
            <a href="{{ route('documents.download', $dok) }}" class="dugme-sekundarno dugme-malo bg-surface-container-lowest"><span class="material-symbols-outlined text-[16px]">download</span>Preuzmi</a>
            @endif
          </div>
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
