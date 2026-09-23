@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md">
    <div>
      <div class="flex items-center gap-space-sm">
        <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-medium">Post-prodajni operativni registar</span>
        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface font-label-xs text-label-xs font-semibold">Interni unos</span>
      </div>
      <h1 class="font-headline-lg text-headline-lg tracking-tight text-on-surface font-bold">Evidencija reklamacija i garancija</h1>
      <p class="font-body-sm text-body-sm text-on-surface-variant">Operativni nadzor garantnog roka. Ručna trijaža predmeta, verifikacija izvođača i kontrola rokova sanacije.</p>
    </div>
    <button data-modal-open="modal-new-claim" class="h-9 px-space-md bg-primary-container hover:bg-primary text-on-primary font-body-sm text-body-sm font-semibold rounded flex items-center gap-space-xs transition-colors shadow-sm self-start">
      <span class="material-symbols-outlined text-[18px]">add</span>+ Nova reklamacija
    </button>
  </div>

  <!-- Metrike -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-space-sm">
    <div class="p-space-md rounded bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Prijavljene</span><span class="font-headline-lg text-headline-lg font-bold text-on-surface">{{ $metrike['prijavljene'] }}</span></div>
    <div class="p-space-md rounded bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">U obradi / Dodeljene</span><span class="font-headline-lg text-headline-lg font-bold text-on-surface">{{ $metrike['u_obradi'] }}</span></div>
    <div class="p-space-md rounded bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-error font-medium">Prekoračen rok</span><span class="font-headline-lg text-headline-lg font-bold text-error">{{ $metrike['prekoracen_rok'] }}</span></div>
    <div class="p-space-md rounded bg-surface-container-lowest shadow-sm flex flex-col gap-1"><span class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Rešene (mesec)</span><span class="font-headline-lg text-headline-lg font-bold text-on-surface">{{ $metrike['resene_mesec'] }}</span></div>
  </div>

  <!-- Filteri -->
  <form method="GET" action="{{ route('claims.index') }}" class="p-space-md rounded bg-surface-container-lowest shadow-sm flex flex-wrap items-end gap-space-md">
    <div class="flex flex-col gap-1 min-w-[180px]">
      <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Objekat / Zgrada</label>
      <select name="zgrada" class="h-9 pl-space-sm pr-8 bg-surface-container-low text-on-surface font-body-sm text-body-sm rounded appearance-none">
        <option value="">Sve zgrade</option>
        @foreach($zgrade as $z)<option value="{{ $z->id }}" {{ request('zgrada') == $z->id ? 'selected' : '' }}>{{ $z->naziv }}</option>@endforeach
      </select>
    </div>
    <div class="flex flex-col gap-1 min-w-[160px]">
      <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Status predmeta</label>
      <select name="status" class="h-9 pl-space-sm pr-8 bg-surface-container-low text-on-surface font-body-sm text-body-sm rounded appearance-none">
        <option value="">Svi statusi</option>
        @foreach($statusi as $st)<option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ \App\Support\Prikaz::label($st) }}</option>@endforeach
      </select>
    </div>
    <div class="flex flex-col gap-1 min-w-[180px]">
      <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Tip problema</label>
      <select name="tip" class="h-9 pl-space-sm pr-8 bg-surface-container-low text-on-surface font-body-sm text-body-sm rounded appearance-none">
        <option value="">Svi tipovi</option>
        @foreach($tipovi as $tp)<option value="{{ $tp }}" {{ request('tip') === $tp ? 'selected' : '' }}>{{ \App\Support\Prikaz::label($tp) }}</option>@endforeach
      </select>
    </div>
    <div class="flex flex-col gap-1 min-w-[200px] flex-1">
      <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Pretraga (stan / opis)</label>
      <input name="q" value="{{ request('q') }}" placeholder="npr. Stan 03 ili curenje..." class="h-9 px-space-sm bg-surface-container-low text-on-surface rounded"/>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="h-9 px-space-md bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-body-sm font-semibold rounded transition-colors">Filtriraj</button>
      <a href="{{ route('claims.index') }}" class="h-9 px-space-md text-on-surface-variant hover:text-on-surface font-label-md flex items-center gap-1">Reset</a>
    </div>
  </form>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
    <!-- Tabela -->
    <div class="lg:col-span-7 bg-surface-container-lowest rounded shadow-sm overflow-hidden flex flex-col">
      <div class="px-space-md py-space-sm bg-surface-container-low flex items-center justify-between">
        <span class="font-label-sm text-label-sm uppercase font-semibold text-on-surface">Tabela reklamacija</span>
        <span class="font-label-xs text-label-xs text-on-surface-variant font-mono">Prikazano {{ $reklamacije->count() }} od {{ $reklamacije->count() }}</span>
      </div>
      <div class="w-full overflow-x-auto">
        <table class="w-full text-left font-body-sm text-body-sm">
          <thead>
            <tr class="bg-surface-container text-on-surface-variant font-label-xs text-label-xs uppercase tracking-wider">
              <th class="py-2.5 px-space-md font-semibold">Stan</th>
              <th class="py-2.5 px-space-md font-semibold">Tip problema</th>
              <th class="py-2.5 px-space-md font-semibold">Datum prijave</th>
              <th class="py-2.5 px-space-md font-semibold">Rok</th>
              <th class="py-2.5 px-space-md font-semibold">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reklamacije as $rek)
            @php $aktivna = $izabrana && $izabrana->id === $rek->id; @endphp
            <tr class="cursor-pointer transition-colors {{ $aktivna ? 'bg-surface-container-high font-medium' : 'hover:bg-surface-container-low' }}" onclick="window.location='{{ route('claims.index', array_merge(request()->only(['zgrada','status','tip','q']), ['reklamacija' => $rek->id])) }}'">
              <td class="py-2.5 px-space-md text-on-surface font-semibold">{{ $rek->unit->oznaka ?? '—' }}<div class="font-label-xs text-label-xs text-on-surface-variant font-normal">{{ $rek->unit->building->naziv ?? '' }}</div></td>
              <td class="py-2.5 px-space-md"><span class="inline-flex items-center px-1.5 py-0.5 rounded bg-surface-container text-on-surface font-label-xs text-label-xs font-medium">{{ \App\Support\Prikaz::label($rek->tip_problema) }}</span></td>
              <td class="py-2.5 px-space-md font-label-sm font-mono text-on-surface-variant">{{ $rek->datum_prijave?->format('d.m.Y.') }}</td>
              <td class="py-2.5 px-space-md font-label-sm font-mono">
                @if($rek->rok_resavanja)
                  @if($rek->kasni_dana > 0)<span class="text-error font-bold">{{ $rek->rok_resavanja->format('d.m.Y.') }} (kasni {{ $rek->kasni_dana }} d)</span>
                  @else<span class="text-on-surface">{{ $rek->rok_resavanja->format('d.m.Y.') }}</span>@endif
                @else<span class="text-on-surface-variant">—</span>@endif
              </td>
              <td class="py-2.5 px-space-md">
                <x-status :v="$rek->status" />
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-6 px-space-md text-center text-on-surface-variant">Nema reklamacija za izabrane filtere.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Detaljni prikaz -->
    <div class="lg:col-span-5 bg-surface-container-lowest rounded shadow-sm flex flex-col overflow-hidden">
      @if($izabrana)
      <div class="p-space-md bg-surface-container-low flex items-start justify-between">
        <div class="flex flex-col gap-0.5">
          <div class="flex items-center gap-space-xs">
            <span class="font-label-xs text-label-xs uppercase font-mono text-on-surface-variant">Dosije stavke #</span>
            <span class="font-label-xs text-label-xs font-mono font-bold text-on-surface">REC-{{ $izabrana->id }}</span>
            <x-status :v="$izabrana->status" class="ml-2" />
          </div>
          <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">{{ $izabrana->unit->oznaka ?? '—' }} — {{ \App\Support\Prikaz::label($izabrana->tip_problema) }}</h2>
          <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $izabrana->unit->building->naziv ?? '' }}{{ $izabrana->customer ? ' • Kupac: '.$izabrana->customer->ime_prezime : '' }}</span>
        </div>
      </div>
      <div class="p-space-md flex flex-col gap-space-md">
        <div class="flex flex-col gap-1">
          <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold">Opis problema sa terena</span>
          <div class="p-space-sm rounded bg-surface-container text-on-surface font-body-sm text-body-sm leading-relaxed">{{ $izabrana->opis }}</div>
        </div>

        <div class="p-space-sm rounded bg-surface-container-low flex flex-col gap-space-sm">
          <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface font-bold">Ručna obrada i dodela (Nadzor/Admin)</span>
          <form method="POST" action="{{ route('claims.update', $izabrana) }}" class="grid grid-cols-1 md:grid-cols-2 gap-space-sm">
            @csrf @method('PATCH')
            <div class="flex flex-col gap-1">
              <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Status reklamacije</label>
              <select name="status" class="h-9 pl-space-sm pr-7 bg-surface-container-lowest text-on-surface font-body-sm font-semibold rounded appearance-none shadow-sm">
                @foreach($statusi as $st)<option value="{{ $st }}" {{ $izabrana->status === $st ? 'selected' : '' }}>{{ \App\Support\Prikaz::label($st) }}</option>@endforeach
              </select>
            </div>
            @if(!$currentUser->jeNadzor())
            <div class="flex flex-col gap-1">
              <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Odgovorni izvođač (ručna dodela)</label>
              <select name="odgovorni_id" class="h-9 pl-space-sm pr-7 bg-surface-container-lowest text-on-surface font-body-sm rounded appearance-none shadow-sm">
                <option value="">-- Nedodeljeno --</option>
                @foreach($nadzorUsers as $n)<option value="{{ $n->id }}" {{ $izabrana->odgovorni_id === $n->id ? 'selected' : '' }}>{{ $n->ime_prezime }}</option>@endforeach
              </select>
            </div>
            <div class="flex flex-col gap-1">
              <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-medium">Rok rešavanja (ručni unos)</label>
              <input name="rok_resavanja" type="date" value="{{ $izabrana->rok_resavanja?->format('Y-m-d') }}" class="h-9 px-space-sm bg-surface-container-lowest text-on-surface rounded shadow-sm"/>
            </div>
            @endif
            <div class="flex items-end justify-between pb-1 gap-2">
              @if($izabrana->rok_resavanja && $izabrana->kasni_dana > 0 && !in_array($izabrana->status, ['Resena','Odbijena']))
              <span class="font-label-xs text-label-xs text-error font-bold">Kasni {{ $izabrana->kasni_dana }} dana (rok prekoračen)</span>
              @else
              <span class="font-label-xs text-label-xs text-on-surface-variant">Rok: {{ $izabrana->rok_resavanja?->format('d.m.Y.') ?: 'nije definisan' }}</span>
              @endif
              <button class="h-8 px-space-md bg-primary-container hover:bg-primary text-on-primary font-body-sm font-semibold rounded transition-colors shadow-sm">Ažuriraj</button>
            </div>
          </form>
        </div>

        <!-- Hronoloski log (PRD 9.5) -->
        <div class="flex flex-col gap-space-xs">
          <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold">Hronološki log i interne zabeleške</span>
          <div class="flex flex-col gap-space-xs max-h-48 overflow-y-auto pr-1">
            @foreach($izabrana->notes as $beleska)
            <div class="p-space-sm rounded bg-surface-container flex flex-col gap-0.5">
              <div class="flex items-center justify-between">
                <span class="font-label-xs text-label-xs font-semibold text-on-surface">{{ $beleska->user->ime_prezime ?? 'Sistem' }}</span>
                <span class="font-label-xs text-label-xs text-on-surface-variant font-mono">{{ $beleska->created_at->format('d.m.Y. H:i') }}</span>
              </div>
              <p class="font-body-sm text-body-sm text-on-surface">{{ $beleska->tekst }}</p>
            </div>
            @endforeach
          </div>
          <form method="POST" action="{{ route('claims.notes', $izabrana) }}" class="flex gap-space-xs mt-1">
            @csrf
            <input name="tekst" required placeholder="Unesite internu belešku nadzora..." class="flex-1 h-8 px-space-sm bg-surface-container-lowest text-on-surface rounded shadow-sm"/>
            <button class="h-8 px-space-sm bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-body-sm font-semibold rounded flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">send</span></button>
          </form>
        </div>
      </div>
      @else
      <div class="p-space-lg text-center text-on-surface-variant font-body-sm">Izaberite reklamaciju iz tabele da biste videli detalje.</div>
      @endif
    </div>
  </div>
</div>

<!-- MODAL: Nova reklamacija (PRD 6.7 / 10.3) -->
<div class="fixed inset-0 z-50 flex items-center justify-center bg-primary/60 backdrop-blur-sm hidden" id="modal-new-claim">
  <div class="w-full max-w-2xl bg-surface-container-lowest rounded shadow-xl overflow-hidden flex flex-col mx-4 my-8 max-h-[90vh]">
    <div class="px-space-lg py-space-md bg-primary-container text-on-primary flex items-center justify-between">
      <div class="flex flex-col">
        <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-primary-container font-semibold">Interni unos nadzora / operatera</span>
        <h3 class="font-headline-md text-headline-md font-bold text-on-primary">Evidentiranje nove reklamacije kupca</h3>
      </div>
      <button class="text-on-primary-container hover:text-on-primary p-1 rounded" data-modal-close="modal-new-claim"><span class="material-symbols-outlined text-[24px]">close</span></button>
    </div>
    <form class="p-space-lg flex flex-col gap-space-md overflow-y-auto" method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Stan / Jedinica *</label>
          <select name="stan_id" required class="h-9 pl-space-sm pr-8 bg-surface-container-low text-on-surface font-body-sm rounded appearance-none">
            <option value="" disabled selected>Izaberite stan...</option>
            @foreach($stanovi as $stan)<option value="{{ $stan->id }}">{{ $stan->oznaka }} — {{ $stan->building->naziv ?? '' }}</option>@endforeach
          </select>
        </div>
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Tip problema *</label>
          <select name="tip_problema" required class="h-9 pl-space-sm pr-8 bg-surface-container-low text-on-surface font-body-sm rounded appearance-none" onchange="document.getElementById('drugoPolje').classList.toggle('hidden', this.value !== 'Drugo')">
            <option value="" disabled selected>Izaberite klasifikaciju...</option>
            @foreach($tipovi as $tp)<option value="{{ $tp }}">{{ \App\Support\Prikaz::label($tp) }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="flex flex-col gap-1 hidden" id="drugoPolje">
        <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Opišite tip problema *</label>
        <input name="tip_problema_drugo" class="h-9 px-space-sm bg-surface-container-low rounded"/>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Datum prijave (ručni unos) *</label>
          <input name="datum_prijave" type="date" value="{{ now()->format('Y-m-d') }}" required class="h-9 px-space-sm bg-surface-container-low rounded"/>
        </div>
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Prilozi (fotografije, opciono)</label>
          <input name="prilog[]" type="file" multiple accept="image/*" class="h-9 px-space-sm bg-surface-container-low rounded text-sm pt-1.5"/>
        </div>
      </div>
      <div class="flex flex-col gap-1">
        <label class="font-label-xs text-label-xs uppercase text-on-surface-variant font-semibold">Detaljan opis kvara i lokacija unutar stana *</label>
        <textarea name="opis" required rows="3" placeholder="Navesti tačnu poziciju i zapažanja vlasnika stana..." class="w-full p-space-sm bg-surface-container-low rounded"></textarea>
      </div>
      <div class="flex items-center justify-end gap-space-sm pt-space-md">
        <button type="button" data-modal-close="modal-new-claim" class="h-9 px-space-md bg-surface-container-high text-on-surface font-body-sm font-semibold rounded">Otkaži</button>
        <button type="submit" class="h-9 px-space-lg bg-primary-container hover:bg-primary text-on-primary font-body-sm font-semibold rounded flex items-center gap-space-xs"><span class="material-symbols-outlined text-[18px]">check</span>Sačuvaj i evidentiraj reklamaciju</button>
      </div>
    </form>
  </div>
</div>
@endsection
