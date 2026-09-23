<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Checklist;
use App\Models\Claim;
use App\Models\Customer;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\ChecklistService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Tenant ----------
        $tenant = Tenant::create([
            'naziv' => 'BeoGradnja Invest d.o.o.',
            'pib' => '104587632',
            'email' => 'office@beogradnja.rs',
            'grad' => 'Beograd',
        ]);

        // ---------- Korisnici (PRD 6.8) ----------
        $vlasnik = User::create(['tenant_id' => $tenant->id, 'ime_prezime' => 'Nenad Stanković', 'email' => 'vlasnik@structureops.rs', 'password' => Hash::make('password'), 'uloga' => 'Vlasnik']);
        $admin = User::create(['tenant_id' => $tenant->id, 'ime_prezime' => 'Jelena Simić', 'email' => 'admin@structureops.rs', 'password' => Hash::make('password'), 'uloga' => 'Administrator']);
        User::create(['tenant_id' => $tenant->id, 'ime_prezime' => 'Ivana Perić', 'email' => 'operater@structureops.rs', 'password' => Hash::make('password'), 'uloga' => 'Operater']);
        $nadzor = User::create(['tenant_id' => $tenant->id, 'ime_prezime' => 'Milan Kostić', 'email' => 'nadzor@structureops.rs', 'password' => Hash::make('password'), 'uloga' => 'Nadzor_izvodjac']);

        // ---------- Tipovi dokumenata (PRD 6.5 osnovna lista) ----------
        foreach (['Lokacijski_uslovi','Gradjevinska_dozvola','Upotrebna_dozvola','Projekat_za_dozvolu','Projekat_izvedenog_stanja','Geodetski_elaborat','Energetski_pasos','Ugovor_sa_kupcem','Zapisnik_primopredaje_stana','Dokument_o_kretanju_otpada','Interni_dokument'] as $tip) {
            DocumentType::create(['naziv' => $tip]);
        }

        // ---------- Projekti (podaci iz dizajna) ----------
        $projekti = [
            ['naziv' => 'Dorćol Waterfront', 'lokacija_grad' => 'Beograd', 'tip' => 'Stambeno_poslovni', 'broj_planiranih_stanova' => 48, 'status' => 'U izgradnji'],
            ['naziv' => 'Vila Neimar Vračar', 'lokacija_grad' => 'Beograd', 'tip' => 'Stambeni', 'broj_planiranih_stanova' => 24, 'status' => 'Postprodaja (garancije)'],
            ['naziv' => 'Zvezdara Panorama', 'lokacija_grad' => 'Beograd', 'tip' => 'Stambeni', 'broj_planiranih_stanova' => 32, 'status' => 'Postprodaja (garancije)'],
        ];
        foreach ($projekti as $p) {
            $projekat = Project::create($p + ['tenant_id' => $tenant->id, 'lokacija_adresa' => 'Beograd']);
            $zgrada = Building::create([
                'tenant_id' => $tenant->id,
                'projekat_id' => $projekat->id,
                'naziv' => $projekat->naziv,
                'broj_stanova' => $projekat->broj_planiranih_stanova,
                'status' => $projekat->status === 'U izgradnji' ? 'U izgradnji' : 'U garanciji',
            ]);
            ChecklistService::obezbediZaZgradu($zgrada);
        }

        // ---------- Vila Neimar: stanovi, kupci, reklamacije (podaci iz dizajna) ----------
        $vilaNeimar = Building::where('naziv', 'Vila Neimar Vračar')->first();

        $kupci = [];
        foreach ([
            ['Ana Milić', 'ana.milic@example.com'],
            ['Dragan Nikolić', 'dragan.nikolic@example.com'],
            ['Marko Jovanović', 'marko@example.com'],
            ['Ivana Radović', 'ivana.radovic@example.com'],
            ['Petar Lazarević', 'petar.lazarevic@example.com'],
            ['Goran Vasić', 'vasic.goran@example.com'],
        ] as [$ime, $email]) {
            $kupci[$ime] = Customer::create(['tenant_id' => $tenant->id, 'ime_prezime' => $ime, 'email' => $email])->id;
        }

        $stanovi = [
            ['oznaka' => 'Stan 01', 'sprat' => 'Prizemlje', 'kvadratura' => 48.50, 'broj_soba' => 2, 'cena' => 115000, 'status' => 'Prodat_u_garanciji', 'kupac' => 'Ana Milić'],
            ['oznaka' => 'Stan 02', 'sprat' => 'I sprat', 'kvadratura' => 64.20, 'broj_soba' => 2, 'cena' => 152000, 'status' => 'Prodat_u_procesu_uknjizenja', 'kupac' => 'Dragan Nikolić'],
            ['oznaka' => 'Stan 03', 'sprat' => 'I sprat', 'kvadratura' => 78.00, 'broj_soba' => 3, 'cena' => 185000, 'status' => 'Prodat_u_garanciji', 'kupac' => 'Marko Jovanović'],
            ['oznaka' => 'Stan 04', 'sprat' => 'II sprat', 'kvadratura' => 52.10, 'broj_soba' => 2, 'cena' => 124000, 'status' => 'Za_prodaju', 'kupac' => null],
            ['oznaka' => 'Stan 05', 'sprat' => 'II sprat', 'kvadratura' => 76.80, 'broj_soba' => 3, 'cena' => 182000, 'status' => 'Rezervisan', 'kupac' => 'Ivana Radović'],
            ['oznaka' => 'Stan 06', 'sprat' => 'III sprat', 'kvadratura' => 98.20, 'broj_soba' => 4, 'cena' => 235000, 'status' => 'Prodat_u_garanciji', 'kupac' => 'Petar Lazarević'],
            ['oznaka' => 'Stan 07', 'sprat' => 'III sprat', 'kvadratura' => 48.50, 'broj_soba' => 2, 'cena' => 116400, 'status' => 'Za_prodaju', 'kupac' => null],
            ['oznaka' => 'Stan 08', 'sprat' => 'Potkrovlje', 'kvadratura' => 112.40, 'broj_soba' => 5, 'cena' => 275000, 'status' => 'Garancija_istekla', 'kupac' => 'Goran Vasić'],
        ];
        $stanMap = [];
        foreach ($stanovi as $s) {
            $stanMap[$s['oznaka']] = Unit::create([
                'tenant_id' => $tenant->id,
                'zgrada_id' => $vilaNeimar->id,
                'oznaka' => $s['oznaka'],
                'sprat' => $s['sprat'],
                'kvadratura' => $s['kvadratura'],
                'broj_soba' => $s['broj_soba'],
                'cena' => $s['cena'],
                'status' => $s['status'],
                'kupac_id' => $s['kupac'] ? $kupci[$s['kupac']] : null,
            ]);
        }

        // Dokumenti vezani za stanove (bez fajla - demo metapodaci)
        $stanMap['Stan 03']->documents()->createMany([
            ['tenant_id' => $tenant->id, 'tip' => 'Ugovor_sa_kupcem', 'naziv' => 'Ugovor o kupoprodaji nepokretnosti', 'datum_izdavanja' => '2024-06-15'],
            ['tenant_id' => $tenant->id, 'tip' => 'Zapisnik_primopredaje_stana', 'naziv' => 'Zapisnik primopredaje stana', 'datum_izdavanja' => '2024-09-20'],
        ]);

        // ---------- Reklamacije (PRD 6.7) ----------
        $reklamacije = [
            ['stan' => 'Stan 03', 'datum' => '2024-10-24', 'tip' => 'Grejanje_klima', 'opis' => 'Prijavljen gubitak pritiska na potisnom ventilu u dnevnom boravku. Izvođač instalacija obavešten.', 'status' => 'U_obradi', 'odgovorni' => 'Milan Kostić', 'rok' => '2024-10-28'],
            ['stan' => 'Stan 02', 'datum' => '2024-10-18', 'tip' => 'Stolarija', 'opis' => 'Krilo balkonskih vrata kači donju lajsnu pri zatvaranju. Izvođač stolarije najavio servis.', 'status' => 'Dodeljena', 'odgovorni' => 'Milan Kostić', 'rok' => '2024-11-01'],
            ['stan' => 'Stan 06', 'datum' => '2024-10-12', 'tip' => 'Vodovod', 'opis' => 'Fluktuacija pritiska termostatske baterije u master kupatilu.', 'status' => 'Dodeljena', 'odgovorni' => null, 'rok' => '2024-10-29'],
            ['stan' => 'Stan 06', 'datum' => '2024-10-20', 'tip' => 'Zidovi_fasada', 'opis' => 'Sleganje spoja iznad portala hodnika.', 'status' => 'Prijavljena', 'odgovorni' => null, 'rok' => null],
        ];
        foreach ($reklamacije as $r) {
            $claim = Claim::create([
                'tenant_id' => $tenant->id,
                'stan_id' => $stanMap[$r['stan']]->id,
                'kupac_id' => $stanMap[$r['stan']]->kupac_id,
                'datum_prijave' => $r['datum'],
                'tip_problema' => $r['tip'],
                'opis' => $r['opis'],
                'status' => $r['status'],
                'odgovorni_id' => $r['odgovorni'] ? $nadzor->id : null,
                'rok_resavanja' => $r['rok'],
            ]);
            $claim->notes()->create(['user_id' => $admin->id, 'tekst' => 'Interni prijem prijave. Telefonski poziv kupca zabeležen i kreiran predmet.']);
        }

        // ---------- Checkliste: par stavki vec reseno (demo) ----------
        $cl = Checklist::where('zgrada_id', $vilaNeimar->id)->where('tip_checkliste', 'Upotrebna_dozvola')->first();
        foreach (['Građevinska dozvola dodat', 'Projekat izvedenog stanja dodat', 'Geodetski elaborat dodat'] as $naziv) {
            $cl->items()->where('naziv_stavke', $naziv)->update(['zavrseno' => true]);
        }

        // Nadzoru dodeljena zgrada
        $nadzor->dodeljeneZgrade()->sync([$vilaNeimar->id]);
        $vlasnik->dodeljeneZgrade()->sync(Building::pluck('id'));

        $this->command->info('Seedovano. Login: admin@structureops.rs / password');
    }
}
