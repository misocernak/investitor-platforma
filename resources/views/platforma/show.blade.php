@use('App\Models\Tenant')
@use('App\Models\User')
@extends('layouts.app')
@section('naslov', $firma->naziv)

@section('content')
@php
  $v = $firma->vlasnik;
  $tonovi = ['na_cekanju' => 'bg-amber-50 text-amber-800', 'aktivan' => 'bg-emerald-50 text-emerald-800', 'odbijen' => 'bg-error-container text-on-error-container', 'suspendovan' => 'bg-surface-container-high text-on-surface-variant'];
  $zastupnik = $v && in_array($v->funkcija, ['direktor', 'zakonski_zastupnik'], true);
@endphp

<x-zaglavlje :naslov="$firma->naziv" :putanja="['Firme i zahtevi' => route('platforma.index'), $firma->naziv => null]"
  :opis="'MB '.$firma->maticni_broj.' · PIB '.$firma->pib.($firma->registrovan_at ? ' · registrovana '.$firma->registrovan_at->format('d.m.Y. H:i') : '')">
  <x-slot:uzNaslov>
    <span class="inline-flex items-center h-[22px] px-2 rounded font-label-sm text-label-sm {{ $tonovi[$firma->status] ?? '' }}">{{ Tenant::STATUSI[$firma->status] ?? $firma->status }}</span>
  </x-slot:uzNaslov>
</x-zaglavlje>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
  <div class="lg:col-span-7 flex flex-col gap-space-lg">
    <section class="kartica p-space-lg flex flex-col gap-space-md">
      <h2 class="font-headline-sm text-headline-sm">Firma</h2>
      <dl class="flex flex-col divide-y divide-surface-container font-body-md text-body-md">
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Naziv</dt><dd class="font-medium text-right">{{ $firma->naziv }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Matični broj</dt><dd class="font-mono-num">{{ $firma->maticni_broj }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">PIB</dt><dd class="font-mono-num">{{ $firma->pib }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Sedište</dt><dd class="text-right">{{ collect([$firma->adresa, $firma->grad])->filter()->implode(', ') ?: '—' }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">APR registar</dt>
          <dd class="text-right">@if($firma->apr_provereno)<span class="text-emerald-700">✓ Pronađena{{ $firma->apr_status ? ' · '.$firma->apr_status : '' }}</span>@else<span class="text-amber-700">Nije automatski potvrđena — proverite ručno</span>@endif</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Korisnika u firmi</dt><dd class="font-mono-num">{{ $firma->users_count }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Veza sa Temeljem</dt><dd class="text-right">
          @if($firma->temelj_veza_status === 'odobrena')<span class="text-emerald-700">✓ Povezana</span>@if($firma->temelj_profil_url) · <a href="{{ $firma->temelj_profil_url }}" target="_blank" rel="noopener" class="underline underline-offset-2">profil na Temelju</a>@endif
          @else — @endif
        </dd></div>
      </dl>
    </section>

    <section class="kartica p-space-lg flex flex-col gap-space-md">
      <h2 class="font-headline-sm text-headline-sm">Lice koje je registrovalo nalog</h2>
      @if($v)
      <dl class="flex flex-col divide-y divide-surface-container font-body-md text-body-md">
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Ime i prezime</dt><dd class="font-medium">{{ $v->ime_prezime }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Funkcija</dt><dd>{{ User::FUNKCIJE[$v->funkcija] ?? '—' }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Email</dt><dd class="text-right"><a href="mailto:{{ $v->email }}" class="underline underline-offset-2">{{ $v->email }}</a> @if($v->email_potvrdjen_at)<span class="text-emerald-700">✓ potvrđen</span>@else<span class="text-amber-700">nije potvrđen</span>@endif</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Telefon</dt><dd>@if($v->telefon)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $v->telefon) }}" class="underline underline-offset-2 font-mono-num">{{ $v->telefon }}</a>@else — @endif</dd></div>
      </dl>
      @endif
      @if($firma->ovlascenje_putanja)
        <a href="{{ route('platforma.ovlascenje', $firma) }}" target="_blank" rel="noopener" class="dugme-sekundarno self-start"><span class="material-symbols-outlined text-[18px]">description</span>Otvori punomoćje: {{ \Illuminate\Support\Str::limit($firma->ovlascenje_naziv, 40) }}</a>
      @elseif($v && !$zastupnik)
        <p class="font-body-md text-body-md text-error">Ovlašćeno lice nije priložilo punomoćje.</p>
      @endif
    </section>
  </div>

  <div class="lg:col-span-5 flex flex-col gap-space-lg">
    <section class="kartica p-space-lg flex flex-col gap-space-md">
      <h2 class="font-headline-sm text-headline-sm">Kako proveriti</h2>
      <ol class="flex flex-col gap-space-sm font-body-md text-body-md list-decimal pl-5">
        <li>U javnoj <a href="https://pretraga.apr.gov.rs/unifiedentitysearch" target="_blank" rel="noopener" class="underline underline-offset-2">APR pretrazi</a> unesite MB <strong class="font-mono-num">{{ $firma->maticni_broj }}</strong> i proverite da je firma aktivna.</li>
        @if($zastupnik)
          <li>Uporedite da je <strong>{{ $v->ime_prezime }}</strong> upisan kao zakonski zastupnik firme.</li>
        @else
          <li>Otvorite punomoćje i proverite da ga je potpisao zakonski zastupnik iz APR-a.</li>
        @endif
        <li>Po potrebi pozovite broj firme iz APR-a (ne broj iz zahteva) i potvrdite zahtev.</li>
      </ol>
    </section>

    @if($firma->status === 'na_cekanju' || $firma->status === 'odbijen')
    <section class="kartica p-space-lg flex flex-col gap-space-md">
      <h2 class="font-headline-sm text-headline-sm">Odluka</h2>
      <form method="POST" action="{{ route('platforma.odobri', $firma) }}" data-potvrdi="Odobriti nalog firme {{ $firma->naziv }}? Firma dobija pristup aplikaciji i odmah se povezuje sa Temeljem.">
        @csrf
        <button class="dugme-primarno w-full h-10"><span class="material-symbols-outlined text-[18px]">verified</span>Odobri nalog i poveži sa Temeljem</button>
      </form>
      @if($firma->status === 'na_cekanju')
      <form method="POST" action="{{ route('platforma.odbij', $firma) }}" class="flex flex-col gap-space-sm pt-space-md border-t border-surface-container">
        @csrf
        <x-polje labela="Razlog odbijanja (dobija ga podnosilac)" za="pl-razlog">
          <input class="polje" id="pl-razlog" name="razlog" required maxlength="255" placeholder="npr. Lice nije upisano kao zastupnik u APR-u"/>
        </x-polje>
        <button class="dugme-sekundarno w-full text-error"><span class="material-symbols-outlined text-[18px]">block</span>Odbij zahtev</button>
      </form>
      @endif
    </section>
    @elseif(in_array($firma->status, ['aktivan', 'suspendovan'], true))
    <section class="kartica p-space-lg flex flex-col gap-space-sm">
      <h2 class="font-headline-sm text-headline-sm">Pristup</h2>
      <p class="font-body-md text-body-md text-on-surface-variant">{{ $firma->status === 'aktivan' ? 'Firma je odobrena'.($firma->odobren_at ? ' '.$firma->odobren_at->format('d.m.Y.') : '').' i ima pun pristup.' : 'Korisnici firme trenutno ne mogu da pristupe aplikaciji.' }}</p>
      <form method="POST" action="{{ route('platforma.suspenzija', $firma) }}" data-potvrdi="{{ $firma->status === 'aktivan' ? 'Suspendovati nalog firme? Korisnici gube pristup dok ga ponovo ne aktivirate.' : 'Ponovo aktivirati nalog firme?' }}">
        @csrf
        <button class="dugme-sekundarno w-full"><span class="material-symbols-outlined text-[18px]">{{ $firma->status === 'aktivan' ? 'pause' : 'play_arrow' }}</span>{{ $firma->status === 'aktivan' ? 'Suspenduj nalog' : 'Ponovo aktiviraj nalog' }}</button>
      </form>
    </section>
    @endif
  </div>
</div>
@endsection
