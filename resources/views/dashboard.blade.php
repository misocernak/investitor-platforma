@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-md">
    <div>
      <div class="flex items-center gap-space-sm text-on-surface-variant font-label-xs text-label-xs uppercase tracking-wider">
        <span>Operativni pregled</span><span>/</span><span class="text-secondary font-semibold">Stanje portfelja</span>
      </div>
      <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight mt-0.5">Nadzorna tabla</h1>
    </div>
    <button data-modal-open="modal-new-project" class="flex items-center gap-space-xs px-space-md py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md font-medium shadow-sm hover:bg-primary-container transition-colors self-start sm:self-auto">
      <span class="material-symbols-outlined text-[18px]">add</span><span>Novi projekat</span>
    </button>
  </div>

  <!-- Traka sa brojcanim karticama - TACNO 4 (PRD 9.1) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between h-28">
      <div class="flex items-center justify-between"><span class="font-label-xs text-label-xs text-on-surface-variant uppercase tracking-wider">Broj aktivnih projekata</span><span class="material-symbols-outlined text-secondary text-[20px]">apartment</span></div>
      <div class="flex items-baseline gap-space-xs"><span class="font-headline-xl text-headline-xl font-bold text-on-surface">{{ $karte['aktivni_projekti'] }}</span><span class="font-label-sm text-label-sm text-on-surface-variant">u portfelju</span></div>
    </div>
    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between h-28">
      <div class="flex items-center justify-between"><span class="font-label-xs text-label-xs text-on-surface-variant uppercase tracking-wider">Zgrade u garanciji / postprodaji</span><span class="material-symbols-outlined text-on-surface-variant text-[20px]">verified</span></div>
      <div class="flex items-baseline gap-space-xs"><span class="font-headline-xl text-headline-xl font-bold text-on-surface">{{ $karte['zgrade_garancija'] }}</span><span class="font-label-sm text-label-sm text-on-surface-variant">objekata</span></div>
    </div>
    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between h-28">
      <div class="flex items-center justify-between"><span class="font-label-xs text-label-xs text-error uppercase tracking-wider font-semibold">Otvorene reklamacije</span><span class="material-symbols-outlined text-error text-[20px]">report_problem</span></div>
      <div class="flex items-baseline gap-space-xs"><span class="font-headline-xl text-headline-xl font-bold text-error">{{ $karte['otvorene_reklamacije'] }}</span><span class="font-label-sm text-label-sm text-on-surface-variant">zahteva kupaca</span></div>
    </div>
    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between h-28">
      <div class="flex items-center justify-between"><span class="font-label-xs text-label-xs text-on-surface-variant uppercase tracking-wider">Nedostajuće stavke checklisti</span><span class="material-symbols-outlined text-on-surface-variant text-[20px]">rule_folder</span></div>
      <div class="flex items-baseline gap-space-xs"><span class="font-headline-xl text-headline-xl font-bold text-on-surface">{{ $karte['nedostajuce_stavke'] }}</span><span class="font-label-sm text-label-sm text-on-surface-variant">dokumenta u toku</span></div>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-3 gap-space-lg items-start">
    <!-- Tabela projekata (PRD 9.1) -->
    <div class="xl:col-span-2 bg-surface-container-lowest rounded-xl shadow-sm flex flex-col">
      <div class="p-space-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-space-sm">
        <div>
          <h2 class="font-headline-sm text-headline-sm font-semibold text-on-surface">Projekti u portfelju</h2>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Glavni registar stambeno-poslovnih objekata investitora</p>
        </div>
        <div class="flex items-center gap-space-xs text-on-surface-variant font-label-xs text-label-xs">
          <span class="material-symbols-outlined text-[16px]">touch_app</span><span>Kliknite na red za otvaranje dosijea</span>
        </div>
      </div>
      <div class="overflow-x-auto w-full">
        <table class="w-full text-left font-body-sm text-body-sm">
          <thead>
            <tr class="bg-surface-container-low text-on-surface-variant font-label-xs text-label-xs uppercase tracking-wider">
              <th class="py-2.5 px-space-md font-medium">Naziv projekta</th>
              <th class="py-2.5 px-space-md font-medium">Lokacija (grad)</th>
              <th class="py-2.5 px-space-md font-medium text-right">Broj stanova</th>
              <th class="py-2.5 px-space-md font-medium text-center">Status</th>
              <th class="py-2.5 px-space-md font-medium text-right">Otvorene reklamacije</th>
              <th class="py-2.5 px-space-md text-center w-12"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-surface-container-low">
            @foreach($projekti as $projekat)
            <tr class="hover:bg-surface-container-low cursor-pointer transition-colors group" onclick="window.location='{{ route('projects.show', $projekat) }}'">
              <td class="py-3 px-space-md font-medium text-on-surface">
                <div class="flex items-center gap-space-xs">
                  <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:text-secondary transition-colors">domain</span>
                  <span class="font-semibold">{{ $projekat->naziv }}</span>
                </div>
              </td>
              <td class="py-3 px-space-md text-on-surface-variant">{{ $projekat->lokacija_grad ?: '—' }}</td>
              <td class="py-3 px-space-md text-right font-label-md text-label-md text-on-surface">{{ $projekat->broj_planiranih_stanova ?: '—' }}</td>
              <td class="py-3 px-space-md text-center">
                <x-status :v="$projekat->status" />
              </td>
              <td class="py-3 px-space-md text-right">
                @if($projekat->otvorene_reklamacije > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] px-1.5 py-0.5 rounded font-label-xs text-label-xs font-bold bg-error-container text-error">{{ $projekat->otvorene_reklamacije }}</span>
                @else
                <span class="inline-flex items-center justify-center min-w-[20px] px-1.5 py-0.5 rounded font-label-xs text-label-xs font-bold bg-surface-container-high text-on-surface-variant">0</span>
                @endif
              </td>
              <td class="py-3 px-space-md text-center text-on-surface-variant group-hover:text-on-surface"><span class="material-symbols-outlined text-[18px]">chevron_right</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="p-space-sm flex items-center justify-between text-on-surface-variant font-label-xs text-label-xs">
        <span>Ukupno objekata: {{ $projekti->count() }}</span><span>Sistem: StructureOps Dosije Engine v1.0</span>
      </div>
    </div>

    <!-- To-do blok (PRD 9.1) -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm flex flex-col">
      <div class="p-space-md pb-space-sm flex items-center justify-between">
        <div class="flex items-center gap-space-xs">
          <span class="material-symbols-outlined text-error text-[20px]">assignment_late</span>
          <h2 class="font-headline-sm text-headline-sm font-semibold text-on-surface">To-Do: Nedostajući prilozi</h2>
        </div>
        <span class="font-label-xs text-label-xs px-2 py-0.5 rounded bg-error-container text-error font-semibold">{{ $karte['nedostajuce_stavke'] }} stavki</span>
      </div>
      <p class="px-space-md pb-space-sm font-body-sm text-body-sm text-on-surface-variant">Obavezne tehničke i pravne stavke checklisti koje blokiraju narednu fazu gradnje ili tehnički prijem:</p>
      <div class="flex flex-col p-space-sm gap-space-xs">
        @forelse($todo as $stavka)
        <div class="p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
          <div class="flex flex-col min-w-0 pr-space-xs">
            <span class="font-body-md text-body-md font-medium text-on-surface leading-tight">{{ $stavka->checklist->building->naziv }} — {{ \App\Support\Prikaz::label($stavka->checklist->tip_checkliste) }} — {{ $stavka->naziv_stavke }}</span>
            <span class="font-label-xs text-label-xs text-on-surface-variant mt-1">Projekat: {{ $stavka->checklist->building->project->naziv ?? '—' }}</span>
          </div>
          <a href="{{ route('checklists.show', ['building' => $stavka->checklist->zgrada_id, 'tip' => $stavka->checklist->tip_checkliste]) }}" class="self-end sm:self-center shrink-0 px-space-md py-1 bg-surface-container-lowest text-on-surface hover:bg-primary hover:text-on-primary rounded font-label-sm text-label-sm font-semibold shadow-sm transition-colors">Otvori</a>
        </div>
        @empty
        <div class="p-space-md text-center text-on-surface-variant font-body-sm">Sve checklist stavke su rešene.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

@include('projects._modal_novi')
@endsection
