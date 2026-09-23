@props(['v', 'tacka' => true])
@php
  [$pozadina, $bojaTacke] = \App\Support\Prikaz::KLASE[\App\Support\Prikaz::ton($v)];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 h-[22px] px-2 rounded font-label-sm text-label-sm whitespace-nowrap $pozadina"]) }}>
  @if($tacka)<span class="w-1.5 h-1.5 rounded-full {{ $bojaTacke }}"></span>@endif
  {{ \App\Support\Prikaz::label($v) }}
</span>
