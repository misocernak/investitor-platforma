{{-- Brojčana kartica (KPI). Ako ima "href", cela kartica je link (npr. ka filtriranoj listi). --}}
@props(['labela', 'vrednost', 'opis' => null, 'ikonica' => null, 'ton' => null, 'href' => null, 'aktivno' => false])
@php
  $boja = $ton === 'greska' && $vrednost > 0 ? 'text-error' : 'text-on-surface';
  $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'kartica p-space-md flex flex-col gap-space-sm transition-shadow '.($href ? 'hover:shadow-md ' : '').($aktivno ? 'ring-2 ring-primary' : '')]) }}>
  <div class="flex items-center justify-between gap-space-sm">
    <span class="oznaka">{{ $labela }}</span>
    @if($ikonica)<span class="material-symbols-outlined text-[20px] {{ $ton === 'greska' && $vrednost > 0 ? 'text-error' : 'text-on-surface-variant' }}">{{ $ikonica }}</span>@endif
  </div>
  <div class="flex items-baseline gap-space-sm">
    <span class="font-headline-lg text-headline-lg font-mono-num {{ $boja }}">{{ $vrednost }}</span>
    @if($opis)<span class="font-body-sm text-body-sm text-on-surface-variant">{{ $opis }}</span>@endif
  </div>
</{{ $tag }}>
