@extends('layouts.gost')
@section('naslov', 'Status zahteva')
@section('sirina', '560px')

@section('content')
@php $status = $firma?->status; @endphp
@if($errors->any())
<div class="p-space-sm rounded bg-error-container text-on-error-container font-body-md text-body-md" role="alert">{{ $errors->first() }}</div>
@endif
<div class="kartica p-space-lg flex flex-col gap-space-md">
  <div class="flex items-start gap-space-md">
    <span class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 {{ in_array($status, ['odbijen', 'raskinut'], true) ? 'bg-error-container text-on-error-container' : ($status === 'suspendovan' ? 'bg-surface-container-high text-on-surface' : 'bg-amber-50 text-amber-700') }}">
      <span class="material-symbols-outlined text-[24px]">{{ in_array($status, ['odbijen', 'raskinut'], true) ? 'block' : ($status === 'suspendovan' ? 'pause' : 'hourglass_top') }}</span>
    </span>
    <div class="flex flex-col gap-1">
      @if($status === 'odbijen')
        <h1 class="font-headline-md text-headline-md">Zahtev nije odobren</h1>
        <p class="font-body-md text-body-md text-on-surface-variant">Nismo mogli da potvrdimo podatke firme {{ $firma->naziv }}.</p>
        @if($firma->razlog_odbijanja)<p class="font-body-md text-body-md"><strong>Razlog:</strong> {{ $firma->razlog_odbijanja }}</p>@endif
        <p class="font-body-md text-body-md text-on-surface-variant">Ako mislite da je u pitanju greška, odgovorite na email koji ste dobili.</p>
      @elseif($status === 'raskinut')
        <h1 class="font-headline-md text-headline-md">Nalog firme je zatvoren</h1>
        <p class="font-body-md text-body-md text-on-surface-variant">Nalog firme {{ $firma->naziv }} više nije povezan sa profilom na Temelju.</p>
        @if($firma->razlog_odbijanja)<p class="font-body-md text-body-md"><strong>Razlog:</strong> {{ $firma->razlog_odbijanja }}</p>@endif
        <p class="font-body-md text-body-md text-on-surface-variant">Ako mislite da je u pitanju greška, odgovorite na email koji ste dobili.</p>
      @elseif($status === 'suspendovan')
        <h1 class="font-headline-md text-headline-md">Nalog firme je privremeno suspendovan</h1>
        <p class="font-body-md text-body-md text-on-surface-variant">Pristup aplikaciji je trenutno isključen. Za više informacija javite se timu Temelja.</p>
      @else
        <h1 class="font-headline-md text-headline-md">Zahtev je poslat — proveravamo firmu</h1>
        <p class="font-body-md text-body-md text-on-surface-variant">Proveravamo da li ste ovlašćeni za firmu <strong class="text-on-surface">{{ $firma->naziv ?? '' }}</strong> (MB {{ $firma->maticni_broj ?? '' }}). Obično traje do jednog radnog dana — javićemo vam emailom.</p>
      @endif
    </div>
  </div>

  @if($status === 'na_cekanju')
  <ol class="flex flex-col gap-space-sm pt-space-md border-t border-surface-container font-body-md text-body-md">
    <li class="flex items-center gap-space-sm"><span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>Zahtev za nalog je primljen</li>
    <li class="flex items-center gap-space-sm">
      @if($korisnik->email_potvrdjen_at)
        <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>Email adresa je potvrđena
      @else
        <span class="material-symbols-outlined text-[20px] text-amber-600">pending</span>
        <span class="flex-1">Potvrdite email — link smo poslali na <strong>{{ $korisnik->email }}</strong></span>
      @endif
    </li>
    <li class="flex items-center gap-space-sm"><span class="material-symbols-outlined text-[20px] text-on-surface-variant">hourglass_top</span>Provera firme i odobrenje naloga</li>
  </ol>
  @unless($korisnik->email_potvrdjen_at)
  <form method="POST" action="{{ route('registracija.ponovo') }}">
    @csrf
    <button class="dugme-sekundarno dugme-malo"><span class="material-symbols-outlined text-[16px]">send</span>Pošalji link ponovo</button>
  </form>
  @endunless
  @endif

  <form method="POST" action="{{ route('logout') }}" class="pt-space-sm">
    @csrf
    <button class="dugme-tiho dugme-malo"><span class="material-symbols-outlined text-[16px]">logout</span>Odjavi se</button>
  </form>
</div>
@endsection
