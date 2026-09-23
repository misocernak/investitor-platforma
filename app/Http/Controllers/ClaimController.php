<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Claim;
use App\Models\ClaimNote;
use App\Models\Unit;
use App\Models\User;
use App\Support\FiksneListe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClaimController extends Controller
{
    // PRD 9.5: tabela + filteri (zgrada, status, tip problema) + detaljni prikaz
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Claim::with('unit.building.project', 'odgovorni')->latest('datum_prijave');

        // Nadzor/izvodjac vidi samo svoje dodele (PRD 5)
        if ($user->jeNadzor()) {
            $query->where('odgovorni_id', $user->id);
        }

        if ($zgradaId = $request->get('zgrada')) {
            $query->whereHas('unit', fn ($q) => $q->where('zgrada_id', $zgradaId));
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($tip = $request->get('tip')) {
            $query->where('tip_problema', $tip);
        }
        if ($pretraga = $request->get('q')) {
            $query->where(function ($q) use ($pretraga) {
                $q->where('opis', 'like', "%{$pretraga}%")
                  ->orWhereHas('unit', fn ($u) => $u->where('oznaka', 'like', "%{$pretraga}%"));
            });
        }

        $reklamacije = $query->get();

        // Karte metrika (PRD dizajn) - sabloni sa istom logikom filtra
        $osnovniQuery = Claim::query();
        if ($user->jeNadzor()) {
            $osnovniQuery->where('odgovorni_id', $user->id);
        }
        $metrike = [
            'prijavljene' => (clone $osnovniQuery)->where('status', 'Prijavljena')->count(),
            'u_obradi' => (clone $osnovniQuery)->whereIn('status', ['U_obradi', 'Dodeljena'])->count(),
            'prekoracen_rok' => (clone $osnovniQuery)->whereNotIn('status', ['Resena', 'Odbijena'])
                ->whereNotNull('rok_resavanja')->where('rok_resavanja', '<', now()->toDateString())->count(),
            'resene_mesec' => (clone $osnovniQuery)->where('status', 'Resena')
                ->whereMonth('updated_at', now()->month)->count(),
        ];

        // Izabrana reklamacija za detaljni prikaz
        $izabrana = null;
        if ($id = $request->get('reklamacija')) {
            $izabrana = $reklamacije->firstWhere('id', (int) $id) ?? $reklamacije->first();
        } else {
            $izabrana = $reklamacije->first();
        }

        if ($izabrana) {
            $izabrana->load('notes.user', 'files', 'customer', 'unit.building.project');
        }

        $zgrade = \App\Models\Building::where('arhiviran', false)->get();
        $nadzorUsers = User::where('uloga', 'Nadzor_izvodjac')->where('status_naloga', 'Aktivan')->get();
        $stanovi = Unit::with('building')->where('arhiviran', false)->orderBy('oznaka')->get();
        $statusi = config('statusi.status_reklamacije');
        $tipovi = config('statusi.tip_problema');

        return view('claims.index', compact(
            'reklamacije', 'metrike', 'izabrana', 'zgrade', 'nadzorUsers', 'stanovi', 'statusi', 'tipovi'
        ));
    }

    // PRD 10.3: interni unos reklamacije u ime kupca
    public function store(Request $request)
    {
        $data = $request->validate([
            'stan_id' => ['required', 'exists:units,id'],
            'tip_problema' => ['required', FiksneListe::pravila('tip_problema')],
            'tip_problema_drugo' => ['nullable', 'required_if:tip_problema,Drugo', 'string', 'max:255'],
            'opis' => ['required', 'string'],
            'datum_prijave' => ['required', 'date'], // rucni unos, default danas
            'prilog.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,heic,pdf', 'max:10240'],
        ]);

        $reklamacija = Claim::create([
            'stan_id' => $data['stan_id'],
            'datum_prijave' => $data['datum_prijave'],
            'tip_problema' => $data['tip_problema'],
            'tip_problema_drugo' => $data['tip_problema'] === 'Drugo' ? ($data['tip_problema_drugo'] ?? null) : null,
            'opis' => $data['opis'],
            'status' => 'Prijavljena', // PRD 10.3
        ]);

        if ($request->hasFile('prilog')) {
            foreach ($request->file('prilog') as $fajl) {
                $reklamacija->files()->create([
                    'putanja_fajla' => $fajl->store('reklamacije', 'documents'),
                    'originalni_naziv' => $fajl->getClientOriginalName(),
                ]);
            }
        }

        AuditLog::zabelezi('kreirana_reklamacija', $reklamacija, [
            'stan_id' => $reklamacija->stan_id, 'tip' => $reklamacija->tip_problema,
        ]);

        return redirect()->route('claims.index', ['reklamacija' => $reklamacija->id])
            ->with('uspesno', 'Reklamacija je evidentirana u statusu "Prijavljena".');
    }

    // Rucne izmene: status, odgovorni, rok (PRD 2.2 / 2.3 / 2.5 / 9.5)
    public function update(Request $request, Claim $claim)
    {
        $user = auth()->user();

        $data = $request->validate([
            'status' => ['nullable', FiksneListe::pravila('status_reklamacije')],
            'odgovorni_id' => ['nullable', 'exists:users,id'],
            'rok_resavanja' => ['nullable', 'date'],
        ]);

        // Nadzor sme samo status na sopstvenim dodelama (PRD 5)
        if ($user->jeNadzor()) {
            abort_unless($claim->odgovorni_id === $user->id, 403);
            $claim->update(['status' => $data['status'] ?? $claim->status]);
        } else {
            $claim->update(array_filter([
                'status' => $data['status'] ?? null,
                'odgovorni_id' => $data['odgovorni_id'] ?? null,
                'rok_resavanja' => $data['rok_resavanja'] ?? null,
            ], fn ($v) => $v !== null));
        }

        AuditLog::zabelezi('izmena_reklamacije', $claim, array_filter($data));

        return back()->with('uspesno', 'Reklamacija je ažurirana.');
    }

    public function dodajBelesku(Request $request, Claim $claim)
    {
        $data = $request->validate(['tekst' => ['required', 'string', 'max:2000']]);

        $claim->notes()->create([
            'user_id' => auth()->id(),
            'tekst' => $data['tekst'],
        ]);

        return back()->with('uspesno', 'Beleška je dodata.');
    }
}
