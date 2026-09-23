<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'projekat_id', 'naziv', 'broj_stanova', 'status', 'napomena', 'arhiviran',
        // podaci za oglase na Temelju
        'lat', 'lng', 'adresa', 'spratnost', 'grejanje', 'lift', 'energetski_razred', 'parking',
        'dozvola_broj', 'dozvola_datum', 'dozvola_izdavalac', 'katastarska_parcela', 'prijava_radova_datum',
    ];

    protected $casts = [
        'arhiviran' => 'boolean',
        'lift' => 'boolean',
        'dozvola_datum' => 'date',
        'prijava_radova_datum' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'projekat_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'zgrada_id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'zgrada_id');
    }

    public function nadzorUsers()
    {
        return $this->belongsToMany(User::class, 'building_user', 'building_id', 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'zgrada_id');
    }

    public function nedostajuceStavke()
    {
        return ChecklistItem::whereIn('checklist_id', $this->checklists()->select('id'))
            ->where('zavrseno', false);
    }
}
