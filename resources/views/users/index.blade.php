@extends('layouts.app')
@section('content')
<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">
  <div class="flex items-center justify-between">
    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Korisnici i uloge</h1>
    <button data-modal-open="modal-new-user" class="h-9 px-space-md bg-primary text-on-primary font-body-sm font-semibold rounded-lg shadow-sm">+ Novi korisnik</button>
  </div>
  <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-left font-body-sm text-body-sm">
      <thead>
        <tr class="bg-surface-container-low text-on-surface-variant font-label-xs uppercase tracking-wider">
          <th class="py-2.5 px-space-md">Ime i prezime</th>
          <th class="py-2.5 px-space-md">Email</th>
          <th class="py-2.5 px-space-md">Uloga</th>
          <th class="py-2.5 px-space-md">Status naloga</th>
          <th class="py-2.5 px-space-md">Dodeljene zgrade</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-surface-container-low">
        @foreach($korisnici as $k)
        <tr class="hover:bg-surface-container-low">
          <td class="py-3 px-space-md font-semibold text-on-surface">{{ $k->ime_prezime }}</td>
          <td class="py-3 px-space-md text-on-surface-variant">{{ $k->email }}</td>
          <td class="py-3 px-space-md">{{ \App\Support\Prikaz::label($k->uloga) }}</td>
          <td class="py-3 px-space-md"><span class="font-label-xs px-2 py-0.5 rounded font-semibold {{ $k->status_naloga === 'Aktivan' ? 'bg-tertiary-fixed text-on-tertiary-fixed-variant' : 'bg-surface-container-high text-on-surface' }}">{{ \App\Support\Prikaz::label($k->status_naloga) }}</span></td>
          <td class="py-3 px-space-md text-on-surface-variant">{{ $k->dodeljeneZgrade->pluck('naziv')->implode(', ') ?: '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="fixed inset-0 z-50 flex items-center justify-center bg-primary/50 backdrop-blur-sm hidden" id="modal-new-user">
  <div class="bg-surface-container-lowest w-full max-w-lg rounded-xl shadow-xl p-space-lg flex flex-col gap-space-md mx-4 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between">
      <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface">Novi interni korisnik</h3>
      <button class="p-1 rounded-lg text-on-surface-variant" data-modal-close="modal-new-user"><span class="material-symbols-outlined text-[20px]">close</span></button>
    </div>
    <form method="POST" action="{{ route('users.store') }}" class="flex flex-col gap-space-md">
      @csrf
      <div class="grid grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1"><label class="font-label-xs uppercase text-on-surface-variant font-semibold">Ime i prezime *</label><input name="ime_prezime" required class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
        <div class="flex flex-col gap-1"><label class="font-label-xs uppercase text-on-surface-variant font-semibold">Email *</label><input name="email" type="email" required class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
      </div>
      <div class="grid grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1"><label class="font-label-xs uppercase text-on-surface-variant font-semibold">Lozinka *</label><input name="password" type="password" required minlength="8" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
        <div class="flex flex-col gap-1"><label class="font-label-xs uppercase text-on-surface-variant font-semibold">Uloga *</label>
          <select name="uloga" required class="h-9 px-space-sm bg-surface-container-low rounded-lg">
            @foreach($uloge as $u)<option value="{{ $u }}">{{ \App\Support\Prikaz::label($u) }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="flex flex-col gap-1"><label class="font-label-xs uppercase text-on-surface-variant font-semibold">Dodeljene zgrade (samo za Nadzor/izvođač)</label>
        <select name="dodeljene_zgrade[]" multiple class="h-24 px-space-sm bg-surface-container-low rounded-lg">
          @foreach($zgrade as $z)<option value="{{ $z->id }}">{{ $z->naziv }}</option>@endforeach
        </select>
      </div>
      <div class="flex justify-end gap-space-sm"><button type="button" data-modal-close="modal-new-user" class="px-space-md py-2 bg-surface-container-high rounded-lg font-label-md">Otkaži</button><button class="px-space-lg py-2 bg-primary text-on-primary rounded-lg font-label-md shadow-sm">Kreiraj korisnika</button></div>
    </form>
  </div>
</div>
@endsection
