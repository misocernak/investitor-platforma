<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = ['zgrada_id', 'tip_checkliste'];

    public function building()
    {
        return $this->belongsTo(Building::class, 'zgrada_id');
    }

    public function items()
    {
        return $this->hasMany(ChecklistItem::class, 'checklist_id');
    }

    public function getUkupnoAttribute(): int
    {
        return $this->items()->count();
    }

    public function getResenoAttribute(): int
    {
        return $this->items()->where('zavrseno', true)->count();
    }
}
