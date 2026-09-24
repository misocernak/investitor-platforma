<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Samostalna registracija firmi i admin platforme.
 * - tenants: status naloga firme (na_cekanju → aktivan / odbijen / suspendovan), podaci iz APR-a, ovlašćenje
 * - users: firma nije obavezna (admin platforme nema firmu), funkcija, telefon, potvrda emaila
 * Postojeće firme ostaju "aktivan", a postojeći korisnici imaju potvrđen email.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('status')->default('aktivan')->after('naziv');   // na_cekanju | aktivan | odbijen | suspendovan
            $table->string('razlog_odbijanja')->nullable();
            $table->timestamp('registrovan_at')->nullable();
            $table->timestamp('odobren_at')->nullable();
            $table->boolean('apr_provereno')->default(false);                 // podaci firme potvrđeni iz APR registra (preko Temelja)
            $table->string('apr_status')->nullable();
            $table->string('ovlascenje_putanja')->nullable();                 // punomoćje / ovlašćenje (privatni fajl)
            $table->string('ovlascenje_naziv')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->change();  // admin platforme nema firmu
            $table->string('funkcija')->nullable()->after('ime_prezime');    // direktor | zakonski_zastupnik | ovlasceno_lice
            $table->string('telefon', 50)->nullable()->after('email');
            $table->string('email_token', 64)->nullable();
            $table->timestamp('email_potvrdjen_at')->nullable();
        });

        // Postojeći korisnici su uneti ručno — smatramo da im je email potvrđen
        \Illuminate\Support\Facades\DB::table('users')->whereNull('email_potvrdjen_at')->update(['email_potvrdjen_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['funkcija', 'telefon', 'email_token', 'email_potvrdjen_at']);
        });
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['status', 'razlog_odbijanja', 'registrovan_at', 'odobren_at', 'apr_provereno', 'apr_status',
                'ovlascenje_putanja', 'ovlascenje_naziv']);
        });
    }
};
