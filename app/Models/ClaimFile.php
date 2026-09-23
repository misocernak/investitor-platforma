<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimFile extends Model
{
    protected $fillable = ['claim_id', 'putanja_fajla', 'originalni_naziv'];

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }
}
