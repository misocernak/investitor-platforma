{{-- Stanje oglasa na Temelju (čip sa tačkom). --}}
@props(['oglas', 'tenant'])
@php
  [$labela, $ton, $objasnjenje] = \App\Support\Prikaz::oglasStanje($oglas, $tenant);
  [$pozadina, $tacka] = \App\Support\Prikaz::KLASE[$ton];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 h-[22px] px-2 rounded font-label-sm text-label-sm whitespace-nowrap $pozadina"]) }} title="{{ $objasnjenje }}">
  <span class="w-1.5 h-1.5 rounded-full {{ $tacka }}"></span>{{ $labela }}
</span>
