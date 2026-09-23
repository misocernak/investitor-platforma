@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', 'Oglas · Stan '.$stan->oznaka)

@push('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
@endpush

@section('content')
@php
  $zgrada = $stan->building;
  $projekat = $zgrada->project;
  $slike = $oglas?->slike ?? collect();
  $naslovnaId = $slike->firstWhere('tip', 'slika')?->id;
  $novi = !$oglas;
  $sobeOpcije = ['0.5' => 'Garsonjera', '1.0' => 'Jednosoban (1.0)', '1.5' => 'Jednoiposoban (1.5)', '2.0' => 'Dvosoban (2.0)', '2.5' => 'Dvoiposoban (2.5)',
    '3.0' => 'Trosoban (3.0)', '3.5' => 'Troiposoban (3.5)', '4.0' => 'Četvorosoban (4.0)', '4.5' => 'Četvoroiposoban (4.5)', '5.0' => 'Petosoban i veći (5.0+)'];
  $trenutneSobe = old('broj_soba', $stan->broj_soba !== null ? number_format((float) $stan->broj_soba, 1, '.', '') : '');
  $imaGradiliste = $zgrada->dozvola_broj || $zgrada->katastarska_parcela || $zgrada->prijava_radova_datum;
@endphp

<x-zaglavlje :naslov="($novi ? 'Novi oglas' : 'Oglas').' · Stan '.$stan->oznaka"
  :putanja="['Oglasi na Temelju' => route('oglasi.index'), 'Stan '.$stan->oznaka => route('units.show', $stan), 'Oglas' => null]"
  :opis="$projekat->naziv.' · '.$zgrada->naziv.' — tri koraka: fotografije, cena, lokacija. Sve ostalo ide automatski.'">
  @if($oglas)<x-slot:uzNaslov><x-oglas-stanje :oglas="$oglas" :tenant="$tenant" /></x-slot:uzNaslov>@endif
  @if($oglas?->temelj_url && $oglas->status === 'aktivan' && $oglas->sinhronizovan_at)
  <a href="{{ $oglas->temelj_url }}" target="_blank" rel="noopener" class="dugme-sekundarno"><span class="material-symbols-outlined text-[18px]">open_in_new</span>Pogledaj na Temelju</a>
  @endif
</x-zaglavlje>

@unless($tenant->povezanSaTemeljem())
  @include('oglasi._veza')
@endunless

@if($oglas?->greska_sinhronizacije && $tenant->povezanSaTemeljem())
<section class="kartica px-space-lg py-space-md flex flex-wrap items-center justify-between gap-space-sm border-l-4 border-error">
  <span class="flex items-center gap-space-sm font-body-md text-body-md"><span class="material-symbols-outlined text-[20px] text-error">sync_problem</span>{{ $oglas->greska_sinhronizacije }}</span>
  <form method="POST" action="{{ route('oglasi.ponovi', $oglas) }}">@csrf<button class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">sync</span>Pošalji ponovo</button></form>
</section>
@endif

<form method="POST" action="{{ route('oglasi.sacuvaj', $stan) }}" enctype="multipart/form-data" class="flex flex-col gap-space-lg" id="oglas-forma">
  @csrf

  {{-- 1. Fotografije --}}
  <section class="kartica p-space-lg flex flex-col gap-space-md">
    <div class="flex items-start gap-space-md">
      <span class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-semibold shrink-0">1</span>
      <div>
        <h2 class="font-headline-sm text-headline-sm">Fotografije</h2>
        <p class="font-body-md text-body-md text-on-surface-variant">Do {{ config('temelj.maks_slika') }} fotografija stana, zgrade ili renderi. Naslovna se prikazuje u listi stanova. Tlocrt označite da bi kupci videli raspored.</p>
      </div>
    </div>

    @if($slike->isNotEmpty())
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-space-sm">
      @foreach($slike as $s)
      <div class="rounded-lg overflow-hidden bg-surface-container-low flex flex-col" data-slika>
        <div class="relative aspect-[4/3] bg-surface-container">
          <img src="{{ $s->url() }}" alt="" class="w-full h-full {{ $s->tip === 'tlocrt' ? 'object-contain bg-white' : 'object-cover' }}" loading="lazy">
          @if($s->id === $naslovnaId)<span class="absolute top-1.5 left-1.5 cip bg-primary text-on-primary">Naslovna</span>@endif
        </div>
        <div class="p-2 flex flex-col gap-1 font-body-sm text-body-sm">
          <label class="flex items-center gap-1.5 cursor-pointer"><input type="radio" name="naslovna" value="{{ $s->id }}" class="accent-black" @checked($s->id === $naslovnaId)> Naslovna</label>
          <label class="flex items-center gap-1.5 cursor-pointer"><input type="checkbox" name="tlocrt[]" value="{{ $s->id }}" class="accent-black" @checked($s->tip === 'tlocrt')> Ovo je tlocrt</label>
          <label class="flex items-center gap-1.5 cursor-pointer text-error"><input type="checkbox" name="obrisi[]" value="{{ $s->id }}" class="accent-red-600" onchange="this.closest('[data-slika]').classList.toggle('opacity-40', this.checked)"> Ukloni</label>
        </div>
      </div>
      @endforeach
    </div>
    @endif

    <label class="flex flex-col items-center justify-center gap-1 p-space-lg rounded-lg border-2 border-dashed border-outline-variant bg-surface-container-low hover:bg-surface-container cursor-pointer text-center transition-colors" for="nove-slike" id="zona-slika">
      <span class="material-symbols-outlined text-[32px] text-on-surface-variant">add_photo_alternate</span>
      <span class="font-label-md text-label-md">{{ $slike->isEmpty() ? 'Dodajte fotografije' : 'Dodajte još fotografija' }}</span>
      <span class="font-body-sm text-body-sm text-on-surface-variant">Kliknite ili prevucite fajlove ovde · JPG, PNG, WebP · do 15 MB po fotografiji</span>
      <input type="file" id="nove-slike" name="slike[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">
    </label>
    <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-8 gap-space-sm" id="pregled-slika"></div>
    @error('slike')<p class="font-body-md text-body-md text-error">{{ $message }}</p>@enderror
    @error('slike.*')<p class="font-body-md text-body-md text-error">{{ $message }}</p>@enderror
  </section>

  {{-- 2. Cena i podaci stana --}}
  <section class="kartica p-space-lg flex flex-col gap-space-md">
    <div class="flex items-start gap-space-md">
      <span class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-semibold shrink-0">2</span>
      <div>
        <h2 class="font-headline-sm text-headline-sm">Cena i podaci stana</h2>
        <p class="font-body-md text-body-md text-on-surface-variant">Isti podaci su u dosijeu stana — izmena ovde menja i dosije.</p>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
      <x-polje labela="Cena sa PDV-om (€)" za="og-cena" pomoc="Kupci vide i cenu po m². Ako cenu ne želite javno, uključite „Cena na upit“.">
        <input class="polje" id="og-cena" name="cena" type="number" min="0" step="1" value="{{ old('cena', $stan->cena !== null ? (int) $stan->cena : '') }}" placeholder="npr. 108400"/>
      </x-polje>
      <div class="flex items-center pt-0 md:pt-6">
        <label class="flex items-center gap-space-sm font-body-md text-body-md cursor-pointer select-none">
          <input type="checkbox" name="cena_na_upit" value="1" class="w-4 h-4 accent-black" @checked(old('cena_na_upit', $oglas?->cena_na_upit))>
          Prikaži „Cena na upit“ umesto cene
        </label>
      </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-space-md">
      <x-polje labela="Kvadratura (m²)" za="og-m2"><input class="polje" id="og-m2" name="kvadratura" type="number" min="0" step="0.01" value="{{ old('kvadratura', $stan->kvadratura) }}"/></x-polje>
      <x-polje labela="Struktura" za="og-sobe">
        <select class="polje" id="og-sobe" name="broj_soba">
          <option value="">—</option>
          @foreach($sobeOpcije as $v => $naziv)<option value="{{ $v }}" @selected($trenutneSobe === $v)>{{ $naziv }}</option>@endforeach
        </select>
      </x-polje>
      <x-polje labela="Sprat" za="og-sprat"><input class="polje" id="og-sprat" name="sprat" value="{{ old('sprat', $stan->sprat) }}" placeholder="npr. 2 ili Prizemlje"/></x-polje>
      <x-polje labela="Terasa / lođa (m²)" za="og-terasa"><input class="polje" id="og-terasa" name="terasa_m2" type="number" min="0" step="0.01" value="{{ old('terasa_m2', $stan->terasa_m2) }}"/></x-polje>
    </div>
    <x-polje labela="Opis (opciono)" za="og-opis" pomoc="Kratko i konkretno: orijentacija, raspored, šta je uključeno u cenu, uslovi plaćanja.">
      <textarea class="polje" id="og-opis" name="opis" rows="4" maxlength="5000" placeholder="npr. Jugoistočna orijentacija, dnevna soba sa izlazom na lođu, podno grejanje. Moguće plaćanje na rate tokom gradnje.">{{ old('opis', $oglas?->opis) }}</textarea>
    </x-polje>
  </section>

  {{-- 3. Zgrada i lokacija (jednom za celu zgradu) --}}
  <section class="kartica p-space-lg flex flex-col gap-space-md">
    <div class="flex items-start gap-space-md">
      <span class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-semibold shrink-0">3</span>
      <div>
        <h2 class="font-headline-sm text-headline-sm">Zgrada i lokacija</h2>
        <p class="font-body-md text-body-md text-on-surface-variant">Unosite jednom — važi za sve oglase u zgradi „{{ $zgrada->naziv }}“.</p>
      </div>
    </div>

    <div class="flex flex-col gap-space-sm">
      <span class="oznaka">Lokacija na mapi</span>
      <div class="flex flex-wrap gap-space-sm">
        <input class="polje flex-1 min-w-[220px]" id="mapa-pretraga" value="{{ old('zgrada.adresa', $zgrada->adresa ?: trim(($projekat->lokacija_adresa ?? '').', '.($projekat->lokacija_grad ?? ''), ', ')) }}" placeholder="Ulica i broj, grad" aria-label="Adresa za pretragu mape">
        <button type="button" class="dugme-sekundarno" id="mapa-trazi"><span class="material-symbols-outlined text-[18px]">search</span>Pronađi na mapi</button>
      </div>
      <div id="mapa" class="h-72 rounded-lg bg-surface-container z-0" data-lat="{{ old('zgrada.lat', $zgrada->lat) }}" data-lng="{{ old('zgrada.lng', $zgrada->lng) }}" data-grad="{{ $projekat->lokacija_grad }}"></div>
      <p class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1.5" id="mapa-poruka">
        <span class="material-symbols-outlined text-[16px]">touch_app</span>
        <span>{{ $zgrada->lat ? 'Lokacija je označena. Kliknite na mapu da je pomerite.' : 'Kliknite na mapu tačno na mesto zgrade (ili pronađite adresu).' }}</span>
      </p>
      <input type="hidden" name="zgrada[lat]" id="mapa-lat" value="{{ old('zgrada.lat', $zgrada->lat) }}">
      <input type="hidden" name="zgrada[lng]" id="mapa-lng" value="{{ old('zgrada.lng', $zgrada->lng) }}">
      @error('zgrada.lat')<p class="font-body-md text-body-md text-error">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
      <x-polje labela="Adresa zgrade" za="og-adresa" pomoc="Ako je prazno, koristi se adresa projekta.">
        <input class="polje" id="og-adresa" name="zgrada[adresa]" value="{{ old('zgrada.adresa', $zgrada->adresa) }}" placeholder="{{ $projekat->lokacija_adresa }}"/>
      </x-polje>
      <x-polje labela="Spratnost zgrade" za="og-spratnost"><input class="polje" id="og-spratnost" name="zgrada[spratnost]" value="{{ old('zgrada.spratnost', $zgrada->spratnost) }}" placeholder="npr. Po+P+5+Pk"/></x-polje>
      <x-polje labela="Grejanje" za="og-grejanje">
        <input class="polje" id="og-grejanje" name="zgrada[grejanje]" list="grejanja" value="{{ old('zgrada.grejanje', $zgrada->grejanje) }}" placeholder="npr. Centralno sa kalorimetrima"/>
        <datalist id="grejanja"><option value="Centralno (daljinsko)"><option value="Centralno sa kalorimetrima"><option value="Podno grejanje"><option value="Toplotne pumpe"><option value="Gas (sopstveni kotao)"><option value="Etažno električno"></datalist>
      </x-polje>
      <x-polje labela="Parking / garaža" za="og-parking"><input class="polje" id="og-parking" name="zgrada[parking]" value="{{ old('zgrada.parking', $zgrada->parking) }}" placeholder="npr. Garažno mesto 12.000 €"/></x-polje>
      <x-polje labela="Lift" za="og-lift">
        @php $lift = old('zgrada.lift', $zgrada->lift === null ? '' : ($zgrada->lift ? '1' : '0')); @endphp
        <select class="polje" id="og-lift" name="zgrada[lift]"><option value="">—</option><option value="1" @selected($lift === '1')>Da</option><option value="0" @selected($lift === '0')>Ne</option></select>
      </x-polje>
      <x-polje labela="Energetski razred" za="og-energija">
        <select class="polje" id="og-energija" name="zgrada[energetski_razred]">
          <option value="">—</option>
          @foreach(['A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'] as $r)<option value="{{ $r }}" @selected(old('zgrada.energetski_razred', $zgrada->energetski_razred) === $r)>{{ $r }}</option>@endforeach
        </select>
      </x-polje>
    </div>

    <details class="rounded-lg bg-surface-container-low p-space-md" @if($imaGradiliste) open @endif>
      <summary class="font-label-md text-label-md cursor-pointer flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">verified</span>Status gradilišta — opciono (kupci ga vide samo ako unesete)</summary>
      <p class="font-body-sm text-body-sm text-on-surface-variant mt-space-sm">Broj građevinske dozvole i parcela povećavaju poverenje kupaca. Na Temelju piše da ih je uneo investitor.</p>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md mt-space-md">
        <x-polje labela="Broj građevinske dozvole" za="og-dozvola"><input class="polje" id="og-dozvola" name="zgrada[dozvola_broj]" value="{{ old('zgrada.dozvola_broj', $zgrada->dozvola_broj) }}" placeholder="npr. ROP-NSD-11922-CPI-4/2024"/></x-polje>
        <x-polje labela="Datum dozvole" za="og-dozvola-datum"><input class="polje" id="og-dozvola-datum" name="zgrada[dozvola_datum]" type="date" value="{{ old('zgrada.dozvola_datum', $zgrada->dozvola_datum?->format('Y-m-d')) }}"/></x-polje>
        <x-polje labela="Izdavalac" za="og-izdavalac"><input class="polje" id="og-izdavalac" name="zgrada[dozvola_izdavalac]" value="{{ old('zgrada.dozvola_izdavalac', $zgrada->dozvola_izdavalac) }}" placeholder="npr. Gradska uprava za urbanizam"/></x-polje>
        <x-polje labela="Katastarska parcela" za="og-parcela"><input class="polje" id="og-parcela" name="zgrada[katastarska_parcela]" value="{{ old('zgrada.katastarska_parcela', $zgrada->katastarska_parcela) }}" placeholder="npr. KP 4412/1 KO Novi Sad II"/></x-polje>
        <x-polje labela="Prijava početka radova" za="og-prijava"><input class="polje" id="og-prijava" name="zgrada[prijava_radova_datum]" type="date" value="{{ old('zgrada.prijava_radova_datum', $zgrada->prijava_radova_datum?->format('Y-m-d')) }}"/></x-polje>
      </div>
    </details>
  </section>

  {{-- Dugme za objavu — uvek vidljivo na dnu ekrana --}}
  <div class="sticky bottom-0 z-20 -mx-margin-mobile sm:-mx-margin px-margin-mobile sm:px-margin py-space-md bg-surface/95 backdrop-blur border-t border-outline-variant/40 flex flex-wrap items-center justify-between gap-space-sm">
    <span class="font-body-md text-body-md text-on-surface-variant">
      @if($tenant->povezanSaTemeljem())
        {{ $novi ? 'Oglas će odmah biti vidljiv kupcima na Temelju.' : 'Izmene se odmah šalju na Temelj.' }}
      @else
        Oglas se čuva sada i objavljuje čim Temelj odobri povezivanje firme.
      @endif
    </span>
    <div class="flex items-center gap-space-sm">
      <a href="{{ route('units.show', $stan) }}" class="dugme-tiho">Otkaži</a>
      <button type="submit" class="dugme-primarno h-10 px-5"><span class="material-symbols-outlined text-[18px]">{{ $novi ? 'campaign' : 'save' }}</span>{{ $novi ? ($tenant->povezanSaTemeljem() ? 'Objavi na Temelju' : 'Sačuvaj oglas') : 'Sačuvaj izmene' }}</button>
    </div>
  </div>
</form>

@if($oglas && $oglas->status !== 'skinut')
<section class="kartica px-space-lg py-space-md flex flex-wrap items-center justify-between gap-space-sm">
  <span class="font-body-md text-body-md text-on-surface-variant">
    {{ $oglas->status === 'pauziran' ? 'Oglas je pauziran — kupci ga ne vide.' : 'Kad prodate stan, promenite mu status — oglas se sam uklanja.' }}
  </span>
  <div class="flex items-center gap-space-sm">
    <form method="POST" action="{{ route('oglasi.status', $oglas) }}">
      @csrf
      <input type="hidden" name="akcija" value="{{ $oglas->status === 'pauziran' ? 'aktiviraj' : 'pauziraj' }}">
      <button class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">{{ $oglas->status === 'pauziran' ? 'play_arrow' : 'pause' }}</span>{{ $oglas->status === 'pauziran' ? 'Aktiviraj oglas' : 'Pauziraj oglas' }}</button>
    </form>
    <form method="POST" action="{{ route('oglasi.status', $oglas) }}" data-potvrdi="Ukloniti oglas sa Temelja? Možete ga kasnije ponovo objaviti.">
      @csrf
      <input type="hidden" name="akcija" value="ukloni">
      <button class="dugme-tiho dugme-malo text-error hover:text-error"><span class="material-symbols-outlined text-[16px]">visibility_off</span>Ukloni sa Temelja</button>
    </form>
  </div>
</section>
@endif
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
(function () {
  // --- Pregled novih fotografija (i prevlačenje fajlova na zonu) ---
  const unos = document.getElementById('nove-slike'), pregled = document.getElementById('pregled-slika'), zona = document.getElementById('zona-slika');
  const prikazi = () => {
    pregled.innerHTML = '';
    Array.from(unos.files).forEach(f => {
      const d = document.createElement('div');
      d.className = 'aspect-[4/3] rounded overflow-hidden bg-surface-container';
      const i = document.createElement('img');
      i.className = 'w-full h-full object-cover';
      i.src = URL.createObjectURL(f);
      d.appendChild(i);
      pregled.appendChild(d);
    });
  };
  unos.addEventListener('change', prikazi);
  ['dragover', 'dragenter'].forEach(t => zona.addEventListener(t, e => { e.preventDefault(); zona.classList.add('bg-surface-container'); }));
  ['dragleave', 'drop'].forEach(t => zona.addEventListener(t, () => zona.classList.remove('bg-surface-container')));
  zona.addEventListener('drop', e => { e.preventDefault(); if (e.dataTransfer.files.length) { unos.files = e.dataTransfer.files; prikazi(); } });

  // --- Mapa: klik postavlja tačku zgrade ---
  const el = document.getElementById('mapa');
  if (!window.L || !el) return;
  const latPolje = document.getElementById('mapa-lat'), lngPolje = document.getElementById('mapa-lng');
  const poruka = document.querySelector('#mapa-poruka span:last-child');
  const lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
  const imaTacku = !isNaN(lat) && !isNaN(lng);
  const grad = (el.dataset.grad || '').toLowerCase();
  const pocetak = imaTacku ? [lat, lng] : (grad.includes('novi sad') ? [45.2671, 19.8335] : (grad.includes('niš') || grad.includes('nis') ? [43.3209, 21.8958] : [44.8125, 20.4612]));
  const mapa = L.map(el, { scrollWheelZoom: false }).setView(pocetak, imaTacku ? 16 : 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapa);
  let marker = imaTacku ? L.marker(pocetak, { draggable: true }).addTo(mapa) : null;
  const postavi = (ll, pomeri) => {
    latPolje.value = ll.lat.toFixed(7);
    lngPolje.value = ll.lng.toFixed(7);
    if (!marker) {
      marker = L.marker(ll, { draggable: true }).addTo(mapa);
      marker.on('dragend', () => postavi(marker.getLatLng(), false));
    } else {
      marker.setLatLng(ll);
    }
    if (pomeri) mapa.setView(ll, 17);
    poruka.textContent = 'Lokacija je označena. Kliknite na mapu ili prevucite tačku da je pomerite.';
  };
  if (marker) marker.on('dragend', () => postavi(marker.getLatLng(), false));
  mapa.on('click', e => postavi(e.latlng, false));

  // Pretraga adrese (OpenStreetMap Nominatim)
  const trazi = () => {
    const q = document.getElementById('mapa-pretraga').value.trim();
    if (!q) return;
    poruka.textContent = 'Tražim adresu…';
    fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=rs&q=' + encodeURIComponent(q), { headers: { 'Accept-Language': 'sr-Latn' } })
      .then(r => r.json())
      .then(rez => {
        if (rez && rez[0]) postavi(L.latLng(parseFloat(rez[0].lat), parseFloat(rez[0].lon)), true);
        else poruka.textContent = 'Adresa nije pronađena — kliknite direktno na mapu.';
      })
      .catch(() => { poruka.textContent = 'Pretraga trenutno ne radi — kliknite direktno na mapu.'; });
  };
  document.getElementById('mapa-trazi').addEventListener('click', trazi);
  document.getElementById('mapa-pretraga').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); trazi(); } });
})();
</script>
@endpush
