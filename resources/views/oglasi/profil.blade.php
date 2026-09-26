@extends('layouts.app')
@section('naslov', 'Profil firme na Temelju')

@section('content')
<x-zaglavlje naslov="Profil firme na Temelju" :putanja="['Oglasi na Temelju' => route('oglasi.index'), 'Profil firme' => null]" opis="Opis firme i veb-sajt koje kupci vide na vašem profilu investitora na Temelj.rs.">
  @if($profil && ($profil['profil_url'] ?? null))
  <a href="{{ $profil['profil_url'] }}" target="_blank" rel="noopener" class="dugme-sekundarno">Pogledaj profil na Temelju<span class="material-symbols-outlined text-[18px]">open_in_new</span></a>
  @endif
</x-zaglavlje>

@if(! $tenant->povezanSaTemeljem())
  <x-prazno ikonica="link" naslov="Firma još nije povezana sa Temeljem" tekst="Profil na Temelju možete da uređujete čim veza bude odobrena. Povezivanje pokrećete na stranici „Oglasi na Temelju“.">
    <a href="{{ route('oglasi.index') }}" class="dugme-primarno">Idi na Oglasi na Temelju</a>
  </x-prazno>
@elseif($greska)
  <section class="kartica p-space-lg flex items-start gap-space-md border-l-4 border-error" role="alert">
    <span class="material-symbols-outlined text-[22px] text-error">error</span>
    <div class="flex flex-col gap-space-sm">
      <p class="font-body-md text-body-md">{{ $greska }}</p>
      <div><a href="{{ route('temelj.profil') }}" class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">refresh</span>Pokušaj ponovo</a></div>
    </div>
  </section>
@else
  <section class="kartica p-space-lg max-w-3xl">
    <form method="POST" action="{{ route('temelj.profil') }}" class="flex flex-col gap-space-lg">
      @csrf
      <div class="flex flex-col gap-1">
        <h2 class="font-headline-sm text-headline-sm">{{ $profil['naziv'] ?? $tenant->naziv }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant">Naziv, matični broj i PIB stižu iz APR-a i ne menjaju se ovde.</p>
      </div>

      <x-polje labela="O firmi" za="pf-opis" pomoc="Do 2000 znakova. Kratko: od kada gradite, gde, šta vas izdvaja (rokovi, kvalitet, garancije). Bez telefona i mejla — kupci vam pišu preko oglasa.">
        <textarea class="polje" id="pf-opis" name="opis" rows="9" maxlength="2000" placeholder="npr. Gradimo stambene zgrade u Novom Sadu od 2008. godine. Do sada smo završili 14 zgrada sa preko 900 stanova…">{{ old('opis', $profil['opis'] ?? '') }}</textarea>
      </x-polje>
      <div class="-mt-space-md font-body-sm text-body-sm text-on-surface-variant text-right"><span id="pf-brojac">0</span> / 2000</div>

      <x-polje labela="Veb-sajt (nije obavezno)" za="pf-sajt">
        <input class="polje max-w-md" id="pf-sajt" name="sajt" type="text" inputmode="url" maxlength="255" value="{{ old('sajt', $profil['sajt'] ?? '') }}" placeholder="www.vasafirma.rs"/>
      </x-polje>

      <div class="flex flex-wrap items-center gap-space-sm">
        <button class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">save</span>Sačuvaj i objavi na Temelju</button>
        <span class="font-body-sm text-body-sm text-on-surface-variant">Izmena je na Temelju vidljiva odmah.</span>
      </div>
    </form>
  </section>

  @if(!empty($profil['znacka_url']) && !empty($profil['profil_url']))
  @php
    $kodZnacke = '<a href="'.e($profil['profil_url']).'" target="_blank" rel="noopener"><img src="'.e($profil['znacka_url']).'" alt="Ocena kupaca na Temelju" width="240" height="56" loading="lazy"></a>';
  @endphp
  <section class="kartica p-space-lg max-w-3xl flex flex-col gap-space-md">
    <div class="flex flex-col gap-1">
      <h2 class="font-headline-sm text-headline-sm">Bedž za vaš sajt</h2>
      <p class="font-body-md text-body-md text-on-surface-variant">Prikažite ocenu kupaca sa Temelja na svom sajtu. Ocena se sama osvežava; klik vodi na vaš profil. Kod nalepite u HTML sajta (ili ga pošaljite osobi koja održava sajt).</p>
    </div>
    <div class="p-space-md rounded bg-surface-container-low flex items-center">
      <img src="{{ $profil['znacka_url'] }}" alt="Ocena kupaca na Temelju" width="240" height="56">
    </div>
    <x-polje labela="HTML kod" za="znacka-kod">
      <textarea class="polje font-mono-num text-[13px]" id="znacka-kod" rows="3" readonly onclick="this.select()">{{ $kodZnacke }}</textarea>
    </x-polje>
    <div><button type="button" class="dugme-sekundarno dugme-malo" id="znacka-kopiraj"><span class="material-symbols-outlined text-[16px]">content_copy</span>Kopiraj kod</button></div>
  </section>
  @endif
@endif
@endsection

@push('scripts')
<script>
(function () {
  var polje = document.getElementById('pf-opis'), brojac = document.getElementById('pf-brojac');
  if (!polje || !brojac) return;
  var osvezi = function () { brojac.textContent = polje.value.length; };
  polje.addEventListener('input', osvezi);
  osvezi();
})();
(function () {
  var dugme = document.getElementById('znacka-kopiraj'), kod = document.getElementById('znacka-kod');
  if (!dugme || !kod) return;
  dugme.addEventListener('click', function () {
    kod.select();
    (navigator.clipboard ? navigator.clipboard.writeText(kod.value) : Promise.reject()).catch(function () { document.execCommand('copy'); })
      .finally(function () { dugme.lastChild.textContent = 'Kopirano'; setTimeout(function () { dugme.lastChild.textContent = 'Kopiraj kod'; }, 1800); });
  });
})();
</script>
@endpush
