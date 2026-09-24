@use('App\Models\Tenant')
@extends('layouts.app')
@section('naslov', 'Firme i zahtevi')

@section('content')
@php
  $tonovi = ['na_cekanju' => 'bg-amber-50 text-amber-800', 'aktivan' => 'bg-emerald-50 text-emerald-800', 'odbijen' => 'bg-error-container text-on-error-container', 'suspendovan' => 'bg-surface-container-high text-on-surface-variant', 'raskinut' => 'bg-surface-container-high text-on-surface-variant'];
@endphp
<x-zaglavlje naslov="Firme i zahtevi" opis="Zahtevi za nalog i nalozi firmi na Temelj Investitoru. Ovde se vide samo podaci firme i kontakt lica — ne i projekti, stanovi ni upiti firmi." />

<nav class="flex flex-wrap items-center gap-1 p-1 rounded-lg bg-surface-container-low self-start" aria-label="Filter po statusu">
  @foreach(['' => 'Sve'] + Tenant::STATUSI as $k => $naziv)
  @php $aktivan = ($status ?? '') === $k || ($k === '' && !isset(Tenant::STATUSI[$status])); $broj = $k === '' ? $brojevi->sum() : ($brojevi[$k] ?? 0); @endphp
  <a href="{{ route('platforma.index', $k ? ['status' => $k] : []) }}" @if($aktivan) aria-current="page" @endif
     class="inline-flex items-center gap-1.5 h-8 px-space-md rounded font-label-md text-label-md transition-colors {{ $aktivan ? 'bg-surface-container-lowest text-on-surface font-semibold shadow-sm' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}">
    {{ $naziv }}
    <span class="cip {{ $k === 'na_cekanju' && $broj ? 'bg-amber-50 text-amber-800' : 'bg-surface-container text-on-surface-variant' }}">{{ $broj }}</span>
  </a>
  @endforeach
</nav>

<section class="kartica overflow-hidden">
  @if($firme->isEmpty())
    <x-prazno ikonica="domain" naslov="Nema firmi u ovoj grupi" tekst="Kad investitor popuni registraciju, zahtev se pojavljuje ovde kao „Čeka odobrenje“." />
  @else
  <div class="overflow-x-auto">
    <table class="tabela">
      <thead>
        <tr><th>Firma</th><th>Kontakt lice</th><th>Registrovana</th><th>Temelj</th><th>Status</th><th class="w-10"></th></tr>
      </thead>
      <tbody>
        @foreach($firme as $f)
        @php $v = $f->vlasnik; @endphp
        <tr data-href="{{ route('platforma.show', $f) }}" class="{{ $f->status === 'na_cekanju' ? 'bg-amber-50/40' : '' }}">
          <td>
            <a href="{{ route('platforma.show', $f) }}" class="font-semibold hover:underline underline-offset-2">{{ $f->naziv }}</a>
            <div class="font-body-sm text-body-sm text-on-surface-variant">MB {{ $f->maticni_broj ?: '—' }} · PIB {{ $f->pib }}@if($f->apr_provereno) · <span class="text-emerald-700">✓ APR</span>@endif</div>
          </td>
          <td>
            {{ $v->ime_prezime ?? $f->kontakt_osoba ?? '—' }}
            <div class="font-body-sm text-body-sm text-on-surface-variant">{{ $v ? (\App\Models\User::FUNKCIJE[$v->funkcija] ?? '') : '' }}@if($v && !$v->email_potvrdjen_at) · <span class="text-amber-700">email nije potvrđen</span>@endif</div>
          </td>
          <td class="whitespace-nowrap font-mono-num text-on-surface-variant">{{ $f->registrovan_at?->format('d.m.Y. H:i') ?? $f->created_at?->format('d.m.Y.') }}</td>
          <td class="whitespace-nowrap">
            @if($f->temelj_veza_status === 'odobrena')<span class="text-emerald-700 font-body-sm text-body-sm">✓ povezana</span>
            @else<span class="text-on-surface-variant font-body-sm text-body-sm">—</span>@endif
          </td>
          <td><span class="inline-flex items-center h-[22px] px-2 rounded font-label-sm text-label-sm whitespace-nowrap {{ $tonovi[$f->status] ?? '' }}">{{ Tenant::STATUSI[$f->status] ?? $f->status }}</span></td>
          <td class="text-right text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">chevron_right</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>
@endsection
