<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

// Upit kupca sa Temelj.rs za stan u ponudi
class Upit extends Model
{
    use BelongsToTenant;

    protected $table = 'upiti';

    protected $fillable = [
        'tenant_id', 'stan_id', 'temelj_id', 'ime', 'email', 'telefon', 'poruka', 'oglas_url',
        'status', 'beleska', 'primljeno_at', 'procitano_at',
    ];

    protected $casts = [
        'primljeno_at' => 'datetime',
        'procitano_at' => 'datetime',
    ];

    public const STATUSI = ['novo' => 'Novo', 'u_kontaktu' => 'U kontaktu', 'zatvoreno' => 'Zatvoreno'];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'stan_id');
    }
}
