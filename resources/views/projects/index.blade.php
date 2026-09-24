@extends('layouts.app')
@section('naslov', 'Projekti i zgrade')
@section('content')
@php
  $mapa = config('statusi.milestone_map');
  $koraci = config('statusi.milestone_koraci');
@endphp
<x-zaglavlje naslov="Projekti i zgrade" opis="Svi projekti firme. Klik na red otvara dosije projekta.">
  <button type="button" data-modal-open="modal-novi-projekat" class="dugme-primarno">
    <span class="material-symbols-outlined text-[18px]">add</span>Novi projekat
  </button>
</x-zaglavlje>

<section class="kartica overflow-hidden">
  @if($projekti->isEmpty())
    <x-prazno ikonica="domain_add" naslov="Još nema projekata" tekst="Kreirajte prvi projekat, zatim mu dodajte zgradu — dobićete dosije sa dokumentacijom, stanovima, checklistama i reklamacijama.">
      <button type="button" data-modal-open="modal-novi-projekat" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Novi projekat</button>
    </x-prazno>
  @else
  <div class="overflow-x-auto">
    <table class="tabela">
      <thead>
        <tr>
          <th>Projekat</th>
          <th>Lokacija</th>
          <th class="text-right">Zgrade</th>
          <th class="text-right">Jedinice</th>
          <th>Faza</th>
          <th>Status</th>
          <th class="text-center">Otvorene reklamacije</th>
          <th class="w-10"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($projekti as $projekat)
        @php
          $faza = $mapa[$projekat->status] ?? 1;
          $jedinica = $projekat->buildings->sum('units_count');
          $otvorene = $otvorenePoProjektu[$projekat->id] ?? 0;
        @endphp
        <tr data-href="{{ route('projects.show', $projekat) }}">
          <td>
            <a href="{{ route('projects.show', $projekat) }}" class="font-semibold hover:underline underline-offset-2">{{ $projekat->naziv }}</a>
            <div class="font-body-sm text-body-sm text-on-surface-variant">{{ \App\Support\Prikaz::label($projekat->tip) }}</div>
          </td>
          <td class="text-on-surface-variant">
            {{ $projekat->lokacija_grad ?: '—' }}
            @if($projekat->lokacija_adresa)<div class="font-body-sm text-body-sm">{{ $projekat->lokacija_adresa }}</div>@endif
          </td>
          <td class="text-right font-mono-num">{{ $projekat->buildings->count() }}</td>
          <td class="text-right font-mono-num">{{ $jedinica }}@if($projekat->broj_planiranih_stanova && $projekat->broj_planiranih_stanova != $jedinica)<span class="text-on-surface-variant"> / {{ $projekat->broj_planiranih_stanova }}</span>@endif</td>
          <td class="min-w-[160px]">
            <div class="flex items-center gap-space-sm" title="Faza {{ $faza }}/6 · {{ $koraci[$faza] ?? '' }}">
              <div class="grid grid-cols-6 gap-0.5 w-20 shrink-0">
                @for($i = 1; $i <= 6; $i++)
                <span class="h-1.5 rounded-full {{ $i <= $faza ? 'bg-secondary' : 'bg-surface-container-high' }}"></span>
                @endfor
              </div>
              <span class="font-body-sm text-body-sm text-on-surface-variant whitespace-nowrap">{{ $koraci[$faza] ?? '' }}</span>
            </div>
          </td>
          <td><x-status :v="$projekat->status" /></td>
          <td class="text-center">
            @if($otvorene > 0)<span class="cip bg-error-container text-on-error-container">{{ $otvorene }}</span>@else<span class="text-on-surface-variant">0</span>@endif
          </td>
          <td class="text-right text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">chevron_right</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>

@include('projects._modal_novi')
@endsection
