<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dokumentacija za kupca na Temelju: investitor bira koje dokumente kupac vidi.
 * Postojeći dokumenti stana postaju vidljivi kupcu tog stana; dokumenti zgrade ostaju skriveni dok ih investitor ne uključi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('vidljivo_kupcu')->default(false);
            $table->index(['zgrada_id', 'stan_id', 'aktivna_verzija', 'vidljivo_kupcu'], 'documents_kupac_idx');
        });
        DB::table('documents')->whereNotNull('stan_id')->update(['vidljivo_kupcu' => true]);
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_kupac_idx');
            $table->dropColumn('vidljivo_kupcu');
        });
    }
};
