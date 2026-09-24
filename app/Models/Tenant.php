<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'naziv', 'pib', 'maticni_broj', 'adresa', 'grad',
        'kontakt_osoba', 'telefon', 'email', 'logo', 'napomena',
        'status', 'razlog_odbijanja', 'registrovan_at', 'odobren_at', 'apr_provereno', 'apr_status',
        'ovlascenje_putanja', 'ovlascenje_naziv',
    ];

    protected $casts = [
        'temelj_veza_provereno_at' => 'datetime',
        'registrovan_at' => 'datetime',
        'odobren_at' => 'datetime',
        'apr_provereno' => 'boolean',
    ];

    public const STATUSI = [
        'na_cekanju' => 'Čeka odobrenje',
        'aktivan' => 'Aktivan',
        'odbijen' => 'Odbijen',
        'suspendovan' => 'Suspendovan',
        'raskinut' => 'Vlasništvo raskinuto',
    ];

    public function aktivan(): bool
    {
        return $this->status === 'aktivan';
    }

    /** Korisnik koji je registrovao firmu (prvi Vlasnik). */
    public function vlasnik()
    {
        return $this->hasOne(User::class, 'tenant_id')->ofMany(['id' => 'min'], fn ($q) => $q->where('uloga', 'Vlasnik'));
    }

    public function povezanSaTemeljem(): bool
    {
        return $this->temelj_veza_status === 'odobrena';
    }

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}
