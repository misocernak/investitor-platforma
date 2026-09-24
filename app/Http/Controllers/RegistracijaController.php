<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Registracija;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Samostalna registracija firme: MB → podaci iz APR-a, lice koje registruje, ovlašćenje; zatim čeka odobrenje
class RegistracijaController extends Controller
{
    public function forma(Request $request)
    {
        return view('auth.registracija', ['mb' => preg_replace('/\D/', '', (string) $request->get('mb'))]);
    }

    /** Provera firme po MB (poziva je forma dok korisnik kuca). */
    public function firma(Request $request)
    {
        $mb = preg_replace('/\D/', '', (string) $request->get('mb'));
        if (strlen($mb) !== 8) {
            return response()->json(['ok' => false, 'poruka' => 'Matični broj ima 8 cifara.']);
        }
        if ($this->firmaVecImaNalog($mb)) {
            return response()->json(['ok' => false, 'poruka' => 'Ova firma već ima nalog na Temelj Investitoru. Obratite se vlasniku naloga u firmi.']);
        }
        $odg = Registracija::firmaIzRegistra($mb);
        if ($odg === null) {
            return response()->json(['ok' => true, 'pronadjena' => false, 'poruka' => 'Registar trenutno nije dostupan — unesite podatke firme ručno.']);
        }
        return response()->json($odg + ['ok' => true]);
    }

    public function sacuvaj(Request $request)
    {
        // Zaštita od botova: skriveno polje mora ostati prazno
        if (filled($request->input('sajt'))) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'maticni_broj' => ['required', 'digits:8'],
            'naziv' => ['required', 'string', 'max:255'],
            'pib' => ['required', 'digits:9'],
            'adresa' => ['nullable', 'string', 'max:255'],
            'grad' => ['nullable', 'string', 'max:120'],
            'ime_prezime' => ['required', 'string', 'max:255'],
            'funkcija' => ['required', 'in:'.implode(',', array_keys(User::FUNKCIJE))],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefon' => ['required', 'string', 'max:50', 'regex:/^[0-9 +\/()\-]{6,50}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'ovlascenje' => ['required_if:funkcija,ovlasceno_lice', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'izjava' => ['accepted'],
            'uslovi' => ['accepted'],
        ], [
            'maticni_broj.digits' => 'Matični broj firme ima tačno 8 cifara.',
            'pib.digits' => 'PIB ima tačno 9 cifara.',
            'email.unique' => 'Nalog sa ovom email adresom već postoji — prijavite se.',
            'telefon.regex' => 'Broj telefona nije ispravan.',
            'password.confirmed' => 'Lozinke se ne poklapaju.',
            'ovlascenje.required_if' => 'Ovlašćeno lice mora da priloži punomoćje ili ovlašćenje koje je potpisao zakonski zastupnik.',
            'izjava.accepted' => 'Potvrdite da ste zakonski zastupnik ili ovlašćeno lice firme.',
            'uslovi.accepted' => 'Prihvatite uslove korišćenja.',
        ]);

        if ($this->firmaVecImaNalog($data['maticni_broj'])) {
            return back()->withInput()->withErrors(['maticni_broj' => 'Ova firma već ima nalog na Temelj Investitoru.']);
        }

        // Podaci iz registra imaju prednost nad unetim (da se ne može registrovati tuđa firma pod drugim imenom)
        $registar = Registracija::firmaIzRegistra($data['maticni_broj']);
        $izRegistra = $registar && ! empty($registar['pronadjena']);
        if ($izRegistra) {
            $data['naziv'] = $registar['naziv'] ?: $data['naziv'];
            $data['pib'] = $registar['pib'] ?: $data['pib'];
            $data['adresa'] = $registar['adresa'] ?: $data['adresa'];
            $data['grad'] = $registar['grad'] ?: $data['grad'];
        }

        $korisnik = DB::transaction(function () use ($data, $request, $izRegistra, $registar) {
            $firma = Tenant::create([
                'naziv' => $data['naziv'],
                'pib' => $data['pib'],
                'maticni_broj' => $data['maticni_broj'],
                'adresa' => $data['adresa'] ?? null,
                'grad' => $data['grad'] ?? null,
                'kontakt_osoba' => $data['ime_prezime'],
                'telefon' => $data['telefon'],
                'email' => $data['email'],
                'status' => 'na_cekanju',
                'registrovan_at' => now(),
                'apr_provereno' => $izRegistra,
                'apr_status' => $izRegistra ? ($registar['apr_status'] ?? null) : null,
            ]);

            if ($request->hasFile('ovlascenje')) {
                $fajl = $request->file('ovlascenje');
                $firma->forceFill([
                    'ovlascenje_putanja' => $fajl->store('ovlascenja/'.$firma->id, 'documents'),
                    'ovlascenje_naziv' => mb_substr($fajl->getClientOriginalName(), 0, 255),
                ])->save();
            }

            return User::create([
                'tenant_id' => $firma->id,
                'ime_prezime' => $data['ime_prezime'],
                'funkcija' => $data['funkcija'],
                'email' => $data['email'],
                'telefon' => $data['telefon'],
                'password' => $data['password'],
                'uloga' => 'Vlasnik',
                'status_naloga' => 'Aktivan',
            ]);
        });

        Auth::login($korisnik);
        $request->session()->regenerate();
        AuditLog::zabelezi('registracija_firme', $korisnik->tenant, ['mb' => $data['maticni_broj']]);
        Registracija::posaljiPotvrduEmaila($korisnik);

        return redirect()->route('registracija.status');
    }

    /** Ekran dok se zahtev proverava (i posle odbijanja / suspenzije). */
    public function status()
    {
        $korisnik = auth()->user();
        if ($korisnik->jePlatforma()) {
            return redirect()->route('platforma.index');
        }
        $firma = $korisnik->tenant;
        if ($firma?->aktivan()) {
            return redirect()->route('dashboard');
        }
        return view('auth.status', compact('korisnik', 'firma'));
    }

    public function potvrda(string $token)
    {
        $korisnik = User::where('email_token', hash('sha256', $token))->first();
        if (! $korisnik) {
            return redirect()->route(auth()->check() ? 'registracija.status' : 'login')
                ->withErrors(['email' => 'Link za potvrdu nije ispravan ili je već iskorišćen.']);
        }
        $korisnik->forceFill(['email_potvrdjen_at' => now(), 'email_token' => null])->save();

        return redirect()->route(auth()->check() ? 'registracija.status' : 'login')
            ->with('uspesno', 'Email adresa je potvrđena.');
    }

    public function ponovoPotvrda()
    {
        $korisnik = auth()->user();
        if (! $korisnik->email_potvrdjen_at) {
            Registracija::posaljiPotvrduEmaila($korisnik);
        }
        return back()->with('uspesno', 'Poslali smo novi link za potvrdu na '.$korisnik->email.'.');
    }

    private function firmaVecImaNalog(string $mb): bool
    {
        return Tenant::where('maticni_broj', $mb)->whereIn('status', ['na_cekanju', 'aktivan', 'suspendovan'])->exists();
    }
}
