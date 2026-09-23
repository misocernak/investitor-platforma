{{-- Prazno stanje: kad lista nema stavki, objasni šta je sledeći korak. --}}
@props(['ikonica' => 'info', 'naslov', 'tekst' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center text-center gap-space-sm py-space-xl px-space-lg']) }}>
  <span class="w-11 h-11 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant"><span class="material-symbols-outlined text-[24px]">{{ $ikonica }}</span></span>
  <h3 class="font-headline-sm text-headline-sm text-on-surface">{{ $naslov }}</h3>
  @if($tekst)<p class="font-body-md text-body-md text-on-surface-variant max-w-md">{{ $tekst }}</p>@endif
  @if($slot->isNotEmpty())<div class="mt-space-xs">{{ $slot }}</div>@endif
</div>
