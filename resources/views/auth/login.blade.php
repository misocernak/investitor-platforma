<!DOCTYPE html>
<html lang="sr">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300..800&family=JetBrains+Mono:wght@400..700&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:"#091426","on-primary":"#ffffff","primary-container":"#1e293b","on-primary-container":"#8590a6","secondary":"#0051d5","surface":"#f8f9ff","on-surface":"#0b1c30","on-surface-variant":"#45474c","surface-container":"#e5eeff","surface-container-high":"#dce9ff","surface-container-low":"#eff4ff","surface-container-lowest":"#ffffff","error":"#ba1a1a"},fontFamily:{body:["Hanken Grotesk"],mono:["JetBrains Mono"]}}}}</script>
<title>Prijava — StructureOps</title>
</head>
<body class="bg-surface font-body min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-sm bg-surface-container-lowest rounded-xl shadow-xl p-space-lg flex flex-col gap-space-lg">
  <div class="flex flex-col items-center gap-2">
    <span class="w-12 h-12 rounded-xl bg-primary-container flex items-center justify-center font-bold text-on-primary text-2xl">S</span>
    <div class="text-center">
      <h1 class="text-xl font-bold text-on-surface tracking-tight">StructureOps</h1>
      <p class="text-sm text-on-surface-variant">Interni administrativni alat investitora</p>
    </div>
  </div>
  <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-space-md">
    @csrf
    <div class="flex flex-col gap-1">
      <label class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="email">Email</label>
      <input class="w-full h-10 px-3 bg-surface-container-low text-on-surface rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus/>
    </div>
    <div class="flex flex-col gap-1">
      <label class="text-xs uppercase tracking-wider text-on-surface-variant font-semibold" for="password">Lozinka</label>
      <input class="w-full h-10 px-3 bg-surface-container-low text-on-surface rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary" id="password" name="password" type="password" required/>
    </div>
    @if($errors->any())
    <div class="p-3 rounded-lg bg-error/10 text-error text-sm">
      @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif
    <button type="submit" class="w-full h-10 bg-primary text-on-primary rounded-lg font-semibold hover:bg-primary-container transition-colors">Prijavi se</button>
  </form>
  <p class="text-center text-xs text-on-surface-variant">Dosije po zgradi · Dosije po stanu · Reklamacije</p>
</div>
</body>
</html>
