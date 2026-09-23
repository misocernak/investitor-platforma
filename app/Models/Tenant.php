<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'naziv', 'pib', 'maticni_broj', 'adresa', 'grad',
        'kontakt_osoba', 'telefon', 'email', 'logo', 'napomena',
    ];

    protected $casts = [
        'temelj_veza_provereno_at' => 'datetime',
    ];

    public function povezanSaTemeljem(): bool
    {
        return $this->temelj_veza_status === 'odobrena';
    }

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}
