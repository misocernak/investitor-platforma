<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Oglas;
use App\Models\OglasSlika;
use App\Models\Unit;
use App\Models\Upit;
use App\Services\OglasiNaTemelju;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Oglašavanje stanova na Temelj.rs — jedna stranica za oglas, sve ostalo automatski
class OglasController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        OglasiNaTemelju::proveriVezu($tenant);
        if ($tenant->povezanSaTemeljem()) {
            OglasiNaTemelju::posaljiNeposlate($tenant, 10);
        }

        $oglasi = Oglas::with(['unit.building.project', 'slike'])
            ->whereHas('unit')
            ->orderByRaw("FIELD(status, 'aktivan', 'pauziran', 'skinut')")
            ->latest('updated_at')->get();

        $upitiPoStanu = Upit::selectRaw('stan_id, COUNT(*) AS ukupno, SUM(status = ?) AS novih', ['novo'])
            ->groupBy('stan_id')->get()->keyBy('stan_id');

        // Stanovi u prodaji koji još nemaju oglas — jedan klik do oglasa
        $spremni = Unit::with('building.project')
            ->whereIn('status', Oglas::STATUSI_U_PRODAJI)
            ->where('arhiviran', false)
            ->whereDoesntHave('oglas')
            ->orderBy('zgrada_id')->orderBy('oznaka')
            ->get();

        return view('oglasi.index', compact('tenant', 'oglasi', 'upitiPoStanu', 'spremni'));
    }

    public function forma(Unit $unit)
    {
        if (! in_array($unit->status, Oglas::STATUSI_U_PRODAJI, true)) {
            return redirect()->route('units.show', $unit)
                ->withErrors(['status' => 'Oglas je moguć samo za stan u statusu „Za prodaju“ ili „Rezervisan“. Prvo promenite status stana.']);
        }
        $unit->load('building.project', 'oglas.slike');
        $tenant = auth()->user()->tenant;
        OglasiNaTemelju::proveriVezu($tenant);

        return view('oglasi.forma', ['stan' => $unit, 'oglas' => $unit->oglas, 'tenant' => $tenant]);
    }

    public function sacuvaj(Request $request, Unit $unit)
    {
        abort_unless(in_array($unit->status, Oglas::STATUSI_U_PRODAJI, true), 422, 'Stan nije u prodaji.');

        $data = $request->validate([
            'cena' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'cena_na_upit' => ['nullable', 'boolean'],
            'opis' => ['nullable', 'string', 'max:5000'],
            'kvadratura' => ['nullable', 'numeric', 'min:0', 'max:2000'],
            'broj_soba' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'sprat' => ['nullable', 'string', 'max:50'],
            'terasa_m2' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'slike' => ['nullable', 'array'],
            'slike.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'obrisi' => ['nullable', 'array'],
            'obrisi.*' => ['integer'],
            'tlocrt' => ['nullable', 'array'],
            'tlocrt.*' => ['integer'],
            'naslovna' => ['nullable', 'integer'],
            'zgrada.lat' => ['nullable', 'numeric', 'between:40,47'],
            'zgrada.lng' => ['nullable', 'numeric', 'between:17,24'],
            'zgrada.adresa' => ['nullable', 'string', 'max:255'],
            'zgrada.spratnost' => ['nullable', 'string', 'max:50'],
            'zgrada.grejanje' => ['nullable', 'string', 'max:120'],
            'zgrada.lift' => ['nullable', 'in:0,1'],
            'zgrada.energetski_razred' => ['nullable', 'string', 'max:10'],
            'zgrada.parking' => ['nullable', 'string', 'max:160'],
            'zgrada.dozvola_broj' => ['nullable', 'string', 'max:120'],
            'zgrada.dozvola_datum' => ['nullable', 'date'],
            'zgrada.dozvola_izdavalac' => ['nullable', 'string', 'max:255'],
            'zgrada.katastarska_parcela' => ['nullable', 'string', 'max:160'],
            'zgrada.prijava_radova_datum' => ['nullable', 'date'],
        ], [
            'slike.*.image' => 'Svaki fajl mora biti fotografija (JPG, PNG ili WebP).',
            'slike.*.max' => 'Fotografija može biti najviše 15 MB.',
            'zgrada.lat.between' => 'Lokacija na mapi mora biti u Srbiji.',
            'zgrada.lng.between' => 'Lokacija na mapi mora biti u Srbiji.',
        ]);

        $oglas = $unit->oglas ?: new Oglas(['stan_id' => $unit->id, 'status' => 'aktivan']);
        $postojece = $oglas->exists ? $oglas->slike()->get() : collect();
        $zaBrisanje = collect($data['obrisi'] ?? [])->map(fn ($id) => (int) $id);
        $noveSlike = $request->file('slike', []);

        $ostaje = $postojece->reject(fn ($s) => $zaBrisanje->contains($s->id));
        $maks = config('temelj.maks_slika');
        if ($ostaje->count() + count($noveSlike) > $maks) {
            return back()->withInput()->withErrors(['slike' => "Oglas može imati najviše {$maks} fotografija. Uklonite višak."]);
        }
        $tlocrt = collect($data['tlocrt'] ?? [])->map(fn ($id) => (int) $id);
        if ($ostaje->reject(fn ($s) => $tlocrt->contains($s->id))->isEmpty() && count($noveSlike) === 0) {
            return back()->withInput()->withErrors(['slike' => 'Dodajte bar jednu fotografiju stana ili zgrade — oglasi bez fotografije se ne objavljuju.']);
        }

        // 1) Stan (cena i osnovni podaci su isti kao u dosijeu stana)
        $unit->fill(collect($data)->only(['kvadratura', 'broj_soba', 'sprat', 'terasa_m2'])->all());
        if (array_key_exists('cena', $data)) {
            $unit->cena = $data['cena'];
        }
        $unit->save();

        // 2) Zgrada — unosi se jednom, važi za sve oglase u zgradi
        $zgrada = $unit->building;
        $podaciZgrade = $data['zgrada'] ?? [];
        if (array_key_exists('lift', $podaciZgrade)) {
            $podaciZgrade['lift'] = $podaciZgrade['lift'] === null ? null : (bool) $podaciZgrade['lift'];
        }
        $zgrada->fill($podaciZgrade)->save();

        // 3) Oglas
        $oglas->fill([
            'cena_na_upit' => (bool) ($data['cena_na_upit'] ?? false),
            'opis' => $data['opis'] ?? null,
        ]);
        if ($oglas->status === 'skinut') {
            $oglas->status = 'aktivan'; // ponovo u prodaji
        }
        $oglas->save();

        // 4) Fotografije: brisanje, tip (tlocrt), nove, redosled (naslovna prva)
        foreach ($postojece as $s) {
            if ($zaBrisanje->contains($s->id)) {
                Storage::disk('oglasi')->delete($s->putanja);
                $s->delete();
            } else {
                $s->update(['tip' => $tlocrt->contains($s->id) ? 'tlocrt' : 'slika']);
            }
        }
        $redosled = (int) $oglas->slike()->max('redosled');
        foreach ($noveSlike as $fajl) {
            $oglas->slike()->create([
                'putanja' => $this->sacuvajSliku($fajl, $oglas),
                'tip' => 'slika',
                'redosled' => ++$redosled,
            ]);
        }
        $this->urediRedosled($oglas, isset($data['naslovna']) ? (int) $data['naslovna'] : null);

        AuditLog::zabelezi('sacuvan_oglas', $oglas, ['stan' => $unit->oznaka]);

        // 5) Slanje na Temelj
        $tenant = auth()->user()->tenant;
        if (! $tenant->povezanSaTemeljem()) {
            $poruka = 'Oglas je sačuvan. Pojaviće se na Temelju čim Temelj odobri povezivanje vaše firme (Oglasi → Povezivanje).';
        } elseif (OglasiNaTemelju::sinhronizuj($oglas->fresh())) {
            $poruka = 'Oglas je objavljen na Temelju. Upiti kupaca stižu u „Upiti kupaca“.';
        } else {
            $poruka = 'Oglas je sačuvan, ali Temelj trenutno nije odgovorio — poslaćemo ga ponovo automatski.';
        }

        return redirect()->route('units.show', $unit)->with('uspesno', $poruka);
    }

    /** Pauziranje, ponovno aktiviranje ili uklanjanje oglasa sa Temelja. */
    public function status(Request $request, Oglas $oglas)
    {
        $akcija = $request->validate(['akcija' => ['required', 'in:pauziraj,aktiviraj,ukloni']])['akcija'];
        $stan = $oglas->unit;

        if ($akcija === 'aktiviraj' && ! in_array($stan->status, Oglas::STATUSI_U_PRODAJI, true)) {
            return back()->withErrors(['status' => 'Stan nije u prodaji — prvo mu promenite status u „Za prodaju“.']);
        }
        $oglas->update(['status' => ['pauziraj' => 'pauziran', 'aktiviraj' => 'aktivan', 'ukloni' => 'skinut'][$akcija]]);
        AuditLog::zabelezi('oglas_'.$akcija, $oglas, ['stan' => $stan->oznaka]);
        OglasiNaTemelju::sinhronizuj($oglas);

        return back()->with('uspesno', [
            'pauziraj' => 'Oglas je pauziran — ne vidi se na Temelju dok ga ponovo ne aktivirate.',
            'aktiviraj' => 'Oglas je ponovo aktivan na Temelju.',
            'ukloni' => 'Oglas je uklonjen sa Temelja.',
        ][$akcija]);
    }

    public function ponovi(Oglas $oglas)
    {
        $ok = OglasiNaTemelju::sinhronizuj($oglas);
        return back()->with('uspesno', $ok ? 'Izmene su poslate na Temelj.' : 'Temelj i dalje ne odgovara — pokušaćemo ponovo automatski.');
    }

    /** Čuva fotografiju; ako je GD dostupan, smanjuje je na najviše 2000px (brže slanje i učitavanje). */
    private function sacuvajSliku(UploadedFile $fajl, Oglas $oglas): string
    {
        $ime = $oglas->tenant_id.'/'.$oglas->id.'/'.Str::uuid().'.jpg';
        // Vrlo velike fotografije (preko ~40 MP) ne obrađujemo — premalo memorije na hostingu
        $dim = @getimagesize($fajl->getRealPath());
        $izvor = $dim && $dim[0] * $dim[1] <= 40_000_000 && function_exists('imagecreatefromstring')
            ? @imagecreatefromstring((string) file_get_contents($fajl->getRealPath()))
            : null;
        if ($izvor && function_exists('imagejpeg')) {
            [$w, $h] = [imagesx($izvor), imagesy($izvor)];
            $nw = min($w, 2000);
            $nh = (int) round($h * $nw / $w);
            $slika = imagecreatetruecolor($nw, $nh);
            imagefill($slika, 0, 0, imagecolorallocate($slika, 255, 255, 255));
            imagecopyresampled($slika, $izvor, 0, 0, 0, 0, $nw, $nh, $w, $h);
            ob_start();
            imagejpeg($slika, null, 85);
            Storage::disk('oglasi')->put($ime, ob_get_clean());
            imagedestroy($slika);
            imagedestroy($izvor);
            return $ime;
        }
        $ime = preg_replace('/\.jpg$/', '.'.$fajl->extension(), $ime);
        Storage::disk('oglasi')->putFileAs(dirname($ime), $fajl, basename($ime));
        return $ime;
    }

    /** Naslovna fotografija ide prva, ostale zadržavaju svoj redosled. */
    private function urediRedosled(Oglas $oglas, ?int $naslovnaId): void
    {
        $slike = $oglas->slike()->get()->sortBy(fn (OglasSlika $s) => [
            (int) $s->id === $naslovnaId ? 0 : 1, $s->tip === 'tlocrt' ? 1 : 0, $s->redosled,
        ])->values();
        foreach ($slike as $i => $s) {
            if ((int) $s->redosled !== $i) {
                $s->update(['redosled' => $i]);
            }
        }
    }
}
