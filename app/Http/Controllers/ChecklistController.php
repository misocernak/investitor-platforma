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
        $sveCheckliste = Checklist::where('zgrada_id', $building->id)->withCount([
            'items as ukupno_stavki',
            'items as reseno_stavki' => fn ($q) => $q->where('zavrseno', true),
        ])->get();

        // Najnovija aktivna verzija dokumenta po tipu — prikaz uz stavku i "vizuelni predlog" (PRD 6.6)
        $dokumentiPoTipu = \App\Models\Document::where('zgrada_id', $building->id)
            ->where('aktivna_verzija', true)
            ->orderByDesc('verzija')
            ->get()
            ->unique('tip')
            ->keyBy('tip');

        $tipoviDokumenata = \App\Models\DocumentType::zaTenant()->orderBy('naziv')->get();
        $building->load('project');

        return view('checklists.show', compact('building', 'checklist', 'tip', 'tipovi', 'sveCheckliste', 'dokumentiPoTipu', 'tipoviDokumenata'));
    }

    // Rucno stikliranje stavke - nezavisno od postojanja dokumenta (PRD 6.6 / 2.3)
    public function toggleItem(ChecklistItem $item)
    {
        abort_unless(auth()->user()->mozeUredjivati(), 403);
        // Stavka mora pripadati zgradi ove firme (Building ima tenant filter)
        abort_unless(Building::whereKey($item->checklist->zgrada_id)->exists(), 404);

        $item->update(['zavrseno' => !$item->zavrseno]);

        AuditLog::zabelezi('checklist_stavka_'.($item->zavrseno ? 'zavrsena' : 'vracena'), $item, [
            'stavka' => $item->naziv_stavke,
        ]);

        return back()->with('uspesno', 'Stavka "'.$item->naziv_stavke.'" je '.($item->zavrseno ? 'označena kao završena' : 'vraćena u nezavršene').'.');
    }
}
