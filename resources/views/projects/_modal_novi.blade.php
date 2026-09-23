{{-- Novi projekat (PRD 10.1 / 6.2). Posle čuvanja otvara se dosije gde se dodaje prva zgrada. --}}
<x-modal id="modal-novi-projekat" naslov="Novi projekat" ikonica="domain_add" sirina="max-w-xl">
  <form class="flex flex-col gap-space-md" method="POST" action="{{ route('projects.store') }}">
    @csrf
    <x-polje labela="Naziv projekta *" za="np-naziv">
      <input class="polje" id="np-naziv" name="naziv" required placeholder="npr. Telep Residence"/>
    </x-polje>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
      <x-polje labela="Adresa" za="np-adresa"><input class="polje" id="np-adresa" name="lokacija_adresa" placeholder="npr. Bulevar Patrijarha Pavla 14"/></x-polje>
      <x-polje labela="Grad" za="np-grad"><input class="polje" id="np-grad" name="lokacija_grad" placeholder="npr. Novi Sad"/></x-polje>
      <x-polje labela="Tip objekta" za="np-tip">
        <select class="polje" id="np-tip" name="tip">
          @foreach(config('statusi.tip_projekta') as $tp)<option value="{{ $tp }}">{{ \App\Support\Prikaz::label($tp) }}</option>@endforeach
        </select>
      </x-polje>
      <x-polje labela="Broj planiranih stanova" za="np-broj"><input class="polje" id="np-broj" name="broj_planiranih_stanova" type="number" min="1"/></x-polje>
      <x-polje labela="Početak gradnje" za="np-pocetak"><input class="polje" id="np-pocetak" name="datum_pocetka_gradnje" type="date"/></x-polje>
      <x-polje labela="Planirani završetak" za="np-kraj"><input class="polje" id="np-kraj" name="planirani_datum_zavrsetka" type="date"/></x-polje>
    </div>
    <p class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">info</span>Projekat počinje u statusu „Planiranje“. Status menjate ručno u dosijeu.</p>
    <div class="flex items-center justify-end gap-space-sm pt-space-xs">
      <button class="dugme-sekundarno" data-modal-close="modal-novi-projekat" type="button">Otkaži</button>
      <button class="dugme-primarno" type="submit">Sačuvaj i otvori dosije</button>
    </div>
  </form>
</x-modal>
