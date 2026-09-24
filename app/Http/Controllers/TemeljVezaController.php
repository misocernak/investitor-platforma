<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\OglasiNaTemelju;
use App\Services\TemeljApi;
use Illuminate\Http\Request;

// Povezivanje firme sa profilom investitora na Temelj.rs (jednom; odobrava Temelj)
class TemeljVezaController extends Controller
{
    public function zatrazi(Request $request)
    {
        $data = $request->validate([
            'maticni_broj' => ['required', 'digits:8'],
        ], [
            'maticni_broj.digits' => 'Matični broj firme ima tačno 8 cifara (vidi rešenje APR-a).',
        ]);

        $tenant = auth()->user()->tenant;
        $tenant->update(['maticni_broj' => $data['maticni_broj']]);
        $poruka = OglasiNaTemelju::zatraziVezu($tenant);
        AuditLog::zabelezi('zahtev_veza_temelj', $tenant, ['mb' => $data['maticni_broj']]);

        return back()->with('uspesno', $poruka);
    }

    public function proveri()
    {
        OglasiNaTemelju::proveriVezu(auth()->user()->tenant, true);
        return back();
    }

    /** Profil firme na Temelju (opis i veb-sajt). Podaci se čuvaju na Temelju — ovde se samo uređuju. */
    public function profil()
    {
        $tenant = auth()->user()->tenant;
        $profil = null;
        $greska = null;
        if ($tenant->povezanSaTemeljem()) {
            [$ok, $odg, $kod] = TemeljApi::posalji('/api/v1/profil', ['tenant_id' => $tenant->id, 'akcija' => 'citaj']);
            if ($ok) {
                $profil = $odg;
            } elseif ($kod === 409) {
                OglasiNaTemelju::proveriVezu($tenant, true);
                $greska = 'Firma više nije povezana sa Temeljem.';
            } else {
                $greska = 'Temelj trenutno nije dostupan — pokušajte ponovo za minut.';
            }
        }

        return view('oglasi.profil', compact('tenant', 'profil', 'greska'));
    }

    public function sacuvajProfil(Request $request)
    {
        $data = $request->validate([
            'opis' => ['nullable', 'string', 'max:2000'],
            'sajt' => ['nullable', 'string', 'max:255'],
        ], [
            'opis.max' => 'Opis može imati najviše 2000 znakova.',
        ]);

        $tenant = auth()->user()->tenant;
        if (! $tenant->povezanSaTemeljem()) {
            return redirect()->route('temelj.profil');
        }
        [$ok, $odg] = TemeljApi::posalji('/api/v1/profil', [
            'tenant_id' => $tenant->id,
            'akcija' => 'sacuvaj',
            'opis' => (string) ($data['opis'] ?? ''),
            'sajt' => (string) ($data['sajt'] ?? ''),
        ]);
        if (! $ok) {
            return back()->withInput()->withErrors(['opis' => $odg['greska'] ?? 'Temelj trenutno nije dostupan — pokušajte ponovo za minut.']);
        }
        AuditLog::zabelezi('profil_temelj_izmenjen', $tenant, []);

        return redirect()->route('temelj.profil')->with('uspesno', 'Sačuvano — profil na Temelju je odmah ažuriran.');
    }
}
