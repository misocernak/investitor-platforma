{{-- Tabela dokumenata. $prikaziProjekat = true u opštoj Dokumentaciji (kolona "Vezano za" sa projektom i zgradom). --}}
@php $prikaziProjekat = $prikaziProjekat ?? false; @endphp
<div class="overflow-x-auto">
  <table class="tabela">
    <thead>
      <tr>
        <th>Naziv dokumenta</th>
        <th>Tip</th>
        <th>Datum izdavanja</th>
        <th>Izdavalac</th>
        <th>Vezano za</th>
        <th class="text-center">Verzija</th>
        <th class="text-right">Akcije</th>
      </tr>
    </thead>
    <tbody>
      @foreach($dokumenti as $dok)
      @php $imaFajl = $dok->imaFajl(); @endphp
      <tr class="{{ $dok->aktivna_verzija ? '' : 'opacity-60' }}">
        <td>
          <div class="flex items-center gap-space-sm min-w-[200px]">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">description</span>
            @if($imaFajl)
              <a href="{{ route('documents.download', $dok) }}" class="font-medium hover:underline underline-offset-2">{{ $dok->naziv }}</a>
            @else
              <span class="font-medium">{{ $dok->naziv }}</span>
            @endif
          </div>
        </td>
        <td><span class="cip bg-surface-container text-on-surface">{{ \App\Support\Prikaz::label($dok->tip) }}</span></td>
        <td class="font-mono-num whitespace-nowrap">{{ $dok->datum_izdavanja?->format('d.m.Y.') ?: '—' }}</td>
        <td class="text-on-surface-variant">{{ $dok->izdavalac ?: '—' }}</td>
        <td class="whitespace-nowrap">
          <span class="inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px] text-on-surface-variant">{{ $dok->stan_id ? 'door_front' : ($dok->zgrada_id ? 'domain' : 'apartment') }}</span>
            @if($dok->stan_id)
              <a href="{{ route('units.show', $dok->stan_id) }}" class="hover:underline underline-offset-2">Stan {{ $dok->unit->oznaka ?? '' }}</a>
            @elseif($dok->zgrada_id)
              {{ $prikaziProjekat ? ($dok->building->naziv ?? 'Zgrada') : 'Cela zgrada' }}
            @else
              Projekat
            @endif
          </span>
          @if($prikaziProjekat && ($dok->project ?? $dok->building?->project))
            <div class="font-body-sm text-body-sm text-on-surface-variant">{{ ($dok->project ?? $dok->building->project)->naziv }}</div>
          @endif
        </td>
        <td class="text-center">
          <span class="cip {{ $dok->verzija > 1 ? 'bg-secondary-fixed text-on-secondary-fixed-variant' : 'bg-surface-container text-on-surface' }}">v{{ $dok->verzija }}</span>
          @unless($dok->aktivna_verzija)<div class="font-body-sm text-body-sm text-on-surface-variant">arhiva</div>@endunless
        </td>
        <td class="text-right whitespace-nowrap">
          <div class="inline-flex items-center gap-1">
            @if($imaFajl)
            <a href="{{ route('documents.download', $dok) }}" class="dugme-sekundarno dugme-malo" title="Preuzmi fajl"><span class="material-symbols-outlined text-[16px]">download</span>Preuzmi</a>
            @endif
            @if($dok->aktivna_verzija && $currentTenant?->povezanSaTemeljem())
            <form method="POST" action="{{ route('documents.kupcu', $dok) }}">
              @csrf
              <button class="dugme-tiho dugme-malo {{ $dok->vidljivo_kupcu ? 'text-emerald-700' : '' }}" title="{{ $dok->vidljivo_kupcu ? 'Kupac vidi dokument na Temelju — klik da sakrijete' : 'Kupac ne vidi dokument — klik da ga prikažete na Temelju' }}"><span class="material-symbols-outlined text-[16px]">{{ $dok->vidljivo_kupcu ? 'visibility' : 'visibility_off' }}</span></button>
            </form>
            @endif
            @if($dok->aktivna_verzija && $currentUser->mozeAdministrirati())
            <form method="POST" action="{{ route('documents.destroy', $dok) }}" data-potvrdi="Arhivirati ovu verziju dokumenta? Ostaje sačuvana u arhivi.">
              @csrf @method('DELETE')
              <button class="dugme-tiho dugme-malo" title="Arhiviraj verziju"><span class="material-symbols-outlined text-[16px]">archive</span></button>
            </form>
            @endif
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
