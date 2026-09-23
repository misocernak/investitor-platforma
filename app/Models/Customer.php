<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'ime_prezime', 'email', 'telefon', 'napomena'];

    public function units()
    {
        return $this->hasMany(Unit::class, 'kupac_id');
    }
}
