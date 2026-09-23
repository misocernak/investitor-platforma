@extends('layouts.app')
@section('content')
@php
  $mapa = config('statusi.milestone_map');
  $koraci = config('statusi.milestone_koraci');
@endphp
<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-md">
    <div>
      <div class="flex items-center gap-space-sm text-on-surface-variant font-label-xs text-label-xs uppercase tracking-wider">
        <span>Portfelj</span><span>/</span><span class="text-secondary font-semibold">Registar objekata</span>
      </div>
      <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight mt-0.5">Projekti &amp; Zgrade</h1>
    </div>
    <button data-modal-open="modal-new-project" class="flex items-center gap-space-xs px-space-md py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md font-medium shadow-sm hover:bg-primary-container transition-colors self-start sm:self-auto">
      <span class="material-symbols-outlined text-[18px]">add</span><span>Novi projekat</span>
    </button>
  </div>

  @if($projekti->isEmpty())
  <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-xl flex flex-col items-center text-center gap-space-sm">
    <span class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center text-secondary"><span class="material-symbols-outlined text-[26px]">domain_add</span></span>
    <h2 class="font-headline-md text-headline-md text-on-surface">Još nema projekata</h2>
    <p class="font-body-md text-body-md text-on-surface-variant max-w-md">Kreirajte prvi projekat, zatim mu dodajte zgradu — dobićete dosije sa dokumentacijom, stanovima, checklistama i reklamacijama.</p>
    <button data-modal-open="modal-new-project" class="mt-space-xs px-space-lg py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md font-medium shadow-sm hover:bg-primary-container">+ Kreiraj projekat</button>
  </div>
  @else
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-space-md">
    @foreach($projekti as $projekat)
    @php
      $faza = $mapa[$projekat->status] ?? 1;
      $jedinica = $projekat->buildings->sum('units_count');
      $otvorene = $projekat->otvorene_reklamacije;
    @endphp
    <a href="{{ route('projects.show', $projekat) }}" class="group bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-shadow flex flex-col overflow-hidden">
      <div class="p-space-md flex flex-col gap-space-sm flex-1">
        <div class="flex items-start justify-between gap-space-sm">
          <div class="flex items-center gap-space-sm min-w-0">
            <span class="w-9 h-9 shrink-0 rounded-lg bg-surface-container flex items-center justify-center text-secondary"><span class="material-symbols-outlined text-[20px]">domain</span></span>
            <div class="min-w-0">
              <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold truncate group-hover:text-secondary transition-colors">{{ $projekat->naziv }}</h2>
              <p class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ trim(($projekat->lokacija_adresa ? $projekat->lokacija_adresa.', ' : '').($projekat->lokacija_grad ?? '')) ?: 'Lokacija nije uneta' }}</p>
            </div>
          </div>
          <x-status :v="$projekat->status" class="shrink-0" />
        </div>

        <div class="grid grid-cols-3 gap-space-xs pt-space-xs">
          <div class="rounded-lg bg-surface-container-low px-space-sm py-space-xs">
            <div class="font-label-xs text-label-xs uppercase text-on-surface-variant">Zgrade</div>
            <div class="font-headline-sm text-headline-sm text-on-surface">{{ $projekat->buildings->count() }}</div>
          </div>
          <div class="rounded-lg bg-surface-container-low px-space-sm py-space-xs">
            <div class="font-label-xs text-label-xs uppercase text-on-surface-variant">Jedinice</div>
            <div class="font-headline-sm text-headline-sm text-on-surface">{{ $jedinica ?: ($projekat->broj_planiranih_stanova ?: 0) }}</div>
          </div>
          <div class="rounded-lg px-space-sm py-space-xs {{ $otvorene > 0 ? 'bg-error-container' : 'bg-surface-container-low' }}">
            <div class="font-label-xs text-label-xs uppercase {{ $otvorene > 0 ? 'text-on-error-container' : 'text-on-surface-variant' }}">Reklamacije</div>
            <div class="font-headline-sm text-headline-sm {{ $otvorene > 0 ? 'text-error' : 'text-on-surface' }}">{{ $otvorene }}</div>
          </div>
        </div>
      </div>

      <div class="px-space-md py-space-sm bg-surface-container-low flex flex-col gap-1.5">
        <div class="flex items-center justify-between font-label-xs text-label-xs text-on-surface-variant">
          <span>Faza {{ $faza }}/6 · {{ $koraci[$faza] ?? '' }}</span>
          <span class="material-symbols-outlined text-[16px] group-hover:text-secondary">chevron_right</span>
        </div>
        <div class="grid grid-cols-6 gap-1">
          @for($i = 1; $i <= 6; $i++)
          <span class="h-1.5 rounded-full {{ $i < $faza ? 'bg-secondary' : ($i === $faza ? 'bg-secondary/60' : 'bg-surface-container-high') }}"></span>
          @endfor
        </div>
      </div>
    </a>
    @endforeach
  </div>
  @endif
</div>
@include('projects._modal_novi')
@endsection
