<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimNote extends Model
{
    protected $fillable = ['claim_id', 'user_id', 'tekst', 'od_kupca', 'vidljivo_kupcu'];

    protected $casts = ['od_kupca' => 'boolean', 'vidljivo_kupcu' => 'boolean'];

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
