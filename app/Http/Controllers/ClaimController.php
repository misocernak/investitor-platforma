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
            // Grupe: "otvorene" = nisu rešene ni odbijene; "u_radu" = u obradi ili dodeljene
            match ($status) {
                'otvorene' => $query->whereNotIn('status', ['Resena', 'Odbijena']),
                'u_radu' => $query->whereIn('status', ['U_obradi', 'Dodeljena']),
                default => $query->where('status', $status),
            };
        }
        if ($request->get('rok') === 'kasni') {
            $query->whereNotIn('status', ['Resena', 'Odbijena'])
                ->whereNotNull('rok_resavanja')->where('rok_resavanja', '<', now()->toDateString());
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
                ->whereYear('updated_at', now()->year)->whereMonth('updated_at', now()->month)->count(),
        ];

        $zgrade = \App\Models\Building::where('arhiviran', false)->orderBy('naziv')->get();
        $stanovi = Unit::with('building')->where('arhiviran', false)->orderBy('oznaka')->get();
        $statusi = config('statusi.status_reklamacije');
        $tipovi = config('statusi.tip_problema');

        return view('claims.index', compact(
            'reklamacije', 'metrike', 'zgrade', 'stanovi', 'statusi', 'tipovi'
        ));
    }

    // Detalj reklamacije na zasebnoj stranici (umesto bočnog panela)
    public function show(Claim $claim)
    {
        $user = auth()->user();
        abort_if($user->jeNadzor() && $claim->odgovorni_id !== $user->id, 403);

        $claim->load('notes.user', 'files', 'customer', 'odgovorni', 'unit.building.project', 'unit.customer');

        return view('claims.show', [
            'rek' => $claim,
            'statusi' => config('statusi.status_reklamacije'),
            'nadzorUsers' => User::where('uloga', 'Nadzor_izvodjac')->where('status_naloga', 'Aktivan')->orderBy('ime_prezime')->get(),
        ]);
    }

    // Prikaz priloženog fajla (fotografija / PDF) — samo za korisnike koji vide tu reklamaciju
    public function prilog(\App\Models\ClaimFile $file)
    {
        $claim = Claim::find($file->claim_id); // tenant filter: druga firma dobija 404
        abort_unless($claim, 404);
        $user = auth()->user();
        abort_if($user->jeNadzor() && $claim->odgovorni_id !== $user->id, 403);
        abort_unless(Storage::disk('documents')->exists($file->putanja_fajla), 404);

        return Storage::disk('documents')->response($file->putanja_fajla, $file->originalni_naziv);
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

        return redirect()->route('claims.show', $reklamacija)
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
            // Polja poslata iz forme se upisuju i kad su prazna (npr. "Nedodeljeno" skida odgovornog)
            $izmene = [];
            if (!empty($data['status'])) {
                $izmene['status'] = $data['status'];
            }
            foreach (['odgovorni_id', 'rok_resavanja'] as $polje) {
                if ($request->exists($polje)) {
                    $izmene[$polje] = $data[$polje] ?? null;
                }
            }
            $claim->update($izmene);
        }

        AuditLog::zabelezi('izmena_reklamacije', $claim, array_filter($data));
        if ($claim->wasChanged('status')) {
            \App\Services\KupciNaTemelju::obavestiKupca($claim, 'status');
        }

        return back()->with('uspesno', 'Reklamacija je ažurirana.');
    }

    public function dodajBelesku(Request $request, Claim $claim)
    {
        $data = $request->validate([
            'tekst' => ['required', 'string', 'max:2000'],
            'vidljivo_kupcu' => ['nullable', 'boolean'],
        ]);
        // Nadzor/izvođač piše samo interne beleške; poruku kupcu šalje tim firme
        $kupcu = $request->boolean('vidljivo_kupcu') && ! auth()->user()->jeNadzor();

        $claim->notes()->create([
            'user_id' => auth()->id(),
            'tekst' => $data['tekst'],
            'vidljivo_kupcu' => $kupcu,
        ]);
        if ($kupcu) {
            \App\Services\KupciNaTemelju::obavestiKupca($claim, 'poruka');
        }

        return back()->with('uspesno', $kupcu ? 'Poruka je poslata kupcu — vidi je na Temelju.' : 'Beleška je dodata.');
    }
}
