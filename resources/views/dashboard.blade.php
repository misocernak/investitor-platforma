@extends('layouts.app')
@section('naslov', 'Nadzorna tabla')

@section('content')
<x-zaglavlje naslov="Nadzorna tabla" opis="Stanje svih projekata, otvorene reklamacije i dokumenta koja nedostaju.">
  <button type="button" data-modal-open="modal-novi-projekat" class="dugme-primarno">
    <span class="material-symbols-outlined text-[18px]">add</span>Novi projekat
  </button>
</x-zaglavlje>

@if($noviUpiti->isNotEmpty())
<section class="kartica overflow-hidden border-l-4 border-emerald-500" aria-label="Novi upiti kupaca">
  <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
    <div class="flex items-center gap-space-sm">
      <span class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">forum</span></span>
      <h2 class="font-headline-sm text-headline-sm">{{ $noviUpiti->count() === 1 ? 'Imate novi upit kupca' : 'Imate nove upite kupaca' }}</h2>
    </div>
    <a href="{{ route('upiti.index', ['status' => 'novo']) }}" class="dugme-tiho dugme-malo">Svi upiti<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
  </div>
  <div class="px-space-sm pb-space-sm flex flex-col gap-1">
    @foreach($noviUpiti as $u)
    <a href="{{ route('upiti.show', $u) }}" class="flex items-center justify-between gap-space-md px-space-md py-2.5 rounded bg-emerald-50/60 hover:bg-emerald-50">
      <span class="min-w-0"><strong class="font-label-md text-label-md">{{ $u->ime }}</strong> <span class="font-body-md text-body-md text-on-surface-variant">— stan {{ $u->unit->oznaka ?? '' }} · {{ \Illuminate\Support\Str::limit($u->poruka, 70) }}</span></span>
      <span class="font-body-sm text-body-sm text-on-surface-variant whitespace-nowrap">{{ $u->primljeno_at->format('d.m. H:i') }}</span>
    </a>
    @endforeach
  </div>
</section>
@endif

{{-- 4 brojčane kartice (PRD 9.1) — klik vodi na odgovarajuću listu --}}
<section class="grid grid-cols-2 lg:grid-cols-4 gap-gutter" aria-label="Ključni pokazatelji">
  <x-pokazatelj labela="Aktivni projekti" :vrednost="$karte['aktivni_projekti']" ikonica="apartment" opis="u portfelju" :href="route('projects.index')" />
  <x-pokazatelj labela="Zgrade u garanciji" :vrednost="$karte['zgrade_garancija']" ikonica="verified_user" opis="završene / u garanciji" :href="route('checklists.index')" />
  <x-pokazatelj labela="Otvorene reklamacije" :vrednost="$karte['otvorene_reklamacije']" ikonica="report_problem" ton="greska" opis="prijavljene ili u radu" :href="route('claims.index', ['status' => 'otvorene'])" />
  <x-pokazatelj labela="Nedostajuće stavke" :vrednost="$karte['nedostajuce_stavke']" ikonica="checklist" opis="u checklistama" :href="route('checklists.index')" />
</section>

{{-- Projekti (PRD 9.1) --}}
<section class="kartica overflow-hidden" aria-label="Projekti">
  <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
    <div class="flex items-center gap-space-sm">
      <h2 class="font-headline-sm text-headline-sm">Projekti</h2>
      <span class="cip bg-surface-container text-on-surface-variant">{{ $projekti->count() }}</span>
    </div>
    <a href="{{ route('projects.index') }}" class="dugme-tiho dugme-malo">Svi projekti<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
  </div>
  @if($projekti->isEmpty())
    <x-prazno ikonica="domain_add" naslov="Još nema projekata" tekst="Kreirajte prvi projekat i dodajte mu zgradu — dobićete dosije sa dokumentacijom, stanovima, checklistama i reklamacijama.">
      <button type="button" data-modal-open="modal-novi-projekat" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Novi projekat</button>
    </x-prazno>
  @else
  <div class="overflow-x-auto">
    <table class="tabela">
      <thead>
        <tr>
          <th>Naziv projekta</th>
          <th>Lokacija</th>
          <th class="text-right">Stanova</th>
          <th>Status</th>
          <th class="text-center">Otvorene reklamacije</th>
          <th class="w-10"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($projekti as $projekat)
        @php
          $otvorene = $projekat->otvorene_reklamacije;
          $jedinica = $projekat->buildings->sum('units_count');
        @endphp
        <tr data-href="{{ route('projects.show', $projekat) }}">
          <td><a href="{{ route('projects.show', $projekat) }}" class="font-semibold hover:underline underline-offset-2">{{ $projekat->naziv }}</a></td>
          <td class="text-on-surface-variant">{{ $projekat->lokacija_grad ?: '—' }}</td>
          <td class="text-right font-mono-num">{{ $jedinica ?: ($projekat->broj_planiranih_stanova ?: '—') }}</td>
          <td><x-status :v="$projekat->status" /></td>
          <td class="text-center">
            @if($otvorene > 0)
              <a href="{{ route('claims.index', ['status' => 'otvorene']) }}" class="cip bg-error-container text-on-error-container">{{ $otvorene }}</a>
            @else
              <span class="text-on-surface-variant">0</span>
            @endif
          </td>
          <td class="text-right text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">chevron_right</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-space-lg items-start">
  {{-- To-do: nedostajuće stavke checklisti (PRD 9.1) --}}
  <section class="kartica overflow-hidden" aria-label="Nedostajuće stavke checklisti">
    <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
      <div class="flex items-center gap-space-sm">
        <span class="w-6 h-6 rounded bg-error/10 text-error flex items-center justify-center"><span class="material-symbols-outlined text-[16px]">priority_high</span></span>
        <h2 class="font-headline-sm text-headline-sm">Nedostaje u checklistama</h2>
      </div>
      <a href="{{ route('checklists.index') }}" class="dugme-tiho dugme-malo">Sve checkliste<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
    </div>
    <div class="px-space-sm pb-space-sm flex flex-col gap-1">
      @forelse($todo as $stavka)
      <a href="{{ route('checklists.show', ['building' => $stavka->checklist->zgrada_id, 'tip' => $stavka->checklist->tip_checkliste]) }}" class="group flex items-center justify-between gap-space-md px-space-md py-2.5 rounded bg-surface-container-low hover:bg-surface-container transition-colors">
        <div class="flex items-center gap-space-sm min-w-0">
          <span class="w-2 h-2 rounded-full bg-error shrink-0"></span>
          <div class="min-w-0">
            <div class="font-label-md text-label-md text-on-surface truncate">{{ \App\Support\Prikaz::stavka($stavka->naziv_stavke) }}</div>
            <div class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $stavka->checklist->building->naziv }} · {{ \App\Support\Prikaz::label($stavka->checklist->tip_checkliste) }}</div>
          </div>
        </div>
        <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:text-on-surface">chevron_right</span>
      </a>
      @empty
      <x-prazno ikonica="task_alt" naslov="Sve je na mestu" tekst="Nijedna checklist stavka ne nedostaje." />
      @endforelse
    </div>
  </section>

  {{-- Reklamacije kojima je rok prošao ili ističe za 7 dana --}}
  <section class="kartica overflow-hidden" aria-label="Hitne reklamacije">
    <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
      <div class="flex items-center gap-space-sm">
        <span class="w-6 h-6 rounded bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center"><span class="material-symbols-outlined text-[16px]">schedule</span></span>
        <h2 class="font-headline-sm text-headline-sm">Reklamacije sa rokom</h2>
      </div>
      <a href="{{ route('claims.index', ['status' => 'otvorene']) }}" class="dugme-tiho dugme-malo">Sve reklamacije<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
    </div>
    <div class="px-space-sm pb-space-sm flex flex-col gap-1">
      @forelse($hitneReklamacije as $rek)
      @php $rok = \App\Support\Prikaz::rok($rek); @endphp
      <a href="{{ route('claims.show', $rek) }}" class="group flex items-center justify-between gap-space-md px-space-md py-2.5 rounded bg-surface-container-low hover:bg-surface-container transition-colors">
        <div class="flex items-center gap-space-sm min-w-0">
          <span class="material-symbols-outlined text-[18px] text-on-surface-variant">{{ \App\Support\Prikaz::ikonaProblema($rek->tip_problema) }}</span>
          <div class="min-w-0">
            <div class="font-label-md text-label-md text-on-surface truncate">{{ $rek->unit->oznaka ?? '—' }} · {{ \App\Support\Prikaz::label($rek->tip_problema) }}</div>
            <div class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $rek->unit->building->naziv ?? '' }} · rok {{ $rek->rok_resavanja->format('d.m.Y.') }}</div>
          </div>
        </div>
        @if($rok)<span class="font-label-md text-label-md whitespace-nowrap {{ $rok[1] }}">{{ $rok[0] }}</span>@endif
      </a>
      @empty
      <x-prazno ikonica="event_available" naslov="Nema hitnih reklamacija" tekst="Nijednoj otvorenoj reklamaciji rok ne ističe u narednih 7 dana." />
      @endforelse
    </div>
  </section>
</div>

@include('projects._modal_novi')
@endsection
