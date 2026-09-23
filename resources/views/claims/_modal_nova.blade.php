{{-- Nova reklamacija (PRD 6.7 / 10.3) — interni unos u ime kupca.
     $stanovi = stanovi za izbor; $izabraniStan (opciono) = unapred izabran stan. --}}
@php $izabraniStan = $izabraniStan ?? null; @endphp
<x-modal id="modal-nova-reklamacija" naslov="Nova reklamacija" ikonica="build_circle" sirina="max-w-xl">
  <form method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data" class="flex flex-col gap-space-md">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
      <x-polje labela="Stan *" za="nr-stan">
        <select class="polje" id="nr-stan" name="stan_id" required>
          @unless($izabraniStan)<option value="" disabled selected>Izaberite stan…</option>@endunless
          @foreach($stanovi as $s)<option value="{{ $s->id }}" @selected($izabraniStan == $s->id)>{{ $s->oznaka }} — {{ $s->building->naziv ?? '' }}</option>@endforeach
        </select>
      </x-polje>
      <x-polje labela="Tip problema *" za="nr-tip">
        <select class="polje" id="nr-tip" name="tip_problema" required onchange="document.getElementById('nr-drugo').classList.toggle('hidden', this.value !== 'Drugo')">
          <option value="" disabled selected>Izaberite…</option>
          @foreach(config('statusi.tip_problema') as $tp)<option value="{{ $tp }}">{{ \App\Support\Prikaz::label($tp) }}</option>@endforeach
        </select>
      </x-polje>
    </div>
    <div class="hidden" id="nr-drugo">
      <x-polje labela="Opišite tip problema *" za="nr-drugo-polje"><input class="polje" id="nr-drugo-polje" name="tip_problema_drugo"/></x-polje>
    </div>
    <x-polje labela="Opis kvara i mesto u stanu *" za="nr-opis">
      <textarea class="polje" id="nr-opis" name="opis" required rows="3" placeholder="Šta je problem, gde se nalazi, šta je kupac prijavio…"></textarea>
    </x-polje>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
      <x-polje labela="Datum prijave *" za="nr-datum"><input class="polje" id="nr-datum" name="datum_prijave" type="date" value="{{ now()->format('Y-m-d') }}" required/></x-polje>
      <x-polje labela="Fotografije / PDF (opciono)" za="nr-prilog"><input class="polje" id="nr-prilog" name="prilog[]" type="file" multiple accept="image/*,.pdf,.heic"/></x-polje>
    </div>
    <p class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">info</span>Upisuje se u statusu „Prijavljena". Odgovornog i rok dodeljujete u detalju reklamacije.</p>
    <div class="flex justify-end gap-space-sm pt-space-xs">
      <button type="button" data-modal-close="modal-nova-reklamacija" class="dugme-sekundarno">Otkaži</button>
      <button type="submit" class="dugme-primarno">Sačuvaj reklamaciju</button>
    </div>
  </form>
</x-modal>
