<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Checklist;

// Provisioning checklisti na nivou servera (seed logika u bazi, ne hardkod u frontendu - PRD 6.6)
class ChecklistService
{
    public static function obezbediZaZgradu(Building $zgrada): void
    {
        foreach (config('checklists') as $tip => $stavke) {
            $checklist = Checklist::firstOrCreate(
                ['zgrada_id' => $zgrada->id, 'tip_checkliste' => $tip]
            );
            foreach ($stavke as $naziv => $tipDokumenta) {
                $checklist->items()->firstOrCreate(
                    ['naziv_stavke' => $naziv],
                    ['povezani_tip_dokumenta' => $tipDokumenta]
                );
            }
        }
    }
}
