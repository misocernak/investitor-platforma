@use('App\Models\Upit')
@extends('layouts.app')
@section('naslov', 'Upiti kupaca')

@section('content')
<x-zaglavlje naslov="Upiti kupaca" opis="Kupci sa Temelj.rs koji su zainteresovani za vaše stanove. Javite se što pre — brz odgovor najviše utiče na prodaju." />

<nav class="flex flex-wrap items-center gap-1 p-1 rounded-lg bg-surface-container-low self-start" aria-label="Filter upita">
  @foreach(['' => 'Svi', 'novo' => 'Novi', 'u_kontaktu' => 'U kontaktu', 'zatvoreno' => 'Zatvoreni'] as $k => $naziv)
  @php $aktivan = ($status ?? '') === $k || ($k === '' && !isset(Upit::STATUSI[$status])); $broj = $k === '' ? $brojevi->sum() : ($brojevi[$k] ?? 0); @endphp
  <a href="{{ route('upiti.index', $k ? ['status' => $k] : []) }}" @if($aktivan) aria-current="page" @endif
     class="inline-flex items-center gap-1.5 h-8 px-space-md rounded font-label-md text-label-md transition-colors {{ $aktivan ? 'bg-surface-container-lowest text-on-surface font-semibold shadow-sm' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container' }}">
    {{ $naziv }}
    <span class="cip {{ $k === 'novo' && $broj ? 'bg-emerald-50 text-emerald-800' : 'bg-surface-container text-on-surface-variant' }}">{{ $broj }}</span>
  </a>
  @endforeach
</nav>

<section class="kartica overflow-hidden">
  @if($upiti->isEmpty())
    <x-prazno ikonica="forum" :naslov="$status ? 'Nema upita u ovoj grupi' : 'Još nema upita'" tekst="Kad kupac na Temelju pošalje upit za vaš stan, pojaviće se ovde (i u meniju kao zeleni broj). Upit sadrži ime, email i telefon kupca." >
      <a href="{{ route('oglasi.index') }}" class="dugme-sekundarno">Oglasi na Temelju</a>
    </x-prazno>
  @else
  <div class="overflow-x-auto">
    <table class="tabela">
      <thead>
        <tr><th>Kupac</th><th>Stan</th><th>Poruka</th><th>Primljeno</th><th>Status</th><th class="w-10"></th></tr>
      </thead>
      <tbody>
        @foreach($upiti as $u)
        @php $novo = $u->status === 'novo'; @endphp
        <tr data-href="{{ route('upiti.show', $u) }}" class="{{ $novo ? 'bg-emerald-50/40' : '' }}">
          <td>
            <a href="{{ route('upiti.show', $u) }}" class="{{ $novo ? 'font-semibold' : 'font-medium' }} hover:underline underline-offset-2">{{ $u->ime }}</a>
            <div class="font-body-sm text-body-sm text-on-surface-variant">{{ $u->telefon ?: $u->email }}</div>
          </td>
          <td class="whitespace-nowrap">
            @if($u->unit)
              Stan {{ $u->unit->oznaka }}
              <div class="font-body-sm text-body-sm text-on-surface-variant">{{ $u->unit->building->project->naziv ?? '' }}</div>
            @else
              <span class="text-on-surface-variant">—</span>
            @endif
          </td>
          <td class="max-w-[360px]"><span class="line-clamp-2 text-on-surface-variant">{{ $u->poruka }}</span></td>
          <td class="whitespace-nowrap font-mono-num text-on-surface-variant">{{ $u->primljeno_at->format('d.m.Y. H:i') }}</td>
          <td>
            @if($novo)
              <span class="inline-flex items-center gap-1.5 h-[22px] px-2 rounded font-label-sm text-label-sm bg-emerald-50 text-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Novo — javite se</span>
            @else
              <span class="inline-flex items-center gap-1.5 h-[22px] px-2 rounded font-label-sm text-label-sm {{ $u->status === 'u_kontaktu' ? 'bg-secondary-fixed text-on-secondary-fixed-variant' : 'bg-surface-container-high text-on-surface-variant' }}">{{ Upit::STATUSI[$u->status] ?? $u->status }}</span>
            @endif
          </td>
          <td class="text-right text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">chevron_right</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>
@endsection
