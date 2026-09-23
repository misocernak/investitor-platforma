<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('naziv');
            $table->string('lokacija_adresa')->nullable();
            $table->string('lokacija_grad')->nullable();
            $table->string('tip')->nullable(); // Stambeni, Stambeno_poslovni, Drugo
            $table->unsignedInteger('broj_planiranih_stanova')->nullable();
            $table->date('datum_pocetka_gradnje')->nullable();
            $table->date('planirani_datum_zavrsetka')->nullable();
            $table->string('status')->default('Planiranje'); // menja ISKLJUCIVO korisnik rucno (PRD 2.3)
            $table->text('napomena')->nullable();
            $table->boolean('arhiviran')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
