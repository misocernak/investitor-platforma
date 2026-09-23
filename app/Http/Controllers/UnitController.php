<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Unit;
use App\Support\FiksneListe;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // Samostalan ekran Stanovi/Jedinice (PRD 9.3)
    public function index(Request $request)
    {
        $zgrade = Building::with('project')->where('arhiviran', false)->orderBy('naziv')->get();
        $zgrada = $zgrade->firstWhere('id', (int) $request->get('zgrada')) ?: $zgrade->first();

        $stanovi = collect();
        $statistika = [];
        if ($zgrada) {
            // Broj otvorenih reklamacija u istom upitu (umesto posebnog upita za svaki red)
            $svi = $zgrada->units()->with('customer')->withCount([
                'claims as otvorene_reklamacije_count' => fn ($q) => $q->whereNotIn('status', ['Resena', 'Odbijena']),
            ])->where('arhiviran', false)->orderBy('oznaka')->get();

            $statistika = [
                'ukupno' => $svi->count(),
                'prodato' => $svi->whereIn('status', ['Prodat_u_procesu_uknjizenja', 'Prodat_u_garanciji', 'Garancija_istekla'])->count(),
                'za_prodaju' => $svi->where('status', 'Za_prodaju')->count(),
                'rezervisan' => $svi->where('status', 'Rezervisan')->count(),
                'otvorene_reklamacije' => $svi->sum('otvorene_reklamacije_count'),
            ];

            // Filteri: pretraga (oznaka / kupac) i grupa statusa (klik na karticu)
            $stanovi = $svi;
            if ($q = trim((string) $request->get('q'))) {
                $stanovi = $stanovi->filter(fn ($s) => str_contains(mb_strtolower($s->oznaka.' '.($s->customer->ime_prezime ?? '')), mb_strtolower($q)));
            }
            $grupe = [
                'prodato' => ['Prodat_u_procesu_uknjizenja', 'Prodat_u_garanciji', 'Garancija_istekla'],
                'za_prodaju' => ['Za_prodaju'],
                'rezervisan' => ['Rezervisan'],
            ];
            if (isset($grupe[$request->get('grupa')])) {
                $stanovi = $stanovi->whereIn('status', $grupe[$request->get('grupa')]);
            }
            if ($request->get('grupa') === 'reklamacije') {
                $stanovi = $stanovi->where('otvorene_reklamacije_count', '>', 0);
            }
        }

        $statusiStana = config('statusi.status_stana');
        return view('units.index', compact('zgrade', 'zgrada', 'stanovi', 'statistika', 'statusiStana'));
    }

    public function store(Request $request, Building $building)
    {
        $data = $request->validate([
            'oznaka' => ['required', 'string', 'max:50'],
            'sprat' => ['nullable', 'string', 'max:50'],
            'kvadratura' => ['nullable', 'numeric', 'min:0'],
            'broj_soba' => ['nullable', 'integer', 'min:0', 'max:10'],
            'cena' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', FiksneListe::pravila('status_stana')],
            'kupac_ime' => ['nullable', 'string', 'max:255'],
            'kupac_email' => ['nullable', 'email', 'max:255'],
            'kupac_telefon' => ['nullable', 'string', 'max:50'],
        ]);

        $kupacId = null;
        if (!empty($data['kupac_ime'])) {
            $kupac = Customer::firstOrCreate(
                ['tenant_id' => $building->tenant_id, 'ime_prezime' => $data['kupac_ime']],
                ['email' => $data['kupac_email'] ?? null, 'telefon' => $data['kupac_telefon'] ?? null]
            );
            $kupacId = $kupac->id;
        }

        $stan = Unit::create([
            'zgrada_id' => $building->id,
            'oznaka' => $data['oznaka'],
            'sprat' => $data['sprat'] ?? null,
            'kvadratura' => $data['kvadratura'] ?? null,
            'broj_soba' => $data['broj_soba'] ?? null,
            'cena' => $data['cena'] ?? null,
            'status' => $data['status'],
            'kupac_id' => $kupacId,
        ]);

        AuditLog::zabelezi('kreiran_stan', $stan, ['oznaka' => $stan->oznaka]);

        return back()->with('uspesno', 'Jedinica "'.$stan->oznaka.'" je evidentirana.');
    }

    // Dosije jedinice (PRD 9.3): osnovni podaci, dokumenti, reklamacije — na zasebnoj stranici
    public function show(Unit $unit)
    {
        $unit->load('building.project', 'customer', 'documents', 'claims.odgovorni');

        return view('units.show', [
            'stan' => $unit,
            'statusiStana' => config('statusi.status_stana'),
            'tipoviDokumenata' => \App\Models\DocumentType::zaTenant()->orderBy('naziv')->get(),
            'tipoviProblema' => config('statusi.tip_problema'),
        ]);
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'oznaka' => ['required', 'string', 'max:50'],
            'sprat' => ['nullable', 'string', 'max:50'],
            'kvadratura' => ['nullable', 'numeric', 'min:0'],
            'broj_soba' => ['nullable', 'integer', 'min:0', 'max:10'],
            'cena' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', FiksneListe::pravila('status_stana')],
            'kupac_ime' => ['nullable', 'string', 'max:255'],
            'kupac_email' => ['nullable', 'email', 'max:255'],
            'kupac_telefon' => ['nullable', 'string', 'max:50'],
        ]);

        $stariStatus = $unit->status;
        $unit->fill(collect($data)->except(['kupac_ime', 'kupac_email', 'kupac_telefon'])->all());

        // Kupac se menja samo ako je forma poslala polja kupca (dosije stana)
        if ($request->has('kupac_ime')) {
            if (!empty($data['kupac_ime'])) {
                $kupac = Customer::firstOrCreate(
                    ['tenant_id' => $unit->tenant_id, 'ime_prezime' => $data['kupac_ime']]
                );
                $kupac->update(['email' => $data['kupac_email'] ?? null, 'telefon' => $data['kupac_telefon'] ?? null]);
                $unit->kupac_id = $kupac->id;
            } else {
                $unit->kupac_id = null;
            }
        }

        $unit->save();

        if ($stariStatus !== $unit->status) {
            AuditLog::zabelezi('promena_statusa_stana', $unit, [
                'stari' => $stariStatus, 'novi' => $unit->status,
            ]);
        }

        return back()->with('uspesno', 'Podaci jedinice su sačuvani.');
    }
}
