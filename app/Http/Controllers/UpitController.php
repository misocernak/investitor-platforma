<?php

namespace App\Http\Controllers;

use App\Models\Upit;
use App\Services\OglasiNaTemelju;
use Illuminate\Http\Request;

// Upiti kupaca sa Temelj.rs za stanove u ponudi
class UpitController extends Controller
{
    public function index(Request $request)
    {
        OglasiNaTemelju::preuzmiUpite(auth()->user()->tenant);

        $status = $request->get('status');
        $upiti = Upit::with('unit.building.project')
            ->when(isset(Upit::STATUSI[$status]), fn ($q) => $q->where('status', $status))
            ->orderByRaw("status = 'novo' DESC")
            ->latest('primljeno_at')
            ->get();
        $brojevi = Upit::selectRaw('status, COUNT(*) AS n')->groupBy('status')->pluck('n', 'status');

        return view('upiti.index', compact('upiti', 'status', 'brojevi'));
    }

    public function show(Upit $upit)
    {
        if (! $upit->procitano_at) {
            $upit->update(['procitano_at' => now()]);
        }
        $upit->load('unit.building.project', 'unit.oglas');
        $drugiUpiti = Upit::where('email', $upit->email)->where('id', '!=', $upit->id)->with('unit')->latest('primljeno_at')->get();

        return view('upiti.show', compact('upit', 'drugiUpiti'));
    }

    public function update(Request $request, Upit $upit)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Upit::STATUSI))],
            'beleska' => ['nullable', 'string', 'max:5000'],
        ]);
        $upit->update($data);

        return back()->with('uspesno', 'Upit je sačuvan.');
    }
}
