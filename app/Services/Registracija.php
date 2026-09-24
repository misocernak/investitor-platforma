<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Samostalna registracija firme i odobravanje od strane admina platforme.
 * Odobrenje je jedina provera: aktivira nalog i odmah povezuje firmu sa Temeljem
 * (Temelj pravi profil investitora ako ga nema).
 */
class Registracija
{
    /** Podaci firme iz APR registra (preko Temelja). null = Temelj nedostupan. */
    public static function firmaIzRegistra(string $mb): ?array
    {
        [$ok, $odg] = TemeljApi::posalji('/api/v1/firma', ['maticni_broj' => $mb]);
        return $ok ? $odg : null;
    }

    /** Novi token za potvrdu emaila i slanje linka. */
    public static function posaljiPotvrduEmaila(User $korisnik): void
    {
        $token = Str::random(48);
        $korisnik->forceFill(['email_token' => hash('sha256', $token)])->save();
        self::email($korisnik->email, 'Potvrdite email adresu — Temelj Investitor', [
            'naslov' => 'Potvrdite email adresu',
            'tekst' => 'Hvala na registraciji firme na Temelj Investitoru. Potvrdite da je ovo vaša email adresa — posle toga proveravamo podatke firme i javljamo vam se čim nalog bude odobren.',
            'dugme' => 'Potvrdi email',
            'link' => route('registracija.potvrda', $token),
        ]);
    }

    /** Odobrava firmu: aktivan nalog + veza sa Temeljem. Vraća poruku za admina. */
    public static function odobri(Tenant $firma): string
    {
        $firma->forceFill(['status' => 'aktivan', 'razlog_odbijanja' => null, 'odobren_at' => now()])->save();
        AuditLog::zabelezi('odobrena_firma', $firma, ['mb' => $firma->maticni_broj]);

        // Veza sa Temeljem se odobrava odmah — ovo je jedina provera firme
        [$ok, $odg] = TemeljApi::posalji('/api/v1/veza', [
            'tenant_id' => $firma->id,
            'maticni_broj' => $firma->maticni_broj,
            'naziv' => $firma->naziv,
            'pib' => $firma->pib,
            'email' => $firma->email,
            'adresa' => $firma->adresa,
            'grad' => $firma->grad,
            'odobreno_na_platformi' => true,
        ]);
        if ($ok) {
            OglasiNaTemelju::upisiStanjeVeze($firma, $odg);
        }

        $vlasnik = $firma->vlasnik;
        if ($vlasnik) {
            self::email($vlasnik->email, 'Nalog je odobren — Temelj Investitor', [
                'naslov' => 'Vaš nalog je odobren',
                'tekst' => 'Firma '.$firma->naziv.' je potvrđena. Možete da se prijavite, unesete projekte i stanove i objavite oglase na Temelju — povezani su sa profilom vaše firme i ocenama kupaca.',
                'dugme' => 'Prijavi se',
                'link' => route('login'),
            ]);
        }

        return $ok
            ? 'Firma je odobrena i povezana sa Temeljem. Vlasniku je poslat email.'
            : 'Firma je odobrena, ali Temelj trenutno nije odgovorio — veza će se napraviti kad firma prvi put otvori Oglase.';
    }

    public static function odbij(Tenant $firma, string $razlog): void
    {
        $firma->forceFill(['status' => 'odbijen', 'razlog_odbijanja' => $razlog])->save();
        AuditLog::zabelezi('odbijena_firma', $firma, ['razlog' => $razlog]);
        if ($vlasnik = $firma->vlasnik) {
            self::email($vlasnik->email, 'Zahtev za nalog nije odobren — Temelj Investitor', [
                'naslov' => 'Zahtev nije odobren',
                'tekst' => 'Nismo mogli da potvrdimo podatke firme '.$firma->naziv.'. Razlog: '.$razlog.' Ako mislite da je u pitanju greška, odgovorite na ovaj email.',
                'dugme' => null,
                'link' => null,
            ]);
        }
    }

    /** Šalje jednostavan email; greška u slanju ne sme da prekine registraciju. */
    public static function email(string $za, string $naslov, array $podaci): void
    {
        try {
            Mail::send('emails.poruka', $podaci, fn ($m) => $m->to($za)->subject($naslov));
        } catch (\Throwable $e) {
            Log::error('Email nije poslat ('.$naslov.'): '.$e->getMessage());
        }
    }
}
