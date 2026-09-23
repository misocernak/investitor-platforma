<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id', 'ime_prezime', 'email', 'password', 'uloga', 'status_naloga',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
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
