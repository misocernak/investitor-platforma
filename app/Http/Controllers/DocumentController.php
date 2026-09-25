<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentType;
use App\Support\FiksneListe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // Centralni registar dokumenata svih projekata (meni "Dokumentacija")
    public function index(Request $request)
    {
        $query = Document::with('project', 'building.project', 'unit')->orderByDesc('created_at');

        if ($q = trim((string) $request->get('q'))) {
            $query->where(fn ($w) => $w->where('naziv', 'like', "%{$q}%")->orWhere('izdavalac', 'like', "%{$q}%"));
        }
        if ($tip = $request->get('tip')) {
            $query->where('tip', $tip);
        }
        if ($zgradaId = $request->get('zgrada')) {
            $query->where('zgrada_id', $zgradaId);
        }
        if (!$request->boolean('arhiva')) {
            $query->where('aktivna_verzija', true);
        }

        return view('documents.index', [
            'dokumenti' => $query->get(),
            'tipoviDokumenata' => DocumentType::zaTenant()->orderBy('naziv')->get(),
            'zgrade' => \App\Models\Building::with('project')->where('arhiviran', false)->orderBy('naziv')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fajl' => ['required', 'file', 'mimes:pdf,dwg,jpg,jpeg,png,zip,docx,xlsx', 'max:51200'], // 50MB po PRD 6.5
            'tip' => ['required', 'string', 'max:100'],
            'naziv' => ['required', 'string', 'max:255'],
            'datum_izdavanja' => ['nullable', 'date'],
            'izdavalac' => ['nullable', 'string', 'max:255'],
            'projekat_id' => ['nullable', 'exists:projects,id'],
            'zgrada_id' => ['required_without:stan_id', 'nullable', 'exists:buildings,id'],
            'stan_id' => ['nullable', 'exists:units,id'],
            'vidljivo_kupcu' => ['nullable', 'boolean'],
        ]);

        // Dokument stana pripada i zgradi tog stana
        if (empty($data['zgrada_id']) && !empty($data['stan_id'])) {
            $data['zgrada_id'] = \App\Models\Unit::find($data['stan_id'])?->zgrada_id;
        }
        // Projekat se popunjava iz zgrade kad nije poslat (npr. unos iz opšte Dokumentacije)
        if (empty($data['projekat_id']) && !empty($data['zgrada_id'])) {
            $data['projekat_id'] = \App\Models\Building::find($data['zgrada_id'])?->projekat_id;
        }

        // Verzionisanje: nova verzija iste "linije" (isti tip + zgrada/stan + osnovni naziv)
        $postojeci = Document::where('tip', $data['tip'])
            ->when($data['zgrada_id'] ?? null, fn ($q, $v) => $q->where('zgrada_id', $v))
            ->when($data['stan_id'] ?? null, fn ($q, $v) => $q->where('stan_id', $v))
            ->when($data['projekat_id'] ?? null, fn ($q, $v) => $q->where('projekat_id', $v))
            ->orderByDesc('verzija')
            ->first();

        $verzija = $postojeci ? $postojeci->verzija + 1 : 1;

        if ($postojeci) {
            $postojeci->update(['aktivna_verzija' => false]);
        }

        $fajl = $request->file('fajl');
        $putanja = $fajl->store('dokumenti', 'documents'); // Storage facade - lako prebaciti na S3 (PRD 12.3)

        $dokument = Document::create([
            'projekat_id' => $data['projekat_id'] ?? null,
            'zgrada_id' => $data['zgrada_id'] ?? null,
            'stan_id' => $data['stan_id'] ?? null,
            'tip' => $data['tip'],
            'naziv' => $data['naziv'],
            'datum_izdavanja' => $data['datum_izdavanja'] ?? null,
            'izdavalac' => $data['izdavalac'] ?? null,
            'putanja_fajla' => $putanja,
            'verzija' => $verzija,
            'aktivna_verzija' => true,
            'vidljivo_kupcu' => $request->boolean('vidljivo_kupcu'),
        ]);

        AuditLog::zabelezi('upload_dokumenta', $dokument, [
            'naziv' => $dokument->naziv, 'tip' => $dokument->tip, 'verzija' => $verzija,
        ]);

        return back()->with('uspesno', 'Dokument "'.$dokument->naziv.'" (v'.$verzija.') je sačuvan.');
    }

    public function download(Document $document)
    {
        abort_unless($document->imaFajl(), 404);
        AuditLog::zabelezi('preuzimanje_dokumenta', $document, ['naziv' => $document->naziv]);
        return Storage::disk('documents')->download(
            $document->putanja_fajla,
            $document->naziv.'_v'.$document->verzija.'.'.pathinfo($document->putanja_fajla, PATHINFO_EXTENSION)
        );
    }

    /**
     * Preuzimanje za kupca sa Temelja: link potpisuje ova aplikacija (middleware "signed"), važi 5 minuta
     * i vezan je za stan. Pristup se ponovo proverava — ako je kupcu u međuvremenu uklonjen pristup
     * ili je dokument sakriven, link više ne radi.
     */
    public function preuzmiKupac(Request $request, int $dokument)
    {
        $stan = \App\Models\Unit::withoutGlobalScopes()->where('temelj_kupac_status', 'potvrdjen')->find((int) $request->query('stan'));
        $dok = $stan ? \App\Services\KupciNaTemelju::dokumentiKupca($stan)->find($dokument) : null;
        abort_unless($dok && $dok->imaFajl(), 404);

        return Storage::disk('documents')->download(
            $dok->putanja_fajla,
            $dok->naziv.'_v'.$dok->verzija.'.'.pathinfo($dok->putanja_fajla, PATHINFO_EXTENSION),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']
        );
    }

    /** Uključuje/isključuje vidljivost dokumenta kupcu na Temelju. */
    public function kupcu(Document $document)
    {
        $document->update(['vidljivo_kupcu' => ! $document->vidljivo_kupcu]);
        AuditLog::zabelezi($document->vidljivo_kupcu ? 'dokument_vidljiv_kupcu' : 'dokument_skriven_od_kupca', $document, ['naziv' => $document->naziv]);

        return back()->with('uspesno', $document->vidljivo_kupcu
            ? 'Kupac(i) sada vide dokument "'.$document->naziv.'" na Temelju.'
            : 'Dokument "'.$document->naziv.'" više nije vidljiv kupcu.');
    }

    public function destroy(Document $document)
    {
        // Brisanje: Vlasnik/Administrator (PRD 5); mički "arhiviranje verzije" = deaktivacija
        abort_unless(auth()->user()->mozeAdministrirati(), 403);
        $document->update(['aktivna_verzija' => false]);
        AuditLog::zabelezi('deaktivirana_verzija_dokumenta', $document, ['naziv' => $document->naziv]);
        return back()->with('uspesno', 'Verzija dokumenta je deaktivirana (ostaje u arhivi).');
    }
}
