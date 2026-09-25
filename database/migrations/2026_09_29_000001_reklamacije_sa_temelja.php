<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reklamacije koje kupac prijavljuje sa Temelja i prepiska sa kupcem.
 * - claims.izvor: "temelj" kad je kupac sam prijavio (inače null — uneo tim firme)
 * - claim_notes: user_id prazan za poruke kupca; od_kupca / vidljivo_kupcu određuju šta kupac vidi na Temelju
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('izvor', 20)->nullable()->after('status');
        });
        Schema::table('claim_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->boolean('od_kupca')->default(false);
            $table->boolean('vidljivo_kupcu')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('claim_notes', function (Blueprint $table) {
            $table->dropColumn(['od_kupca', 'vidljivo_kupcu']);
        });
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn('izvor');
        });
    }
};
