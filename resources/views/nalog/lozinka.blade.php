@extends('layouts.app')
@section('naslov', 'Lozinka')

@section('content')
<x-zaglavlje naslov="Moj nalog" :opis="$currentUser->ime_prezime.' · '.$currentUser->email" />

<section class="kartica p-space-lg max-w-xl">
  <form method="POST" action="{{ route('nalog.lozinka') }}" class="flex flex-col gap-space-md">
    @csrf
    <h2 class="font-headline-sm text-headline-sm">Promena lozinke</h2>
    <x-polje labela="Trenutna lozinka" za="lz-trenutna"><input class="polje" id="lz-trenutna" name="trenutna" type="password" required autocomplete="current-password"/></x-polje>
    <x-polje labela="Nova lozinka (najmanje 8 znakova)" za="lz-nova"><input class="polje" id="lz-nova" name="password" type="password" required minlength="8" autocomplete="new-password"/></x-polje>
    <x-polje labela="Ponovite novu lozinku" za="lz-nova2"><input class="polje" id="lz-nova2" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"/></x-polje>
    <div><button class="dugme-primarno"><span class="material-symbols-outlined text-[18px]">save</span>Promeni lozinku</button></div>
  </form>
</section>
@endsection
