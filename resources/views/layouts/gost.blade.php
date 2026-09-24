<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names={{ implode(',', config('ikonice')) }}&display=block" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: 1 }}"/>
<title>@yield('naslov') · Temelj Investitor</title>
</head>
<body class="min-h-screen bg-surface font-body-md text-body-md text-on-surface antialiased">
  <div class="w-full mx-auto px-margin-mobile py-space-xl flex flex-col gap-space-lg" style="max-width: @yield('sirina', '440px')">
    <a href="{{ route('login') }}" class="flex items-center gap-space-sm self-start">
      <span class="w-9 h-9 rounded bg-primary text-on-primary flex items-center justify-center font-bold text-[18px]">T</span>
      <span class="flex flex-col leading-tight">
        <span class="font-label-md text-label-md font-semibold">Temelj Investitor</span>
        <span class="font-body-sm text-body-sm text-on-surface-variant">Projekti, stanovi, oglasi i reklamacije na jednom mestu</span>
      </span>
    </a>
    @if(session('uspesno'))
      <div class="p-space-sm rounded bg-emerald-50 text-emerald-800 font-body-md text-body-md flex gap-space-sm" role="status"><span class="material-symbols-outlined text-[18px]">task_alt</span>{{ session('uspesno') }}</div>
    @endif
    @yield('content')
    <p class="text-center font-body-sm text-body-sm text-on-surface-variant">Deo platforme Temelj.rs</p>
  </div>
  @stack('scripts')
</body>
</html>
