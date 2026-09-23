<!-- MODAL: + Novi projekat (PRD 10.1 / 6.2) -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-primary/50 backdrop-blur-sm hidden" id="modal-new-project">
  <div class="bg-surface-container-lowest rounded-xl shadow-xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] overflow-y-auto">
    <div class="px-space-lg py-space-md bg-surface-container-low flex items-center justify-between">
      <div class="flex items-center gap-space-xs">
        <span class="material-symbols-outlined text-secondary text-[22px]">domain_add</span>
        <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface">Kreiraj novi projekat</h3>
      </div>
      <button class="p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high" data-modal-close="modal-new-project" type="button"><span class="material-symbols-outlined text-[20px]">close</span></button>
    </div>
    <form class="p-space-lg flex flex-col gap-space-md" method="POST" action="{{ route('projects.store') }}">
      @csrf
      <div class="flex flex-col gap-1">
        <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="naziv">Naziv projekta *</label>
        <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="naziv" name="naziv" required placeholder="npr. Vračar Smart Residence"/>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="lokacija_adresa">Lokacija (Adresa)</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="lokacija_adresa" name="lokacija_adresa" placeholder="npr. Južni bulevar 42"/>
        </div>
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="lokacija_grad">Lokacija (Grad)</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="lokacija_grad" name="lokacija_grad" value="Beograd"/>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="tip">Tip objekta</label>
          <select class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="tip" name="tip">
            <option value="Stambeni">Stambeni objekat</option>
            <option value="Stambeno_poslovni">Stambeno-poslovni objekat</option>
            <option value="Drugo">Drugo</option>
          </select>
        </div>
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="broj_planiranih_stanova">Broj planiranih stanova</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-secondary" id="broj_planiranih_stanova" name="broj_planiranih_stanova" type="number" min="1"/>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="datum_pocetka_gradnje">Datum početka gradnje</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm" id="datum_pocetka_gradnje" name="datum_pocetka_gradnje" type="date"/>
        </div>
        <div class="flex flex-col gap-1">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="planirani_datum_zavrsetka">Planirani datum završetka</label>
          <input class="w-full h-9 px-space-sm bg-surface-container-low text-on-surface rounded-lg shadow-sm" id="planirani_datum_zavrsetka" name="planirani_datum_zavrsetka" type="date"/>
        </div>
      </div>
      <div class="p-space-sm bg-surface-container-low rounded-lg flex items-center justify-between">
        <span class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant font-semibold">Početni status dosijea:</span>
        <span class="inline-flex items-center px-2 py-0.5 rounded font-label-xs text-label-xs font-semibold bg-surface-container-high text-on-surface">Planiranje</span>
      </div>
      <div class="flex items-center justify-end gap-space-sm pt-space-xs">
        <button class="px-space-md py-2 bg-surface-container-high text-on-surface rounded-lg font-label-md text-label-md font-medium" data-modal-close="modal-new-project" type="button">Otkaži</button>
        <button class="px-space-lg py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md font-medium hover:bg-primary-container shadow-sm" type="submit">Sačuvaj i otvori dosije</button>
      </div>
    </form>
  </div>
</div>
