<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names={{ implode(',', config('ikonice')) }}&display=block" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: 1 }}"/>
<title>Prijava · Temelj Investitori</title>
</head>
<body class="min-h-screen bg-surface font-body-md text-body-md text-on-surface antialiased flex items-center justify-center p-margin-mobile">
  <div class="w-full max-w-sm flex flex-col gap-space-lg">
    <div class="flex items-center gap-space-sm">
      <span class="w-9 h-9 rounded bg-primary text-on-primary flex items-center justify-center font-bold text-[18px]">T</span>
      <div class="flex flex-col leading-tight">
        <span class="font-label-md text-label-md font-semibold text-on-surface">Temelj Investitori</span>
        <span class="font-body-sm text-body-sm text-on-surface-variant">Projekti, stanovi, dokumentacija i reklamacije</span>
      </div>
    </div>

    <div class="kartica p-space-lg flex flex-col gap-space-lg">
      <div class="flex flex-col gap-1">
        <h1 class="font-headline-md text-headline-md text-on-surface">Prijava</h1>
        <p class="font-body-md text-body-md text-on-surface-variant">Prijavite se na nalog vaše firme.</p>
      </div>
      <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-space-md">
        @csrf
        <x-polje labela="Email" za="email">
          <input class="polje h-10" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus/>
        </x-polje>
        <x-polje labela="Lozinka" za="password">
          <input class="polje h-10" id="password" name="password" type="password" autocomplete="current-password" required/>
        </x-polje>
        <label class="flex items-center gap-space-sm font-body-md text-body-md text-on-surface-variant cursor-pointer select-none">
          <input type="checkbox" name="zapamti" value="1" class="w-4 h-4 accent-black"> Zapamti me na ovom računaru
        </label>
        @if($errors->any())
        <div class="p-space-sm rounded bg-error-container text-on-error-container font-body-md text-body-md flex gap-space-sm" role="alert">
          <span class="material-symbols-outlined text-[18px]">error</span>
          <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        </div>
        @endif
        <button type="submit" class="dugme-primarno h-10 w-full">Prijavi se</button>
      </form>
    </div>
    <p class="text-center font-body-sm text-body-sm text-on-surface-variant">Deo platforme Temelj.rs · Nalog otvara administrator vaše firme</p>
  </div>
</body>
</html>
