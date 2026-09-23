{{-- Nova stambena jedinica u zgradi $zgrada --}}
<x-modal id="modal-novi-stan" :naslov="'Nova jedinica — '.$zgrada->naziv" ikonica="add_home">
  <form method="POST" action="{{ route('units.store', $zgrada) }}" class="flex flex-col gap-space-md">
    @csrf
    <div class="grid grid-cols-2 gap-space-md">
      <x-polje labela="Oznaka *" za="ns-oznaka"><input class="polje" id="ns-oznaka" name="oznaka" required placeholder="npr. B-12"/></x-polje>
      <x-polje labela="Sprat" za="ns-sprat"><input class="polje" id="ns-sprat" name="sprat" placeholder="npr. 2"/></x-polje>
      <x-polje labela="Kvadratura (m²)" za="ns-kv"><input class="polje" id="ns-kv" name="kvadratura" type="number" step="0.01" min="0"/></x-polje>
      <x-polje labela="Broj soba" za="ns-sobe"><input class="polje" id="ns-sobe" name="broj_soba" type="number" min="0" max="20" step="0.5"/></x-polje>
      <x-polje labela="Cena (€, interno)" za="ns-cena"><input class="polje" id="ns-cena" name="cena" type="number" step="0.01" min="0"/></x-polje>
      <x-polje labela="Status *" za="ns-status">
        <select class="polje" id="ns-status" name="status" required>
          @foreach($statusiStana as $st)<option value="{{ $st }}">{{ \App\Support\Prikaz::label($st) }}</option>@endforeach
        </select>
      </x-polje>
    </div>
    <div class="pt-space-md border-t border-surface-container flex flex-col gap-space-md">
      <span class="oznaka">Kupac (opciono)</span>
      <x-polje labela="Ime i prezime" za="ns-kupac"><input class="polje" id="ns-kupac" name="kupac_ime"/></x-polje>
      <div class="grid grid-cols-2 gap-space-md">
        <x-polje labela="Email" za="ns-email"><input class="polje" id="ns-email" name="kupac_email" type="email"/></x-polje>
        <x-polje labela="Telefon" za="ns-tel"><input class="polje" id="ns-tel" name="kupac_telefon"/></x-polje>
      </div>
    </div>
    <div class="flex justify-end gap-space-sm pt-space-xs">
      <button type="button" data-modal-close="modal-novi-stan" class="dugme-sekundarno">Otkaži</button>
      <button class="dugme-primarno">Sačuvaj jedinicu</button>
    </div>
  </form>
</x-modal>
