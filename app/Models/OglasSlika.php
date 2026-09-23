<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OglasSlika extends Model
{
    protected $table = 'oglas_slike';

    protected $fillable = ['oglas_id', 'putanja', 'tip', 'redosled'];

    public function url(): string
    {
        return Storage::disk('oglasi')->url($this->putanja);
    }
}
