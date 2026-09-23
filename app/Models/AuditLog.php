<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'akcija', 'model_type', 'model_id', 'nove_vrednosti', 'ip_address',
    ];

    protected $casts = ['nove_vrednosti' => 'array'];

    public static function zabelezi(string $akcija, ?Model $model = null, array $noveVrednosti = []): void
    {
        static::create([
            'tenant_id' => auth()->user()?->tenant_id,
            'user_id' => auth()->id(),
            'akcija' => $akcija,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'nove_vrednosti' => !empty($noveVrednosti) ? $noveVrednosti : null,
            'ip_address' => request()->ip(),
        ]);
    }
}
