<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Komunikacija server–server sa Temelj.rs. Svaki zahtev je JSON POST potpisan HMAC-SHA256
 * zajedničkim ključem (config/temelj.php). Ključ se nikad ne šalje u browser.
 *   X-Temelj-Vreme  — unix vreme (prihvata se ±5 min)
 *   X-Temelj-Potpis — hex(hmac_sha256(vreme . "\n" . telo, kljuc))
 */
class TemeljApi
{
    public static function podesen(): bool
    {
        return strlen((string) config('temelj.kljuc')) >= 32 && config('temelj.url');
    }

    public static function potpis(string $vreme, string $telo): string
    {
        return hash_hmac('sha256', $vreme."\n".$telo, (string) config('temelj.kljuc'));
    }

    /** Šalje potpisan zahtev Temelju. Vraća [uspeh, odgovor(json|null), http_kod]. */
    public static function posalji(string $putanja, array $podaci): array
    {
        if (! self::podesen()) {
            return [false, null, 0];
        }
        $telo = json_encode($podaci, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $vreme = (string) time();
        try {
            $odgovor = Http::timeout(25)->connectTimeout(5)
                ->withHeaders([
                    'X-Temelj-Vreme' => $vreme,
                    'X-Temelj-Potpis' => self::potpis($vreme, $telo),
                    'Accept' => 'application/json',
                ])
                ->withBody($telo, 'application/json')
                ->post(config('temelj.url').$putanja);
        } catch (\Throwable $e) {
            Log::warning('Temelj API '.$putanja.' nedostupan: '.$e->getMessage());
            return [false, null, 0];
        }
        $json = $odgovor->json();
        if (! $odgovor->successful()) {
            Log::warning('Temelj API '.$putanja.' → HTTP '.$odgovor->status());
        }
        return [$odgovor->successful() && ($json['ok'] ?? false), is_array($json) ? $json : null, $odgovor->status()];
    }

    /** Proverava potpis dolaznog zahteva sa Temelja. */
    public static function ispravanZahtev(Request $request): bool
    {
        if (! self::podesen()) {
            return false;
        }
        $vreme = (string) $request->header('X-Temelj-Vreme', '');
        $potpis = (string) $request->header('X-Temelj-Potpis', '');
        return ctype_digit($vreme)
            && abs(time() - (int) $vreme) <= 300
            && hash_equals(self::potpis($vreme, $request->getContent()), $potpis);
    }
}
