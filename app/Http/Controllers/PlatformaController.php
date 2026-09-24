<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Services\Registracija;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin platforme: zahtevi za registraciju i nalozi firmi.
 * Vidi samo podatke firme i kontakt lica — nikad projekte, stanove, reklamacije ni upite firmi.
 */
class PlatformaController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $firme = Tenant::with('vlasnik')
            ->withCount('users')
            ->when(isset(Tenant::STATUSI[$status]), fn ($q) => $q->where('status', $status))
            ->orderByRaw("status = 'na_cekanju' DESC")
            ->latest('registrovan_at')->latest('id')
            ->get();
        $brojevi = Tenant::selectRaw('status, COUNT(*) AS n')->groupBy('status')->pluck('n', 'status');

        return view('platforma.index', compact('firme', 'status', 'brojevi'));
    }

    public function show(Tenant $firma)
    {
        $firma->load('vlasnik')->loadCount('users');
        return view('platforma.show', compact('firma'));
    }

    public function odobri(Tenant $firma)
    {
        abort_if($firma->status === 'aktivan', 422, 'Firma je već aktivna.');
        return redirect()->route('platforma.show', $firma)->with('uspesno', Registracija::odobri($firma));
    }

    public function odbij(Request $request, Tenant $firma)
    {
        $razlog = $request->validate(['razlog' => ['required', 'string', 'max:255']], [
            'razlog.required' => 'Upišite razlog — dobija ga osoba koja se registrovala.',
        ])['razlog'];
        Registracija::odbij($firma, $razlog);
        return redirect()->route('platforma.index')->with('uspesno', 'Zahtev je odbijen i podnosiocu je poslat email.');
    }

    /** Suspenzija blokira pristup aplikaciji (npr. neplaćanje, zloupotreba); ponovno aktiviranje ga vraća. */
    public function suspenzija(Tenant $firma)
    {
        abort_unless(in_array($firma->status, ['aktivan', 'suspendovan'], true), 422);
        $novi = $firma->status === 'aktivan' ? 'suspendovan' : 'aktivan';
        $firma->forceFill(['status' => $novi])->save();
        AuditLog::zabelezi($novi === 'suspendovan' ? 'suspendovana_firma' : 'reaktivirana_firma', $firma);

        return back()->with('uspesno', $novi === 'suspendovan'
            ? 'Nalog firme je suspendovan — korisnici ne mogu da pristupe aplikaciji.'
            : 'Nalog firme je ponovo aktivan.');
    }

    /** Raskid vlasništva nad profilom na Temelju — oslobađa matični broj za pravog vlasnika. */
    public function raskini(Request $request, Tenant $firma)
    {
        abort_unless(in_array($firma->status, ['aktivan', 'suspendovan'], true), 422);
        $data = $request->validate([
            'razlog' => ['required', 'string', 'max:255'],
            'obrisi_opis' => ['nullable', 'boolean'],
        ], [
            'razlog.required' => 'Upišite razlog — dobija ga vlasnik naloga.',
        ]);
        [$ok, $poruka] = Registracija::raskini($firma, $data['razlog'], (bool) ($data['obrisi_opis'] ?? false));

        return $ok
            ? redirect()->route('platforma.show', $firma)->with('uspesno', $poruka)
            : back()->withInput()->withErrors(['razlog' => $poruka]);
    }

    public function ovlascenje(Tenant $firma)
    {
        abort_unless($firma->ovlascenje_putanja && Storage::disk('documents')->exists($firma->ovlascenje_putanja), 404);
        AuditLog::zabelezi('pregled_ovlascenja', $firma);
        return Storage::disk('documents')->response($firma->ovlascenje_putanja, $firma->ovlascenje_naziv);
    }
}
