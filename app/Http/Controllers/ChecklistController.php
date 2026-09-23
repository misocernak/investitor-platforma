<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Building;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    // Pregled svih zgrada i napretka njihove 3 fiksne checkliste (meni "Checkliste")
    public function index()
    {
        $zgrade = Building::with(['project', 'checklists' => fn ($q) => $q->withCount([
            'items as ukupno_stavki',
            'items as reseno_stavki' => fn ($q) => $q->where('zavrseno', true),
        ])])->where('arhiviran', false)->orderBy('naziv')->get();

        return view('checklists.index', [
            'zgrade' => $zgrade,
            'tipovi' => config('statusi.tip_checkliste'),
        ]);
    }

    // PRD 9.4: naslov [Naziv zgrade] — [Naziv checkliste]
    public function show(Request $request, Building $building)
    {
        $tip = $request->get('tip', 'Upotrebna_dozvola');
        if (!\App\Support\FiksneListe::validacija('tip_checkliste', $tip)) {
            $tip = 'Upotrebna_dozvola';
        }

        $checklist = Checklist::with('items')
            ->where('zgrada_id', $building->id)
            ->where('tip_checkliste', $tip)
            ->firstOrFail();

        $tipovi = config('statusi.tip_checkliste');
        $sveCheckliste = Checklist::where('zgrada_id', $building->id)->get();

        return view('checklists.show', compact('building', 'checklist', 'tip', 'tipovi', 'sveCheckliste'));
    }

    // Rucno stikliranje stavke - nezavisno od postojanja dokumenta (PRD 6.6 / 2.3)
    public function toggleItem(ChecklistItem $item)
    {
        abort_unless(auth()->user()->mozeUredjivati(), 403);

        $item->update(['zavrseno' => !$item->zavrseno]);

        AuditLog::zabelezi('checklist_stavka_'.($item->zavrseno ? 'zavrsena' : 'vracena'), $item, [
            'stavka' => $item->naziv_stavke,
        ]);

        return back()->with('uspesno', 'Stavka "'.$item->naziv_stavke.'" je '.($item->zavrseno ? 'označena kao završena' : 'vraćena u nezavršene').'.');
    }
}
