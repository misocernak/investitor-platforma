<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\FiksneListe;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $korisnici = User::with('dodeljeneZgrade')->where('status_naloga', '!=', 'Deaktiviran')->get();
        $uloge = config('statusi.uloga');
        $zgrade = \App\Models\Building::where('arhiviran', false)->get();
        return view('users.index', compact('korisnici', 'uloge', 'zgrade'));
    }

    // Slanje pozivnica: Vlasnik i Administrator (PRD 5)
    public function store(Request $request)
    {
        $data = $request->validate([
            'ime_prezime' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'uloga' => ['required', FiksneListe::pravila('uloga')],
            'dodeljene_zgrade' => ['nullable', 'array'],
            'dodeljene_zgrade.*' => ['exists:buildings,id'],
        ]);

        $korisnik = User::create([
            'ime_prezime' => $data['ime_prezime'],
            'email' => $data['email'],
            'password' => $data['password'],
            'uloga' => $data['uloga'],
            'status_naloga' => 'Aktivan',
        ]);

        if ($korisnik->uloga === 'Nadzor_izvodjac' && !empty($data['dodeljene_zgrade'])) {
            $korisnik->dodeljeneZgrade()->sync($data['dodeljene_zgrade']);
        }

        AuditLog::zabelezi('kreiran_korisnik', $korisnik, ['uloga' => $korisnik->uloga]);

        return back()->with('uspesno', 'Korisnik "'.$korisnik->ime_prezime.'" je kreiran.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'uloga' => ['required', FiksneListe::pravila('uloga')],
            'status_naloga' => ['required', FiksneListe::pravila('status_naloga')],
        ]);

        // Admin ne sme menjati ulogu Vlasniku (PRD 5)
        if ($user->uloga === 'Vlasnik' && auth()->user()->uloga !== 'Vlasnik') {
            abort(403);
        }

        $user->update($data);
        AuditLog::zabelezi('izmena_korisnika', $user, $data);

        return back()->with('uspesno', 'Korisnik je ažuriran.');
    }
}
