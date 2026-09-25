<?php

namespace App\Services;

use App\Models\AuditLog;
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
