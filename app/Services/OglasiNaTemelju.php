<?php

namespace App\Services;

use App\Models\Oglas;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Upit;

/**
 * Oglašavanje stanova na Temelj.rs: slanje oglasa, provera veze firme i preuzimanje upita kupaca.
 * Temelj čuva sopstvenu kopiju oglasa; ovde je izvor podataka (stan, zgrada, projekat, fotografije).
 */
class OglasiNaTemelju
{
    public const PORUKA_NEDOSTUPAN = 'Temelj trenutno nije odgovorio — izmene će biti poslate ponovo.';

    // ------------------------------------------------------------------
    //  Veza firme sa profilom investitora na Temelju
    // ------------------------------------------------------------------

    /** Šalje zahtev za povezivanje (MB firme). Vraća poruku za korisnika. */
    public static function zatraziVezu(Tenant $tenant): string
    {
        [$ok, $odg] = TemeljApi::posalji('/api/v1/veza', [
            'tenant_id' => $tenant->id,
            'maticni_broj' => $tenant->maticni_broj,
            'naziv' => $tenant->naziv,
            'pib' => $tenant->pib,
            'email' => $tenant->email,
        ]);
        if (! $ok) {
            return $odg['greska'] ?? 'Temelj trenutno nije dostupan. Pokušajte ponovo za nekoliko minuta.';
        }
        self::upisiStanjeVeze($tenant, $odg);
        return $tenant->temelj_veza_status === 'odobrena'
            ? 'Firma je već povezana sa Temeljem.'
            : 'Zahtev je poslat. Temelj proverava podatke firme — obično u roku od jednog radnog dana.';
    }

    /** Osvežava stanje veze sa Temelja (ne češće od jednom u 2 minuta, osim ako je $odmah). */
    public static function proveriVezu(Tenant $tenant, bool $odmah = false): void
    {
        if (! $tenant->temelj_veza_status || ! TemeljApi::podesen()) {
            return;
        }
        if (! $odmah && $tenant->temelj_veza_provereno_at && $tenant->temelj_veza_provereno_at->gt(now()->subMinutes(2))) {
            return;
        }
        [$ok, $odg] = TemeljApi::posalji('/api/v1/veza/status', ['tenant_id' => $tenant->id]);
        if ($ok) {
            self::upisiStanjeVeze($tenant, $odg);
        }
    }

    /** Upisuje stanje veze (iz odgovora ili obaveštenja sa Temelja); pri odobrenju šalje oglase koji čekaju. */
    public static function upisiStanjeVeze(Tenant $tenant, array $odg): void
    {
        $status = in_array($odg['status'] ?? null, ['na_cekanju', 'odobrena', 'odbijena'], true) ? $odg['status'] : null;
        $bilaOdobrena = $tenant->temelj_veza_status === 'odobrena';
        $tenant->forceFill([
            'temelj_veza_status' => $status,
            'temelj_veza_poruka' => $status === 'odbijena' ? ($odg['razlog'] ?? null) : null,
            'temelj_profil_url' => $odg['profil_url'] ?? null,
            'temelj_veza_provereno_at' => now(),
        ])->save();

        if ($status === 'odobrena' && ! $bilaOdobrena) {
            self::posaljiNeposlate($tenant);
        }
    }

    // ------------------------------------------------------------------
    //  Oglasi
    // ------------------------------------------------------------------

    /** Podaci oglasa za Temelj. */
    public static function paket(Oglas $oglas): array
    {
        $stan = $oglas->unit()->withoutGlobalScopes()->with(['building' => fn ($q) => $q->withoutGlobalScopes()])->first();
        $zgrada = $stan->building;
        $projekat = $zgrada ? $zgrada->project()->withoutGlobalScopes()->first() : null;

        $uProdaji = in_array($stan->status, Oglas::STATUSI_U_PRODAJI, true) && ! $stan->arhiviran;
        $statusGradnje = match ($zgrada?->status) {
            'U izgradnji' => 'u_izgradnji',
            'Zavrsena', 'U garanciji', 'Zatvorena' => 'useljivo',
            default => null,
        };

        return [
            'spoljni_id' => $stan->id,
            'status' => $uProdaji ? $oglas->status : 'skinut',
            'rezervisan' => $stan->status === 'Rezervisan',
            'oznaka' => $stan->oznaka,
            'projekat' => $projekat?->naziv,
            'zgrada' => $zgrada && $zgrada->naziv !== $projekat?->naziv ? $zgrada->naziv : null,
            'adresa' => $zgrada?->adresa ?: $projekat?->lokacija_adresa,
            'grad' => $projekat?->lokacija_grad,
            'lat' => $zgrada?->lat,
            'lng' => $zgrada?->lng,
            'kvadratura' => $stan->kvadratura,
            'broj_soba' => $stan->broj_soba,
            'sprat' => $stan->sprat,
            'spratnost' => $zgrada?->spratnost,
            'terasa_m2' => $stan->terasa_m2,
            'cena' => $oglas->cena_na_upit ? null : $stan->cena,
            'cena_na_upit' => $oglas->cena_na_upit || ! $stan->cena,
            'opis' => $oglas->opis,
            'grejanje' => $zgrada?->grejanje,
            'lift' => $zgrada?->lift,
            'energetski_razred' => $zgrada?->energetski_razred,
            'parking' => $zgrada?->parking,
            'status_gradnje' => $statusGradnje,
            'rok_zavrsetka' => $statusGradnje === 'u_izgradnji' ? $projekat?->planirani_datum_zavrsetka?->format('Y-m-d') : null,
            'dozvola_broj' => $zgrada?->dozvola_broj,
            'dozvola_datum' => $zgrada?->dozvola_datum?->format('Y-m-d'),
            'dozvola_izdavalac' => $zgrada?->dozvola_izdavalac,
            'katastarska_parcela' => $zgrada?->katastarska_parcela,
            'prijava_radova_datum' => $zgrada?->prijava_radova_datum?->format('Y-m-d'),
        ];
    }

    /** Šalje oglas na Temelj. Vraća true ako je stigao. */
    public static function sinhronizuj(Oglas $oglas): bool
    {
        $tenant = Tenant::find($oglas->tenant_id);
        if (! $tenant || $tenant->temelj_veza_status !== 'odobrena') {
            // Čeka odobrenje veze — poslaće se automatski čim Temelj odobri
            $oglas->forceFill(['sinhronizovan_at' => null, 'greska_sinhronizacije' => null])->save();
            return false;
        }

        $podaci = self::paket($oglas);
        [$ok, $odg, $kod] = TemeljApi::posalji('/api/v1/oglasi', [
            'tenant_id' => $tenant->id,
            'oglas' => $podaci,
            'slike' => $podaci['status'] === 'skinut' ? [] : $oglas->slike()->get()
                ->map(fn ($s) => ['url' => $s->url(), 'url_mala' => $s->urlMala(), 'tip' => $s->tip, 'redosled' => $s->redosled])->values()->all(),
        ]);

        if ($ok) {
            $oglas->forceFill([
                'temelj_url' => $odg['url'] ?? $oglas->temelj_url,
                'sinhronizovan_at' => now(),
                'greska_sinhronizacije' => null,
                'objavljen_at' => $oglas->objavljen_at ?? ($podaci['status'] === 'aktivan' ? now() : null),
                'status' => $podaci['status'] === 'skinut' ? 'skinut' : $oglas->status,
            ])->save();
            return true;
        }

        if ($kod === 409 && is_array($odg)) {
            // Temelj kaže da veza više nije odobrena
            self::upisiStanjeVeze($tenant, ['status' => $odg['status_veze'] ?? 'na_cekanju']);
        }
        $oglas->forceFill(['greska_sinhronizacije' => $odg['greska'] ?? self::PORUKA_NEDOSTUPAN])->save();
        return false;
    }

    /** Šalje sve oglase firme koji još nisu stigli na Temelj (posle odobrenja veze ili prekida veze). */
    public static function posaljiNeposlate(Tenant $tenant, int $najvise = 30): void
    {
        Oglas::withoutGlobalScopes()->where('tenant_id', $tenant->id)
            ->where(fn ($q) => $q->whereNull('sinhronizovan_at')->orWhereNotNull('greska_sinhronizacije'))
            ->where(fn ($q) => $q->where('status', '!=', 'skinut')->orWhereNotNull('sinhronizovan_at'))
            ->limit($najvise)->get()
            ->each(fn (Oglas $o) => self::sinhronizuj($o));
    }

    /** Slanje posle odgovora korisniku — ekran se ne zadržava dok Temelj obrađuje oglas. */
    public static function uPozadini(\Closure $posao): void
    {
        dispatch($posao)->afterResponse();
    }

    /** Posle izmene stana ili zgrade: prodat stan se skida sa Temelja, ostale izmene se šalju. */
    public static function posleIzmeneStana(Unit $stan): void
    {
        $oglas = Oglas::withoutGlobalScopes()->where('stan_id', $stan->id)->first();
        if (! $oglas) {
            return;
        }
        if (! in_array($stan->status, Oglas::STATUSI_U_PRODAJI, true) && $oglas->status !== 'skinut') {
            $oglas->status = 'skinut';
            $oglas->save();
        }
        if ($oglas->status === 'skinut' && $oglas->sinhronizovan_at === null) {
            return; // nikad nije ni bio na Temelju
        }
        self::sinhronizuj($oglas);
    }

    /** Posle izmene zgrade ili projekta: šalju se oglasi svih stanova u zgradi koji su na Temelju. */
    public static function posleIzmeneZgrade(\App\Models\Building $zgrada): void
    {
        Oglas::withoutGlobalScopes()->where('tenant_id', $zgrada->tenant_id)
            ->whereIn('stan_id', Unit::withoutGlobalScopes()->where('zgrada_id', $zgrada->id)->select('id'))
            ->whereNotNull('sinhronizovan_at')
            ->where('status', '!=', 'skinut')
            ->get()
            ->each(fn (Oglas $o) => self::sinhronizuj($o));
    }

    // ------------------------------------------------------------------
    //  Upiti kupaca
    // ------------------------------------------------------------------

    /** Upisuje upit sa Temelja (idempotentno po temelj_id). */
    public static function sacuvajUpit(int $tenantId, array $u): ?Upit
    {
        $temeljId = (string) ($u['id'] ?? '');
        if ($temeljId === '' || empty($u['email']) || empty($u['poruka'])) {
            return null;
        }
        $stan = Unit::withoutGlobalScopes()->where('tenant_id', $tenantId)->find((int) ($u['spoljni_id'] ?? 0));

        return Upit::withoutGlobalScopes()->firstOrCreate(['temelj_id' => $temeljId], [
            'tenant_id' => $tenantId,
            'stan_id' => $stan?->id,
            'ime' => mb_substr((string) ($u['ime'] ?? 'Kupac'), 0, 255),
            'email' => mb_substr((string) $u['email'], 0, 255),
            'telefon' => isset($u['telefon']) ? mb_substr((string) $u['telefon'], 0, 50) : null,
            'poruka' => mb_substr((string) $u['poruka'], 0, 5000),
            'oglas_url' => $u['oglas_url'] ?? null,
            'status' => 'novo',
            'primljeno_at' => isset($u['datum']) ? \Illuminate\Support\Carbon::parse($u['datum']) : now(),
        ]);
    }

    /** Preuzima upite koje Temelj nije uspeo odmah da isporuči (najviše jednom u minutu). */
    public static function preuzmiUpite(Tenant $tenant): void
    {
        if ($tenant->temelj_veza_status !== 'odobrena') {
            return;
        }
        // Najviše jednom u minutu po sesiji korisnika (bez keša/baze za keš)
        if (session('temelj_upiti_at', 0) > time() - 60) {
            return;
        }
        session(['temelj_upiti_at' => time()]);
        [$ok, $odg] = TemeljApi::posalji('/api/v1/upiti/preuzmi', ['tenant_id' => $tenant->id]);
        if ($ok) {
            foreach ((array) ($odg['upiti'] ?? []) as $u) {
                self::sacuvajUpit($tenant->id, (array) $u);
            }
        }
    }
}
