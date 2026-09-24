{{-- Prodajni status stana jednim klikom: Za prodaju / Rezervisan / Prodat. Očekuje $stan. --}}
@php
  $opcije = ['Za_prodaju' => ['Za prodaju', 'sell'], 'Rezervisan' => ['Rezervisan', 'bookmark'], 'Prodat_u_procesu_uknjizenja' => ['Prodat', 'handshake']];
  $prodat = in_array($stan->status, ['Prodat_u_procesu_uknjizenja', 'Prodat_u_garanciji', 'Garancija_istekla'], true);
@endphp
<div class="inline-flex items-center gap-1 p-1 rounded-lg bg-surface-container-low" role="group" aria-label="Prodajni status stana">
  @foreach($opcije as $vrednost => [$naziv, $ikonica])
  @php $aktivan = $stan->status === $vrednost || ($vrednost === 'Prodat_u_procesu_uknjizenja' && $prodat); @endphp
  <form method="POST" action="{{ route('units.status', $stan) }}" @if($vrednost === 'Prodat_u_procesu_uknjizenja' && !$aktivan) data-potvrdi="Označiti stan {{ $stan->oznaka }} kao prodat? Oglas se uklanja sa Temelja." @endif>
    @csrf
    <input type="hidden" name="status" value="{{ $vrednost }}">
    <button type="submit" @disabled($aktivan) class="inline-flex items-center gap-1.5 h-8 px-3 rounded font-label-md text-label-md whitespace-nowrap transition-colors {{ $aktivan ? ($vrednost === 'Prodat_u_procesu_uknjizenja' ? 'bg-emerald-600 text-white' : ($vrednost === 'Rezervisan' ? 'bg-amber-500 text-white' : 'bg-primary text-on-primary')) : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
      <span class="material-symbols-outlined text-[16px]">{{ $ikonica }}</span>{{ $naziv }}
    </button>
  </form>
  @endforeach
</div>
