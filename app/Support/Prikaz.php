<?php

namespace App\Support;

/**
 * Prikaz vrednosti iz fiksnih lista (config/statusi.php) čitljivo za korisnika.
 * U bazi ostaju tehničke vrednosti ("Prodat_u_garanciji"); na ekranu se prikazuje
 * lepa labela ("Prodat · u garanciji") i boja statusa.
 */
class Prikaz
{
    private const LABELE = [
        // Projekat / zgrada
        'Zavrsen' => 'Završen',
        'Zavrsena' => 'Završena',
        'Uknjizen' => 'Uknjižen',
        'Postprodaja (garancije)' => 'Postprodaja (garancije)',
        'Stambeno_poslovni' => 'Stambeno-poslovni',
        // Stan
        'Za_prodaju' => 'Za prodaju',
        'Prodat_u_procesu_uknjizenja' => 'Prodat · uknjižba u toku',
        'Prodat_u_garanciji' => 'Prodat · u garanciji',
        'Garancija_istekla' => 'Garancija istekla',
        // Reklamacija
        'U_obradi' => 'U obradi',
        'Resena' => 'Rešena',
        'Elektro_instalacije' => 'Elektro instalacije',
        'Grejanje_klima' => 'Grejanje i klima',
        'Podovi_zavrsne_obrade' => 'Podovi i završne obrade',
        'Zidovi_fasada' => 'Zidovi i fasada',
        // Checkliste
        'Upotrebna_dozvola' => 'Upotrebna dozvola',
        'Uknjizba' => 'Uknjižba',
        'Paket_za_banku' => 'Paket za banku',
        // Korisnici
        'Nadzor_izvodjac' => 'Nadzor / izvođač',
        'Pozvan_ceka_aktivaciju' => 'Pozvan · čeka aktivaciju',
    ];

    /** Ton (boja) statusa: uspeh, info, upozorenje, greska, neutralno. */
    private const TONOVI = [
        'uspeh' => ['Zavrsen', 'Zavrsena', 'Uknjizen', 'Resena', 'Prodat_u_garanciji', 'Aktivan'],
        'info' => ['U izgradnji', 'Za_prodaju', 'Prijavljena', 'Dodeljena', 'Prodat_u_procesu_uknjizenja'],
        'upozorenje' => ['Planiranje', 'U_obradi', 'Rezervisan', 'U garanciji', 'Postprodaja (garancije)', 'Pozvan_ceka_aktivaciju'],
        'greska' => ['Kasni'],
        'neutralno' => ['Zatvoren', 'Zatvorena', 'Arhiviran', 'Garancija_istekla', 'Odbijena', 'Deaktiviran'],
    ];

    /** CSS klase za svaki ton (pozadina, tekst, tačka). */
    public const KLASE = [
        'uspeh' => ['bg-emerald-50 text-emerald-800', 'bg-emerald-500'],
        'info' => ['bg-secondary-fixed text-on-secondary-fixed-variant', 'bg-secondary'],
        'upozorenje' => ['bg-amber-50 text-amber-800', 'bg-amber-500'],
        'greska' => ['bg-error-container text-on-error-container', 'bg-error'],
        'neutralno' => ['bg-surface-container-high text-on-surface-variant', 'bg-outline'],
    ];

    public static function label(?string $vrednost): string
    {
        if ($vrednost === null || $vrednost === '') {
            return '—';
        }
        return self::LABELE[$vrednost] ?? str_replace('_', ' ', $vrednost);
    }

    public static function ton(?string $vrednost): string
    {
        foreach (self::TONOVI as $ton => $vrednosti) {
            if (in_array($vrednost, $vrednosti, true)) {
                return $ton;
            }
        }
        return 'neutralno';
    }

    /** Za JavaScript (panel stana): labele i tonovi svih poznatih vrednosti. */
    public static function zaJs(): array
    {
        $sve = [];
        foreach (config('statusi') as $lista) {
            if (!is_array($lista) || !array_is_list($lista)) {
                continue;
            }
            foreach ($lista as $v) {
                if (is_string($v)) {
                    $sve[$v] = ['label' => self::label($v), 'klase' => self::KLASE[self::ton($v)]];
                }
            }
        }
        return $sve;
    }
}
