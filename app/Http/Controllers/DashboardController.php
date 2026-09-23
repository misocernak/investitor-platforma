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

        $projekti = Project::with('buildings')->where('arhiviran', false)->latest()->get();

        // To-do blok: najkriticnije nedostajuce stavke (PRD 9.1)
        $todo = ChecklistItem::with('checklist.building.project')
            ->where('zavrseno', false)
            ->whereHas('checklist.building', fn ($q) => $q->where('arhiviran', false))
            ->orderBy('updated_at')
            ->limit(10)
            ->get();

        return view('dashboard', compact('karte', 'projekti', 'todo'));
    }
}
