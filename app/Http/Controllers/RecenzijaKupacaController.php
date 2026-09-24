<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\OglasiNaTemelju;
use App\Services\TemeljApi;
use Illuminate\Http\Request;

/**
 * Recenzije kupaca sa Temelja i javni odgovori firme. Recenzije se čuvaju samo na Temelju —
 * ovde se čitaju i odgovara se preko potpisanog API-ja (/api/v1/recenzije, /api/v1/recenzije/odgovor).
 */
class RecenzijaKupacaController extends Controller
{
    public function index(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $filter = $request->get('filter') === 'bez_odgovora' ? 'bez_odgovora' : 'sve';
        $podaci = null;
        $greska = null;

        if ($tenant->povezanSaTemeljem()) {
            [$ok, $odg, $kod] = TemeljApi::posalji('/api/v1/recenzije', ['tenant_id' => $tenant->id]);
            if ($ok) {
                $podaci = $odg;
            } elseif ($kod === 409) {
                OglasiNaTemelju::proveriVezu($tenant, true);
                $greska = 'Firma više nije povezana sa Temeljem.';
            } else {
                $greska = 'Temelj trenutno nije dostupan — pokušajte ponovo za minut.';
            }
        }

        $sve = collect($podaci['recenzije'] ?? []);
        $bezOdgovora = $sve->filter(fn ($r) => empty($r['odgovor']))->count();
        $recenzije = $filter === 'bez_odgovora' ? $sve->filter(fn ($r) => empty($r['odgovor']))->values() : $sve;

        return view('recenzije.index', compact('tenant', 'podaci', 'greska', 'recenzije', 'bezOdgovora', 'filter'));
    }

    public function odgovor(Request $request)
    {
        $data = $request->validate([
            'recenzija_id' => ['required', 'string', 'max:36'],
            'tekst' => ['nullable', 'string', 'max:1500'],
        ], [
            'tekst.max' => 'Odgovor može imati najviše 1500 znakova.',
        ]);

        $tenant = auth()->user()->tenant;
        abort_unless($tenant->povezanSaTemeljem(), 409);
        $tekst = trim((string) ($data['tekst'] ?? ''));

        [$ok, $odg] = TemeljApi::posalji('/api/v1/recenzije/odgovor', [
            'tenant_id' => $tenant->id,
            'recenzija_id' => $data['recenzija_id'],
            'tekst' => $tekst,
        ]);
        if (! $ok) {
            return back()->withErrors(['tekst' => $odg['greska'] ?? 'Temelj trenutno nije dostupan — pokušajte ponovo za minut.']);
        }
        AuditLog::zabelezi($tekst === '' ? 'obrisan_odgovor_na_recenziju' : 'odgovor_na_recenziju', $tenant, ['recenzija' => $data['recenzija_id']]);

        return back()->with('uspesno', $tekst === ''
            ? 'Odgovor je obrisan sa Temelja.'
            : 'Odgovor je objavljen na Temelju, ispod recenzije.');
    }
}
