<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'naziv', 'lokacija_adresa', 'lokacija_grad', 'tip',
        'broj_planiranih_stanova', 'datum_pocetka_gradnje', 'planirani_datum_zavrsetka',
        'status', 'napomena', 'arhiviran',
    ];

    protected $casts = [
        'datum_pocetka_gradnje' => 'date',
        'planirani_datum_zavrsetka' => 'date',
        'arhiviran' => 'boolean',
    ];

    public function buildings()
    {
        return $this->hasMany(Building::class, 'projekat_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'projekat_id');
    }

    // Broj otvorenih reklamacija povezanih sa projektom (za tabelu na dashboardu, PRD 9.1)
    public function getOtvoreneReklamacijeAttribute(): int
    {
        return Claim::whereIn('stan_id', Unit::whereIn('zgrada_id', $this->buildings()->select('id'))->select('id'))
            ->whereNotIn('status', ['Resena', 'Odbijena'])
            ->count();
    }
}
