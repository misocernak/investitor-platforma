{{-- Povezivanje firme sa Temeljem — prikazuje se dok veza nije odobrena (i kao tanka traka kad jeste). --}}
@php
  $status = $tenant->temelj_veza_status;
  $mozeAdmin = $currentUser->mozeAdministrirati();
  $podeseno = \App\Services\TemeljApi::podesen();
@endphp

@if(!$podeseno)
  <section class="kartica p-space-lg flex items-start gap-space-md border-l-4 border-error" role="alert">
    <span class="material-symbols-outlined text-[22px] text-error">error</span>
    <div>
      <h2 class="font-headline-sm text-headline-sm">Veza sa Temeljem nije podešena na serveru</h2>
      <p class="font-body-md text-body-md text-on-surface-variant">U <code>.env</code> fajl aplikacije treba upisati <code>TEMELJ_API_KLJUC</code> (isti ključ kao na Temelju). Dok to nije urađeno, oglasi se čuvaju ali ne šalju.</p>
    </div>
  </section>
@elseif($status === 'odobrena')
  <section class="kartica px-space-lg py-space-md flex flex-wrap items-center gap-space-sm justify-between">
    <span class="flex items-center gap-space-sm font-body-md text-body-md">
      <span class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">verified</span></span>
      <span><strong>Firma je povezana sa Temeljem.</strong> <span class="text-on-surface-variant">Oglasi se objavljuju odmah, a upiti kupaca stižu u „Upiti kupaca“.</span></span>
    </span>
    <span class="flex flex-wrap items-center gap-space-xs">
      @if($mozeAdmin)
      <a href="{{ route('temelj.profil') }}" class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">edit</span>Opis firme na Temelju</a>
      @endif
      @if($tenant->temelj_profil_url)
      <a href="{{ $tenant->temelj_profil_url }}" target="_blank" rel="noopener" class="dugme-tiho dugme-malo">Vaš profil na Temelju<span class="material-symbols-outlined text-[16px]">open_in_new</span></a>
      @endif
    </span>
  </section>
@else
  <section class="kartica p-space-lg flex flex-col gap-space-md {{ $status === 'na_cekanju' ? 'border-l-4 border-amber-500' : ($status === 'odbijena' ? 'border-l-4 border-error' : '') }}">
    <div class="flex items-start gap-space-md">
      <span class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $status === 'na_cekanju' ? 'bg-amber-50 text-amber-700' : ($status === 'odbijena' ? 'bg-error-container text-on-error-container' : 'bg-surface-container text-on-surface') }}">
        <span class="material-symbols-outlined text-[22px]">{{ $status === 'na_cekanju' ? 'hourglass_top' : ($status === 'odbijena' ? 'block' : 'link') }}</span>
      </span>
      <div class="flex flex-col gap-1">
        @if($status === 'na_cekanju')
          <h2 class="font-headline-sm text-headline-sm">Zahtev je poslat — Temelj proverava vašu firmu</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Obično traje do jednog radnog dana. Oglase već sada možete da pripremite — pojaviće se na Temelju automatski čim veza bude odobrena.</p>
        @elseif($status === 'odbijena')
          <h2 class="font-headline-sm text-headline-sm">Temelj nije odobrio povezivanje</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">{{ $tenant->temelj_veza_poruka ?: 'Podaci firme nisu mogli da se potvrde.' }} Proverite matični broj i pošaljite zahtev ponovo, ili pišite Temelju.</p>
        @else
          <h2 class="font-headline-sm text-headline-sm">Prvi korak: povežite firmu sa Temeljem (samo jednom)</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Unesite matični broj firme. Temelj proverava da li je firma stvarno vaša, pa oglase povezuje sa vašim profilom investitora i ocenama kupaca.</p>
        @endif
      </div>
    </div>
    @if($status !== 'na_cekanju')
      @if($mozeAdmin)
      <form method="POST" action="{{ route('temelj.veza') }}" class="flex flex-wrap items-end gap-space-sm pl-0 sm:pl-14">
        @csrf
        <x-polje labela="Matični broj firme (8 cifara)" za="veza-mb">
          <input class="polje w-48" id="veza-mb" name="maticni_broj" inputmode="numeric" maxlength="8" required value="{{ old('maticni_broj', $tenant->maticni_broj) }}" placeholder="npr. 21234567"/>
        </x-polje>
        <button class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">send</span>Pošalji zahtev za povezivanje</button>
      </form>
      @else
      <p class="font-body-md text-body-md sm:pl-14">Povezivanje radi vlasnik ili administrator firme.</p>
      @endif
    @else
      <form method="POST" action="{{ route('temelj.veza.proveri') }}" class="sm:pl-14">
        @csrf
        <button class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">refresh</span>Proveri da li je odobreno</button>
      </form>
    @endif
  </section>
@endif
