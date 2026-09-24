<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id', 'ime_prezime', 'funkcija', 'email', 'telefon', 'password', 'uloga', 'status_naloga',
        'email_token', 'email_potvrdjen_at',
    ];

    public const FUNKCIJE = [
        'direktor' => 'Direktor',
        'zakonski_zastupnik' => 'Zakonski zastupnik',
        'ovlasceno_lice' => 'Ovlašćeno lice (uz punomoćje)',
    ];

    protected $hidden = ['password', 'remember_token', 'email_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_potvrdjen_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function dodeljeneZgrade()
    {
        return $this->belongsToMany(Building::class, 'building_user', 'user_id', 'building_id');
    }

    /** Admin platforme: upravlja nalozima firmi, bez uvida u njihove podatke. */
    public function jePlatforma(): bool
    {
        return $this->uloga === 'Platforma';
    }

    public function jeNadzor(): bool
    {
        return $this->uloga === 'Nadzor_izvodjac';
    }

    public function mozeAdministrirati(): bool
    {
        return in_array($this->uloga, ['Vlasnik', 'Administrator']);
    }

    public function mozeUredjivati(): bool
    {
        return in_array($this->uloga, ['Vlasnik', 'Administrator', 'Operater']);
    }
}
