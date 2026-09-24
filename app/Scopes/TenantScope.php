<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

// Shared database, shared schema - izolacija preko tenant_id (PRD 12.2)
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();
        if (! $user) {
            return; // bez prijave (API sa Temelja, komande) — upiti tu uvek eksplicitno filtriraju firmu
        }
        if ($user->tenant_id) {
            $builder->where($model->getTable().'.tenant_id', $user->tenant_id);
        } else {
            // Prijavljen korisnik bez firme (admin platforme) ne sme da vidi podatke nijedne firme
            $builder->whereRaw('1 = 0');
        }
    }
}
