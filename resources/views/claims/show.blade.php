@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', 'Reklamacija #'.$rek->id)

@section('content')
@php
  $stan = $rek->unit;
  $zgrada = $stan?->building;
  $kupac = $rek->customer ?? $stan?->customer;
  $rok = Prikaz::rok($rek);
  $tipNaziv = $rek->tip_problema === 'Drugo' && $rek->tip_problema_drugo ? $rek->tip_problema_drugo : Prikaz::label($rek->tip_problema);
  $jeNadzor = $currentUser->jeNadzor();
  $slike = $rek->files->filter(fn ($f) => preg_match('/\.(jpe?g|png|gif|webp)$/i', $f->putanja_fajla));
  $ostaliPrilozi = $rek->files->diff($slike);
@endphp

<x-zaglavlje :naslov="($stan->oznaka ?? '—').' — '.$tipNaziv"
  :putanja="[($jeNadzor ? 'Moje reklamacije' : 'Reklamacije') => route('claims.index'), '#REK-'.$rek->id => null]"
  :opis="collect([$zgrada?->naziv, $zgrada?->project?->naziv, 'prijavljeno '.$rek->datum_prijave?->format('d.m.Y.')])->filter()->implode(' · ')">
  <x-slot:uzNaslov><x-status :v="$rek->status" /></x-slot:uzNaslov>
  @if($stan && !$jeNadzor)
  <a href="{{ route('units.show', $stan) }}" class="dugme-sekundarno"><span class="material-symbols-outlined text-[18px]">door_front</span>Dosije stana</a>
  @endif
</x-zaglavlje>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
  <div class="lg:col-span-8 flex flex-col gap-space-lg">
    {{-- Opis i prilozi --}}
    <section class="kartica p-space-lg flex flex-col gap-space-md">
      <h2 class="oznaka">Opis problema</h2>
      <p class="font-body-lg text-body-lg leading-relaxed whitespace-pre-line">{{ $rek->opis }}</p>

      @if($rek->files->isNotEmpty())
      <div class="flex flex-col gap-space-sm pt-space-sm border-t border-surface-container">
        <h3 class="oznaka">Prilozi ({{ $rek->files->count() }})</h3>
        @if($slike->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-space-sm">
          @foreach($slike as $f)
          <a href="{{ route('claims.file', $f) }}" target="_blank" rel="noopener" class="group relative block aspect-[4/3] rounded overflow-hidden bg-surface-container" title="{{ $f->originalni_naziv }}">
            <img src="{{ route('claims.file', $f) }}" alt="{{ $f->originalni_naziv }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"/>
            <span class="absolute inset-0 bg-primary/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"><span class="material-symbols-outlined text-on-primary text-[24px]">zoom_in</span></span>
          </a>
          @endforeach
        </div>
        @endif
        @foreach($ostaliPrilozi as $f)
        <a href="{{ route('claims.file', $f) }}" target="_blank" rel="noopener" class="flex items-center gap-space-sm px-space-md py-2 rounded bg-surface-container-low hover:bg-surface-container font-body-md text-body-md">
          <span class="material-symbols-outlined text-[18px] text-on-surface-variant">attach_file</span>{{ $f->originalni_naziv }}
        </a>
        @endforeach
      </div>
      @endif
    </section>

    {{-- Hronološki log (PRD 9.5) --}}
    <section class="kartica p-space-lg flex flex-col gap-space-md">
      <h2 class="font-headline-sm text-headline-sm">Beleške i tok rešavanja</h2>
      <form method="POST" action="{{ route('claims.notes', $rek) }}" class="flex flex-col gap-space-sm">
        @csrf
        <label for="beleska" class="sr-only">Nova beleška</label>
        <textarea id="beleska" name="tekst" required rows="2" maxlength="2000" class="polje" placeholder="Upišite belešku: uviđaj, dogovor sa kupcem, nalog izvođaču…"></textarea>
        <div class="flex justify-end"><button class="dugme-primarno dugme-malo"><span class="material-symbols-outlined text-[16px]">send</span>Dodaj belešku</button></div>
      </form>
      <ol class="flex flex-col gap-space-sm">
        @forelse($rek->notes as $b)
        @php $ini = collect(explode(' ', $b->user->ime_prezime ?? '?'))->filter()->take(2)->map(fn ($d) => mb_strtoupper(mb_substr($d, 0, 1)))->implode(''); @endphp
        <li class="flex items-start gap-space-sm p-space-md rounded bg-surface-container-low">
          <span class="w-8 h-8 rounded-full bg-secondary-fixed text-on-secondary-fixed flex items-center justify-center shrink-0 text-[12px] font-semibold">{{ $ini }}</span>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-x-space-sm">
              <span class="font-label-md text-label-md font-semibold">{{ $b->user->ime_prezime ?? 'Sistem' }}</span>
              <span class="font-mono-num text-body-sm text-on-surface-variant">{{ $b->created_at->format('d.m.Y. H:i') }}</span>
            </div>
            <p class="font-body-md text-body-md mt-0.5 whitespace-pre-line">{{ $b->tekst }}</p>
          </div>
        </li>
        @empty
        <li class="font-body-md text-body-md text-on-surface-variant">Još nema beleški.</li>
        @endforelse
        <li class="flex items-center gap-space-sm px-space-md font-body-sm text-body-sm text-on-surface-variant">
          <span class="material-symbols-outlined text-[16px]">flag</span>Reklamacija evidentirana {{ $rek->datum_prijave?->format('d.m.Y.') }}
        </li>
      </ol>
    </section>
  </div>

  <div class="lg:col-span-4 flex flex-col gap-space-lg">
    {{-- Ručna obrada: status, odgovorni, rok (PRD 2.2 / 2.3 / 2.5) --}}
    <section class="kartica p-space-lg">
      <form method="POST" action="{{ route('claims.update', $rek) }}" class="flex flex-col gap-space-md">
        @csrf @method('PATCH')
        <h2 class="font-headline-sm text-headline-sm">Obrada</h2>
        <x-polje labela="Status" za="rek-status">
          <select class="polje" id="rek-status" name="status">
            @foreach($statusi as $st)<option value="{{ $st }}" @selected($rek->status === $st)>{{ Prikaz::label($st) }}</option>@endforeach
          </select>
        </x-polje>
        @if($jeNadzor)
          <div class="font-body-md text-body-md flex flex-col gap-1">
            <span class="oznaka">Rok rešavanja</span>
            <span class="font-mono-num">{{ $rek->rok_resavanja?->format('d.m.Y.') ?: 'Nije zadat' }}</span>
            @if($rok)<span class="font-body-sm text-body-sm font-medium {{ $rok[1] }}">{{ $rok[0] }}</span>@endif
          </div>
        @else
          <x-polje labela="Odgovorno lice (nadzor / izvođač)" za="rek-odgovorni" :pomoc="$nadzorUsers->isEmpty() ? 'Nema korisnika sa ulogom Nadzor / izvođač — dodajte ih u Korisnici i uloge.' : null">
            <select class="polje" id="rek-odgovorni" name="odgovorni_id">
              <option value="">Nedodeljeno</option>
              @foreach($nadzorUsers as $n)<option value="{{ $n->id }}" @selected($rek->odgovorni_id == $n->id)>{{ $n->ime_prezime }}</option>@endforeach
            </select>
          </x-polje>
          <x-polje labela="Rok rešavanja" za="rek-rok">
            <input class="polje" id="rek-rok" name="rok_resavanja" type="date" value="{{ $rek->rok_resavanja?->format('Y-m-d') }}"/>
            @if($rok)<span class="font-body-sm text-body-sm font-medium flex items-center gap-1 {{ $rok[1] }}"><span class="material-symbols-outlined text-[14px]">schedule</span>{{ $rok[0] }}</span>@endif
          </x-polje>
        @endif
        <button class="dugme-primarno w-full"><span class="material-symbols-outlined text-[18px]">save</span>Sačuvaj</button>
      </form>
    </section>

    {{-- Stan i kupac --}}
    <section class="kartica p-space-lg flex flex-col gap-space-md">
      <h2 class="font-headline-sm text-headline-sm">Stan i kupac</h2>
      <dl class="flex flex-col divide-y divide-surface-container font-body-md text-body-md">
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Stan</dt><dd class="font-medium text-right">{{ $stan->oznaka ?? '—' }}{{ $stan?->sprat ? ', sprat '.$stan->sprat : '' }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Zgrada</dt><dd class="font-medium text-right">{{ $zgrada->naziv ?? '—' }}</dd></div>
        <div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Kupac</dt><dd class="font-medium text-right">{{ $kupac->ime_prezime ?? '—' }}</dd></div>
        @if($kupac?->telefon)<div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Telefon</dt><dd class="text-right"><a class="hover:underline underline-offset-2 font-mono-num" href="tel:{{ preg_replace('/[^0-9+]/', '', $kupac->telefon) }}">{{ $kupac->telefon }}</a></dd></div>@endif
        @if($kupac?->email)<div class="flex justify-between gap-space-md py-2"><dt class="text-on-surface-variant">Email</dt><dd class="text-right truncate"><a class="hover:underline underline-offset-2" href="mailto:{{ $kupac->email }}">{{ $kupac->email }}</a></dd></div>@endif
      </dl>
    </section>
  </div>
</div>
@endsection
