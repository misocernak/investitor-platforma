<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\OglasiNaTemelju;
use App\Services\TemeljApi;
use Illuminate\Http\Request;

// Zahtevi koje šalje Temelj.rs (server–server, potpisani HMAC-om)
class TemeljController extends Controller
{
    /** Novi upit kupca za stan. */
    public function upit(Request $request)
    {
        abort_unless(TemeljApi::ispravanZahtev($request), 401);
        $tenant = Tenant::find((int) $request->input('tenant_id'));
        if (! $tenant) {
            return response()->json(['ok' => false, 'greska' => 'Nepoznata firma.'], 404);
        }
        $upit = OglasiNaTemelju::sacuvajUpit($tenant->id, (array) $request->input('upit', []));

        return response()->json(['ok' => (bool) $upit]);
    }

    /** Obaveštenje da je kupac potvrdio stan na Temelju. */
    public function kupac(Request $request)
    {
        abort_unless(TemeljApi::ispravanZahtev($request), 401);
        if ($request->input('status') === 'potvrdjen') {
            \App\Services\KupciNaTemelju::upisiPotvrdu((int) $request->input('tenant_id'), (int) $request->input('stan_id'));
        }

        return response()->json(['ok' => true]);
    }

    /** Obaveštenje da je veza firme odobrena, odbijena ili opozvana. */
    public function veza(Request $request)
    {
        abort_unless(TemeljApi::ispravanZahtev($request), 401);
        $tenant = Tenant::find((int) $request->input('tenant_id'));
        if (! $tenant) {
            return response()->json(['ok' => false], 404);
        }
        $podaci = $request->only(['status', 'razlog', 'profil_url']);
        // Slanje oglasa koji čekaju ide posle odgovora, da Temelj ne čeka
        dispatch(fn () => OglasiNaTemelju::upisiStanjeVeze(Tenant::find($tenant->id), $podaci))->afterResponse();

        return response()->json(['ok' => true]);
    }
}
