<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names={{ implode(',', config('ikonice')) }}&display=block" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: 1 }}"/>
<title>Prijava · Temelj Investitori</title>
</head>
<body class="min-h-screen bg-surface font-body-md text-body-md text-on-surface grid lg:grid-cols-2">
  {{-- Leva strana: brend (samo na većim ekranima) --}}
  <div class="hidden lg:flex flex-col justify-between bg-primary text-on-primary p-margin-lg">
    <div class="flex items-center gap-space-sm">
      <span class="w-9 h-9 rounded-lg bg-secondary-container flex items-center justify-center"><span class="material-symbols-outlined text-[22px]">foundation</span></span>
      <span class="font-headline-md text-headline-md">Temelj <span class="text-on-primary-container font-medium">Investitori</span></span>
    </div>
    <div class="max-w-md">
      <h2 class="font-headline-xl text-headline-xl">Svi projekti, dokumenti i reklamacije na jednom mestu.</h2>
      <p class="mt-space-md font-body-lg text-body-lg text-on-primary-container">Dosije po zgradi i po stanu, checkliste za upotrebnu dozvolu i uknjižbu, evidencija reklamacija u garantnom roku.</p>
    </div>
    <p class="font-label-xs text-label-xs uppercase tracking-wider text-on-primary-container">Deo platforme Temelj.rs</p>
  </div>

  {{-- Desna strana: forma --}}
  <div class="flex items-center justify-center p-space-lg">
    <div class="w-full max-w-sm flex flex-col gap-space-xl">
      <div class="flex flex-col gap-space-xs">
        <span class="lg:hidden w-10 h-10 rounded-lg bg-primary text-on-primary flex items-center justify-center mb-space-sm"><span class="material-symbols-outlined text-[22px]">foundation</span></span>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Prijava</h1>
        <p class="font-body-md text-body-md text-on-surface-variant">Prijavite se na nalog vaše firme.</p>
      </div>
      <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-space-md">
        @csrf
        <div class="flex flex-col gap-1.5">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant" for="email">Email</label>
          <input class="w-full h-11 px-space-md bg-surface-container-lowest text-on-surface rounded-lg border border-outline-variant focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus/>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="font-label-xs text-label-xs uppercase tracking-wider text-on-surface-variant" for="password">Lozinka</label>
          <input class="w-full h-11 px-space-md bg-surface-container-lowest text-on-surface rounded-lg border border-outline-variant focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20" id="password" name="password" type="password" autocomplete="current-password" required/>
        </div>
        @if($errors->any())
        <div class="p-space-sm rounded-lg bg-error-container text-on-error-container font-body-sm text-body-sm" role="alert">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif
        <button type="submit" class="w-full h-11 bg-primary text-on-primary rounded-lg font-body-md text-body-md font-semibold hover:bg-primary-container transition-colors flex items-center justify-center gap-space-xs">
          <span class="material-symbols-outlined text-[18px]">login</span> Prijavi se
        </button>
      </form>
    </div>
  </div>
</body>
</html>
