@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', 'Dokumentacija')

@section('content')
@php
  $filtrirano = collect(request()->only(['q', 'tip', 'zgrada']))->filter()->isNotEmpty();
@endphp

<x-zaglavlje naslov="Dokumentacija" opis="Dozvole, projekti, ugovori i zapisnici svih projekata na jednom mestu.">
  @if($zgrade->isNotEmpty())
  <button type="button" data-modal-open="modal-dokument" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Dodaj dokument</button>
  @endif
</x-zaglavlje>

<section class="kartica overflow-hidden">
  <form method="GET" action="{{ route('documents.index') }}" class="px-space-lg py-space-md flex flex-wrap items-center gap-space-sm">
    <div class="relative flex-1 min-w-[200px] max-w-xs">
      <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant pointer-events-none">search</span>
      <input name="q" value="{{ request('q') }}" placeholder="Naziv ili izdavalac…" class="polje pl-9" aria-label="Pretraga"/>
    </div>
    <select name="tip" data-auto-submit class="polje w-auto" aria-label="Tip dokumenta">
      <option value="">Svi tipovi</option>
      @foreach($tipoviDokumenata as $t)<option value="{{ $t->naziv }}" @selected(request('tip') === $t->naziv)>{{ Prikaz::label($t->naziv) }}</option>@endforeach
    </select>
    <select name="zgrada" data-auto-submit class="polje w-auto" aria-label="Zgrada">
      <option value="">Sve zgrade</option>
      @foreach($zgrade as $z)<option value="{{ $z->id }}" @selected(request('zgrada') == $z->id)>{{ $z->naziv }}{{ $z->project ? ' — '.$z->project->naziv : '' }}</option>@endforeach
    </select>
    <label class="inline-flex items-center gap-1.5 font-body-md text-body-md text-on-surface-variant cursor-pointer select-none">
      <input type="checkbox" name="arhiva" value="1" data-auto-submit @checked(request()->boolean('arhiva')) class="w-4 h-4 accent-black"> Arhivirane verzije
    </label>
    @if($filtrirano || request()->boolean('arhiva'))
    <a href="{{ route('documents.index') }}" class="dugme-tiho"><span class="material-symbols-outlined text-[16px]">filter_alt_off</span>Poništi</a>
    @endif
    <span class="ml-auto font-body-sm text-body-sm text-on-surface-variant">Prikazano: {{ $dokumenti->count() }}</span>
  </form>

  @if($dokumenti->isEmpty())
    <x-prazno ikonica="folder_open" :naslov="$filtrirano ? 'Nema rezultata' : 'Još nema dokumenata'" :tekst="$filtrirano ? 'Nijedan dokument ne odgovara filterima.' : 'Dodajte građevinsku dozvolu, projekte, ugovore sa kupcima i zapisnike — sve se čuva sa verzijama.'" />
  @else
    @include('documents._tabela', ['prikaziProjekat' => true])
  @endif
</section>

@if($zgrade->isNotEmpty())
  @include('documents._modal', ['zgrade' => $zgrade])
@endif
@endsection
