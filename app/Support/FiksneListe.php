<?php

namespace App\Support;

class FiksneListe
{
    public static function validacija(string $kljuc, ?string $vrednost): bool
    {
        return in_array($vrednost, config('statusi.'.$kljuc, []), true);
    }

    public static function pravila(string $kljuc): string
    {
        return 'in:'.implode(',', config('statusi.'.$kljuc, []));
    }
}
