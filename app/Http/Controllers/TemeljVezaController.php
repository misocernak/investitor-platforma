<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\OglasiNaTemelju;
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
}
