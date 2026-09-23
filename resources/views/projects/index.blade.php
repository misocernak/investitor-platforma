@extends('layouts.app')
@section('content')
<div class="flex flex-col gap-space-lg w-full max-w-7xl mx-auto pt-space-xs">
  <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Projekti &amp; Zgrade</h1>
  <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-left font-body-sm text-body-sm">
      <thead>
        <tr class="bg-surface-container-low text-on-surface-variant font-label-xs text-label-xs uppercase tracking-wider">
          <th class="py-2.5 px-space-md font-medium">Naziv projekta</th>
          <th class="py-2.5 px-space-md font-medium">Lokacija</th>
          <th class="py-2.5 px-space-md font-medium text-right">Stanova</th>
          <th class="py-2.5 px-space-md font-medium text-center">Status</th>
          <th class="py-2.5 px-space-md text-center w-12"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-surface-container-low">
        @foreach($projekti as $projekat)
        <tr class="hover:bg-surface-container-low cursor-pointer" onclick="window.location='{{ route('projects.show', $projekat) }}'">
          <td class="py-3 px-space-md font-semibold text-on-surface">{{ $projekat->naziv }}</td>
          <td class="py-3 px-space-md text-on-surface-variant">{{ $projekat->lokacija_grad ?: '—' }}</td>
          <td class="py-3 px-space-md text-right">{{ $projekat->broj_planiranih_stanova ?: '—' }}</td>
          <td class="py-3 px-space-md text-center"><span class="inline-flex items-center px-2 py-0.5 rounded font-label-xs text-label-xs font-semibold bg-surface-container-high text-on-surface">{{ $projekat->status }}</span></td>
          <td class="py-3 px-space-md text-center text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">chevron_right</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
