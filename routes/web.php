<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NalogController;
use App\Http\Controllers\PlatformaController;
use App\Http\Controllers\RegistracijaController;
use App\Http\Controllers\OglasController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TemeljVezaController;
use App\Http\Controllers\UpitController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'prikaziFormu'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Samostalna registracija firme (odobrava admin platforme)
    Route::get('/registracija', [RegistracijaController::class, 'forma'])->name('registracija');
    Route::post('/registracija', [RegistracijaController::class, 'sacuvaj'])->middleware('throttle:6,1');
    Route::get('/registracija/firma', [RegistracijaController::class, 'firma'])->middleware('throttle:30,1')->name('registracija.firma');
});

Route::get('/registracija/potvrda/{token}', [RegistracijaController::class, 'potvrda'])->name('registracija.potvrda');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Prijavljeni, bez obzira na stanje firme: ekran statusa zahteva i promena lozinke
Route::middleware('auth')->group(function () {
    Route::get('/registracija/status', [RegistracijaController::class, 'status'])->name('registracija.status');
    Route::post('/registracija/potvrda-ponovo', [RegistracijaController::class, 'ponovoPotvrda'])->middleware('throttle:3,1')->name('registracija.ponovo');
    Route::get('/nalog/lozinka', [NalogController::class, 'forma'])->name('nalog.lozinka');
    Route::post('/nalog/lozinka', [NalogController::class, 'lozinka']);
});

// Admin platforme — samo nalozi firmi, bez pristupa njihovim podacima
Route::middleware(['auth', 'role:Platforma'])->prefix('platforma')->name('platforma.')->group(function () {
    Route::get('/', [PlatformaController::class, 'index'])->name('index');
    Route::get('/firme/{firma}', [PlatformaController::class, 'show'])->name('show');
    Route::post('/firme/{firma}/odobri', [PlatformaController::class, 'odobri'])->name('odobri');
    Route::post('/firme/{firma}/odbij', [PlatformaController::class, 'odbij'])->name('odbij');
    Route::post('/firme/{firma}/suspenzija', [PlatformaController::class, 'suspenzija'])->name('suspenzija');
    Route::get('/firme/{firma}/ovlascenje', [PlatformaController::class, 'ovlascenje'])->name('ovlascenje');
});

// Aplikacija firme — samo za korisnike odobrene i aktivne firme
Route::middleware(['auth', 'firma'])->group(function () {

    // Nadzor/izvodjac - samo svoje dodele (PRD 5)
    Route::middleware('role:Vlasnik,Administrator,Operater')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/projekti', [ProjectController::class, 'index'])->name('projects.index');
        Route::post('/projekti', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projekti/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::patch('/projekti/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projekti/{project}', [ProjectController::class, 'destroy'])
            ->middleware('role:Vlasnik')->name('projects.destroy');

        Route::post('/projekti/{project}/zgrade', [BuildingController::class, 'store'])->name('buildings.store');
        Route::patch('/zgrade/{building}', [BuildingController::class, 'update'])->name('buildings.update');

        Route::get('/stanovi', [UnitController::class, 'index'])->name('units.index');
        Route::post('/zgrade/{building}/stanovi', [UnitController::class, 'store'])->name('units.store');
        Route::get('/stanovi/{unit}', [UnitController::class, 'show'])->name('units.show');
        Route::patch('/stanovi/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::post('/stanovi/{unit}/brzi-status', [UnitController::class, 'brziStatus'])->name('units.status');

        Route::get('/dokumenti', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/dokumenti', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/dokumenti/{document}/preuzmi', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/dokumenti/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('/checkliste', [ChecklistController::class, 'index'])->name('checklists.index');
        Route::get('/zgrade/{building}/checkliste', [ChecklistController::class, 'show'])->name('checklists.show');
        Route::patch('/checklist-stavke/{item}', [ChecklistController::class, 'toggleItem'])->name('checklists.toggle');

        // Oglasi na Temelj.rs i upiti kupaca
        Route::get('/oglasi', [OglasController::class, 'index'])->name('oglasi.index');
        Route::get('/stanovi/{unit}/oglas', [OglasController::class, 'forma'])->name('oglasi.forma');
        Route::post('/stanovi/{unit}/oglas', [OglasController::class, 'sacuvaj'])->name('oglasi.sacuvaj');
        Route::post('/oglasi/{oglas}/status', [OglasController::class, 'status'])->name('oglasi.status');
        Route::post('/oglasi/{oglas}/ponovi', [OglasController::class, 'ponovi'])->name('oglasi.ponovi');
        Route::post('/temelj/veza', [TemeljVezaController::class, 'zatrazi'])->middleware('role:Vlasnik,Administrator')->name('temelj.veza');
        Route::post('/temelj/veza/proveri', [TemeljVezaController::class, 'proveri'])->name('temelj.veza.proveri');
        Route::get('/temelj/profil', [TemeljVezaController::class, 'profil'])->middleware('role:Vlasnik,Administrator')->name('temelj.profil');
        Route::post('/temelj/profil', [TemeljVezaController::class, 'sacuvajProfil'])->middleware('role:Vlasnik,Administrator');
        Route::get('/upiti', [UpitController::class, 'index'])->name('upiti.index');
        Route::get('/upiti/{upit}', [UpitController::class, 'show'])->name('upiti.show');
        Route::patch('/upiti/{upit}', [UpitController::class, 'update'])->name('upiti.update');

        // Korisnici - samo Vlasnik/Administrator (PRD 5)
        Route::get('/korisnici', [UserController::class, 'index'])->middleware('role:Vlasnik,Administrator')->name('users.index');
        Route::post('/korisnici', [UserController::class, 'store'])->middleware('role:Vlasnik,Administrator')->name('users.store');
        Route::patch('/korisnici/{user}', [UserController::class, 'update'])->middleware('role:Vlasnik,Administrator')->name('users.update');
    });

    // Reklamacije - svi ulogovani (Nadzor vidi samo svoje, logika u kontroleru)
    Route::get('/reklamacije', [ClaimController::class, 'index'])->name('claims.index');
    Route::post('/reklamacije', [ClaimController::class, 'store'])->middleware('role:Vlasnik,Administrator,Operater')->name('claims.store');
    Route::get('/reklamacije/prilog/{file}', [ClaimController::class, 'prilog'])->name('claims.file');
    Route::get('/reklamacije/{claim}', [ClaimController::class, 'show'])->name('claims.show');
    Route::patch('/reklamacije/{claim}', [ClaimController::class, 'update'])->name('claims.update');
    Route::post('/reklamacije/{claim}/beleske', [ClaimController::class, 'dodajBelesku'])->name('claims.notes');
});
