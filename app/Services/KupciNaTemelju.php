<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Claim;
use App\Models\ClaimNote;
use App\Models\Document;
use App\Support\Prikaz;
use App\Models\Tenant;
use App\Models\Unit;

/**
 * Kupac stana na Temelju. Kad je stan prodat i upisan je email kupca, Temelj šalje kupcu poziv
 * (jednokratan link). Kupac potvrđuje stan svojim nalogom na Temelju; tada Temelj javlja "potvrdjen".
 * Temelj nikad ne javlja da li adresa već ima nalog — ovde se vidi samo "poziv poslat" / "potvrdio".
 */
class KupciNaTemelju
{
    /** Statusi u kojima stan nema kupca (ostali znače da je prodat). */
    public const NEPRODAT = ['Za_prodaju', 'Rezervisan'];

    /** Posle izmene stana (dosije, brza promena statusa) — poziv, osvežavanje ili uklanjanje pristupa. */
    public static function posleIzmeneStana(int $stanId, bool $promenjenStatus): void
    {
        $stan = self::stan($stanId);
        if (! $stan || ! self::moze($stan)) {
            return;
        }
        $email = mb_strtolower(trim((string) $stan->customer?->email));
        $prodat = $stan->status && ! in_array($stan->status, self::NEPRODAT, true);

        if ($prodat && $email !== '') {
            if ($stan->temelj_kupac_email !== $email || ! $stan->temelj_kupac_status) {
                self::posalji($stan, 'poziv', $email);
            } elseif ($promenjenStatus) {
                self::posalji($stan, 'status');
            }
        } elseif ($stan->temelj_kupac_status) {
            // Stan vraćen u prodaju ili je kupac obrisan — prethodni kupac gubi pristup
            self::posalji($stan, 'ukloni');
        }
    }

    /** Ručno ponovno slanje poziva (npr. kupac nije dobio mejl). */
    public static function ponoviPoziv(Unit $stan): bool
    {
        $email = mb_strtolower(trim((string) $stan->customer?->email));
        return $email !== '' && self::moze($stan) && self::posalji($stan, 'poziv', $email);
    }

    public static function ukloniPristup(Unit $stan): bool
    {
        return self::moze($stan) && self::posalji($stan, 'ukloni');
    }

    /**
     * Stan čiji je kupac potvrdio pristup na Temelju (inače null) — jedina ulazna tačka za dokumente kupca.
     * Temelj šalje tenant_id + stan_id tek pošto proveri da stan pripada prijavljenom kupcu; ovde se to
     * dodatno proverava sa strane investitora (stan te firme, kupac potvrdio).
     */
    public static function potvrdjenStan(int $tenantId, int $stanId): ?Unit
    {
        return Unit::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('temelj_kupac_status', 'potvrdjen')->find($stanId);
    }

    /** Aktivne verzije dokumenata koje kupac tog stana sme da vidi: dokumenti njegove zgrade i njegovog stana. */
    public static function dokumentiKupca(Unit $stan)
    {
        return Document::withoutGlobalScopes()
            ->where('tenant_id', $stan->tenant_id)
            ->where('aktivna_verzija', true)
            ->where('vidljivo_kupcu', true)
            ->where(fn ($q) => $q->where('stan_id', $stan->id)
                ->orWhere(fn ($w) => $w->whereNull('stan_id')->where('zgrada_id', $stan->zgrada_id)));
    }

    // ------------------------------------------------------------------
    //  Reklamacije kupca (prijava i prepiska sa Temelja)
    // ------------------------------------------------------------------

    /** Reklamacije koje kupac vidi: njegov stan i on kao kupac (ne reklamacije prethodnog vlasnika). */
    public static function reklamacijeKupca(Unit $stan)
    {
        return Claim::withoutGlobalScopes()
            ->where('tenant_id', $stan->tenant_id)
            ->where('stan_id', $stan->id)
            ->when($stan->kupac_id, fn ($q) => $q->where('kupac_id', $stan->kupac_id), fn ($q) => $q->whereRaw('1 = 0'));
    }

    /** Sve za stranicu "Moj stan" na Temelju u jednom odgovoru: dokumenti, reklamacije sa porukama, tipovi problema. */
    public static function pregled(Unit $stan): array
    {
        $dokumenti = self::dokumentiKupca($stan)
            ->orderByRaw('stan_id IS NULL')->orderByDesc('datum_izdavanja')->orderByDesc('id')
            ->get(['id', 'stan_id', 'tip', 'naziv', 'datum_izdavanja', 'izdavalac', 'verzija', 'putanja_fajla'])
            ->map(fn ($d) => [
                'id' => $d->id,
                'nivo' => $d->stan_id ? 'stan' : 'zgrada',
                'tip' => Prikaz::label($d->tip),
                'naziv' => $d->naziv,
                'datum' => $d->datum_izdavanja?->format('d.m.Y.'),
                'izdavalac' => $d->izdavalac,
                'verzija' => $d->verzija,
                'format' => strtoupper(pathinfo((string) $d->putanja_fajla, PATHINFO_EXTENSION)),
            ])->values();

        $reklamacije = self::reklamacijeKupca($stan)
            ->with(['notes' => fn ($q) => $q->where(fn ($w) => $w->where('vidljivo_kupcu', true)->orWhere('od_kupca', true))->reorder('created_at')])
            ->latest('id')->limit(50)->get()
            ->map(fn (Claim $r) => [
                'id' => $r->id,
                'broj' => 'REK-'.$r->id,
                'tip' => $r->tip_problema === 'Drugo' && $r->tip_problema_drugo ? $r->tip_problema_drugo : Prikaz::label($r->tip_problema),
                'opis' => $r->opis,
                'status' => $r->status,
                'status_naziv' => Prikaz::label($r->status),
                'zatvorena' => in_array($r->status, ['Resena', 'Odbijena'], true),
                'datum' => $r->datum_prijave?->format('d.m.Y.'),
                'rok' => $r->rok_resavanja?->format('d.m.Y.'),
                'poruke' => $r->notes->map(fn (ClaimNote $n) => [
                    'od' => $n->od_kupca ? 'kupac' : 'investitor',
                    'tekst' => $n->tekst,
                    'datum' => $n->created_at?->format('d.m.Y. H:i'),
                ])->values(),
            ])->values();

        $tipovi = collect(config('statusi.tip_problema'))->mapWithKeys(fn ($t) => [$t => Prikaz::label($t)]);

        return ['dokumenti' => $dokumenti, 'reklamacije' => $reklamacije, 'tipovi' => $tipovi];
    }

    /** Nova reklamacija kupca sa Temelja. Vraća [reklamacija|null, greška|null]. */
    public static function prijaviReklamaciju(Unit $stan, string $tip, ?string $tipDrugo, string $opis): array
    {
        if (! $stan->kupac_id) {
            return [null, 'Investitor još nije upisao kupca za ovaj stan.'];
        }
        if (! in_array($tip, config('statusi.tip_problema'), true)) {
            return [null, 'Izaberite vrstu problema.'];
        }
        $danas = Claim::withoutGlobalScopes()->where('stan_id', $stan->id)->where('izvor', 'temelj')
            ->where('created_at', '>', now()->subDay())->count();
        if ($danas >= 5) {
            return [null, 'Za danas ste prijavili dovoljno reklamacija za ovaj stan — dopunite postojeće porukom ili pokušajte sutra.'];
        }

        $rek = new Claim([
            'stan_id' => $stan->id,
            'kupac_id' => $stan->kupac_id,
            'datum_prijave' => now()->toDateString(),
            'tip_problema' => $tip,
            'tip_problema_drugo' => $tip === 'Drugo' ? $tipDrugo : null,
            'opis' => $opis,
            'status' => 'Prijavljena',
            'izvor' => 'temelj',
        ]);
        $rek->tenant_id = $stan->tenant_id;
        $rek->save();
        AuditLog::create([
            'tenant_id' => $stan->tenant_id, 'user_id' => null, 'akcija' => 'reklamacija_sa_temelja',
            'model_type' => Claim::class, 'model_id' => $rek->id, 'nove_vrednosti' => ['tip' => $tip],
        ]);
        self::javiFirmi($stan->tenant_id, 'Nova reklamacija kupca sa Temelja', 'Kupac stana '.$stan->oznaka.' je prijavio reklamaciju ('.Prikaz::label($tip).').', $rek);

        return [$rek, null];
    }

    /** Poruka kupca na postojećoj reklamaciji. Vraća grešku ili null. */
    public static function porukaKupca(Unit $stan, int $reklamacijaId, string $tekst): ?string
    {
        $rek = self::reklamacijeKupca($stan)->find($reklamacijaId);
        if (! $rek) {
            return 'Reklamacija nije pronađena.';
        }
        $danas = ClaimNote::where('claim_id', $rek->id)->where('od_kupca', true)->where('created_at', '>', now()->subDay())->count();
        if ($danas >= 20) {
            return 'Poslali ste dovoljno poruka za danas — investitor će vam odgovoriti.';
        }
        $rek->notes()->create(['user_id' => null, 'tekst' => $tekst, 'od_kupca' => true, 'vidljivo_kupcu' => true]);
        $rek->touch();
        self::javiFirmi($stan->tenant_id, 'Nova poruka kupca — REK-'.$rek->id, 'Kupac stana '.$stan->oznaka.' je poslao poruku na reklamaciji REK-'.$rek->id.'.', $rek);

        return null;
    }

    /**
     * Obaveštenje kupcu (mejl šalje Temelj) kad firma promeni status ili pošalje poruku kupcu.
     * Samo za reklamaciju sadašnjeg kupca koji je potvrdio stan; šalje se posle odgovora.
     */
    public static function obavestiKupca(Claim $rek, string $tip): void
    {
        $stanId = $rek->stan_id;
        $kupacId = $rek->kupac_id;
        dispatch(function () use ($stanId, $kupacId, $tip, $rek) {
            $stan = Unit::withoutGlobalScopes()->find($stanId);
            if (! $stan || $stan->temelj_kupac_status !== 'potvrdjen' || ! $kupacId || $stan->kupac_id !== $kupacId || ! self::moze($stan)) {
                return;
            }
            TemeljApi::posalji('/api/v1/kupac/obavestenje', [
                'tenant_id' => $stan->tenant_id,
                'stan_id' => $stan->id,
                'tip' => $tip,
                'broj' => 'REK-'.$rek->id,
                'status' => Prikaz::label($rek->status),
            ]);
        })->afterResponse();
    }

    /** Kratko obaveštenje vlasniku firme (email) — bez sadržaja reklamacije, samo link. */
    private static function javiFirmi(int $tenantId, string $naslov, string $tekst, Claim $rek): void
    {
        dispatch(function () use ($tenantId, $naslov, $tekst, $rek) {
            $vlasnik = Tenant::find($tenantId)?->vlasnik;
            if ($vlasnik?->email) {
                Registracija::email($vlasnik->email, $naslov.' — Temelj Investitor', [
                    'naslov' => $naslov, 'tekst' => $tekst, 'dugme' => 'Otvori reklamaciju', 'link' => route('claims.show', $rek->id),
                ]);
            }
        })->afterResponse();
    }

    /** Obaveštenje sa Temelja: kupac je potvrdio stan. */
    public static function upisiPotvrdu(int $tenantId, int $stanId): void
    {
        $stan = Unit::withoutGlobalScopes()->where('tenant_id', $tenantId)->find($stanId);
        if ($stan && $stan->temelj_kupac_status === 'poslat') {
            $stan->forceFill(['temelj_kupac_status' => 'potvrdjen', 'temelj_kupac_at' => now()])->save();
        }
    }

    private static function stan(int $id): ?Unit
    {
        return Unit::withoutGlobalScopes()
            ->with([
                'customer' => fn ($q) => $q->withoutGlobalScopes(),
                'building' => fn ($q) => $q->withoutGlobalScopes()->with(['project' => fn ($p) => $p->withoutGlobalScopes()]),
            ])
            ->find($id);
    }

    private static function moze(Unit $stan): bool
    {
        return TemeljApi::podesen() && (bool) Tenant::find($stan->tenant_id)?->povezanSaTemeljem();
    }

    private static function posalji(Unit $stan, string $akcija, ?string $email = null): bool
    {
        $stan->loadMissing([
            'building' => fn ($q) => $q->withoutGlobalScopes()->with(['project' => fn ($p) => $p->withoutGlobalScopes()]),
        ]);
        $zgrada = $stan->building;
        $projekat = $zgrada?->project;

        [$ok, $odg] = TemeljApi::posalji('/api/v1/kupac/stan', array_filter([
            'tenant_id' => $stan->tenant_id,
            'stan_id' => $stan->id,
            'akcija' => $akcija,
            'email' => $email,
            'stan' => [
                'projekat' => $projekat?->naziv,
                'zgrada' => $zgrada && $zgrada->naziv !== $projekat?->naziv ? $zgrada->naziv : null,
                'oznaka' => $stan->oznaka,
                'grad' => $projekat?->lokacija_grad,
                'adresa' => $zgrada?->adresa ?: $projekat?->lokacija_adresa,
                'status' => $stan->status,
            ],
        ], fn ($v) => $v !== null));
        if (! $ok) {
            return false;
        }

        if ($akcija === 'ukloni') {
            $stan->forceFill(['temelj_kupac_status' => null, 'temelj_kupac_email' => null, 'temelj_kupac_at' => now()])->save();
            AuditLog::zabelezi('temelj_kupac_uklonjen', $stan);
        } elseif ($akcija === 'poziv') {
            $stan->forceFill([
                'temelj_kupac_status' => ($odg['status'] ?? '') === 'potvrdjen' ? 'potvrdjen' : 'poslat',
                'temelj_kupac_email' => $email,
                'temelj_kupac_at' => now(),
            ])->save();
            AuditLog::zabelezi('temelj_kupac_pozvan', $stan);
        }
        return true;
    }
}
