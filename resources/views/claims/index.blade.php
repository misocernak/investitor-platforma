@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', 'Reklamacije')

@section('content')
@php
  $status = request('status');
  $rok = request('rok');
  $filtrirano = request()->hasAny(['zgrada', 'status', 'tip', 'q', 'rok']) && collect(request()->only(['zgrada', 'status', 'tip', 'q', 'rok']))->filter()->isNotEmpty();
  // Klik na karticu = filter; ponovni klik skida filter
  $kartica = fn (array $filter) => route('claims.index', collect($filter)->every(fn ($v, $k) => request($k) === $v) ? [] : $filter);
@endphp

<x-zaglavlje :naslov="$currentUser->jeNadzor() ? 'Moje reklamacije' : 'Reklamacije'" :opis="$currentUser->jeNadzor() ? 'Reklamacije koje su dodeljene vama.' : 'Prijave kupaca u garantnom roku — dodela, rokovi i tok rešavanja.'">
  @unless($currentUser->jeNadzor())
  <button type="button" data-modal-open="modal-nova-reklamacija" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Nova reklamacija</button>
  @endunless
</x-zaglavlje>

<section class="grid grid-cols-2 lg:grid-cols-4 gap-gutter" aria-label="Pregled reklamacija">
  <x-pokazatelj labela="Nove (prijavljene)" :vrednost="$metrike['prijavljene']" ikonica="fiber_new" opis="čekaju dodelu" :href="$kartica(['status' => 'Prijavljena'])" :aktivno="$status === 'Prijavljena'" />
  <x-pokazatelj labela="U radu" :vrednost="$metrike['u_obradi']" ikonica="engineering" opis="u obradi / dodeljene" :href="$kartica(['status' => 'u_radu'])" :aktivno="$status === 'u_radu'" />
  <x-pokazatelj labela="Probijen rok" :vrednost="$metrike['prekoracen_rok']" ikonica="schedule" ton="greska" opis="otvorene posle roka" :href="$kartica(['rok' => 'kasni'])" :aktivno="$rok === 'kasni'" />
  <x-pokazatelj labela="Rešene ovog meseca" :vrednost="$metrike['resene_mesec']" ikonica="task_alt" :href="$kartica(['status' => 'Resena'])" :aktivno="$status === 'Resena'" />
</section>

<section class="kartica overflow-hidden">
  {{-- Filteri se primenjuju odmah po izboru --}}
  <form method="GET" action="{{ route('claims.index') }}" class="px-space-lg py-space-md flex flex-wrap items-center gap-space-sm">
    @if($rok)<input type="hidden" name="rok" value="{{ $rok }}"/>@endif
    <div class="relative flex-1 min-w-[200px] max-w-xs">
      <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant pointer-events-none">search</span>
      <input name="q" value="{{ request('q') }}" placeholder="Stan ili opis…" class="polje pl-9" aria-label="Pretraga"/>
    </div>
    <select name="zgrada" data-auto-submit class="polje w-auto" aria-label="Zgrada">
      <option value="">Sve zgrade</option>
      @foreach($zgrade as $z)<option value="{{ $z->id }}" @selected(request('zgrada') == $z->id)>{{ $z->naziv }}</option>@endforeach
    </select>
    <select name="status" data-auto-submit class="polje w-auto" aria-label="Status">
      <option value="">Svi statusi</option>
      <option value="otvorene" @selected($status === 'otvorene')>Sve otvorene</option>
      <option value="u_radu" @selected($status === 'u_radu')>U radu (obrada + dodeljene)</option>
      @foreach($statusi as $st)<option value="{{ $st }}" @selected($status === $st)>{{ Prikaz::label($st) }}</option>@endforeach
    </select>
    <select name="tip" data-auto-submit class="polje w-auto" aria-label="Tip problema">
      <option value="">Svi tipovi</option>
      @foreach($tipovi as $tp)<option value="{{ $tp }}" @selected(request('tip') === $tp)>{{ Prikaz::label($tp) }}</option>@endforeach
    </select>
    @if($filtrirano)
    <a href="{{ route('claims.index') }}" class="dugme-tiho"><span class="material-symbols-outlined text-[16px]">filter_alt_off</span>Poništi</a>
    @endif
    <span class="ml-auto font-body-sm text-body-sm text-on-surface-variant">Prikazano: {{ $reklamacije->count() }}</span>
  </form>

  @if($reklamacije->isEmpty())
    <x-prazno ikonica="build_circle" :naslov="$filtrirano ? 'Nema rezultata' : 'Nema reklamacija'" :tekst="$filtrirano ? 'Nijedna reklamacija ne odgovara filterima.' : 'Kad kupac prijavi problem, unesite ga ovde — dodelite odgovornog i rok, i pratite tok do rešenja.'" />
  @else
    @include('claims._tabela')
  @endif
</section>

@unless($currentUser->jeNadzor())
  @include('claims._modal_nova')
@endunless
@endsection
