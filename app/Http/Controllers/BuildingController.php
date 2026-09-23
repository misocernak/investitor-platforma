<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Building;
use App\Models\Project;
use App\Services\ChecklistService;
use App\Support\FiksneListe;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'naziv' => ['required', 'string', 'max:255'],
            'broj_stanova' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', FiksneListe::pravila('status_zgrade')],
        ]);
        $data['status'] = $data['status'] ?? 'U izgradnji';
        $data['projekat_id'] = $project->id;

        $zgrada = Building::create($data);
        ChecklistService::obezbediZaZgradu($zgrada);

        AuditLog::zabelezi('kreirana_zgrada', $zgrada, ['naziv' => $zgrada->naziv]);

        return redirect()->route('projects.show', $project)->with('uspesno', 'Zgrada "'.$zgrada->naziv.'" dodata, checkliste su formirane.');
    }

    public function update(Request $request, Building $building)
    {
        $data = $request->validate([
            'naziv' => ['required', 'string', 'max:255'],
            'broj_stanova' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', FiksneListe::pravila('status_zgrade')],
        ]);

        $stariStatus = $building->status;
        $building->update($data);

        if ($stariStatus !== $building->status) {
            AuditLog::zabelezi('promena_statusa_zgrade', $building, [
                'stari' => $stariStatus, 'novi' => $building->status,
            ]);
        }

        return back()->with('uspesno', 'Podaci zgrade su sačuvani.');
    }
}
