@extends('layouts.app')
@section('naslov', 'Recenzije kupaca')

@section('content')
<x-zaglavlje naslov="Recenzije kupaca" opis="Ocene koje su kupci ostavili vašoj firmi na Temelju. Na svaku možete javno da odgovorite — odgovor se prikazuje ispod recenzije.">
  @if($podaci && ($podaci['profil_url'] ?? null))
  <a href="{{ $podaci['profil_url'] }}#recenzije" target="_blank" rel="noopener" class="dugme-sekundarno">Pogledaj na Temelju<span class="material-symbols-outlined text-[18px]">open_in_new</span></a>
  @endif
</x-zaglavlje>

@if(! $tenant->povezanSaTemeljem())
  <x-prazno ikonica="link" naslov="Firma još nije povezana sa Temeljem" tekst="Recenzije kupaca vidite ovde čim veza sa Temeljem bude odobrena. Povezivanje pokrećete na stranici „Oglasi na Temelju“.">
    <a href="{{ route('oglasi.index') }}" class="dugme-primarno">Idi na Oglasi na Temelju</a>
  </x-prazno>
@elseif($greska)
  <section class="kartica p-space-lg flex items-start gap-space-md border-l-4 border-error" role="alert">
    <span class="material-symbols-outlined text-[22px] text-error">error</span>
    <div class="flex flex-col gap-space-sm">
      <p class="font-body-md text-body-md">{{ $greska }}</p>
      <div><a href="{{ route('recenzije.index') }}" class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">refresh</span>Pokušaj ponovo</a></div>
    </div>
  </section>
@else
  <section class="grid grid-cols-1 sm:grid-cols-3 gap-gutter" aria-label="Pregled recenzija">
    <x-pokazatelj labela="Prosečna ocena" :vrednost="$podaci['prosek'] !== null ? number_format($podaci['prosek'], 1, ',', '.') : '—'" ikonica="star" opis="od 5,0" />
    <x-pokazatelj labela="Recenzija" :vrednost="$podaci['ukupno']" ikonica="reviews" opis="objavljenih na Temelju" />
    <x-pokazatelj labela="Bez odgovora" :vrednost="$bezOdgovora" ikonica="reply" opis="čekaju vaš odgovor" :href="route('recenzije.index', ['filter' => 'bez_odgovora'])" />
  </section>

  <section class="kartica overflow-hidden">
    <div class="px-space-lg h-14 flex items-center justify-between gap-space-sm border-b border-surface-container">
      <div class="flex items-center gap-space-xs">
        <a href="{{ route('recenzije.index') }}" class="cip {{ $filter === 'sve' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant' }}">Sve</a>
        <a href="{{ route('recenzije.index', ['filter' => 'bez_odgovora']) }}" class="cip {{ $filter === 'bez_odgovora' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant' }}">Bez odgovora ({{ $bezOdgovora }})</a>
      </div>
      <span class="hidden md:inline font-body-sm text-body-sm text-on-surface-variant">Kupac se vidi samo pod pseudonimom.</span>
    </div>

    @if($recenzije->isEmpty())
      <x-prazno ikonica="reviews" :naslov="$filter === 'bez_odgovora' ? 'Odgovorili ste na sve recenzije' : 'Još nema recenzija'" :tekst="$filter === 'bez_odgovora' ? 'Nove recenzije će se pojaviti ovde.' : 'Kad kupci ocene vašu firmu na Temelju, recenzije će se pojaviti ovde.'" />
    @else
    <ul class="divide-y divide-surface-container">
      @foreach($recenzije as $r)
      <li class="px-space-lg py-space-md flex flex-col gap-space-sm">
        <div class="flex flex-wrap items-center gap-x-space-sm gap-y-1">
          <strong class="font-label-md text-label-md">{{ $r['pseudonim'] }}</strong>
          @if($r['verifikovan'])<span class="cip bg-emerald-50 text-emerald-800"><span class="material-symbols-outlined text-[14px]">verified</span>Dokaz o kupovini</span>@endif
          <span class="font-body-sm text-body-sm text-on-surface-variant">{{ collect([$r['projekat'] ? 'projekat '.$r['projekat'] : null, \Illuminate\Support\Carbon::parse($r['datum'])->format('d.m.Y.')])->filter()->implode(' · ') }}</span>
          <span class="ml-auto inline-flex items-center gap-1 font-label-md text-label-md"><span class="text-amber-500">★</span>{{ number_format($r['ocena'], 1, ',', '.') }}</span>
        </div>

        @if($r['tekst'])
        <p class="font-body-md text-body-md whitespace-pre-line max-w-4xl">{{ $r['tekst'] }}</p>
        @endif
        @if($r['kriterijumi'])
        <div class="flex flex-wrap gap-1.5">
          @foreach($r['kriterijumi'] as $naziv => $v)
            <span class="cip bg-surface-container text-on-surface-variant">{{ $naziv }} {{ $v }}/5</span>
          @endforeach
        </div>
        @endif

        @if($r['odgovor'])
        <div class="border-l-4 border-primary bg-surface-container-low rounded-r px-space-md py-space-sm flex flex-col gap-1 max-w-4xl">
          <div class="flex flex-wrap items-center gap-space-sm">
            <span class="font-label-md text-label-md">Vaš odgovor</span>
            @if($r['odgovor_datum'])<span class="font-body-sm text-body-sm text-on-surface-variant">{{ \Illuminate\Support\Carbon::parse($r['odgovor_datum'])->format('d.m.Y.') }}</span>@endif
            <span class="ml-auto flex items-center gap-space-xs">
              <button type="button" class="dugme-tiho dugme-malo" data-modal-open="modal-odgovor" data-postavi="{{ json_encode(['recenzija_id' => $r['id'], 'tekst' => $r['odgovor']]) }}"><span class="material-symbols-outlined text-[16px]">edit</span>Izmeni</button>
              <form method="POST" action="{{ route('recenzije.odgovor') }}" data-potvrdi="Obrisati vaš odgovor sa Temelja?">
                @csrf
                <input type="hidden" name="recenzija_id" value="{{ $r['id'] }}">
                <input type="hidden" name="tekst" value="">
                <button class="dugme-tiho dugme-malo text-error"><span class="material-symbols-outlined text-[16px]">delete</span>Obriši</button>
              </form>
            </span>
          </div>
          <p class="font-body-md text-body-md whitespace-pre-line">{{ $r['odgovor'] }}</p>
        </div>
        @else
        <div>
          <button type="button" class="dugme-sekundarno dugme-malo" data-modal-open="modal-odgovor" data-postavi="{{ json_encode(['recenzija_id' => $r['id'], 'tekst' => '']) }}"><span class="material-symbols-outlined text-[16px]">reply</span>Odgovori javno</button>
        </div>
        @endif
      </li>
      @endforeach
    </ul>
    @endif
  </section>

  <x-modal id="modal-odgovor" naslov="Javni odgovor na recenziju" ikonica="reply" sirina="max-w-xl">
    <form method="POST" action="{{ route('recenzije.odgovor') }}" class="flex flex-col gap-space-md">
      @csrf
      <input type="hidden" name="recenzija_id" value="">
      <x-polje labela="Odgovor (vide ga svi na Temelju)" za="odg-tekst" pomoc="Do 1500 znakova. Zahvalite se, objasnite šta ste preduzeli ili ponudite rešenje. Ne navodite lične podatke kupca ni detalje ugovora.">
        <textarea class="polje" id="odg-tekst" name="tekst" rows="7" maxlength="1500" required placeholder="npr. Hvala na iskrenoj oceni. Kašnjenje priključka toplane nije zavisilo od nas, ali smo od tada…"></textarea>
      </x-polje>
      <div class="flex justify-end gap-space-sm">
        <button type="button" class="dugme-tiho" data-modal-close="modal-odgovor">Otkaži</button>
        <button class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">send</span>Objavi odgovor</button>
      </div>
    </form>
  </x-modal>
@endif
@endsection
