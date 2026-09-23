<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'projekat_id', 'zgrada_id', 'stan_id', 'tip', 'naziv',
        'datum_izdavanja', 'izdavalac', 'putanja_fajla', 'verzija', 'aktivna_verzija',
    ];

    protected $casts = [
        'datum_izdavanja' => 'date',
        'aktivna_verzija' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'projekat_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'zgrada_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'stan_id');
    }

    public function imaFajl(): bool
    {
        return $this->putanja_fajla && Storage::disk('documents')->exists($this->putanja_fajla);
    }
}
