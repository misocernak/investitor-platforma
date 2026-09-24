<?php

namespace App\Services;

use App\Models\Oglas;
use App\Models\OglasSlika;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fotografije oglasa: original se samo sačuva (brz odgovor korisniku), a obrada ide u pozadini:
 * ispravna rotacija (fotografije sa telefona), smanjenje i WebP format sa ciljanom veličinom fajla.
 *   velika: do 1600px, ciljano ≤ 250 KB (stranica stana na Temelju)
 *   mala:   do 720px,  ciljano ≤ 60 KB  (liste i sličice)
 */
class ObradaSlika
{
    private const VERZIJE = [
        '' => ['sirina' => 1600, 'kb' => 250],
        '-m' => ['sirina' => 720, 'kb' => 60],
    ];

    /** Čuva original bez obrade; vraća putanju (prepoznaje se po "izvorno-"). */
    public static function sacuvajOriginal(UploadedFile $fajl, Oglas $oglas): string
    {
        $ime = 'izvorno-'.Str::uuid().'.'.strtolower($fajl->extension() ?: 'jpg');
        Storage::disk('oglasi')->putFileAs($oglas->tenant_id.'/'.$oglas->id, $fajl, $ime);
        return $oglas->tenant_id.'/'.$oglas->id.'/'.$ime;
    }

    /**
     * Fotografija već pripremljena u browseru (WebP/JPG, do 1600px) + njena mala verzija:
     * samo se sačuvaju, bez obrade na serveru. Vraća putanju velike verzije.
     */
    public static function sacuvajGotovu(UploadedFile $velika, UploadedFile $mala, Oglas $oglas): string
    {
        $ext = strtolower(pathinfo($velika->getClientOriginalName(), PATHINFO_EXTENSION)) === 'webp' ? 'webp' : 'jpg';
        $folder = $oglas->tenant_id.'/'.$oglas->id;
        $ime = (string) Str::uuid();
        Storage::disk('oglasi')->putFileAs($folder, $velika, $ime.'.'.$ext);
        Storage::disk('oglasi')->putFileAs($folder, $mala, $ime.'-m.'.$ext);
        return $folder.'/'.$ime.'.'.$ext;
    }

    /** Da li oglas ima fotografiju koja još čeka obradu na serveru. */
    public static function imaNeobradjenih(Oglas $oglas): bool
    {
        return $oglas->slike()->where('putanja', 'like', '%/izvorno-%')->exists();
    }

    public static function neobradjena(OglasSlika $slika): bool
    {
        return str_contains(basename($slika->putanja), 'izvorno-');
    }

    /** Obrađuje sve neobrađene fotografije oglasa (poziva se posle odgovora korisniku). */
    public static function obradiOglas(Oglas $oglas): void
    {
        @set_time_limit(300);
        foreach ($oglas->slike()->get() as $slika) {
            if (self::neobradjena($slika)) {
                self::obradi($slika);
            }
        }
    }

    public static function obradi(OglasSlika $slika): void
    {
        $disk = Storage::disk('oglasi');
        $putanjaOriginala = $disk->path($slika->putanja);
        $izvor = self::ucitaj($putanjaOriginala);
        if (! $izvor) {
            return; // ostaje original — i dalje se prikazuje, samo nije optimizovan
        }

        $webp = function_exists('imagewebp');
        $osnova = dirname($slika->putanja).'/'.Str::uuid();
        foreach (self::VERZIJE as $sufiks => $v) {
            $disk->put($osnova.$sufiks.($webp ? '.webp' : '.jpg'), self::kodiraj($izvor, $v['sirina'], $v['kb'] * 1024, $webp));
        }
        imagedestroy($izvor);

        // Isti original može biti preuzet u više oglasa (kopiranje iz drugog stana) — menjamo svuda
        OglasSlika::where('putanja', $slika->putanja)->update(['putanja' => $osnova.($webp ? '.webp' : '.jpg')]);
        $disk->delete($slika->putanja);
    }

    /** Učitava sliku i ispravlja rotaciju iz EXIF-a; null ako je fajl prevelik ili neispravan. */
    private static function ucitaj(string $putanja)
    {
        $dim = @getimagesize($putanja);
        if (! $dim || $dim[0] * $dim[1] > 40_000_000 || ! function_exists('imagecreatefromstring')) {
            return null; // preko ~40 MP — premalo memorije na hostingu
        }
        @ini_set('memory_limit', '512M');
        $slika = @imagecreatefromstring((string) file_get_contents($putanja));
        if (! $slika) {
            return null;
        }
        if (($dim[2] ?? null) === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $ugao = [3 => 180, 6 => -90, 8 => 90][(int) ((@exif_read_data($putanja) ?: [])['Orientation'] ?? 1)] ?? 0;
            if ($ugao && ($rotirana = imagerotate($slika, $ugao, 0))) {
                imagedestroy($slika);
                $slika = $rotirana;
            }
        }
        return $slika;
    }

    /** Smanjuje na zadatu širinu i spušta kvalitet dok fajl ne stane u ciljanu veličinu. */
    private static function kodiraj($izvor, int $maxSirina, int $ciljBajtova, bool $webp): string
    {
        [$w, $h] = [imagesx($izvor), imagesy($izvor)];
        $nw = min($w, $maxSirina);
        $nh = max(1, (int) round($h * $nw / $w));
        $slika = imagecreatetruecolor($nw, $nh);
        imagefill($slika, 0, 0, imagecolorallocate($slika, 255, 255, 255)); // PNG providnost → belo
        imagecopyresampled($slika, $izvor, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $podaci = '';
        foreach ([82, 74, 66, 58, 50, 42] as $kvalitet) {
            ob_start();
            $webp ? imagewebp($slika, null, $kvalitet) : imagejpeg($slika, null, $kvalitet);
            $podaci = ob_get_clean();
            if (strlen($podaci) <= $ciljBajtova) {
                break;
            }
        }
        imagedestroy($slika);
        return $podaci;
    }
}
