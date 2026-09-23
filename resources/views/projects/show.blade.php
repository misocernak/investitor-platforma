@extends('layouts.app')

@section('content')
@php
  $tabovi = ['pregled' => 'Pregled', 'dokumentacija' => 'Dokumentacija', 'stanovi' => 'Stanovi', 'checkliste' => 'Checkliste', 'reklamacije' => 'Reklamacije'];
  $ikoneTabova = ['pregled' => 'info', 'dokumentacija' => 'folder_open', 'stanovi' => 'apartment', 'checkliste' => 'fact_check', 'reklamacije' => 'assignment_late'];
  $milestoneKoraci = config('statusi.milestone_koraci');
  $milestonePozicija = config('statusi.milestone_map')[$project->status] ?? 1;
@endphp

<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">

  <!-- Zaglavlje dosijea + status selektor (rucna promena, PRD 2.3) -->
  <div class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-space-md">
    <div class="flex flex-col gap-space-xs">
      <div class="flex items-center gap-space-sm flex-wrap">
        <span class="font-label-xs text-label-xs uppercase px-space-xs py-0.5 rounded bg-surface-container-high text-on-surface font-semibold tracking-wider">Dosije Projekta</span>
        <span class="font-label-xs text-label-xs text-on-surface-variant font-mono">{{ $project->lokacija_adresa }}{{ $project->lokacija_grad ? ', '.$project->lokacija_grad : '' }}</span>
      </div>
      <h1 class="font-headline-lg text-headline-lg text-on-surface font-semibold tracking-tight">{{ $project->naziv }}</h1>
      <p class="font-body-sm text-body-sm text-on-surface-variant">
        {{ $zgrada ? 'Zgrada: '.$zgrada->naziv.' · Status zgrade: '.\App\Support\Prikaz::label($zgrada->status) : 'Zgrada još nije dodata — dodajte prvu zgradu da biste vodili dosije.' }}
      </p>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center gap-space-sm bg-surface-container-low p-space-sm rounded-lg">
      <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold whitespace-nowrap" for="building-status-select">Status projekta:</label>
      <form method="POST" action="{{ route('projects.update', $project) }}" class="relative inline-block">
        @csrf @method('PATCH')
        <select name="status" onchange="this.form.submit()" class="h-8 pl-space-sm pr-8 bg-surface-container-lowest text-on-surface font-label-md text-label-md rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary cursor-pointer appearance-none">
          @foreach($statusiProjekta as $status)
          <option value="{{ $status }}" {{ $project->status === $status ? 'selected' : '' }}>{{ \App\Support\Prikaz::label($status) }}</option>
          @endforeach
        </select>
        <input type="hidden" name="naziv" value="{{ $project->naziv }}">
        <input type="hidden" name="lokacija_adresa" value="{{ $project->lokacija_adresa }}">
        <input type="hidden" name="lokacija_grad" value="{{ $project->lokacija_grad }}">
        <input type="hidden" name="tip" value="{{ $project->tip }}">
        <input type="hidden" name="broj_planiranih_stanova" value="{{ $project->broj_planiranih_stanova }}">
        <input type="hidden" name="datum_pocetka_gradnje" value="{{ $project->datum_pocetka_gradnje?->format('Y-m-d') }}">
        <input type="hidden" name="planirani_datum_zavrsetka" value="{{ $project->planirani_datum_zavrsetka?->format('Y-m-d') }}">
        <span class="material-symbols-outlined pointer-events-none absolute right-2 top-2 text-on-surface-variant text-[16px]">arrow_drop_down</span>
      </form>
    </div>
  </div>

  @if(!$zgrada)
  <!-- Empty state: prva zgrada (PRD 10.1) -->
  <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg flex flex-col items-center gap-space-md text-center">
    <span class="material-symbols-outlined text-[40px] text-on-surface-variant">domain</span>
    <div>
      <h2 class="font-headline-sm text-headline-sm font-semibold text-on-surface">Dodajte prvu zgradu projekta</h2>
      <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Bez zgrade nema dosijea, checklisti ni evidencije stanova. Ovo je jedini sledeći korak.</p>
    </div>
    <form method="POST" action="{{ route('buildings.store', $project) }}" class="flex flex-col sm:flex-row gap-space-sm w-full max-w-md">
      @csrf
      <input name="naziv" required placeholder="Naziv zgrade (npr. Blok A / Lamela 1)" class="flex-1 h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary"/>
      <input name="broj_stanova" type="number" min="1" placeholder="Br. stanova" class="w-28 h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"/>
      <button class="h-9 px-space-lg bg-primary text-on-primary rounded-lg font-body-sm font-semibold shadow-sm hover:bg-primary-container">+ Dodaj zgradu</button>
    </form>
  </div>
  @endif

  @if($zgrada)
  <!-- Tab navigacija (tacno 5 tabova, PRD 9.2) -->
  <div class="bg-surface-container-lowest rounded-t-xl shadow-sm px-space-md pt-space-xs flex items-center gap-1 overflow-x-auto -mb-space-md">
    @foreach($tabovi as $kljuc => $naziv)
    <a href="{{ route('projects.show', ['project' => $project, 'tab' => $kljuc]) }}" class="px-space-lg py-3 font-label-md text-label-md flex items-center gap-space-xs rounded-t-lg transition-colors {{ $tab === $kljuc ? 'text-on-surface font-semibold bg-surface-container-low' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}">
      <span class="material-symbols-outlined text-[18px]">{{ $ikoneTabova[$kljuc] }}</span><span>{{ $naziv }}</span>
      @if($kljuc === 'dokumentacija')<span class="ml-1 px-1.5 py-0.5 rounded-full bg-surface-container-highest text-on-surface font-mono text-[10px]">{{ $dokumenti->count() }}</span>@endif
      @if($kljuc === 'stanovi')<span class="ml-1 px-1.5 py-0.5 rounded-full bg-surface-container-highest text-on-surface font-mono text-[10px]">{{ $zgrada->units->count() }}</span>@endif
    </a>
    @endforeach
  </div>

  <!-- TAB 1: PREGLED -->
  @if($tab === 'pregled')
  <div class="flex flex-col gap-space-md bg-surface-container-lowest p-space-lg rounded-b-xl shadow-sm">
    <div class="bg-surface-container-low p-space-lg rounded-lg">
      <div class="flex items-center justify-between mb-space-sm">
        <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold">Tehnički i administrativni tok realizacije (Milestones)</span>
        <span class="font-label-xs text-label-xs text-on-surface font-mono font-medium">Faza: {{ $milestonePozicija }}/6 ({{ \App\Support\Prikaz::label($project->status) }})</span>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-space-sm pt-space-xs">
        @foreach($milestoneKoraci as $broj => $naziv)
          @php $stanje = $broj < $milestonePozicija ? 'zavrseno' : ($broj === $milestonePozicija ? 'u_toku' : 'ceka'); @endphp
        <div class="flex flex-col p-space-sm rounded-lg shadow-sm {{ $stanje === 'zavrseno' ? 'bg-surface-container-lowest' : ($stanje === 'u_toku' ? 'bg-surface-container-high' : 'bg-surface-container-low opacity-60') }}">
          <div class="flex items-center justify-between mb-1">
            <span class="font-label-xs text-label-xs font-mono font-semibold text-on-surface-variant">{{ str_pad($broj, 2, '0', STR_PAD_LEFT) }}</span>
            <span class="font-label-xs text-label-xs px-1.5 py-0.5 rounded font-semibold {{ $stanje === 'zavrseno' ? 'bg-surface-container-high text-on-surface' : ($stanje === 'u_toku' ? 'bg-secondary text-on-secondary' : 'bg-surface-container-high text-on-surface-variant') }}">{{ $stanje === 'zavrseno' ? 'Završeno' : ($stanje === 'u_toku' ? 'U toku' : 'Čeka') }}</span>
          </div>
          <span class="font-body-sm text-body-sm font-semibold {{ $stanje === 'ceka' ? 'text-on-surface-variant' : 'text-on-surface' }}">{{ $naziv }}</span>
        </div>
        @endforeach
      </div>
    </div>

    <div class="flex items-center justify-between">
      <h2 class="font-headline-md text-headline-md text-on-surface font-semibold tracking-tight">Osnovni podaci projekta i zgrade</h2>
      <button data-modal-open="modal-edit-project" class="h-8 px-space-md bg-surface-container text-on-surface hover:bg-surface-container-high font-body-sm text-body-sm font-medium rounded-lg shadow-sm flex items-center gap-space-xs transition-colors">
        <span class="material-symbols-outlined text-[16px]">edit</span><span>Uredi</span>
      </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-sm">
      <div class="flex flex-col gap-space-xs bg-surface-container-low p-space-md rounded-lg">
        @foreach([['Naziv projekta', $project->naziv],['Adresa objekta', trim(($project->lokacija_adresa ?: '').' '.($project->lokacija_grad ?: '')) ?: '—'],['Tip objekta', \App\Support\Prikaz::label($project->tip)]] as [$labela, $vrednost])
        <div class="flex items-baseline justify-between py-1 bg-surface-container-lowest px-space-sm rounded gap-2">
          <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">{{ $labela }}:</span>
          <span class="font-body-sm text-body-sm text-on-surface font-semibold text-right">{{ $vrednost }}</span>
        </div>
        @endforeach
      </div>
      <div class="flex flex-col gap-space-xs bg-surface-container-low p-space-md rounded-lg">
        @foreach([['Broj planiranih stanova', $project->broj_planiranih_stanova ?: '—'],['Datum početka radova', $project->datum_pocetka_gradnje?->format('d.m.Y.') ?: '—'],['Planirani završetak', $project->planirani_datum_zavrsetka?->format('d.m.Y.') ?: '—']] as [$labela, $vrednost])
        <div class="flex items-baseline justify-between py-1 bg-surface-container-lowest px-space-sm rounded gap-2">
          <span class="font-body-sm text-body-sm text-on-surface-variant font-medium">{{ $labela }}:</span>
          <span class="font-body-sm text-body-sm text-on-surface font-semibold font-mono text-right">{{ $vrednost }}</span>
        </div>
        @endforeach
      </div>
    </div>

    <div class="mt-space-sm pt-space-md flex flex-wrap items-center gap-space-sm">
      <span class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Brzi prelazak:</span>
      <a href="{{ route('projects.show', ['project' => $project, 'tab' => 'stanovi']) }}" class="px-space-md py-1.5 rounded-lg bg-surface-container-low hover:bg-surface-container-high text-on-surface font-body-sm flex items-center gap-1 transition-colors"><span class="material-symbols-outlined text-[16px]">apartment</span>Spisak i inventar stanova ({{ $zgrada->units->count() }})</a>
      <a href="{{ route('checklists.show', $zgrada) }}" class="px-space-md py-1.5 rounded-lg bg-surface-container-low hover:bg-surface-container-high text-on-surface font-body-sm flex items-center gap-1 transition-colors"><span class="material-symbols-outlined text-[16px]">fact_check</span>Tehničke check-liste</a>
      <a href="{{ route('claims.index', ['zgrada' => $zgrada->id]) }}" class="px-space-md py-1.5 rounded-lg bg-surface-container-low hover:bg-surface-container-high text-on-surface font-body-sm flex items-center gap-1 transition-colors"><span class="material-symbols-outlined text-[16px]">assignment_late</span>Garancije i otvorene reklamacije</a>
    </div>
  </div>
  @endif

  <!-- TAB 2: DOKUMENTACIJA -->
  @if($tab === 'dokumentacija')
  <div class="flex flex-col gap-space-md bg-surface-container-lowest p-space-lg rounded-b-xl shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md bg-surface-container-low p-space-md rounded-lg">
      <form method="GET" action="{{ route('projects.show', $project) }}" class="flex flex-wrap items-center gap-space-sm">
        <input type="hidden" name="tab" value="dokumentacija"/>
        <select name="tip" onchange="this.form.submit()" class="h-8 pl-space-sm pr-7 bg-surface-container-lowest text-on-surface font-body-sm text-body-sm rounded-lg shadow-sm focus:outline-none cursor-pointer appearance-none">
          <option value="">Svi tipovi</option>
          @foreach($tipoviDokumenata as $tipDok)
          <option value="{{ $tipDok->naziv }}" {{ request('tip') === $tipDok->naziv ? 'selected' : '' }}>{{ \App\Support\Prikaz::label($tipDok->naziv) }}</option>
          @endforeach
        </select>
        <button type="submit" class="h-8 px-space-sm rounded-lg bg-surface-container-lowest text-on-surface-variant font-body-sm flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">filter_list</span>Filter</button>
      </form>
      <button data-modal-open="modal-add-doc" class="h-9 px-space-lg bg-primary text-on-primary hover:bg-primary-container font-body-md text-body-md font-semibold rounded-lg shadow-sm flex items-center justify-center gap-space-xs transition-colors whitespace-nowrap">
        <span class="material-symbols-outlined text-[18px]">add</span><span>+ Dodaj dokument</span>
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left font-body-sm text-body-sm border-collapse">
        <thead>
          <tr class="bg-surface-container-high text-on-surface font-label-xs text-label-xs uppercase tracking-wider">
            <th class="py-2.5 px-space-md">Naziv dokumenta</th>
            <th class="py-2.5 px-space-md">Tip</th>
            <th class="py-2.5 px-space-md">Datum izdavanja</th>
            <th class="py-2.5 px-space-md">Izdavalac</th>
            <th class="py-2.5 px-space-md">Vezano za</th>
            <th class="py-2.5 px-space-md text-center">Verzija</th>
            <th class="py-2.5 px-space-md text-right">Akcije</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-container">
          @forelse($dokumenti as $dok)
          <tr class="hover:bg-surface-container-low transition-colors {{ $dok->aktivna_verzija ? '' : 'opacity-50' }}">
            <td class="py-2.5 px-space-md font-medium text-on-surface flex items-center gap-space-xs"><span class="material-symbols-outlined text-[18px] text-on-surface-variant">description</span>{{ $dok->naziv }}</td>
            <td class="py-2.5 px-space-md text-on-surface-variant">{{ \App\Support\Prikaz::label($dok->tip) }}</td>
            <td class="py-2.5 px-space-md font-mono text-on-surface">{{ $dok->datum_izdavanja?->format('d.m.Y.') ?: '—' }}</td>
            <td class="py-2.5 px-space-md text-on-surface-variant">{{ $dok->izdavalac ?: '—' }}</td>
            <td class="py-2.5 px-space-md">
              @if($dok->stan_id)<span class="font-label-xs text-label-xs px-2 py-0.5 rounded bg-surface-container text-on-surface font-semibold">Stan: {{ $dok->unit->oznaka ?? '—' }}</span>
              @elseif($dok->zgrada_id)<span class="font-label-xs text-label-xs px-2 py-0.5 rounded bg-surface-container text-on-surface font-semibold">Zgrada</span>
              @else<span class="font-label-xs text-label-xs px-2 py-0.5 rounded bg-surface-container-high text-on-surface font-semibold">Projekat</span>@endif
            </td>
            <td class="py-2.5 px-space-md font-mono text-center text-on-surface">v{{ $dok->verzija }}{{ $dok->aktivna_verzija ? '' : ' (arh.)' }}</td>
            <td class="py-2.5 px-space-md text-right whitespace-nowrap">
              @if($dok->imaFajl())
              <a href="{{ route('documents.download', $dok) }}" class="p-1 rounded text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high inline-block" title="Preuzmi dokument"><span class="material-symbols-outlined text-[18px]">download</span></a>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="py-6 px-space-md text-center text-on-surface-variant">Nema dokumenata. Kliknite "+ Dodaj dokument".</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @endif

  <!-- TAB 3: STANOVI -->
  @if($tab === 'stanovi')
  <div class="flex flex-col gap-space-md bg-surface-container-lowest p-space-lg rounded-b-xl shadow-sm">
    <div class="flex items-center justify-between pb-space-sm">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface font-semibold">Registar stanova i jedinica ({{ $zgrada->units->count() }})</h2>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Evidencija etažnih celina — klik na red otvara dosije jedinice</p>
      </div>
      <button data-modal-open="modal-add-unit" class="h-8 px-space-md bg-primary text-on-primary font-body-sm text-body-sm rounded-lg shadow-sm font-medium">+ Dodaj jedinicu</button>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left font-body-sm text-body-sm border-collapse">
        <thead>
          <tr class="bg-surface-container-high text-on-surface font-label-xs text-label-xs uppercase">
            <th class="py-2 px-space-md">Oznaka</th>
            <th class="py-2 px-space-md">Sprat</th>
            <th class="py-2 px-space-md text-right">Kvadratura</th>
            <th class="py-2 px-space-md">Kupac / Vlasnik</th>
            <th class="py-2 px-space-md">Status jedinice</th>
            <th class="py-2 px-space-md text-center">Reklamacije</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-container">
          @foreach($zgrada->units as $stan)
          <tr class="hover:bg-surface-container-low cursor-pointer" onclick="window.location='{{ route('units.index', ['zgrada' => $zgrada->id]) }}?stan={{ $stan->id }}'">
            <td class="py-2.5 px-space-md font-mono font-semibold">{{ $stan->oznaka }}</td>
            <td class="py-2.5 px-space-md">{{ $stan->sprat ?: '—' }}</td>
            <td class="py-2.5 px-space-md font-mono text-right">{{ $stan->kvadratura ? number_format($stan->kvadratura, 2, ',', '.').' m²' : '—' }}</td>
            <td class="py-2.5 px-space-md font-medium">{{ $stan->customer?->ime_prezime ?? '—' }}</td>
            <td class="py-2.5 px-space-md"><x-status :v="$stan->status" /></td>
            <td class="py-2.5 px-space-md text-center">
              @php $br = $stan->otvoreneReklamacije()->count(); @endphp
              <span class="font-label-sm text-label-sm px-2 py-0.5 rounded {{ $br > 0 ? 'bg-error-container text-on-error-container font-bold' : 'bg-surface-container text-on-surface-variant' }}">{{ $br }}</span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  <!-- TAB 4: CHECKLISTE -->
  @if($tab === 'checkliste')
  <div class="flex flex-col gap-space-md bg-surface-container-lowest p-space-lg rounded-b-xl shadow-sm">
    <h2 class="font-headline-md text-headline-md text-on-surface font-semibold">Tehničke check-liste i primopredaja</h2>
    <div class="flex flex-col gap-space-xs">
      @foreach($zgrada->checklists as $cl)
      <a href="{{ route('checklists.show', ['building' => $zgrada, 'tip' => $cl->tip_checkliste]) }}" class="flex items-center justify-between p-space-md rounded-lg bg-surface-container-low hover:bg-surface-container transition-colors">
        <div class="flex items-center gap-space-md">
          <span class="material-symbols-outlined {{ $cl->reseno === $cl->ukupno ? 'text-on-tertiary-container' : 'text-secondary' }} text-[22px]">{{ $cl->reseno === $cl->ukupno ? 'check_circle' : 'pending' }}</span>
          <div>
            <div class="font-body-sm text-body-sm font-semibold text-on-surface">{{ \App\Support\Prikaz::label($cl->tip_checkliste) }}</div>
            <div class="font-label-xs text-label-xs text-on-surface-variant">{{ $cl->reseno }}/{{ $cl->ukupno }} stavki zatvoreno</div>
          </div>
        </div>
        <span class="font-label-xs text-label-xs px-2 py-0.5 rounded font-semibold {{ $cl->reseno === $cl->ukupno ? 'bg-tertiary-fixed text-on-tertiary-fixed-variant' : 'bg-surface-container-high text-on-surface' }}">{{ $cl->reseno === $cl->ukupno ? '100% Verifikovano' : round($cl->reseno / max($cl->ukupno,1) * 100).'% U toku' }}</span>
      </a>
      @endforeach
    </div>
  </div>
  @endif

  <!-- TAB 5: REKLAMACIJE -->
  @if($tab === 'reklamacije')
  <div class="flex flex-col gap-space-md bg-surface-container-lowest p-space-lg rounded-b-xl shadow-sm">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="font-headline-md text-headline-md text-on-surface font-semibold">Postprodaja, garancije i reklamacije</h2>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Evidencija primedbi kupaca u garantnom roku</p>
      </div>
      <a href="{{ route('claims.index', ['zgrada' => $zgrada->id]) }}" class="h-8 px-space-md bg-primary text-on-primary font-body-sm text-body-sm rounded-lg shadow-sm font-medium flex items-center">Otvori modul reklamacija</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left font-body-sm text-body-sm border-collapse">
        <thead>
          <tr class="bg-surface-container-high text-on-surface font-label-xs text-label-xs uppercase">
            <th class="py-2 px-space-md">Stan</th>
            <th class="py-2 px-space-md">Tip problema</th>
            <th class="py-2 px-space-md">Datum prijave</th>
            <th class="py-2 px-space-md">Odgovorni</th>
            <th class="py-2 px-space-md">Rok</th>
            <th class="py-2 px-space-md">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-container">
          @forelse($reklamacije as $rek)
          <tr class="hover:bg-surface-container-low cursor-pointer" onclick="window.location='{{ route('claims.index', ['reklamacija' => $rek->id]) }}'">
            <td class="py-2.5 px-space-md font-medium">{{ $rek->unit->oznaka ?? '—' }}</td>
            <td class="py-2.5 px-space-md">{{ \App\Support\Prikaz::label($rek->tip_problema) }}</td>
            <td class="py-2.5 px-space-md font-mono">{{ $rek->datum_prijave?->format('d.m.Y.') }}</td>
            <td class="py-2.5 px-space-md">{{ $rek->odgovorni?->ime_prezime ?? 'Nedodeljeno' }}</td>
            <td class="py-2.5 px-space-md font-mono {{ $rek->kasni_dana ? 'text-error font-bold' : '' }}">{{ $rek->rok_resavanja?->format('d.m.Y.') ?: '—' }}</td>
            <td class="py-2.5 px-space-md"><x-status :v="$rek->status" /></td>
          </tr>
          @empty
          <tr><td colspan="6" class="py-6 px-space-md text-center text-on-surface-variant">Nema reklamacija za ovu zgradu.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @endif

  <!-- MODAL: izmena projekta -->
  <div class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-primary/50 backdrop-blur-sm hidden" id="modal-edit-project">
    <div class="bg-surface-container-lowest rounded-xl shadow-xl w-full max-w-xl p-space-lg flex flex-col gap-space-md max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between">
        <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface">Izmena osnovnih podataka</h3>
        <button class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high" data-modal-close="modal-edit-project"><span class="material-symbols-outlined text-[20px]">close</span></button>
      </div>
      <form method="POST" action="{{ route('projects.update', $project) }}" class="flex flex-col gap-space-md">
        @csrf @method('PATCH')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Naziv projekta *</label><input name="naziv" value="{{ $project->naziv }}" required class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Tip objekta</label>
            <select name="tip" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm">
              @foreach(config('statusi.tip_projekta') as $tp)<option value="{{ $tp }}" {{ $project->tip === $tp ? 'selected' : '' }}>{{ \App\Support\Prikaz::label($tp) }}</option>@endforeach
            </select>
          </div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Lokacija (Adresa)</label><input name="lokacija_adresa" value="{{ $project->lokacija_adresa }}" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Lokacija (Grad)</label><input name="lokacija_grad" value="{{ $project->lokacija_grad }}" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Broj planiranih stanova</label><input name="broj_planiranih_stanova" type="number" min="1" value="{{ $project->broj_planiranih_stanova }}" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Status</label>
            <select name="status" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm">
              @foreach($statusiProjekta as $st)<option value="{{ $st }}" {{ $project->status === $st ? 'selected' : '' }}>{{ \App\Support\Prikaz::label($st) }}</option>@endforeach
            </select>
          </div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Datum početka gradnje</label><input name="datum_pocetka_gradnje" type="date" value="{{ $project->datum_pocetka_gradnje?->format('Y-m-d') }}" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Planirani završetak</label><input name="planirani_datum_zavrsetka" type="date" value="{{ $project->planirani_datum_zavrsetka?->format('Y-m-d') }}" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"/></div>
        </div>
        <div class="flex justify-end gap-space-sm"><button type="button" data-modal-close="modal-edit-project" class="px-space-md py-2 bg-surface-container-high rounded-lg font-label-md">Otkaži</button><button class="px-space-lg py-2 bg-primary text-on-primary rounded-lg font-label-md shadow-sm">Sačuvaj</button></div>
      </form>
    </div>
  </div>

  <!-- MODAL: + Dodaj dokument (PRD 10.2) -->
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-primary/40 p-space-md hidden" id="modal-add-doc">
    <div class="bg-surface-container-lowest w-full max-w-lg rounded-xl shadow-xl p-space-lg flex flex-col gap-space-md max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-space-xs"><span class="material-symbols-outlined text-secondary text-[20px]">post_add</span><h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Dodavanje novog dokumenta</h3></div>
        <button class="p-1 text-on-surface-variant rounded-lg" data-modal-close="modal-add-doc"><span class="material-symbols-outlined text-[20px]">close</span></button>
      </div>
      <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="flex flex-col gap-space-md">
        @csrf
        <input type="hidden" name="projekat_id" value="{{ $project->id }}"/>
        <input type="hidden" name="zgrada_id" value="{{ $zgrada->id }}"/>
        <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Tip dokumenta *</label>
          <select name="tip" required class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm">
            <option value="" disabled selected>Izaberite tip...</option>
            @foreach($tipoviDokumenata as $tipDok)<option value="{{ $tipDok->naziv }}">{{ \App\Support\Prikaz::label($tipDok->naziv) }}</option>@endforeach
          </select>
        </div>
        <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Naziv dokumenta *</label><input name="naziv" required class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm" placeholder="npr. Rešenje o građevinskoj dozvoli"/></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Datum izdavanja</label><input name="datum_izdavanja" type="date" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Vezano za stan (opciono)</label>
            <select name="stan_id" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm"><option value="">— Zgrada/Projekat —</option>
              @foreach($zgrada->units as $stan)<option value="{{ $stan->id }}">{{ $stan->oznaka }}</option>@endforeach
            </select>
          </div>
        </div>
        <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Izdavalac</label><input name="izdavalac" class="h-9 px-space-sm bg-surface-container-low rounded-lg shadow-sm" placeholder="npr. Sekretarijat za urbanizam"/></div>
        <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Fajl * (PDF, DWG, JPG, PNG, ZIP, DOCX, XLSX — max 50MB)</label>
          <input name="fajl" type="file" required class="block w-full text-sm text-on-surface file:mr-3 file:h-9 file:px-space-md file:rounded-lg file:border-0 file:bg-surface-container-high file:text-on-surface"/>
        </div>
        <div class="flex justify-end gap-space-sm"><button type="button" data-modal-close="modal-add-doc" class="h-9 px-space-md bg-surface-container rounded-lg font-body-sm">Otkaži</button><button class="h-9 px-space-lg bg-primary text-on-primary rounded-lg font-body-sm font-semibold shadow-sm">Sačuvaj dokument</button></div>
      </form>
    </div>
  </div>

  <!-- MODAL: + Dodaj jedinicu -->
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-primary/50 backdrop-blur-sm hidden" id="modal-add-unit">
    <div class="bg-surface-container-lowest w-full max-w-lg rounded-xl shadow-xl p-space-lg flex flex-col gap-space-md max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary text-[22px]">add_home</span><h3 class="font-headline-md text-headline-md text-on-surface">Nova stambena jedinica</h3></div>
        <button class="p-1 rounded hover:bg-surface-container" data-modal-close="modal-add-unit"><span class="material-symbols-outlined text-[18px]">close</span></button>
      </div>
      <form method="POST" action="{{ route('units.store', $zgrada) }}" class="flex flex-col gap-space-md">
        @csrf
        <div class="grid grid-cols-2 gap-space-md">
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Oznaka stana *</label><input name="oznaka" required placeholder="npr. Stan 09" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Sprat / Etaža</label><input name="sprat" placeholder="npr. II sprat" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
        </div>
        <div class="grid grid-cols-2 gap-space-md">
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Kvadratura (m²)</label><input name="kvadratura" type="number" step="0.01" min="0" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Broj soba</label><input name="broj_soba" type="number" min="0" max="10" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
        </div>
        <div class="grid grid-cols-2 gap-space-md">
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Interna cena (€)</label><input name="cena" type="number" step="0.01" min="0" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Status jedinice *</label>
            <select name="status" required class="h-9 px-space-sm bg-surface-container-low rounded-lg">
              @foreach($statusiStana as $st)<option value="{{ $st }}">{{ $st }}</option>@endforeach
            </select>
          </div>
        </div>
        <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Kupac / Ugovarač (opciono)</label><input name="kupac_ime" placeholder="Ime i prezime kupca" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
        <div class="grid grid-cols-2 gap-space-md">
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Email kupca</label><input name="kupac_email" type="email" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Telefon kupca</label><input name="kupac_telefon" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
        </div>
        <div class="flex justify-end gap-space-sm"><button type="button" data-modal-close="modal-add-unit" class="px-space-md py-1.5 rounded-lg bg-surface-container font-body-sm">Otkaži</button><button class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-body-sm font-semibold">Potvrdi unos jedinice</button></div>
      </form>
    </div>
  </div>
  @endif
</div>
@endsection
