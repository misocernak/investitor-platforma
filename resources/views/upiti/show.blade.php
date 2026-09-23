@use('App\Models\Upit')
@extends('layouts.app')
@section('naslov', 'Upit · '.$upit->ime)

@section('content')
@php
  $stan = $upit->unit;
  $telefon = $upit->telefon ? preg_replace('/[^0-9+]/', '', $upit->telefon) : null;
  $naslovMejla = 'Stan '.($stan->oznaka ?? '').' — odgovor na vaš upit sa Temelja';
@endphp

<x-zaglavlje :naslov="$upit->ime" :putanja="['Upiti kupaca' => route('upiti.index'), $upit->ime => null]"
  :opis="'Upit primljen '.$upit->primljeno_at->format('d.m.Y. \u H:i').($stan ? ' · Stan '.$stan->oznaka.', '.($stan->building->project->naziv ?? '') : '')">
  <x-slot:uzNaslov>
    @if($upit->status === 'novo')
      <span class="inline-flex items-center gap-1.5 h-[22px] px-2 rounded font-label-sm text-label-sm bg-emerald-50 text-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Novo</span>
    @else
      <span class="inline-flex items-center h-[22px] px-2 rounded font-label-sm text-label-sm bg-surface-container-high text-on-surface-variant">{{ Upit::STATUSI[$upit->status] ?? $upit->status }}</span>
    @endif
  </x-slot:uzNaslov>
  @if($telefon)<a href="tel:{{ $telefon }}" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">call</span>Pozovi {{ $upit->telefon }}</a>@endif
  <a href="mailto:{{ $upit->email }}?subject={{ rawurlencode($naslovMejla) }}" class="{{ $telefon ? 'dugme-sekundarno' : 'dugme-primarno' }}"><span class="material-symbols-outlined text-[18px]">mail</span>Odgovori emailom</a>
</x-zaglavlje>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
  <div class="lg:col-span-8 flex flex-col gap-space-lg">
    <section class="kartica p-space-lg flex flex-col gap-space-md">
      <h2 class="oznaka">Poruka kupca</h2>
      <p class="font-body-lg text-body-lg leading-relaxed whitespace-pre-line">{{ $upit->poruka }}</p>
      <dl class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm pt-space-sm border-t border-surface-container font-body-md text-body-md">
        <div><dt class="oznaka">Email</dt><dd class="mt-1"><a href="mailto:{{ $upit->email }}" class="hover:underline underline-offset-2 break-all">{{ $upit->email }}</a></dd></div>
        <div><dt class="oznaka">Telefon</dt><dd class="mt-1">@if($telefon)<a href="tel:{{ $telefon }}" class="hover:underline underline-offset-2 font-mono-num">{{ $upit->telefon }}</a>@else<span class="text-on-surface-variant">Nije ostavio — odgovorite emailom</span>@endif</dd></div>
        <div><dt class="oznaka">Izvor</dt><dd class="mt-1">@if($upit->oglas_url)<a href="{{ $upit->oglas_url }}" target="_blank" rel="noopener" class="hover:underline underline-offset-2 inline-flex items-center gap-1">Oglas na Temelju<span class="material-symbols-outlined text-[14px]">open_in_new</span></a>@else Temelj.rs @endif</dd></div>
      </dl>
      <p class="font-body-sm text-body-sm text-on-surface-variant flex items-start gap-1.5"><span class="material-symbols-outlined text-[16px]">verified_user</span>Kupac je prijavljen na Temelju sa potvrđenom email adresom i dao je saglasnost da ga kontaktirate.</p>
    </section>

    @if($drugiUpiti->isNotEmpty())
    <section class="kartica overflow-hidden">
      <div class="px-space-lg h-12 flex items-center"><h2 class="font-headline-sm text-headline-sm">Isti kupac se javljao i za</h2></div>
      <div class="px-space-sm pb-space-sm flex flex-col gap-1">
        @foreach($drugiUpiti as $d)
        <a href="{{ route('upiti.show', $d) }}" class="flex items-center justify-between gap-space-md px-space-md py-2 rounded bg-surface-container-low hover:bg-surface-container font-body-md text-body-md">
          <span>Stan {{ $d->unit->oznaka ?? '—' }} · {{ $d->primljeno_at->format('d.m.Y.') }}</span>
          <span class="text-on-surface-variant">{{ Upit::STATUSI[$d->status] ?? $d->status }}</span>
        </a>
        @endforeach
      </div>
    </section>
    @endif
  </div>

  <div class="lg:col-span-4 flex flex-col gap-space-lg">
    <section class="kartica p-space-lg">
      <form method="POST" action="{{ route('upiti.update', $upit) }}" class="flex flex-col gap-space-md">
        @csrf @method('PATCH')
        <h2 class="font-headline-sm text-headline-sm">Praćenje</h2>
        <div class="flex flex-col gap-1.5">
          <span class="oznaka">Status upita</span>
          @foreach(Upit::STATUSI as $k => $naziv)
          <label class="flex items-center gap-space-sm px-space-md py-2 rounded border cursor-pointer font-body-md text-body-md {{ $upit->status === $k ? 'border-primary bg-surface-container-low' : 'border-outline-variant' }}">
            <input type="radio" name="status" value="{{ $k }}" class="accent-black" @checked($upit->status === $k)>
            <span><strong class="font-semibold">{{ $naziv }}</strong> <span class="text-on-surface-variant">— {{ ['novo' => 'još se niste javili', 'u_kontaktu' => 'razgovarate sa kupcem', 'zatvoreno' => 'kupio ili odustao'][$k] }}</span></span>
          </label>
          @endforeach
        </div>
        <x-polje labela="Interna beleška" za="upit-beleska">
          <textarea class="polje" id="upit-beleska" name="beleska" rows="3" placeholder="npr. Zakazan obilazak u subotu u 11h">{{ $upit->beleska }}</textarea>
        </x-polje>
        <button class="dugme-primarno w-full"><span class="material-symbols-outlined text-[18px]">save</span>Sačuvaj</button>
      </form>
    </section>

    @if($stan)
    <section class="kartica p-space-lg flex flex-col gap-space-sm">
      <h2 class="font-headline-sm text-headline-sm">Stan</h2>
      <dl class="flex flex-col divide-y divide-surface-container font-body-md text-body-md">
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Oznaka</dt><dd class="font-medium">{{ $stan->oznaka }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Zgrada</dt><dd class="font-medium text-right">{{ $stan->building->naziv ?? '' }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Status</dt><dd><x-status :v="$stan->status" /></dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Cena</dt><dd class="font-mono-num">{{ $stan->cena ? number_format($stan->cena, 0, ',', '.').' €' : '—' }}</dd></div>
      </dl>
      <a href="{{ route('units.show', $stan) }}" class="dugme-sekundarno dugme-malo">Dosije stana<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
      <p class="font-body-sm text-body-sm text-on-surface-variant">Kad kupac kupi stan, u dosijeu promenite status i upišite kupca — oglas se sam skida sa Temelja.</p>
    </section>
    @endif
  </div>
</div>
@endsection
