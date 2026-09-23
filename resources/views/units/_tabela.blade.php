{{-- Tabela stanova (ekran Stanovi i tab "Stanovi" u dosijeu). Klik na red otvara dosije stana. --}}
<div class="overflow-x-auto">
  <table class="tabela">
    <thead>
      <tr>
        <th>Oznaka</th>
        <th>Sprat</th>
        <th class="text-right">Kvadratura</th>
        <th class="text-right">Sobe</th>
        <th>Kupac</th>
        <th>Status</th>
        <th class="text-center">Otvorene reklamacije</th>
        <th class="w-10"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($stanovi as $stan)
      @php $br = $stan->otvorene_reklamacije_count ?? 0; @endphp
      <tr data-href="{{ route('units.show', $stan) }}">
        <td><a href="{{ route('units.show', $stan) }}" class="font-semibold hover:underline underline-offset-2">{{ $stan->oznaka }}</a></td>
        <td class="text-on-surface-variant">{{ $stan->sprat ?: '—' }}</td>
        <td class="text-right font-mono-num whitespace-nowrap">{{ $stan->kvadratura ? number_format($stan->kvadratura, 2, ',', '.').' m²' : '—' }}</td>
        <td class="text-right font-mono-num">{{ $stan->broj_soba ?? '—' }}</td>
        <td>
          @if($stan->customer){{ $stan->customer->ime_prezime }}@else<span class="text-on-surface-variant">—</span>@endif
        </td>
        <td><x-status :v="$stan->status" /></td>
        <td class="text-center">
          @if($br > 0)<span class="cip bg-error-container text-on-error-container">{{ $br }}</span>@else<span class="text-on-surface-variant">0</span>@endif
        </td>
        <td class="text-right text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">chevron_right</span></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
