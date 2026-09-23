@use('App\Support\Prikaz')
@extends('layouts.app')
@section('naslov', 'Checkliste')

@section('content')
<x-zaglavlje naslov="Checkliste" opis="Dokumenta za upotrebnu dozvolu, uknjižbu i paket za banku — po zgradi. Klik otvara checklistu." />

<section class="kartica overflow-hidden">
  @if($zgrade->isEmpty())
    <x-prazno ikonica="fact_check" naslov="Nema zgrada" tekst="Checkliste se prave automatski kada projektu dodate zgradu.">
      <a href="{{ route('projects.index') }}" class="dugme-primarno">Idi na projekte</a>
    </x-prazno>
  @else
  <div class="overflow-x-auto">
    <table class="tabela">
      <thead>
        <tr>
          <th>Zgrada</th>
          <th>Status</th>
          @foreach($tipovi as $tip)<th class="min-w-[200px]">{{ Prikaz::label($tip) }}</th>@endforeach
        </tr>
      </thead>
      <tbody>
        @foreach($zgrade as $zgrada)
        <tr>
          <td>
            <a href="{{ route('projects.show', ['project' => $zgrada->projekat_id, 'zgrada' => $zgrada->id, 'tab' => 'checkliste']) }}" class="font-semibold hover:underline underline-offset-2">{{ $zgrada->naziv }}</a>
            <div class="font-body-sm text-body-sm text-on-surface-variant">{{ $zgrada->project->naziv ?? '—' }}</div>
          </td>
          <td><x-status :v="$zgrada->status" /></td>
          @foreach($tipovi as $tip)
          @php
            $cl = $zgrada->checklists->firstWhere('tip_checkliste', $tip);
            $ukupno = $cl->ukupno_stavki ?? 0;
            $reseno = $cl->reseno_stavki ?? 0;
            $p = $ukupno > 0 ? round($reseno / $ukupno * 100) : 0;
            $gotovo = $ukupno > 0 && $reseno === $ukupno;
          @endphp
          <td>
            <a href="{{ route('checklists.show', ['building' => $zgrada, 'tip' => $tip]) }}" class="group flex flex-col gap-1.5 py-0.5" title="Otvori checklistu">
              <span class="flex items-center justify-between gap-space-sm">
                <span class="font-label-md text-label-md group-hover:underline underline-offset-2 {{ $gotovo ? 'text-emerald-700' : '' }}">{{ $gotovo ? 'Kompletno' : ($ukupno - $reseno).' nedostaje' }}</span>
                <span class="font-mono-num text-body-sm text-on-surface-variant">{{ $reseno }}/{{ $ukupno }}</span>
              </span>
              <span class="h-1.5 rounded-full bg-surface-container overflow-hidden"><span class="block h-full rounded-full {{ $gotovo ? 'bg-emerald-500' : 'bg-primary' }}" style="width: {{ $p }}%"></span></span>
            </a>
          </td>
          @endforeach
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>
@endsection
