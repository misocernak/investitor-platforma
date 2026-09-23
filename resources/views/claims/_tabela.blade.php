{{-- Tabela reklamacija (ekran Reklamacije, dosije projekta). Klik na red otvara detalj. --}}
<div class="overflow-x-auto">
  <table class="tabela">
    <thead>
      <tr>
        <th>Stan</th>
        <th>Tip problema</th>
        <th>Prijavljeno</th>
        <th>Odgovorni</th>
        <th>Rok</th>
        <th>Status</th>
        <th class="w-10"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($reklamacije as $rek)
      @php $rok = \App\Support\Prikaz::rok($rek); @endphp
      <tr data-href="{{ route('claims.show', $rek) }}">
        <td>
          <a href="{{ route('claims.show', $rek) }}" class="font-semibold hover:underline underline-offset-2">{{ $rek->unit->oznaka ?? '—' }}</a>
          <div class="font-body-sm text-body-sm text-on-surface-variant">{{ $rek->unit->building->naziv ?? '' }}</div>
        </td>
        <td>
          <span class="inline-flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-on-surface-variant">{{ \App\Support\Prikaz::ikonaProblema($rek->tip_problema) }}</span>
            {{ $rek->tip_problema === 'Drugo' && $rek->tip_problema_drugo ? $rek->tip_problema_drugo : \App\Support\Prikaz::label($rek->tip_problema) }}
          </span>
        </td>
        <td class="font-mono-num text-on-surface-variant whitespace-nowrap">{{ $rek->datum_prijave?->format('d.m.Y.') }}</td>
        <td>
          @if($rek->odgovorni){{ $rek->odgovorni->ime_prezime }}@else<span class="text-on-surface-variant italic">Nedodeljeno</span>@endif
        </td>
        <td class="whitespace-nowrap">
          @if($rek->rok_resavanja)
            <div class="font-mono-num">{{ $rek->rok_resavanja->format('d.m.Y.') }}</div>
            @if($rok)<div class="font-body-sm text-body-sm font-medium {{ $rok[1] }}">{{ $rok[0] }}</div>@endif
          @else
            <span class="text-on-surface-variant">—</span>
          @endif
        </td>
        <td><x-status :v="$rek->status" /></td>
        <td class="text-right text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">chevron_right</span></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
