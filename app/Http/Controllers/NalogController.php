<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Moj nalog: promena lozinke (svi prijavljeni korisnici)
class NalogController extends Controller
{
    public function forma()
    {
        return view('nalog.lozinka');
    }

    public function lozinka(Request $request)
    {
        $data = $request->validate([
            'trenutna' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'Nove lozinke se ne poklapaju.',
            'password.min' => 'Nova lozinka mora imati bar 8 znakova.',
        ]);
        $korisnik = $request->user();
        if (! Hash::check($data['trenutna'], $korisnik->password)) {
            return back()->withErrors(['trenutna' => 'Trenutna lozinka nije tačna.']);
        }
        $korisnik->update(['password' => $data['password']]);

        return back()->with('uspesno', 'Lozinka je promenjena.');
    }
}
