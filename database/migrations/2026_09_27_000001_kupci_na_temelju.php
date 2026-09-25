<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kupac stana na Temelju: kad je stan prodat i upisan je email kupca, Temelj šalje poziv.
 * Ovde se pamti samo stanje poziva (Temelj ne javlja da li email ima nalog — samo "poslat" / "potvrdjen").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('temelj_kupac_status', 20)->nullable();   // poslat | potvrdjen
            $table->string('temelj_kupac_email')->nullable();        // adresa na koju je poslat poziv (za prepoznavanje promene)
            $table->timestamp('temelj_kupac_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['temelj_kupac_status', 'temelj_kupac_email', 'temelj_kupac_at']);
        });
    }
};
