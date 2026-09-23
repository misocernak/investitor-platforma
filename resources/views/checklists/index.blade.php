@extends('layouts.app')
@section('content')
@php
  $ikone = ['Upotrebna_dozvola' => 'verified', 'Uknjizba' => 'account_balance', 'Paket_za_banku' => 'real_estate_agent'];
@endphp
<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">
  <div>
    <div class="flex items-center gap-space-sm text-on-surface-variant font-label-xs text-label-xs uppercase tracking-wider">
      <span>Dosije objekata</span><span>/</span><span class="text-secondary font-semibold">Tehničke checkliste</span>
    </div>
    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight mt-0.5">Checkliste procesa</h1>
    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Napredak prikupljanja dokumentacije za upotrebnu dozvolu, uknjižbu i paket za banku — po zgradi.</p>
  </div>

  @forelse($zgrade as $zgrada)
  <div class="bg-surface-container-lowest rounded-xl shadow-sm flex flex-col">
    <div class="p-space-md flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
      <div class="flex items-center gap-space-sm min-w-0">
        <span class="w-9 h-9 shrink-0 rounded-lg bg-surface-container flex items-center justify-center text-secondary"><span class="material-symbols-outlined text-[20px]">apartment</span></span>
        <div class="min-w-0">
          <h2 class="font-headline-sm text-headline-sm text-on-surface font-semibold truncate">{{ $zgrada->naziv }}</h2>
          <p class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $zgrada->project->naziv ?? '—' }}</p>
        </div>
      </div>
      <x-status :v="$zgrada->status" class="self-start sm:self-auto" />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-space-sm p-space-sm pt-0">
      @foreach($tipovi as $tip)
      @php
        $cl = $zgrada->checklists->firstWhere('tip_checkliste', $tip);
        $ukupno = $cl->ukupno_stavki ?? 0;
        $reseno = $cl->reseno_stavki ?? 0;
        $proc = $ukupno > 0 ? round($reseno / $ukupno * 100) : 0;
        $gotovo = $ukupno > 0 && $reseno === $ukupno;
      @endphp
      <a href="{{ route('checklists.show', ['building' => $zgrada, 'tip' => $tip]) }}" class="group rounded-lg bg-surface-container-low hover:bg-surface-container transition-colors p-space-md flex flex-col gap-space-sm">
        <div class="flex items-center justify-between gap-space-sm">
          <span class="flex items-center gap-space-xs font-body-md text-body-md font-semibold text-on-surface">
            <span class="material-symbols-outlined text-[18px] {{ $gotovo ? 'text-emerald-600' : 'text-secondary' }}">{{ $gotovo ? 'check_circle' : $ikone[$tip] }}</span>
            {{ \App\Support\Prikaz::label($tip) }}
          </span>
          <span class="font-label-sm text-label-sm {{ $gotovo ? 'text-emerald-700' : 'text-on-surface-variant' }}">{{ $reseno }}/{{ $ukupno }}</span>
        </div>
        <div class="w-full h-1.5 rounded-full bg-surface-container-high overflow-hidden">
          <div class="h-full rounded-full {{ $gotovo ? 'bg-emerald-500' : 'bg-secondary' }}" style="width: {{ $proc }}%"></div>
        </div>
        <span class="font-label-xs text-label-xs {{ $gotovo ? 'text-emerald-700' : 'text-on-surface-variant' }}">{{ $gotovo ? 'Spremno za predaju' : $proc.'% · otvori checklistu' }}</span>
      </a>
      @endforeach
    </div>
  </div>
  @empty
  <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-xl text-center text-on-surface-variant font-body-md">Nema zgrada. Checkliste se prave automatski kada projektu dodate zgradu.</div>
  @endforelse
</div>
@endsection
