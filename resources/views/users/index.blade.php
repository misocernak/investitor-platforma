@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', 'Korisnici i uloge')

@section('content')
<x-zaglavlje naslov="Korisnici i uloge" opis="Ko ima pristup nalogu firme i šta sme da radi. Uloga i status se menjaju direktno u tabeli.">
  <button type="button" data-modal-open="modal-novi-korisnik" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">person_add</span>Novi korisnik</button>
</x-zaglavlje>

<section class="kartica overflow-hidden">
  <div class="overflow-x-auto">
    <table class="tabela">
      <thead>
        <tr>
          <th>Korisnik</th>
          <th>Uloga</th>
          <th>Status naloga</th>
          <th>Dodeljene zgrade</th>
        </tr>
      </thead>
      <tbody>
        @foreach($korisnici as $k)
        @php
          $ini = collect(explode(' ', $k->ime_prezime))->filter()->take(2)->map(fn ($d) => mb_strtoupper(mb_substr($d, 0, 1)))->implode('');
          // Administrator ne sme da menja Vlasnika; niko ne menja sam sebe (da se ne zaključa)
          $mozeMenjati = $k->id !== $currentUser->id && !($k->uloga === 'Vlasnik' && $currentUser->uloga !== 'Vlasnik');
        @endphp
        <tr>
          <td>
            <div class="flex items-center gap-space-sm">
              <span class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center shrink-0 text-[12px] font-semibold">{{ $ini }}</span>
              <div class="min-w-0">
                <div class="font-semibold">{{ $k->ime_prezime }}@if($k->id === $currentUser->id)<span class="font-normal text-on-surface-variant"> (vi)</span>@endif</div>
                <div class="font-body-sm text-body-sm text-on-surface-variant">{{ $k->email }}</div>
              </div>
            </div>
          </td>
          @if($mozeMenjati)
          <td colspan="2">
            <form method="POST" action="{{ route('users.update', $k) }}" class="flex flex-wrap items-center gap-space-sm">
              @csrf @method('PATCH')
              <select name="uloga" data-auto-submit class="polje h-8 w-auto" aria-label="Uloga">
                @foreach($uloge as $u)<option value="{{ $u }}" @selected($k->uloga === $u)>{{ Prikaz::label($u) }}</option>@endforeach
              </select>
              <select name="status_naloga" data-auto-submit class="polje h-8 w-auto" aria-label="Status naloga">
                @foreach(config('statusi.status_naloga') as $s)<option value="{{ $s }}" @selected($k->status_naloga === $s)>{{ Prikaz::label($s) }}</option>@endforeach
              </select>
            </form>
          </td>
          @else
          <td>{{ Prikaz::label($k->uloga) }}</td>
          <td><x-status :v="$k->status_naloga" /></td>
          @endif
          <td class="text-on-surface-variant">{{ $k->dodeljeneZgrade->pluck('naziv')->implode(', ') ?: '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</section>

<div class="p-space-md rounded-lg bg-surface-container-low flex items-start gap-space-md">
  <span class="material-symbols-outlined text-[20px] text-on-surface-variant">info</span>
  <ul class="font-body-md text-body-md text-on-surface-variant flex flex-col gap-0.5">
    <li><strong class="text-on-surface font-semibold">Vlasnik / Administrator</strong> — sve, uključujući korisnike.</li>
    <li><strong class="text-on-surface font-semibold">Operater</strong> — unos i izmena projekata, stanova, dokumenata i reklamacija.</li>
    <li><strong class="text-on-surface font-semibold">Nadzor / izvođač</strong> — vidi samo reklamacije koje su mu dodeljene i menja im status.</li>
  </ul>
</div>

<x-modal id="modal-novi-korisnik" naslov="Novi korisnik" ikonica="person_add">
  <form method="POST" action="{{ route('users.store') }}" class="flex flex-col gap-space-md">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
      <x-polje labela="Ime i prezime *" za="nk-ime"><input class="polje" id="nk-ime" name="ime_prezime" required/></x-polje>
      <x-polje labela="Email *" za="nk-email"><input class="polje" id="nk-email" name="email" type="email" required/></x-polje>
      <x-polje labela="Početna lozinka *" za="nk-lozinka" pomoc="Najmanje 8 znakova. Prosledite je korisniku."><input class="polje" id="nk-lozinka" name="password" type="password" required minlength="8" autocomplete="new-password"/></x-polje>
      <x-polje labela="Uloga *" za="nk-uloga">
        <select class="polje" id="nk-uloga" name="uloga" required onchange="document.getElementById('nk-zgrade').classList.toggle('hidden', this.value !== 'Nadzor_izvodjac')">
          @foreach($uloge as $u)<option value="{{ $u }}" @selected($u === 'Operater')>{{ Prikaz::label($u) }}</option>@endforeach
        </select>
      </x-polje>
    </div>
    <div id="nk-zgrade" class="hidden">
      <x-polje labela="Dodeljene zgrade" za="nk-zgrade-izbor" pomoc="Držite Ctrl za izbor više zgrada.">
        <select class="polje" id="nk-zgrade-izbor" name="dodeljene_zgrade[]" multiple size="5">
          @foreach($zgrade as $z)<option value="{{ $z->id }}">{{ $z->naziv }}</option>@endforeach
        </select>
      </x-polje>
    </div>
    <div class="flex justify-end gap-space-sm pt-space-xs">
      <button type="button" data-modal-close="modal-novi-korisnik" class="dugme-sekundarno">Otkaži</button>
      <button class="dugme-primarno">Kreiraj korisnika</button>
    </div>
  </form>
</x-modal>
@endsection
