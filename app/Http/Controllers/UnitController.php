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
        $zgrade = Building::with('project')->where('arhiviran', false)->get();
        $zgrada = $zgrade->firstWhere('id', (int) $request->get('zgrada'));
        $zgrada = $zgrada ?: $zgrade->first();

        $stanovi = collect();
        $statistika = [];
        if ($zgrada) {
            $zgrada->load('units.customer');
            $stanovi = $zgrada->units;
            $statistika = [
                'ukupno' => $stanovi->count(),
                'za_prodaju' => $stanovi->where('status', 'Za_prodaju')->count(),
                'rezervisan' => $stanovi->where('status', 'Rezervisan')->count(),
                'uknjizenje_garancija' => $stanovi->whereIn('status', ['Prodat_u_procesu_uknjizenja', 'Prodat_u_garanciji'])->count(),
                'garancija_istekla' => $stanovi->where('status', 'Garancija_istekla')->count(),
                'otvorene_reklamacije' => $zgrada->units->sum(fn ($u) => $u->otvoreneReklamacije()->count()),
            ];
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

    public function show(Unit $unit)
    {
        $unit->load('customer', 'documents', 'claims.odgovorni');
        return response()->json([
            'id' => $unit->id,
            'oznaka' => $unit->oznaka,
            'sprat' => $unit->sprat,
            'kvadratura' => $unit->kvadratura,
            'broj_soba' => $unit->broj_soba,
            'cena' => $unit->cena,
            'status' => $unit->status,
            'kupac' => $unit->customer?->ime_prezime,
            'kupac_email' => $unit->customer?->email,
            'kupac_telefon' => $unit->customer?->telefon,
            'dokumenti' => $unit->documents->map(fn ($d) => [
                'id' => $d->id, 'naziv' => $d->naziv, 'tip' => $d->tip, 'ima_fajl' => $d->imaFajl(),
            ]),
            'reklamacije' => $unit->claims->map(fn ($c) => [
                'id' => $c->id, 'tip' => $c->tip_problema, 'status' => $c->status,
                'datum' => $c->datum_prijave?->format('d.m.Y.'),
            ]),
            'otvorene_reklamacije' => $unit->otvoreneReklamacije()->count(),
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
        ]);

        $stariStatus = $unit->status;
        $unit->update($data);

        if ($stariStatus !== $unit->status) {
            AuditLog::zabelezi('promena_statusa_stana', $unit, [
                'stari' => $stariStatus, 'novi' => $unit->status,
            ]);
        }

        return back()->with('uspesno', 'Podaci jedinice su sačuvani.');
    }
}
