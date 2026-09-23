<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\Claim;
use App\Support\FiksneListe;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projekti = Project::with(['buildings' => fn ($q) => $q->where('arhiviran', false)->withCount('units')])
            ->where('arhiviran', false)->latest()->get();
        return view('projects.index', compact('projekti'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'naziv' => ['required', 'string', 'max:255'],
            'lokacija_adresa' => ['nullable', 'string', 'max:255'],
            'lokacija_grad' => ['nullable', 'string', 'max:255'],
            'tip' => ['nullable', FiksneListe::pravila('tip_projekta')],
            'broj_planiranih_stanova' => ['nullable', 'integer', 'min:1'],
            'datum_pocetka_gradnje' => ['nullable', 'date'],
            'planirani_datum_zavrsetka' => ['nullable', 'date'],
        ]);
        $data['status'] = 'Planiranje'; // PRD 10.1
        $projekat = Project::create($data);

        AuditLog::zabelezi('kreiran_projekat', $projekat, ['naziv' => $projekat->naziv]);

        return redirect()
            ->route('projects.show', $projekat)
            ->with('uspesno', 'Projekat "'.$projekat->naziv.'" uspešno kreiran. Sada dodajte prvu zgradu.');
    }

    public function show(Request $request, Project $project)
    {
        $project->load(['buildings' => fn ($q) => $q->where('arhiviran', false)->withCount('units')]);

        // Dosije se vodi po zgradi: izabrana zgrada (?zgrada=ID) ili prva zgrada projekta
        $zgrada = $project->buildings->firstWhere('id', (int) $request->get('zgrada')) ?? $project->buildings->first();

        $tab = $request->get('tab', 'pregled');
        if (!in_array($tab, ['pregled', 'dokumentacija', 'stanovi', 'checkliste', 'reklamacije'], true)) {
            $tab = 'pregled';
        }

        $dokumenti = collect();
        $reklamacije = collect();
        $stanovi = collect();
        $checkliste = collect();

        if ($zgrada) {
            $stanovi = $zgrada->units()->with('customer')->withCount([
                'claims as otvorene_reklamacije_count' => fn ($q) => $q->whereNotIn('status', ['Resena', 'Odbijena']),
            ])->where('arhiviran', false)->orderBy('oznaka')->get();

            $checkliste = $zgrada->checklists()->withCount([
                'items as ukupno_stavki',
                'items as reseno_stavki' => fn ($q) => $q->where('zavrseno', true),
            ])->get();

            $query = Document::with('unit')->where('zgrada_id', $zgrada->id);
            if ($tip = $request->get('tip')) {
                $query->where('tip', $tip);
            }
            if (!$request->boolean('arhiva')) {
                $query->where('aktivna_verzija', true);
            }
            $dokumenti = $query->orderByDesc('created_at')->get();

            $reklamacije = Claim::with('unit.building', 'odgovorni')
                ->whereIn('stan_id', $stanovi->pluck('id'))
                ->latest('datum_prijave')
                ->get();
        }

        $tipoviDokumenata = DocumentType::zaTenant()->orderBy('naziv')->get();
        $statusiProjekta = config('statusi.status_projekta');
        $statusiZgrade = config('statusi.status_zgrade');
        $statusiStana = config('statusi.status_stana');

        return view('projects.show', compact(
            'project', 'zgrada', 'tab', 'dokumenti', 'tipoviDokumenata', 'stanovi', 'checkliste',
            'reklamacije', 'statusiProjekta', 'statusiZgrade', 'statusiStana'
        ));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'naziv' => ['required', 'string', 'max:255'],
            'lokacija_adresa' => ['nullable', 'string', 'max:255'],
            'lokacija_grad' => ['nullable', 'string', 'max:255'],
            'tip' => ['nullable', FiksneListe::pravila('tip_projekta')],
            'broj_planiranih_stanova' => ['nullable', 'integer', 'min:1'],
            'datum_pocetka_gradnje' => ['nullable', 'date'],
            'planirani_datum_zavrsetka' => ['nullable', 'date'],
            'status' => ['required', FiksneListe::pravila('status_projekta')],
            'napomena' => ['nullable', 'string', 'max:5000'],
        ]);

        $staro = ['status' => $project->status];
        $project->update($data);

        if ($staro['status'] !== $project->status) {
            AuditLog::zabelezi('promena_statusa_projekta', $project, [
                'stari' => $staro['status'], 'novi' => $project->status,
            ]);
        }

        return back()->with('uspesno', 'Podaci projekta su sačuvani.');
    }

    public function destroy(Project $project)
    {
        // Hard delete samo Vlasnik (PRD 5), uz potvrdu na frontendu
        abort_unless(auth()->user()->uloga === 'Vlasnik', 403);
        AuditLog::zabelezi('obrisan_projekat', $project, ['naziv' => $project->naziv]);
        $project->delete();
        return redirect()->route('dashboard')->with('uspesno', 'Projekat je obrisan.');
    }
}
