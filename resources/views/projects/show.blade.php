@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', $project->naziv)

@section('content')
@php
  $koraci = config('statusi.milestone_koraci');
  $faza = config('statusi.milestone_map')[$project->status] ?? 1;
  $url = fn ($t, $extra = []) => route('projects.show', array_merge(['project' => $project, 'tab' => $t], $zgrada ? ['zgrada' => $zgrada->id] : [], $extra));
  $otvorene = $reklamacije->whereNotIn('status', ['Resena', 'Odbijena']);
  if ($zgrada) { $stanovi->each->setRelation('building', $zgrada); }
  $tabovi = [
    'pregled' => ['Pregled', 'dashboard', null],
    'dokumentacija' => ['Dokumentacija', 'folder_open', $dokumenti->count()],
    'stanovi' => ['Stanovi', 'door_front', $stanovi->count()],
    'checkliste' => ['Checkliste', 'fact_check', $checkliste->sum('reseno_stavki').'/'.$checkliste->sum('ukupno_stavki')],
    'reklamacije' => ['Reklamacije', 'build_circle', $otvorene->count()],
  ];
  $opis = collect([Prikaz::label($project->tip), trim(($project->lokacija_adresa ? $project->lokacija_adresa.', ' : '').($project->lokacija_grad ?? ''), ', ')])->filter(fn ($v) => $v && $v !== '—')->implode(' · ');
@endphp

<x-zaglavlje :naslov="$project->naziv" :putanja="['Projekti i zgrade' => route('projects.index'), $project->naziv => null]" :opis="$opis ?: null">
  <x-slot:uzNaslov>
    {{-- Ručna promena statusa projekta (PRD 2.3) --}}
    <form method="POST" action="{{ route('projects.update', $project) }}">
      @csrf @method('PATCH')
      @foreach(['naziv', 'lokacija_adresa', 'lokacija_grad', 'tip', 'broj_planiranih_stanova'] as $polje)
      <input type="hidden" name="{{ $polje }}" value="{{ $project->$polje }}">
      @endforeach
      <input type="hidden" name="datum_pocetka_gradnje" value="{{ $project->datum_pocetka_gradnje?->format('Y-m-d') }}">
      <input type="hidden" name="planirani_datum_zavrsetka" value="{{ $project->planirani_datum_zavrsetka?->format('Y-m-d') }}">
      <label class="sr-only" for="status-projekta">Status projekta</label>
      <select id="status-projekta" name="status" data-auto-submit class="polje h-8 w-auto font-label-md text-label-md {{ Prikaz::KLASE[Prikaz::ton($project->status)][0] }} border-transparent" title="Promeni status projekta">
        @foreach($statusiProjekta as $st)<option value="{{ $st }}" @selected($project->status === $st)>{{ Prikaz::label($st) }}</option>@endforeach
      </select>
    </form>
  </x-slot:uzNaslov>
  <button type="button" data-modal-open="modal-izmena-projekta" class="dugme-sekundarno"><span class="material-symbols-outlined text-[18px]">edit</span>Uredi projekat</button>
  @if($zgrada)
  <button type="button" data-modal-open="modal-dokument" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Dodaj dokument</button>
  @endif
</x-zaglavlje>

@if(!$zgrada)
  {{-- Prvi korak posle kreiranja projekta: zgrada (PRD 10.1) --}}
  <section class="kartica">
    <x-prazno ikonica="domain_add" naslov="Dodajte prvu zgradu" tekst="Dosije, stanovi, checkliste i reklamacije vode se po zgradi (lamela, blok). Dodajte bar jednu.">
      <form method="POST" action="{{ route('buildings.store', $project) }}" class="flex flex-col sm:flex-row gap-space-sm w-full max-w-lg">
        @csrf
        <input name="naziv" required placeholder="Naziv zgrade (npr. Lamela A)" class="polje flex-1"/>
        <input name="broj_stanova" type="number" min="1" placeholder="Br. stanova" class="polje sm:w-32"/>
        <button class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Dodaj zgradu</button>
      </form>
    </x-prazno>
  </section>
@else

{{-- Zgrade projekta: izbor zgrade čiji se dosije prikazuje --}}
<section class="kartica px-space-md py-space-sm flex flex-wrap items-center gap-space-sm" aria-label="Zgrade projekta">
  <span class="oznaka mr-1">Zgrada</span>
  @foreach($project->buildings as $z)
  @php $aktivna = $z->id === $zgrada->id; @endphp
  <a href="{{ route('projects.show', ['project' => $project, 'zgrada' => $z->id, 'tab' => $tab]) }}"
     class="inline-flex items-center gap-1.5 h-8 px-3 rounded font-label-md text-label-md transition-colors {{ $aktivna ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface hover:bg-surface-container' }}">
    <span class="w-1.5 h-1.5 rounded-full {{ Prikaz::KLASE[Prikaz::ton($z->status)][1] }}"></span>
    {{ $z->naziv }}
    <span class="{{ $aktivna ? 'text-on-primary/70' : 'text-on-surface-variant' }} font-mono-num">{{ $z->units_count }}</span>
  </a>
  @endforeach
  <div class="flex items-center gap-1 ml-auto">
    <button type="button" data-modal-open="modal-izmena-zgrade" class="dugme-tiho dugme-malo"><span class="material-symbols-outlined text-[16px]">edit</span>Uredi zgradu</button>
    <button type="button" data-modal-open="modal-nova-zgrada" class="dugme-tiho dugme-malo"><span class="material-symbols-outlined text-[16px]">add</span>Nova zgrada</button>
  </div>
</section>

{{-- Tabovi dosijea (PRD 9.2) --}}
<nav class="kartica px-space-sm flex items-center gap-1 overflow-x-auto" aria-label="Delovi dosijea">
  @foreach($tabovi as $kljuc => [$naziv, $ikonica, $broj])
  @php $aktivan = $tab === $kljuc; @endphp
  <a href="{{ $url($kljuc) }}" @if($aktivan) aria-current="page" @endif
     class="relative flex items-center gap-1.5 h-11 px-space-md font-label-md text-label-md whitespace-nowrap transition-colors border-b-2 {{ $aktivan ? 'border-primary text-on-surface font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
    <span class="material-symbols-outlined text-[18px]">{{ $ikonica }}</span>{{ $naziv }}
    @if($broj !== null)
    <span class="cip {{ $kljuc === 'reklamacije' && $broj > 0 ? 'bg-error-container text-on-error-container' : 'bg-surface-container text-on-surface-variant' }}">{{ $broj }}</span>
    @endif
  </a>
  @endforeach
</nav>

@if($tab === 'pregled')
  {{-- 6 faza realizacije (PRD 9.2) — prikaz prema statusu projekta --}}
  <section class="kartica p-space-lg flex flex-col gap-space-md" aria-label="Faze realizacije">
    <div class="flex flex-wrap items-center justify-between gap-space-sm">
      <h2 class="font-headline-sm text-headline-sm">Faze realizacije</h2>
      <span class="cip bg-surface-container-low text-on-surface-variant">Faza {{ $faza }} od 6 · prema statusu projekta</span>
    </div>
    <ol class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-space-sm">
      @foreach($koraci as $broj => $naziv)
      @php $stanje = $broj < $faza ? 'gotovo' : ($broj === $faza ? 'tok' : 'ceka'); @endphp
      <li class="p-space-md rounded flex flex-col gap-space-sm {{ $stanje === 'tok' ? 'bg-surface-container-highest' : ($stanje === 'gotovo' ? 'bg-surface-container-low' : 'bg-surface-container-low/60') }}">
        <div class="flex items-center justify-between">
          <span class="font-mono-num text-label-sm text-on-surface-variant">{{ str_pad($broj, 2, '0', STR_PAD_LEFT) }}</span>
          @if($stanje === 'gotovo')
            <span class="w-5 h-5 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center"><span class="material-symbols-outlined text-[14px]">check</span></span>
          @elseif($stanje === 'tok')
            <span class="w-5 h-5 rounded-full bg-tertiary-fixed flex items-center justify-center"><span class="w-2 h-2 rounded-full bg-on-tertiary-fixed"></span></span>
          @else
            <span class="w-5 h-5 rounded-full bg-surface-container"></span>
          @endif
        </div>
        <span class="font-label-md text-label-md {{ $stanje === 'ceka' ? 'text-on-surface-variant' : 'text-on-surface font-semibold' }}">{{ $naziv }}</span>
        <span class="cip self-start {{ $stanje === 'gotovo' ? 'bg-secondary-fixed text-on-secondary-fixed-variant' : ($stanje === 'tok' ? 'bg-tertiary-fixed text-on-tertiary-fixed' : 'bg-surface-container text-on-surface-variant') }}">{{ $stanje === 'gotovo' ? 'Završeno' : ($stanje === 'tok' ? 'U toku' : 'Nije započeto') }}</span>
      </li>
      @endforeach
    </ol>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
    <div class="lg:col-span-5 flex flex-col gap-space-lg">
      {{-- Osnovni podaci (PRD 6.2) --}}
      <section class="kartica p-space-lg flex flex-col gap-space-md">
        <div class="flex items-center justify-between">
          <h2 class="font-headline-sm text-headline-sm">Osnovni podaci</h2>
          <button type="button" data-modal-open="modal-izmena-projekta" class="dugme-tiho dugme-malo"><span class="material-symbols-outlined text-[16px]">edit</span>Izmeni</button>
        </div>
        <div class="grid grid-cols-2 gap-space-sm">
          @foreach([
            ['Planirano stanova', $project->broj_planiranih_stanova ?: '—', 'za ceo projekat'],
            ['Evidentirano jedinica', $stanovi->count(), 'u zgradi '.$zgrada->naziv],
            ['Početak gradnje', $project->datum_pocetka_gradnje?->format('d.m.Y.') ?: '—', null],
            ['Planirani završetak', $project->planirani_datum_zavrsetka?->format('d.m.Y.') ?: '—', null],
          ] as [$l, $v, $o])
          <div class="bg-surface-container-low p-space-md rounded flex flex-col gap-0.5">
            <span class="oznaka">{{ $l }}</span>
            <span class="font-headline-sm text-headline-sm font-mono-num">{{ $v }}</span>
            @if($o)<span class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $o }}</span>@endif
          </div>
          @endforeach
        </div>
        <dl class="flex flex-col divide-y divide-surface-container font-body-md text-body-md">
          <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Tip objekta</dt><dd class="font-medium text-right">{{ Prikaz::label($project->tip) }}</dd></div>
          <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Adresa</dt><dd class="font-medium text-right">{{ trim(($project->lokacija_adresa ? $project->lokacija_adresa.', ' : '').($project->lokacija_grad ?? ''), ', ') ?: '—' }}</dd></div>
          <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Zgrada {{ $zgrada->naziv }}</dt><dd><x-status :v="$zgrada->status" /></dd></div>
          <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Broj zgrada u projektu</dt><dd class="font-medium font-mono-num">{{ $project->buildings->count() }}</dd></div>
        </dl>
      </section>

      {{-- Interna napomena projekta --}}
      <section class="kartica p-space-lg flex flex-col gap-space-sm">
        <div class="flex items-center justify-between">
          <h2 class="font-headline-sm text-headline-sm flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px] text-on-surface-variant">edit_note</span>Interna napomena</h2>
          <button type="button" data-modal-open="modal-izmena-projekta" class="dugme-tiho dugme-malo">{{ $project->napomena ? 'Izmeni' : 'Dodaj' }}</button>
        </div>
        @if($project->napomena)
          <p class="bg-surface-container-low p-space-md rounded font-body-md text-body-md leading-relaxed whitespace-pre-line">{{ $project->napomena }}</p>
          <span class="font-body-sm text-body-sm text-on-surface-variant">Ažurirano {{ $project->updated_at?->format('d.m.Y.') }}</span>
        @else
          <p class="font-body-md text-body-md text-on-surface-variant">Nema napomene. Ovde upišite trenutno stanje radova, dogovore sa izvođačem i slično — vidi je samo vaš tim.</p>
        @endif
      </section>
    </div>

    {{-- Sažetak ostalih delova dosijea sa prečicama --}}
    <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-space-lg">
      <section class="kartica p-space-lg flex flex-col gap-space-md">
        <div class="flex items-center justify-between">
          <span class="w-9 h-9 rounded bg-surface-container flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">folder_open</span></span>
          <span class="font-headline-md text-headline-md font-mono-num">{{ $dokumenti->count() }}</span>
        </div>
        <div>
          <h3 class="font-headline-sm text-headline-sm">Dokumentacija</h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Važeće verzije dozvola, projekata, ugovora i zapisnika.</p>
        </div>
        <ul class="flex flex-col gap-1 font-body-md text-body-md">
          @forelse($dokumenti->take(3) as $d)
          <li class="flex items-center gap-1.5 truncate"><span class="material-symbols-outlined text-[16px] text-on-surface-variant">description</span><span class="truncate">{{ $d->naziv }}</span></li>
          @empty
          <li class="text-on-surface-variant">Još nema dokumenata.</li>
          @endforelse
        </ul>
        <a href="{{ $url('dokumentacija') }}" class="dugme-sekundarno dugme-malo mt-auto">Otvori dokumentaciju<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
      </section>

      <section class="kartica p-space-lg flex flex-col gap-space-md">
        <div class="flex items-center justify-between">
          <span class="w-9 h-9 rounded bg-surface-container flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">door_front</span></span>
          <span class="font-headline-md text-headline-md font-mono-num">{{ $stanovi->count() }}</span>
        </div>
        <div>
          <h3 class="font-headline-sm text-headline-sm">Stanovi</h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Raspodela jedinica po statusu.</p>
        </div>
        <ul class="flex flex-col gap-1.5 font-body-md text-body-md">
          @foreach([
            ['Za prodaju', $stanovi->where('status', 'Za_prodaju')->count(), 'bg-secondary'],
            ['Rezervisano', $stanovi->where('status', 'Rezervisan')->count(), 'bg-amber-500'],
            ['Prodato', $stanovi->whereIn('status', ['Prodat_u_procesu_uknjizenja', 'Prodat_u_garanciji', 'Garancija_istekla'])->count(), 'bg-emerald-500'],
          ] as [$l, $v, $b])
          <li class="flex items-center justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full {{ $b }}"></span>{{ $l }}</span><span class="font-semibold font-mono-num">{{ $v }}</span></li>
          @endforeach
        </ul>
        <a href="{{ $url('stanovi') }}" class="dugme-sekundarno dugme-malo mt-auto">Otvori stanove<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
      </section>

      <section class="kartica p-space-lg flex flex-col gap-space-md">
        <div class="flex items-center justify-between">
          <span class="w-9 h-9 rounded bg-surface-container flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">fact_check</span></span>
          <span class="font-headline-md text-headline-md font-mono-num">{{ $checkliste->sum('reseno_stavki') }}/{{ $checkliste->sum('ukupno_stavki') }}</span>
        </div>
        <div>
          <h3 class="font-headline-sm text-headline-sm">Checkliste</h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Upotrebna dozvola, uknjižba i paket za banku.</p>
        </div>
        <div class="flex flex-col gap-space-sm">
          @foreach($checkliste as $cl)
          @php $p = $cl->ukupno_stavki ? round($cl->reseno_stavki / $cl->ukupno_stavki * 100) : 0; @endphp
          <a href="{{ route('checklists.show', ['building' => $zgrada, 'tip' => $cl->tip_checkliste]) }}" class="flex flex-col gap-1 group">
            <span class="flex justify-between font-body-sm text-body-sm"><span class="group-hover:underline underline-offset-2">{{ Prikaz::label($cl->tip_checkliste) }}</span><span class="font-mono-num text-on-surface-variant">{{ $cl->reseno_stavki }}/{{ $cl->ukupno_stavki }}</span></span>
            <span class="h-1.5 rounded-full bg-surface-container overflow-hidden"><span class="block h-full rounded-full {{ $p === 100 ? 'bg-emerald-500' : 'bg-primary' }}" style="width: {{ $p }}%"></span></span>
          </a>
          @endforeach
        </div>
        <a href="{{ $url('checkliste') }}" class="dugme-sekundarno dugme-malo mt-auto">Otvori checkliste<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
      </section>

      <section class="kartica p-space-lg flex flex-col gap-space-md">
        <div class="flex items-center justify-between">
          <span class="w-9 h-9 rounded bg-surface-container flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">build_circle</span></span>
          <span class="font-headline-md text-headline-md font-mono-num {{ $otvorene->count() ? 'text-error' : '' }}">{{ $otvorene->count() }}</span>
        </div>
        <div>
          <h3 class="font-headline-sm text-headline-sm">Otvorene reklamacije</h3>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Prijavljene ili u radu, za stanove ove zgrade.</p>
        </div>
        <div class="flex flex-col gap-1">
          @forelse($otvorene->take(2) as $r)
          <a href="{{ route('claims.show', $r) }}" class="p-2 rounded bg-surface-container-low hover:bg-surface-container flex items-center justify-between gap-2">
            <span class="min-w-0"><span class="block font-label-md text-label-md truncate">{{ $r->unit->oznaka ?? '' }} · {{ Prikaz::label($r->tip_problema) }}</span><span class="block font-body-sm text-body-sm text-on-surface-variant truncate">{{ $r->odgovorni->ime_prezime ?? 'Nedodeljeno' }}</span></span>
            <x-status :v="$r->status" :tacka="false" />
          </a>
          @empty
          <p class="font-body-md text-body-md text-on-surface-variant">Nema otvorenih reklamacija.</p>
          @endforelse
        </div>
        <a href="{{ $url('reklamacije') }}" class="dugme-sekundarno dugme-malo mt-auto">Sve reklamacije zgrade<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
      </section>
    </div>
  </div>
@endif

@if($tab === 'dokumentacija')
  <section class="kartica overflow-hidden">
    <form method="GET" action="{{ route('projects.show', $project) }}" class="px-space-lg py-space-md flex flex-wrap items-center gap-space-sm">
      <input type="hidden" name="tab" value="dokumentacija"/>
      <input type="hidden" name="zgrada" value="{{ $zgrada->id }}"/>
      <select name="tip" data-auto-submit class="polje w-auto min-w-[220px]" aria-label="Tip dokumenta">
        <option value="">Svi tipovi dokumenata</option>
        @foreach($tipoviDokumenata as $t)<option value="{{ $t->naziv }}" @selected(request('tip') === $t->naziv)>{{ Prikaz::label($t->naziv) }}</option>@endforeach
      </select>
      <label class="inline-flex items-center gap-1.5 font-body-md text-body-md text-on-surface-variant cursor-pointer select-none">
        <input type="checkbox" name="arhiva" value="1" data-auto-submit @checked(request()->boolean('arhiva')) class="w-4 h-4 accent-black"> Prikaži i arhivirane verzije
      </label>
      <button type="button" data-modal-open="modal-dokument" class="dugme-primarno ml-auto"><span class="material-symbols-outlined text-[18px]">add</span>Dodaj dokument</button>
    </form>
    @if($dokumenti->isEmpty())
      <x-prazno ikonica="folder_open" naslov="Nema dokumenata" :tekst="request('tip') ? 'Nema dokumenata izabranog tipa za ovu zgradu.' : 'Dodajte dozvole, projekte, ugovore i zapisnike za zgradu '.$zgrada->naziv.'.'" />
    @else
      @include('documents._tabela')
    @endif
  </section>
@endif

@if($tab === 'stanovi')
  <section class="kartica overflow-hidden">
    <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
      <h2 class="font-headline-sm text-headline-sm">Stanovi — {{ $zgrada->naziv }}</h2>
      <div class="flex items-center gap-space-sm">
        <a href="{{ route('units.index', ['zgrada' => $zgrada->id]) }}" class="dugme-tiho dugme-malo">Pretraga i filteri<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
        <button type="button" data-modal-open="modal-novi-stan" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Dodaj stan</button>
      </div>
    </div>
    @if($stanovi->isEmpty())
      <x-prazno ikonica="door_front" naslov="Nema evidentiranih stanova" tekst="Dodajte stanove i lokale ove zgrade — uz svaki vodite kupca, dokumente i reklamacije." />
    @else
      @include('units._tabela')
    @endif
  </section>
@endif

@if($tab === 'checkliste')
  <section class="grid grid-cols-1 md:grid-cols-3 gap-space-lg">
    @foreach($checkliste as $cl)
    @php
      $p = $cl->ukupno_stavki ? round($cl->reseno_stavki / $cl->ukupno_stavki * 100) : 0;
      $gotovo = $cl->ukupno_stavki > 0 && $cl->reseno_stavki === $cl->ukupno_stavki;
    @endphp
    <a href="{{ route('checklists.show', ['building' => $zgrada, 'tip' => $cl->tip_checkliste]) }}" class="kartica p-space-lg flex flex-col gap-space-md hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between">
        <span class="material-symbols-outlined text-[22px] {{ $gotovo ? 'text-emerald-600' : 'text-on-surface-variant' }}">{{ $gotovo ? 'check_circle' : 'pending' }}</span>
        <span class="cip {{ $gotovo ? 'bg-emerald-50 text-emerald-800' : 'bg-surface-container text-on-surface-variant' }}">{{ $gotovo ? 'Kompletno' : $p.'%' }}</span>
      </div>
      <div>
        <h3 class="font-headline-sm text-headline-sm">{{ Prikaz::label($cl->tip_checkliste) }}</h3>
        <p class="font-body-md text-body-md text-on-surface-variant">{{ $cl->reseno_stavki }} od {{ $cl->ukupno_stavki }} stavki rešeno</p>
      </div>
      <span class="h-2 rounded-full bg-surface-container overflow-hidden"><span class="block h-full rounded-full {{ $gotovo ? 'bg-emerald-500' : 'bg-primary' }}" style="width: {{ $p }}%"></span></span>
      <span class="font-label-md text-label-md flex items-center gap-1">Otvori checklistu<span class="material-symbols-outlined text-[16px]">arrow_forward</span></span>
    </a>
    @endforeach
  </section>
@endif

@if($tab === 'reklamacije')
  <section class="kartica overflow-hidden">
    <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm">
      <h2 class="font-headline-sm text-headline-sm">Reklamacije — {{ $zgrada->naziv }}</h2>
      @if($stanovi->isNotEmpty())
      <button type="button" data-modal-open="modal-nova-reklamacija" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">add</span>Nova reklamacija</button>
      @endif
    </div>
    @if($reklamacije->isEmpty())
      <x-prazno ikonica="build_circle" naslov="Nema reklamacija" :tekst="$stanovi->isEmpty() ? 'Reklamacije se vezuju za stan — prvo dodajte stanove zgrade.' : 'Za stanove ove zgrade nije prijavljena nijedna reklamacija.'" />
    @else
      @include('claims._tabela')
    @endif
  </section>
@endif

{{-- Prozori za unos (otvaraju se dugmadima iznad) --}}
@include('documents._modal', ['zgrada' => $zgrada, 'stanovi' => $stanovi])
@include('units._modal_novi')
@if($stanovi->isNotEmpty())
  @include('claims._modal_nova', ['stanovi' => $stanovi])
@endif

<x-modal id="modal-nova-zgrada" naslov="Nova zgrada u projektu" ikonica="domain_add">
  <form method="POST" action="{{ route('buildings.store', $project) }}" class="flex flex-col gap-space-md">
    @csrf
    <x-polje labela="Naziv zgrade *" za="nz-naziv"><input class="polje" id="nz-naziv" name="naziv" required placeholder="npr. Lamela B"/></x-polje>
    <div class="grid grid-cols-2 gap-space-md">
      <x-polje labela="Broj stanova" za="nz-broj"><input class="polje" id="nz-broj" name="broj_stanova" type="number" min="1"/></x-polje>
      <x-polje labela="Status" za="nz-status">
        <select class="polje" id="nz-status" name="status">
          @foreach($statusiZgrade as $st)<option value="{{ $st }}">{{ Prikaz::label($st) }}</option>@endforeach
        </select>
      </x-polje>
    </div>
    <p class="font-body-sm text-body-sm text-on-surface-variant">Checkliste (upotrebna dozvola, uknjižba, paket za banku) prave se automatski.</p>
    <div class="flex justify-end gap-space-sm"><button type="button" data-modal-close="modal-nova-zgrada" class="dugme-sekundarno">Otkaži</button><button class="dugme-primarno">Dodaj zgradu</button></div>
  </form>
</x-modal>

<x-modal id="modal-izmena-zgrade" :naslov="'Zgrada '.$zgrada->naziv" ikonica="domain">
  <form method="POST" action="{{ route('buildings.update', $zgrada) }}" class="flex flex-col gap-space-md">
    @csrf @method('PATCH')
    <x-polje labela="Naziv zgrade *" za="iz-naziv"><input class="polje" id="iz-naziv" name="naziv" value="{{ $zgrada->naziv }}" required/></x-polje>
    <div class="grid grid-cols-2 gap-space-md">
      <x-polje labela="Broj stanova" za="iz-broj"><input class="polje" id="iz-broj" name="broj_stanova" type="number" min="1" value="{{ $zgrada->broj_stanova }}"/></x-polje>
      <x-polje labela="Status zgrade *" za="iz-status">
        <select class="polje" id="iz-status" name="status" required>
          @foreach($statusiZgrade as $st)<option value="{{ $st }}" @selected($zgrada->status === $st)>{{ Prikaz::label($st) }}</option>@endforeach
        </select>
      </x-polje>
    </div>
    <div class="flex justify-end gap-space-sm"><button type="button" data-modal-close="modal-izmena-zgrade" class="dugme-sekundarno">Otkaži</button><button class="dugme-primarno">Sačuvaj</button></div>
  </form>
</x-modal>
@endif

<x-modal id="modal-izmena-projekta" naslov="Podaci projekta" ikonica="edit" sirina="max-w-xl">
  <form method="POST" action="{{ route('projects.update', $project) }}" class="flex flex-col gap-space-md">
    @csrf @method('PATCH')
    <x-polje labela="Naziv projekta *" za="ip-naziv"><input class="polje" id="ip-naziv" name="naziv" value="{{ $project->naziv }}" required/></x-polje>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
      <x-polje labela="Adresa" za="ip-adresa"><input class="polje" id="ip-adresa" name="lokacija_adresa" value="{{ $project->lokacija_adresa }}"/></x-polje>
      <x-polje labela="Grad" za="ip-grad"><input class="polje" id="ip-grad" name="lokacija_grad" value="{{ $project->lokacija_grad }}"/></x-polje>
      <x-polje labela="Tip objekta" za="ip-tip">
        <select class="polje" id="ip-tip" name="tip">
          @foreach(config('statusi.tip_projekta') as $tp)<option value="{{ $tp }}" @selected($project->tip === $tp)>{{ Prikaz::label($tp) }}</option>@endforeach
        </select>
      </x-polje>
      <x-polje labela="Status *" za="ip-status">
        <select class="polje" id="ip-status" name="status" required>
          @foreach($statusiProjekta as $st)<option value="{{ $st }}" @selected($project->status === $st)>{{ Prikaz::label($st) }}</option>@endforeach
        </select>
      </x-polje>
      <x-polje labela="Broj planiranih stanova" za="ip-broj"><input class="polje" id="ip-broj" name="broj_planiranih_stanova" type="number" min="1" value="{{ $project->broj_planiranih_stanova }}"/></x-polje>
      <div class="hidden sm:block"></div>
      <x-polje labela="Početak gradnje" za="ip-pocetak"><input class="polje" id="ip-pocetak" name="datum_pocetka_gradnje" type="date" value="{{ $project->datum_pocetka_gradnje?->format('Y-m-d') }}"/></x-polje>
      <x-polje labela="Planirani završetak" za="ip-kraj"><input class="polje" id="ip-kraj" name="planirani_datum_zavrsetka" type="date" value="{{ $project->planirani_datum_zavrsetka?->format('Y-m-d') }}"/></x-polje>
    </div>
    <x-polje labela="Interna napomena" za="ip-napomena"><textarea class="polje" id="ip-napomena" name="napomena" rows="3" placeholder="Stanje radova, dogovori sa izvođačem…">{{ $project->napomena }}</textarea></x-polje>
    <div class="flex items-center justify-between gap-space-sm pt-space-xs">
      <span></span>
      <div class="flex gap-space-sm"><button type="button" data-modal-close="modal-izmena-projekta" class="dugme-sekundarno">Otkaži</button><button class="dugme-primarno">Sačuvaj</button></div>
    </div>
  </form>
  @if($currentUser->uloga === 'Vlasnik')
  <form method="POST" action="{{ route('projects.destroy', $project) }}" class="mt-space-md pt-space-md border-t border-surface-container flex items-center justify-between gap-space-sm" data-potvrdi="Trajno obrisati projekat „{{ $project->naziv }}“ i sve njegove podatke? Ovo se ne može poništiti.">
    @csrf @method('DELETE')
    <span class="font-body-sm text-body-sm text-on-surface-variant">Brisanje je trajno i dostupno samo vlasniku.</span>
    <button class="dugme-tiho dugme-malo text-error hover:text-error hover:bg-error-container/40"><span class="material-symbols-outlined text-[16px]">delete</span>Obriši projekat</button>
  </form>
  @endif
</x-modal>
@endsection
