<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'naziv', 'pib', 'maticni_broj', 'adresa', 'grad',
        'kontakt_osoba', 'telefon', 'email', 'logo', 'napomena',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}
