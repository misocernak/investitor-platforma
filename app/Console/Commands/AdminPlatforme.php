<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

// Pravi (ili resetuje) nalog admina platforme: php artisan platforma:admin email@primer.rs
class AdminPlatforme extends Command
{
    protected $signature = 'platforma:admin {email} {--ime=Admin platforme}';

    protected $description = 'Pravi nalog admina platforme (bez firme) ili mu postavlja novu privremenu lozinku';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $lozinka = Str::password(14, symbols: false);

        $korisnik = User::where('email', $email)->first();
        if ($korisnik && ! $korisnik->jePlatforma()) {
            $this->error('Ovaj email već koristi korisnik neke firme. Upotrebite drugu adresu.');
            return self::FAILURE;
        }

        User::updateOrCreate(['email' => $email], [
            'tenant_id' => null,
            'ime_prezime' => $korisnik->ime_prezime ?? $this->option('ime'),
            'uloga' => 'Platforma',
            'status_naloga' => 'Aktivan',
            'password' => $lozinka,
            'email_potvrdjen_at' => now(),
        ]);

        $this->info('Nalog admina platforme je spreman.');
        $this->line('Email:    '.$email);
        $this->line('Lozinka:  '.$lozinka);
        $this->line('Posle prijave promenite lozinku (Moj nalog → Lozinka).');
        return self::SUCCESS;
    }
}
