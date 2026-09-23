<?php

// Fiksne liste vrednosti po PRD sekcija 6 i 14 - JEDINI izvor istine za validaciju.
return [
    'status_projekta' => ['Planiranje', 'U izgradnji', 'Zavrsen', 'Uknjizen', 'Postprodaja (garancije)', 'Zatvoren'],
    'status_zgrade' => ['U izgradnji', 'Zavrsena', 'U garanciji', 'Zatvorena'],
    'status_stana' => ['Za_prodaju', 'Rezervisan', 'Prodat_u_procesu_uknjizenja', 'Prodat_u_garanciji', 'Garancija_istekla', 'Arhiviran'],
    'tip_projekta' => ['Stambeni', 'Stambeno_poslovni', 'Drugo'],
    'status_reklamacije' => ['Prijavljena', 'U_obradi', 'Dodeljena', 'Resena', 'Odbijena'],
    'tip_problema' => ['Vodovod', 'Elektro_instalacije', 'Grejanje_klima', 'Stolarija', 'Podovi_zavrsne_obrade', 'Zidovi_fasada', 'Drugo'],
    'tip_checkliste' => ['Upotrebna_dozvola', 'Uknjizba', 'Paket_za_banku'],
    'uloga' => ['Vlasnik', 'Administrator', 'Operater', 'Nadzor_izvodjac'],
    'status_naloga' => ['Aktivan', 'Pozvan_ceka_aktivaciju', 'Deaktiviran'],
    // Mapiranje statusa projekta na milestone poziciju (1-6) - vizuelno, ne menja podatke (PRD 9.2)
    'milestone_map' => [
        'Planiranje' => 1,
        'U izgradnji' => 3,
        'Zavrsen' => 5,
        'Uknjizen' => 6,
        'Postprodaja (garancije)' => 6,
        'Zatvoren' => 6,
    ],
    'milestone_koraci' => [
        1 => 'Građevinska dozvola',
        2 => 'Početak radova',
        3 => 'Izvedeno stanje',
        4 => 'Tehnički pregled',
        5 => 'Upotrebna dozvola',
        6 => 'Uknjižba',
    ],
];
