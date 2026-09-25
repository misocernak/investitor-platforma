<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'zgrada_id', 'oznaka', 'sprat', 'kvadratura', 'terasa_m2', 'broj_soba',
        'cena', 'kupac_id', 'status', 'arhiviran',
    ];

    protected $casts = ['arhiviran' => 'boolean', 'temelj_kupac_at' => 'datetime'];

    public function building()
    {
        return $this->belongsTo(Building::class, 'zgrada_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kupac_id');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'stan_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'stan_id');
    }

    public function oglas()
    {
        return $this->hasOne(Oglas::class, 'stan_id');
    }

    public function upiti()
    {
        return $this->hasMany(Upit::class, 'stan_id')->latest('primljeno_at');
    }

    public function otvoreneReklamacije()
    {
        return $this->claims()->whereNotIn('status', ['Resena', 'Odbijena']);
    }
}
