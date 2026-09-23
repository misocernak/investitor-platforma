<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stan_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('kupac_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('datum_prijave'); // rucni unos (default danas, korisnik moze promeniti)
            $table->string('tip_problema'); // fiksna lista PRD 6.7
            $table->string('tip_problema_drugo')->nullable(); // samo kad je tip = Drugo
            $table->text('opis');
            $table->string('status')->default('Prijavljena');
            $table->foreignId('odgovorni_id')->nullable()->constrained('users')->nullOnDelete(); // rucna dodela (PRD 2.5)
            $table->date('rok_resavanja')->nullable(); // rucni unos, NE automatski (PRD 2.2)
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['stan_id']);
        });

        Schema::create('claim_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->string('putanja_fajla');
            $table->string('originalni_naziv');
            $table->timestamps();
        });

        Schema::create('claim_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('tekst');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_notes');
        Schema::dropIfExists('claim_files');
        Schema::dropIfExists('claims');
    }
};
