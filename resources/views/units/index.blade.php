@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-space-md">
    <div>
      <div class="flex items-center gap-space-sm text-on-surface-variant font-label-xs text-label-xs uppercase tracking-wider">
        <span>Stambeno-poslovni fond</span><span class="material-symbols-outlined text-[14px]">chevron_right</span><span class="text-on-surface font-semibold">Evidencija jedinica</span>
      </div>
      <div class="flex flex-wrap items-center gap-space-md mt-1">
        <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Stanovi i jedinice</h1>
        @if($zgrade->count() > 0)
        <form method="GET" class="relative inline-flex items-center">
          <select name="zgrada" onchange="this.form.submit()" class="appearance-none bg-surface-container-low hover:bg-surface-container text-on-surface font-headline-sm text-headline-sm py-1.5 pl-space-md pr-8 rounded-lg cursor-pointer transition-colors focus:outline-none focus:ring-1 focus:ring-secondary">
            @foreach($zgrade as $z)
            <option value="{{ $z->id }}" {{ $zgrada && $zgrada->id === $z->id ? 'selected' : '' }}>{{ $z->naziv }} ({{ $z->project->naziv ?? '' }})</option>
            @endforeach
          </select>
          <span class="material-symbols-outlined absolute right-2 pointer-events-none text-on-surface-variant text-[18px]">expand_more</span>
        </form>
        @endif
      </div>
    </div>
  </div>

  @if($zgrada)
  <!-- Statistika jedinica -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-space-sm">
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-on-surface-variant">Ukupno jedinica</span><span class="font-label-md text-label-md font-bold text-on-surface">{{ $statistika['ukupno'] }}</span></div>
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-on-surface-variant">Za prodaju</span><span class="font-label-md text-label-md font-bold text-secondary">{{ $statistika['za_prodaju'] }}</span></div>
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-on-surface-variant">Rezervisano</span><span class="font-label-md text-label-md font-bold text-on-surface">{{ $statistika['rezervisan'] }}</span></div>
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-on-surface-variant">Uknjiženje / Garancija</span><span class="font-label-md text-label-md font-bold text-tertiary-container">{{ $statistika['uknjizenje_garancija'] }}</span></div>
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-on-surface-variant">Istekla garancija</span><span class="font-label-md text-label-md font-bold text-on-surface-variant">{{ $statistika['garancija_istekla'] }}</span></div>
    <div class="p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-error">Otvorene reklamacije</span><span class="font-label-md text-label-md font-bold text-error">{{ $statistika['otvorene_reklamacije'] }}</span></div>
  </div>

  <div class="flex flex-col lg:flex-row gap-space-lg items-start relative">
    <!-- Tabela stanova -->
    <div class="w-full flex-1 flex flex-col gap-space-sm bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
      <div class="p-space-md flex items-center justify-between gap-space-sm">
        <p class="font-body-sm text-body-sm text-on-surface-variant">Klik na red otvara detaljni dosije jedinice</p>
        <button data-modal-open="modal-add-unit" class="h-8 px-space-md bg-primary text-on-primary font-body-sm rounded-lg shadow-sm font-medium">+ Dodaj stan / jedinicu</button>
      </div>
      <div class="w-full overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low text-on-surface-variant font-label-xs text-label-xs uppercase tracking-wider">
              <th class="py-2.5 px-space-md font-medium">Oznaka</th>
              <th class="py-2.5 px-space-md font-medium">Sprat</th>
              <th class="py-2.5 px-space-md font-medium text-right">Kvadratura</th>
              <th class="py-2.5 px-space-md font-medium">Kupac</th>
              <th class="py-2.5 px-space-md font-medium">Status jedinice</th>
              <th class="py-2.5 px-space-md font-medium text-center">Reklamacije</th>
            </tr>
          </thead>
          <tbody class="font-body-md text-body-md text-on-surface">
            @foreach($stanovi as $stan)
            <tr class="unit-row cursor-pointer hover:bg-surface-container transition-colors group {{ request('stan') == $stan->id ? 'bg-surface-container-high font-semibold' : '' }}" data-id="{{ $stan->id }}" onclick="otvoriStan({{ $stan->id }})">
              <td class="py-2.5 px-space-md"><span class="font-label-md text-label-md font-bold {{ request('stan') == $stan->id ? 'text-secondary' : 'text-on-surface group-hover:text-secondary' }}">{{ $stan->oznaka }}</span></td>
              <td class="py-2.5 px-space-md text-on-surface-variant font-body-sm">{{ $stan->sprat ?: '—' }}</td>
              <td class="py-2.5 px-space-md text-right font-label-md tabular-nums">{{ $stan->kvadratura ? number_format($stan->kvadratura, 2, ',', '.').' m²' : '—' }}</td>
              <td class="py-2.5 px-space-md"><div class="flex items-center gap-space-xs">@if($stan->customer)<span class="material-symbols-outlined text-secondary text-[16px]">account_circle</span><span class="font-body-sm font-semibold">{{ $stan->customer->ime_prezime }}</span>@else<span class="font-label-sm text-on-surface-variant">—</span>@endif</div></td>
              <td class="py-2.5 px-space-md"><span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full font-label-xs text-label-xs font-medium bg-surface-container-high text-on-surface"><span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>{{ $stan->status }}</span></td>
              <td class="py-2.5 px-space-md text-center">
                @php $br = $stan->otvoreneReklamacije()->count(); @endphp
                <span class="font-label-sm text-label-sm px-2 py-0.5 rounded {{ $br > 0 ? 'bg-error-container text-on-error-container font-bold' : 'bg-surface-container text-on-surface-variant' }}">{{ $br }}</span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="p-space-md flex items-center justify-between font-label-xs text-label-xs text-on-surface-variant">
        <span>Prikazano {{ $stanovi->count() }} od {{ $stanovi->count() }} stambenih jedinica</span>
        <span class="px-2 py-1 bg-surface-container-low rounded">Zgrada: {{ $zgrada->naziv }}</span>
      </div>
    </div>

    <!-- Side-over panel (PRD 9.3: 3 sekcije) -->
    <div class="w-full lg:w-96 flex flex-col bg-surface-container-lowest rounded-xl shadow-xl overflow-hidden sticky top-16" id="sidePanel">
      <div class="p-space-md bg-primary text-on-primary flex items-center justify-between">
        <div class="flex items-center gap-space-sm">
          <span class="material-symbols-outlined text-tertiary-fixed text-[20px]">apartment</span>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-headline-sm text-headline-sm font-bold" id="panelUnitTitle">—</span>
              <span class="font-label-xs text-label-xs px-2 py-0.5 rounded bg-secondary text-on-secondary" id="panelStatusBadge">—</span>
            </div>
            <div class="font-label-xs text-label-xs text-on-primary-container" id="panelUnitSubtitle">{{ $zgrada->naziv }}</div>
          </div>
        </div>
      </div>
      <div class="p-space-md flex flex-col gap-space-md max-h-[calc(100vh-14rem)] overflow-y-auto">
        <!-- Sekcija 1: Osnovni podaci -->
        <div class="flex flex-col gap-space-xs p-space-sm rounded-lg bg-surface-container-low">
          <div class="flex items-center justify-between pb-1"><span class="font-label-xs text-label-xs uppercase font-semibold text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">info</span>Sekcija 1: Osnovni podaci</span></div>
          <div class="grid grid-cols-2 gap-2 text-on-surface pt-1">
            <div><div class="font-label-xs text-label-xs text-on-surface-variant">Oznaka / Etaža</div><div class="font-body-sm font-semibold" id="panelMetaOznaka">—</div></div>
            <div><div class="font-label-xs text-label-xs text-on-surface-variant">Kvadratura / Sobe</div><div class="font-body-sm font-semibold" id="panelMetaKvadratura">—</div></div>
            <div><div class="font-label-xs text-label-xs text-on-surface-variant">Interna ugovorna cena</div><div class="font-label-sm font-bold text-secondary" id="panelMetaCena">—</div></div>
            <div><div class="font-label-xs text-label-xs text-on-surface-variant">Status</div><div class="font-label-sm font-medium" id="panelMetaStatus">—</div></div>
          </div>
          <div class="mt-2 pt-2 bg-surface-container-lowest p-space-sm rounded-lg flex flex-col gap-1">
            <div class="flex items-center justify-between"><span class="font-label-xs text-label-xs uppercase font-medium text-on-surface-variant">Podaci o kupcu</span></div>
            <div class="font-body-sm font-bold text-on-surface" id="panelCustomerName">—</div>
            <div class="flex items-center gap-2 text-on-surface-variant font-body-sm"><span class="material-symbols-outlined text-[14px]">mail</span><span id="panelCustomerEmail">—</span></div>
            <div class="flex items-center gap-2 text-on-surface-variant font-body-sm"><span class="material-symbols-outlined text-[14px]">call</span><span id="panelCustomerPhone">—</span></div>
          </div>
        </div>
        <!-- Sekcija 2: Dokumenti -->
        <div class="flex flex-col gap-space-xs p-space-sm rounded-lg bg-surface-container-low">
          <div class="flex items-center justify-between pb-1"><span class="font-label-xs text-label-xs uppercase font-semibold text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">folder_open</span>Sekcija 2: Dokumenti vezani za stan</span><span class="font-label-xs text-label-xs text-on-surface-variant" id="panelDocCount">0 fajlova</span></div>
          <div class="flex flex-col gap-2" id="panelDocs"></div>
        </div>
        <!-- Sekcija 3: Reklamacije -->
        <div class="flex flex-col gap-space-xs p-space-sm rounded-lg bg-surface-container-low">
          <div class="flex items-center justify-between pb-1"><span class="font-label-xs text-label-xs uppercase font-semibold text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">assignment_late</span>Sekcija 3: Reklamacije vezane za stan</span><span class="font-label-xs text-label-xs px-1.5 py-0.5 rounded bg-surface-container text-on-surface-variant font-bold" id="panelComplaintCount">0</span></div>
          <div class="flex flex-col gap-2" id="panelComplaints"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: + Dodaj jedinicu -->
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-primary/60 backdrop-blur-sm hidden" id="modal-add-unit">
    <div class="w-full max-w-lg bg-surface-container-lowest rounded-xl shadow-2xl p-space-lg flex flex-col gap-space-md mx-4 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between pb-space-sm">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary text-[22px]">add_home</span><h3 class="font-headline-md text-headline-md text-on-surface">Nova stambena jedinica</h3></div>
        <button class="p-1 rounded hover:bg-surface-container" data-modal-close="modal-add-unit"><span class="material-symbols-outlined text-[18px]">close</span></button>
      </div>
      <form class="flex flex-col gap-space-md" method="POST" action="{{ route('units.store', $zgrada) }}">
        @csrf
        <div class="grid grid-cols-2 gap-space-md">
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Oznaka stana *</label><input name="oznaka" required placeholder="npr. Stan 09" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Sprat / Etaža</label><input name="sprat" placeholder="npr. II sprat" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
        </div>
        <div class="grid grid-cols-2 gap-space-md">
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Kvadratura (m²)</label><input name="kvadratura" type="number" step="0.01" min="0" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
          <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Status jedinice *</label>
            <select name="status" required class="h-9 px-space-sm bg-surface-container-low rounded-lg">
              @foreach($statusiStana as $st)<option value="{{ $st }}">{{ $st }}</option>@endforeach
            </select>
          </div>
        </div>
        <div class="flex flex-col gap-1"><label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Kupac / Ugovarač (opciono)</label><input name="kupac_ime" class="h-9 px-space-sm bg-surface-container-low rounded-lg"/></div>
        <div class="flex items-center justify-end gap-space-sm pt-space-xs">
          <button type="button" data-modal-close="modal-add-unit" class="px-space-md py-1.5 rounded-lg bg-surface-container font-body-sm">Otkaži</button>
          <button type="submit" class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-body-sm font-semibold">Potvrdi unos jedinice</button>
        </div>
      </form>
    </div>
  </div>
  @else
  <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg text-center text-on-surface-variant">Nema zgrada u portfelju. Kreirajte projekat sa nadzorne table.</div>
  @endif
</div>
@endsection

@push('scripts')
<script>
// Dosije jedinice se puni sa servera (GET /stanovi/{id} - JSON)
function otvoriStan(id) {
  document.querySelectorAll('.unit-row').forEach(r => r.classList.remove('bg-surface-container-high', 'font-semibold'));
  const row = document.querySelector('.unit-row[data-id="' + id + '"]');
  if (row) row.classList.add('bg-surface-container-high', 'font-semibold');

  fetch('/stanovi/' + id, {headers: {'Accept': 'application/json'}})
    .then(r => r.json())
    .then(u => {
      document.getElementById('panelUnitTitle').textContent = u.oznaka;
      document.getElementById('panelStatusBadge').textContent = u.status;
      document.getElementById('panelUnitSubtitle').textContent = '{{ $zgrada->naziv ?? '' }} • ' + (u.sprat || '—') + ' • ' + (u.kvadratura ? Number(u.kvadratura).toFixed(2) + ' m²' : '—');
      document.getElementById('panelMetaOznaka').textContent = u.oznaka + ' (' + (u.sprat || '—') + ')';
      document.getElementById('panelMetaKvadratura').textContent = (u.kvadratura ? Number(u.kvadratura).toFixed(2) + ' m²' : '—') + (u.broj_soba ? ' (' + u.broj_soba + ' soba)' : '');
      document.getElementById('panelMetaCena').textContent = u.cena ? Number(u.cena).toLocaleString('de-DE') + ' €' : '—';
      document.getElementById('panelMetaStatus').textContent = u.status;
      document.getElementById('panelCustomerName').textContent = u.kupac || 'Nema evidentiranog kupca';
      document.getElementById('panelCustomerEmail').textContent = u.kupac_email || '—';
      document.getElementById('panelCustomerPhone').textContent = u.kupac_telefon || '—';

      document.getElementById('panelDocCount').textContent = u.dokumenti.length + ' fajlova';
      document.getElementById('panelDocs').innerHTML = u.dokumenti.length
        ? u.dokumenti.map(d => '<div class="flex items-center justify-between p-2 rounded bg-surface-container-lowest shadow-sm"><div class="flex items-center gap-2 overflow-hidden"><span class="material-symbols-outlined text-error text-[20px]">picture_as_pdf</span><div class="flex flex-col min-w-0"><span class="font-body-sm font-semibold truncate">' + d.naziv + '</span><span class="font-label-xs text-on-surface-variant">' + d.tip.replaceAll('_', ' ') + '</span></div></div>' + (d.ima_fajl ? '<a href="/dokumenti/' + d.id + '/preuzmi" class="p-1.5 rounded bg-surface-container hover:bg-surface text-on-surface"><span class="material-symbols-outlined text-[16px]">download</span></a>' : '') + '</div>').join('')
        : '<div class="p-space-md text-center bg-surface-container-lowest rounded text-on-surface-variant font-body-sm">Nema dokumenata za ovu jedinicu.</div>';

      document.getElementById('panelComplaintCount').textContent = u.otvorene_reklamacije + ' otvorenih';
      document.getElementById('panelComplaints').innerHTML = u.reklamacije.length
        ? u.reklamacije.map(c => '<div class="p-space-sm rounded bg-surface-container-lowest shadow-sm flex flex-col gap-1"><div class="flex items-center justify-between"><span class="font-label-sm font-bold text-on-surface">#' + c.id + ' ' + c.tip.replaceAll('_', ' ') + '</span><span class="font-label-xs px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface font-semibold">' + c.status.replaceAll('_', ' ') + '</span></div><div class="font-label-xs text-on-surface-variant">Prijavljeno: ' + c.datum + '</div></div>').join('')
        : '<div class="p-space-md text-center bg-surface-container-lowest rounded text-on-surface-variant font-body-sm">Nema prijavljenih reklamacija.</div>';
    });
}
@if(request('stan'))
document.addEventListener('DOMContentLoaded', () => otvoriStan({{ (int) request('stan') }}));
@endif
</script>
@endpush
