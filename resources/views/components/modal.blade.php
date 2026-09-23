{{-- Iskačući prozor. Otvara se dugmetom sa data-modal-open="id"; zatvara sa X, Otkaži, klikom pored ili Esc. --}}
@props(['id', 'naslov', 'ikonica' => null, 'sirina' => 'max-w-lg'])
<div class="fixed inset-0 z-[60] hidden items-start sm:items-center justify-center p-margin-mobile sm:p-margin bg-inverse-surface/40 overflow-y-auto" id="{{ $id }}" data-modal role="dialog" aria-modal="true">
  <div class="kartica shadow-xl w-full {{ $sirina }} my-auto flex flex-col">
    <div class="flex items-center justify-between gap-space-sm px-space-lg h-14 border-b border-surface-container">
      <div class="flex items-center gap-space-sm min-w-0">
        @if($ikonica)<span class="material-symbols-outlined text-[20px] text-on-surface-variant">{{ $ikonica }}</span>@endif
        <h3 class="font-headline-sm text-headline-sm text-on-surface truncate">{{ $naslov }}</h3>
      </div>
      <button type="button" class="w-8 h-8 rounded flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low" data-modal-close="{{ $id }}" aria-label="Zatvori"><span class="material-symbols-outlined text-[20px]">close</span></button>
    </div>
    <div class="p-space-lg">{{ $slot }}</div>
  </div>
</div>
