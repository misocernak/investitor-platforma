<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'stan_id', 'kupac_id', 'datum_prijave', 'tip_problema',
        'tip_problema_drugo', 'opis', 'status', 'odgovorni_id', 'rok_resavanja',
    ];

    protected $casts = [
        'datum_prijave' => 'date',
        'rok_resavanja' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'stan_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kupac_id');
    }

    public function odgovorni()
    {
        return $this->belongsTo(User::class, 'odgovorni_id');
    }

    public function notes()
    {
        return $this->hasMany(ClaimNote::class, 'claim_id')->latest();
    }

    public function files()
    {
        return $this->hasMany(ClaimFile::class, 'claim_id');
    }

    // Cisto oduzimanje datuma - dozvoljeno (PRD 2.2)
    public function getKasniDanaAttribute(): ?int
    {
        if (!$this->rok_resavanja) {
            return null;
        }
        $diff = (int) now()->diffInDays($this->rok_resavanja, false);
        return $diff < 0 ? abs($diff) : 0;
    }
}
