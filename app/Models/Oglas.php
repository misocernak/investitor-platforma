<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Oglas extends Model
{
    use BelongsToTenant;

    protected $table = 'oglasi';

    protected $fillable = [
        'tenant_id', 'stan_id', 'status', 'cena_na_upit', 'opis', 'temelj_url',
        'objavljen_at', 'sinhronizovan_at', 'greska_sinhronizacije',
    ];

    protected $casts = [
        'cena_na_upit' => 'boolean',
        'objavljen_at' => 'datetime',
        'sinhronizovan_at' => 'datetime',
    ];

    // Statusi stana u kojima stan može da bude u ponudi na Temelju
    public const STATUSI_U_PRODAJI = ['Za_prodaju', 'Rezervisan'];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'stan_id');
    }

    public function slike()
    {
        return $this->hasMany(OglasSlika::class, 'oglas_id')->orderBy('redosled');
    }

    /** Izmene su stigle na Temelj (nema greške i sinhronizacija je posle poslednje izmene). */
    public function uskladjen(): bool
    {
        return $this->sinhronizovan_at !== null && $this->greska_sinhronizacije === null;
    }
}
