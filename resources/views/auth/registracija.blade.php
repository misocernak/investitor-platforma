@extends('layouts.gost')
@section('naslov', 'Registracija firme')
@section('sirina', '640px')

@section('content')
<div class="kartica p-space-lg flex flex-col gap-space-lg">
  <div class="flex flex-col gap-1">
    <h1 class="font-headline-md text-headline-md">Registracija firme</h1>
    <p class="font-body-md text-body-md text-on-surface-variant">Nalog otvara zakonski zastupnik ili ovlašćeno lice investitora. Proveravamo podatke i javljamo se čim nalog bude odobren — obično u roku od jednog radnog dana.</p>
  </div>

  @if($errors->any())
  <div class="p-space-sm rounded bg-error-container text-on-error-container font-body-md text-body-md flex gap-space-sm" role="alert">
    <span class="material-symbols-outlined text-[18px]">error</span>
    <ul class="flex flex-col gap-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  <form method="POST" action="{{ route('registracija') }}" enctype="multipart/form-data" class="flex flex-col gap-space-lg" id="registracija">
    @csrf
    <input type="text" name="sajt" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

    {{-- 1. Firma --}}
    <section class="flex flex-col gap-space-md">
      <h2 class="flex items-center gap-space-sm font-headline-sm text-headline-sm"><span class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center text-[13px]">1</span>Firma</h2>
      <div class="flex flex-wrap items-end gap-space-sm">
        <x-polje labela="Matični broj firme (8 cifara) *" za="rg-mb" class="flex-1 min-w-[200px]">
          <input class="polje" id="rg-mb" name="maticni_broj" inputmode="numeric" maxlength="8" required value="{{ old('maticni_broj', $mb) }}" placeholder="npr. 21234567" autocomplete="off"/>
        </x-polje>
        <button type="button" class="dugme-sekundarno" id="rg-proveri"><span class="material-symbols-outlined text-[18px]">search</span>Pronađi firmu</button>
      </div>
      <p class="font-body-md text-body-md hidden" id="rg-mb-poruka" role="status"></p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md" id="rg-firma">
        <x-polje labela="Naziv firme *" za="rg-naziv" class="sm:col-span-2"><input class="polje" id="rg-naziv" name="naziv" required value="{{ old('naziv') }}"/></x-polje>
        <x-polje labela="PIB (9 cifara) *" za="rg-pib"><input class="polje" id="rg-pib" name="pib" inputmode="numeric" maxlength="9" required value="{{ old('pib') }}"/></x-polje>
        <x-polje labela="Grad" za="rg-grad"><input class="polje" id="rg-grad" name="grad" value="{{ old('grad') }}"/></x-polje>
        <x-polje labela="Adresa sedišta" za="rg-adresa" class="sm:col-span-2"><input class="polje" id="rg-adresa" name="adresa" value="{{ old('adresa') }}"/></x-polje>
      </div>
    </section>

    {{-- 2. Lice koje registruje --}}
    <section class="flex flex-col gap-space-md pt-space-md border-t border-surface-container">
      <h2 class="flex items-center gap-space-sm font-headline-sm text-headline-sm"><span class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center text-[13px]">2</span>Vaši podaci</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <x-polje labela="Ime i prezime *" za="rg-ime"><input class="polje" id="rg-ime" name="ime_prezime" required value="{{ old('ime_prezime') }}" autocomplete="name"/></x-polje>
        <x-polje labela="Funkcija u firmi *" za="rg-funkcija">
          <select class="polje" id="rg-funkcija" name="funkcija" required>
            @foreach(\App\Models\User::FUNKCIJE as $k => $naziv)<option value="{{ $k }}" @selected(old('funkcija', 'direktor') === $k)>{{ $naziv }}</option>@endforeach
          </select>
        </x-polje>
        <x-polje labela="Email *" za="rg-email" pomoc="Na ovu adresu stiže link za potvrdu."><input class="polje" id="rg-email" name="email" type="email" required value="{{ old('email') }}" autocomplete="email"/></x-polje>
        <x-polje labela="Telefon *" za="rg-telefon"><input class="polje" id="rg-telefon" name="telefon" type="tel" required value="{{ old('telefon') }}" placeholder="06x xxx xxxx" autocomplete="tel"/></x-polje>
        <x-polje labela="Lozinka * (najmanje 8 znakova)" za="rg-lozinka"><input class="polje" id="rg-lozinka" name="password" type="password" required minlength="8" autocomplete="new-password"/></x-polje>
        <x-polje labela="Ponovite lozinku *" za="rg-lozinka2"><input class="polje" id="rg-lozinka2" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"/></x-polje>
      </div>
    </section>

    {{-- 3. Ovlašćenje --}}
    <section class="flex flex-col gap-space-md pt-space-md border-t border-surface-container">
      <h2 class="flex items-center gap-space-sm font-headline-sm text-headline-sm"><span class="w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center text-[13px]">3</span>Ovlašćenje</h2>
      <div id="rg-punomocje" class="{{ old('funkcija') === 'ovlasceno_lice' ? '' : 'hidden' }}">
        <x-polje labela="Punomoćje ili ovlašćenje * (PDF, JPG, PNG — do 10 MB)" za="rg-ovlascenje" pomoc="Dokument koji je potpisao zakonski zastupnik firme, kojim vas ovlašćuje da otvorite i vodite nalog.">
          <input class="polje" id="rg-ovlascenje" name="ovlascenje" type="file" accept=".pdf,.jpg,.jpeg,.png"/>
        </x-polje>
      </div>
      <label class="flex items-start gap-space-sm font-body-md text-body-md cursor-pointer">
        <input type="checkbox" name="izjava" value="1" class="w-4 h-4 mt-0.5 accent-black" @checked(old('izjava')) required>
        <span>Izjavljujem da sam zakonski zastupnik ili ovlašćeno lice navedene firme i da su uneti podaci tačni.</span>
      </label>
      <label class="flex items-start gap-space-sm font-body-md text-body-md cursor-pointer">
        <input type="checkbox" name="uslovi" value="1" class="w-4 h-4 mt-0.5 accent-black" @checked(old('uslovi')) required>
        <span>Prihvatam uslove korišćenja Temelj Investitora i saglasan/na sam da se podaci firme i oglasi prikazuju na Temelj.rs.</span>
      </label>
    </section>

    <button type="submit" class="dugme-primarno h-10 w-full"><span class="material-symbols-outlined text-[18px]">send</span>Pošalji zahtev za nalog</button>
  </form>
</div>
<p class="text-center font-body-md text-body-md">Već imate nalog? <a href="{{ route('login') }}" class="font-semibold underline underline-offset-2">Prijavite se</a></p>
@endsection

@push('scripts')
<script>
(function () {
  const mb = document.getElementById('rg-mb'), poruka = document.getElementById('rg-mb-poruka');
  const polja = { naziv: 'rg-naziv', pib: 'rg-pib', adresa: 'rg-adresa', grad: 'rg-grad' };
  const prikazi = (tekst, ton) => {
    poruka.textContent = tekst;
    poruka.className = 'font-body-md text-body-md ' + (ton === 'ok' ? 'text-emerald-700' : ton === 'greska' ? 'text-error' : 'text-on-surface-variant');
  };
  let posledji = '';
  const proveri = () => {
    const v = mb.value.replace(/\D/g, '');
    if (v.length !== 8 || v === posledji) return;
    posledji = v;
    prikazi('Tražim firmu u registru…');
    fetch('{{ route('registracija.firma') }}?mb=' + v, { headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(o => {
        if (!o.ok) { prikazi(o.poruka || 'Provera nije uspela.', 'greska'); return; }
        if (!o.pronadjena) { prikazi(o.poruka || 'Firma nije pronađena u registru — unesite podatke ručno. Proverićemo ih pre odobrenja.'); return; }
        Object.keys(polja).forEach(k => { if (o[k]) document.getElementById(polja[k]).value = o[k]; });
        prikazi('✓ Pronađeno u APR registru: ' + o.naziv + (o.apr_status ? ' (' + o.apr_status + ')' : ''), 'ok');
      })
      .catch(() => prikazi('Registar trenutno nije dostupan — unesite podatke ručno.'));
  };
  mb.addEventListener('input', () => { if (mb.value.replace(/\D/g, '').length === 8) proveri(); });
  document.getElementById('rg-proveri').addEventListener('click', () => { posledji = ''; proveri(); });
  if (mb.value.length === 8 && !document.getElementById('rg-naziv').value) proveri();

  // Punomoćje je obavezno samo za ovlašćeno lice
  const funkcija = document.getElementById('rg-funkcija'), punomocje = document.getElementById('rg-punomocje'), fajl = document.getElementById('rg-ovlascenje');
  const osvezi = () => { const treba = funkcija.value === 'ovlasceno_lice'; punomocje.classList.toggle('hidden', !treba); fajl.required = treba; };
  funkcija.addEventListener('change', osvezi);
  osvezi();
})();
</script>
@endpush
