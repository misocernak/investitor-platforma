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
                @php
                  $bojeStatusa = ['Planiranje' => 'bg-surface-container-high text-on-surface-variant', 'U izgradnji' => 'bg-surface-container-highest text-secondary', 'Zavrsen' => 'bg-tertiary-fixed text-tertiary-container', 'Uknjizen' => 'bg-tertiary-fixed text-tertiary-container', 'Postprodaja (garancije)' => 'bg-surface-container-high text-on-surface', 'Zatvoren' => 'bg-surface-container text-on-surface-variant'];
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded font-label-xs text-label-xs font-semibold {{ $bojeStatusa[$projekat->status] ?? 'bg-surface-container-high text-on-surface-variant' }}">{{ $projekat->status }}</span>
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
            <span class="font-body-md text-body-md font-medium text-on-surface leading-tight">{{ $stavka->checklist->building->naziv }} — {{ str_replace('_', ' ', $stavka->checklist->tip_checkliste) }} — {{ $stavka->naziv_stavke }}</span>
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

<!-- MODAL: + Novi projekat (PRD 10.1 / 6.2) -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-primary/50 backdrop-blur-sm hidden" id="modal-new-project">
  <div class="bg-surface-container-lowest rounded-xl shadow-xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] overflow-y-auto">
    <div class="px-space-lg py-space-md bg-surface-container-low flex items-center justify-between">
      <div class="flex items-center gap-space-xs">
        <span class="material-symbols-outlined text-secondary text-[22px]">domain_add</span>
        <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface">Kreiraj novi projekat</h3>
      </div>
      <button class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high" data-modal-close="modal-new-project" type="button"><span class="material-symbols-outlined text-[20px]">close</span></button>
    </div>
    <form class="p-space-lg flex flex-col gap-space-md" method="POST" action="{{ route('projects.store') }}">
      @csrf
      <div class="flex flex-col gap-1">
        <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="naziv">Naziv projekta *</label>
        <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="naziv" name="naziv" required placeholder="npr. Vračar Smart Residence"/>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="lokacija_adresa">Lokacija (Adresa)</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="lokacija_adresa" name="lokacija_adresa" placeholder="npr. Južni bulevar 42"/>
        </div>
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="lokacija_grad">Lokacija (Grad)</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="lokacija_grad" name="lokacija_grad" value="Beograd"/>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="tip">Tip objekta</label>
          <select class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="tip" name="tip">
            <option value="Stambeni">Stambeni objekat</option>
            <option value="Stambeno_poslovni">Stambeno-poslovni objekat</option>
            <option value="Drugo">Drugo</option>
          </select>
        </div>
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="broj_planiranih_stanova">Broj planiranih stanova</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="broj_planiranih_stanova" name="broj_planiranih_stanova" type="number" min="1"/>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="datum_pocetka_gradnje">Datum početka gradnje</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm" id="datum_pocetka_gradnje" name="datum_pocetka_gradnje" type="date"/>
        </div>
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="planirani_datum_zavrsetka">Planirani datum završetka</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm" id="planirani_datum_zavrsetka" name="planirani_datum_zavrsetka" type="date"/>
        </div>
      </div>
      <div class="p-space-sm bg-surface-container-low rounded-lg flex items-center justify-between">
        <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold">Početni status dosijea:</span>
        <span class="inline-flex items-center px-2 py-0.5 rounded font-label-xs text-label-xs font-semibold bg-surface-container-high text-on-surface">Planiranje</span>
      </div>
      <div class="flex items-center justify-end gap-space-sm pt-space-xs">
        <button class="px-space-md py-2 bg-surface-container-high text-on-surface rounded-lg font-label-md text-label-md font-medium" data-modal-close="modal-new-project" type="button">Otkaži</button>
        <button class="px-space-lg py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md font-medium hover:bg-primary-container shadow-sm" type="submit">Sačuvaj i otvori dosije</button>
      </div>
    </form>
  </div>
</div>
@endsection
