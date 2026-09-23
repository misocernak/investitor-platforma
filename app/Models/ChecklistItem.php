<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['checklist_id', 'naziv_stavke', 'povezani_tip_dokumenta', 'zavrseno'];

    protected $casts = ['zavrseno' => 'boolean'];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }

    // Vizuelni predlog (PRD 6.6): postoji li dokument povezanog tipa za tu zgradu
    public function getPredlogZavrsenoAttribute(): bool
    {
        if (!$this->povezani_tip_dokumenta) {
            return false;
        }
        $zgradaId = $this->checklist->zgrada_id;
        return Document::where('zgrada_id', $zgradaId)
            ->where('tip', $this->povezani_tip_dokumenta)
            ->where('aktivna_verzija', true)
            ->exists();
    }
}
