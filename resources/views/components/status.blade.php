@props(['v', 'tacka' => true])
@php
  [$pozadina, $bojaTacke] = \App\Support\Prikaz::KLASE[\App\Support\Prikaz::ton($v)];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full font-label-xs text-label-xs font-semibold whitespace-nowrap $pozadina"]) }}>
  @if($tacka)<span class="w-1.5 h-1.5 rounded-full {{ $bojaTacke }}"></span>@endif
  {{ \App\Support\Prikaz::label($v) }}
</span>
