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

    /** Spisak dokumenata za kupca stana (samo potvrđen kupac, samo dokumenti označeni "vidljivo kupcu"). */
    public function kupacDokumenti(Request $request)
    {
        abort_unless(TemeljApi::ispravanZahtev($request), 401);
        $stan = \App\Services\KupciNaTemelju::potvrdjenStan((int) $request->input('tenant_id'), (int) $request->input('stan_id'));
        if (! $stan) {
            return response()->json(['ok' => false, 'greska' => 'Nema pristupa.'], 404);
        }
        $dokumenti = \App\Services\KupciNaTemelju::dokumentiKupca($stan)
            ->orderByRaw('stan_id IS NULL')->orderByDesc('datum_izdavanja')->orderByDesc('id')
            ->get(['id', 'stan_id', 'tip', 'naziv', 'datum_izdavanja', 'izdavalac', 'verzija', 'putanja_fajla'])
            ->map(fn ($d) => [
                'id' => $d->id,
                'nivo' => $d->stan_id ? 'stan' : 'zgrada',
                'tip' => \App\Support\Prikaz::label($d->tip),
                'naziv' => $d->naziv,
                'datum' => $d->datum_izdavanja?->format('d.m.Y.'),
                'izdavalac' => $d->izdavalac,
                'verzija' => $d->verzija,
                'format' => strtoupper(pathinfo((string) $d->putanja_fajla, PATHINFO_EXTENSION)),
            ])->values();

        return response()->json(['ok' => true, 'dokumenti' => $dokumenti]);
    }

    /** Kratkotrajan potpisan link za preuzimanje jednog dokumenta (važi 5 minuta, vezan za stan). */
    public function kupacDokumentLink(Request $request)
    {
        abort_unless(TemeljApi::ispravanZahtev($request), 401);
        $stan = \App\Services\KupciNaTemelju::potvrdjenStan((int) $request->input('tenant_id'), (int) $request->input('stan_id'));
        $dok = $stan ? \App\Services\KupciNaTemelju::dokumentiKupca($stan)->find((int) $request->input('dokument_id')) : null;
        if (! $dok || ! $dok->imaFajl()) {
            return response()->json(['ok' => false, 'greska' => 'Dokument nije dostupan.'], 404);
        }
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute('temelj.dokument', now()->addMinutes(5), [
            'dokument' => $dok->id, 'stan' => $stan->id,
        ]);

        return response()->json(['ok' => true, 'url' => $url]);
    }

    /** Sve za stranicu "Moj stan" na Temelju (dokumenti + reklamacije + tipovi problema) u jednom pozivu. */
    public function kupacPregled(Request $request)
    {
        abort_unless(TemeljApi::ispravanZahtev($request), 401);
        $stan = \App\Services\KupciNaTemelju::potvrdjenStan((int) $request->input('tenant_id'), (int) $request->input('stan_id'));
        if (! $stan) {
            return response()->json(['ok' => false, 'greska' => 'Nema pristupa.'], 404);
        }

        return response()->json(['ok' => true] + \App\Services\KupciNaTemelju::pregled($stan));
    }

    /** Kupac prijavljuje reklamaciju sa Temelja. */
    public function kupacReklamacija(Request $request)
    {
        abort_unless(TemeljApi::ispravanZahtev($request), 401);
        $stan = \App\Services\KupciNaTemelju::potvrdjenStan((int) $request->input('tenant_id'), (int) $request->input('stan_id'));
        if (! $stan) {
            return response()->json(['ok' => false, 'greska' => 'Nema pristupa.'], 404);
        }
        $opis = trim((string) $request->input('opis'));
        $tipDrugo = mb_substr(trim((string) $request->input('tip_drugo')), 0, 120) ?: null;
        if (mb_strlen($opis) < 10 || mb_strlen($opis) > 3000) {
            return response()->json(['ok' => false, 'greska' => 'Opišite problem (od 10 do 3000 znakova).'], 422);
        }
        [$rek, $greska] = \App\Services\KupciNaTemelju::prijaviReklamaciju($stan, (string) $request->input('tip'), $tipDrugo, $opis);
        if (! $rek) {
            return response()->json(['ok' => false, 'greska' => $greska], 422);
        }

        return response()->json(['ok' => true, 'broj' => 'REK-'.$rek->id]);
    }

    /** Poruka kupca na reklamaciji. */
    public function kupacPoruka(Request $request)
    {
        abort_unless(TemeljApi::ispravanZahtev($request), 401);
        $stan = \App\Services\KupciNaTemelju::potvrdjenStan((int) $request->input('tenant_id'), (int) $request->input('stan_id'));
        if (! $stan) {
            return response()->json(['ok' => false, 'greska' => 'Nema pristupa.'], 404);
        }
        $tekst = trim((string) $request->input('tekst'));
        if ($tekst === '' || mb_strlen($tekst) > 2000) {
            return response()->json(['ok' => false, 'greska' => 'Poruka može imati do 2000 znakova.'], 422);
        }
        $greska = \App\Services\KupciNaTemelju::porukaKupca($stan, (int) $request->input('reklamacija_id'), $tekst);

        return $greska
            ? response()->json(['ok' => false, 'greska' => $greska], 422)
            : response()->json(['ok' => true]);
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
