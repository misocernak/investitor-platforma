<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = ['tenant_id', 'naziv'];

    public function scopeZaTenant($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('tenant_id');
            if (auth()->check() && auth()->user()->tenant_id) {
                $q->orWhere('tenant_id', auth()->user()->tenant_id);
            }
        });
    }
}
