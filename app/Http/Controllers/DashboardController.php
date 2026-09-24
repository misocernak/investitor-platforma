<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\ChecklistItem;
use App\Models\Claim;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Nadzor/izvodjac vidi samo svoje dodele (PRD 5)
        if ($user->jeNadzor()) {
            return redirect()->route('claims.index');
        }

        $karte = [
            'aktivni_projekti' => Project::where('arhiviran', false)->count(),
            'zgrade_garancija' => Building::whereIn('status', ['U garanciji', 'Zavrsena'])
                ->where('arhiviran', false)->count(),
            'otvorene_reklamacije' => Claim::whereNotIn('status', ['Resena', 'Odbijena'])->count(),
            'nedostajuce_stavke' => ChecklistItem::where('zavrseno', false)
                ->whereHas('checklist.building', fn ($q) => $q->where('arhiviran', false))
                ->count(),
        ];

        $projekti = Project::with(['buildings' => fn ($q) => $q->withCount('units')])
            ->where('arhiviran', false)->latest()->get();

        // To-do blok: najkriticnije nedostajuce stavke (PRD 9.1)
        $todo = ChecklistItem::with('checklist.building.project')
            ->where('zavrseno', false)
            ->whereHas('checklist.building', fn ($q) => $q->where('arhiviran', false))
            ->orderBy('updated_at')
            ->limit(8)
            ->get();

        // Otvorene reklamacije kojima je rok prošao ili ističe u narednih 7 dana (najhitnije prve)
        $hitneReklamacije = Claim::with('unit.building')
            ->whereNotIn('status', ['Resena', 'Odbijena'])
            ->whereNotNull('rok_resavanja')
            ->where('rok_resavanja', '<=', now()->addDays(7)->toDateString())
            ->orderBy('rok_resavanja')
            ->limit(8)
            ->get();

        // Novi upiti kupaca sa Temelja — najvažnija prodajna informacija, ide na vrh
        $noviUpiti = \App\Models\Upit::with('unit')->where('status', 'novo')->latest('primljeno_at')->limit(5)->get();

        $otvorenePoProjektu = Project::otvoreneReklamacijePoProjektu();

        return view('dashboard', compact('karte', 'projekti', 'todo', 'hitneReklamacije', 'noviUpiti', 'otvorenePoProjektu'));
    }
}
