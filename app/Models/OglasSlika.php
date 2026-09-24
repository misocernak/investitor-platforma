<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OglasSlika extends Model
{
    protected $table = 'oglas_slike';

    protected $fillable = ['oglas_id', 'putanja', 'tip', 'redosled'];

    /** Javni URL fotografije (do 2000px). Temelj je prikazuje direktno odavde. */
    public function url(): string
    {
        return url('oglasi-slike/'.$this->putanja);
    }

    /** Umanjena verzija (720px) za liste i sličice; ako ne postoji, velika. */
    public function urlMala(): string
    {
        $mala = preg_replace('/\.(jpg|webp)$/', '-m.$1', $this->putanja);
        return $mala !== $this->putanja && Storage::disk('oglasi')->exists($mala)
            ? url('oglasi-slike/'.$mala)
            : $this->url();
    }

    /** Briše fajlove samo ako ih ne koristi i neki drugi oglas (fotografije kopirane iz drugog stana). */
    public function obrisiSaFajlom(): void
    {
        $deljena = static::where('putanja', $this->putanja)->where('id', '!=', $this->id)->exists();
        if (! $deljena) {
            Storage::disk('oglasi')->delete([$this->putanja, preg_replace('/\.(jpg|webp)$/', '-m.$1', $this->putanja)]);
        }
        $this->delete();
    }
}
