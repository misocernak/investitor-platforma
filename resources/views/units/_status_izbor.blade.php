{{-- Prodajni status stana kao kompaktan padajući meni (za tabele). Očekuje $stan. --}}
@php
  $prodat = in_array($stan->status, ['Prodat_u_procesu_uknjizenja', 'Prodat_u_garanciji', 'Garancija_istekla'], true);
  $trenutni = $prodat ? 'Prodat_u_procesu_uknjizenja' : $stan->status;
  $boja = match ($trenutni) {
    'Rezervisan' => 'bg-amber-50 text-amber-800 border-amber-200',
    'Prodat_u_procesu_uknjizenja' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
    default => 'bg-surface-container-lowest text-on-surface border-outline-variant',
  };
@endphp
<form method="POST" action="{{ route('units.status', $stan) }}">
  @csrf
  <label class="sr-only" for="status-stana-{{ $stan->id }}">Status stana {{ $stan->oznaka }}</label>
  <select id="status-stana-{{ $stan->id }}" name="status" data-pre="{{ $trenutni }}"
          class="polje h-8 w-auto min-w-[132px] font-label-md text-label-md {{ $boja }}"
          onchange="if (this.value === 'Prodat_u_procesu_uknjizenja' && !confirm('Označiti stan {{ $stan->oznaka }} kao prodat? Oglas se uklanja sa Temelja.')) { this.value = this.dataset.pre; return; } this.form.submit();">
    <option value="Za_prodaju" @selected($trenutni === 'Za_prodaju')>Za prodaju</option>
    <option value="Rezervisan" @selected($trenutni === 'Rezervisan')>Rezervisan</option>
    <option value="Prodat_u_procesu_uknjizenja" @selected($trenutni === 'Prodat_u_procesu_uknjizenja')>Prodat</option>
    @unless(in_array($trenutni, ['Za_prodaju', 'Rezervisan', 'Prodat_u_procesu_uknjizenja'], true))
    <option value="" selected disabled>{{ \App\Support\Prikaz::label($stan->status) }}</option>
    @endunless
  </select>
</form>
