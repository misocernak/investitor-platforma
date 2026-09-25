{{-- Dodavanje dokumenta (PRD 10.2). Na šta se dokument vezuje zavisi od ekrana:
     $stan    → dokument stana (zgrada se popunjava sama)
     $zgrada  → dokument zgrade, uz opcioni izbor stana iz $stanovi
     $zgrade  → izbor zgrade (opšta Dokumentacija)
     Tip dokumenta može unapred da se postavi dugmetom: data-postavi='{"tip":"Geodetski_elaborat"}' --}}
@php
  $stan = $stan ?? null;
  $zgrada = $zgrada ?? null;
  $zgrade = $zgrade ?? null;
  $stanovi = $stanovi ?? collect();
@endphp
<x-modal id="modal-dokument" naslov="Dodaj dokument" ikonica="upload_file" sirina="max-w-xl">
  <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="flex flex-col gap-space-md">
    @csrf
    @if($stan)
      <input type="hidden" name="stan_id" value="{{ $stan->id }}"/>
      <input type="hidden" name="zgrada_id" value="{{ $stan->zgrada_id }}"/>
      <div class="px-space-md py-space-sm rounded bg-surface-container-low font-body-md text-body-md">Vezano za: <strong>stan {{ $stan->oznaka }}</strong> · {{ $stan->building->naziv ?? '' }}</div>
    @elseif($zgrada)
      <input type="hidden" name="zgrada_id" value="{{ $zgrada->id }}"/>
      <input type="hidden" name="projekat_id" value="{{ $zgrada->projekat_id }}"/>
    @endif

    <x-polje labela="Fajl * (PDF, DWG, JPG, PNG, ZIP, DOCX, XLSX — do 50 MB)" za="dok-fajl">
      <input class="polje" id="dok-fajl" name="fajl" type="file" required accept=".pdf,.dwg,.jpg,.jpeg,.png,.zip,.docx,.xlsx"
             onchange="const n=document.getElementById('dok-naziv'); if(this.files[0] && !n.value) n.value=this.files[0].name.replace(/\.[^.]+$/, '').replace(/[_-]+/g, ' ');"/>
    </x-polje>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
      <x-polje labela="Tip dokumenta *" za="dok-tip">
        <select class="polje" id="dok-tip" name="tip" required>
          <option value="" disabled selected>Izaberite tip…</option>
          @foreach($tipoviDokumenata as $t)<option value="{{ $t->naziv }}">{{ \App\Support\Prikaz::label($t->naziv) }}</option>@endforeach
        </select>
      </x-polje>
      @if($zgrade && !$stan && !$zgrada)
      <x-polje labela="Zgrada *" za="dok-zgrada">
        <select class="polje" id="dok-zgrada" name="zgrada_id" required>
          <option value="" disabled selected>Izaberite zgradu…</option>
          @foreach($zgrade as $z)<option value="{{ $z->id }}">{{ $z->naziv }}{{ $z->project ? ' — '.$z->project->naziv : '' }}</option>@endforeach
        </select>
      </x-polje>
      @elseif($zgrada && $stanovi->isNotEmpty())
      <x-polje labela="Vezano za" za="dok-stan">
        <select class="polje" id="dok-stan" name="stan_id">
          <option value="">Celu zgradu ({{ $zgrada->naziv }})</option>
          @foreach($stanovi as $s)<option value="{{ $s->id }}">Stan {{ $s->oznaka }}</option>@endforeach
        </select>
      </x-polje>
      @endif
    </div>
    <x-polje labela="Naziv dokumenta *" za="dok-naziv" pomoc="Popunjava se iz imena fajla — možete ga izmeniti.">
      <input class="polje" id="dok-naziv" name="naziv" required placeholder="npr. Rešenje o građevinskoj dozvoli"/>
    </x-polje>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
      <x-polje labela="Datum izdavanja" za="dok-datum"><input class="polje" id="dok-datum" name="datum_izdavanja" type="date"/></x-polje>
      <x-polje labela="Izdavalac" za="dok-izdavalac"><input class="polje" id="dok-izdavalac" name="izdavalac" placeholder="npr. Sekretarijat za urbanizam"/></x-polje>
    </div>
    @if($currentTenant?->povezanSaTemeljem())
    <label class="flex items-start gap-space-sm font-body-md text-body-md cursor-pointer">
      <input type="hidden" name="vidljivo_kupcu" value="0">
      <input type="checkbox" name="vidljivo_kupcu" value="1" @checked($stan) class="w-4 h-4 mt-0.5 accent-black">
      <span>Vidljivo kupcu na Temelju<span class="block font-body-sm text-body-sm text-on-surface-variant">{{ $stan ? 'Vidi ga samo kupac ovog stana.' : 'Vide ga kupci stanova u ovoj zgradi.' }}</span></span>
    </label>
    @endif
    <p class="font-body-sm text-body-sm text-on-surface-variant flex items-start gap-1.5"><span class="material-symbols-outlined text-[16px]">policy</span>Isti tip za istu zgradu/stan postaje nova verzija (v2, v3…), a prethodna ostaje u arhivi. Sistem ne proverava pravnu ispravnost dokumenta.</p>
    <div class="flex justify-end gap-space-sm pt-space-xs">
      <button type="button" data-modal-close="modal-dokument" class="dugme-sekundarno">Otkaži</button>
      <button type="submit" class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">save</span>Sačuvaj dokument</button>
    </div>
  </form>
</x-modal>
