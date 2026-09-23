{{-- Zaglavlje stranice: putanja (niz [naziv => url|null]), naslov, opis i dugmad (slot). --}}
@props(['naslov', 'putanja' => [], 'opis' => null])
<div class="flex flex-col md:flex-row md:items-end justify-between gap-space-md">
  <div class="flex flex-col gap-1 min-w-0">
    @if($putanja)
    <nav class="flex flex-wrap items-center gap-1 font-label-md text-label-md text-on-surface-variant" aria-label="Putanja">
      @foreach($putanja as $naziv => $url)
        @if(!$loop->first)<span class="text-outline-variant">/</span>@endif
        @if($url)<a href="{{ $url }}" class="hover:text-on-surface hover:underline underline-offset-2">{{ $naziv }}</a>@else<span>{{ $naziv }}</span>@endif
      @endforeach
    </nav>
    @endif
    <div class="flex flex-wrap items-center gap-space-sm">
      <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ $naslov }}</h1>
      {{ $uzNaslov ?? '' }}
    </div>
    @if($opis)<p class="font-body-md text-body-md text-on-surface-variant">{{ $opis }}</p>@endif
  </div>
  @if($slot->isNotEmpty())
  <div class="flex flex-wrap items-center gap-space-sm shrink-0">{{ $slot }}</div>
  @endif
</div>
