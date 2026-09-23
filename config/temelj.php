<?php

// Veza sa Temelj.rs (oglasi stanova i upiti kupaca). Ključ mora biti isti kao
// 'investitor_api.kljuc' u app/config.php na Temelju. Upisuje se samo u .env na serveru.
return [
    'url' => rtrim(env('TEMELJ_URL', 'https://tmltest.temelj.info'), '/'),
    'kljuc' => env('TEMELJ_API_KLJUC', ''),
    'maks_slika' => 15,
    // Ako je Temelj sajt zaključan lozinkom (cPanel → Directory Privacy, npr. test sajt),
    // ovde idu korisničko ime i lozinka te zaštite. Na javnom sajtu ostaviti prazno.
    'http_korisnik' => env('TEMELJ_HTTP_KORISNIK', ''),
    'http_lozinka' => env('TEMELJ_HTTP_LOZINKA', ''),
];
