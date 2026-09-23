{{-- Labela + polje forme (sadržaj polja ide u slot). --}}
@props(['labela', 'za' => null, 'pomoc' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col gap-1.5']) }}>
  <label class="oznaka" @if($za) for="{{ $za }}" @endif>{{ $labela }}</label>
  {{ $slot }}
  @if($pomoc)<span class="font-body-sm text-body-sm text-on-surface-variant">{{ $pomoc }}</span>@endif
</div>
