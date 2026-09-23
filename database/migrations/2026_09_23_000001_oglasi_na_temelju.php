<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Oglašavanje stanova na Temelj.rs i upiti kupaca.
 * - tenants: stanje veze firme sa profilom investitora na Temelju
 * - buildings: podaci zgrade za oglas (lokacija na mapi, opremljenost, opciono dozvola/parcela)
 * - units: broj soba sa polovinama (2.5), terasa
 * - oglasi, oglas_slike, upiti
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('temelj_veza_status')->nullable();   // na_cekanju | odobrena | odbijena
            $table->string('temelj_veza_poruka')->nullable();   // razlog odbijanja
            $table->string('temelj_profil_url')->nullable();
            $table->timestamp('temelj_veza_provereno_at')->nullable();
        });

        Schema::table('buildings', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('adresa')->nullable();              // ako se razlikuje od adrese projekta
            $table->string('spratnost', 50)->nullable();        // npr. Po+P+5+Pk
            $table->string('grejanje', 120)->nullable();
            $table->boolean('lift')->nullable();
            $table->string('energetski_razred', 10)->nullable();
            $table->string('parking', 160)->nullable();
            $table->string('dozvola_broj', 120)->nullable();
            $table->date('dozvola_datum')->nullable();
            $table->string('dozvola_izdavalac')->nullable();
            $table->string('katastarska_parcela', 160)->nullable();
            $table->date('prijava_radova_datum')->nullable();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->decimal('broj_soba', 3, 1)->nullable()->change();
            $table->decimal('terasa_m2', 6, 2)->nullable()->after('kvadratura');
        });

        Schema::create('oglasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stan_id')->unique()->constrained('units')->cascadeOnDelete();
            $table->string('status')->default('aktivan');       // aktivan | pauziran | skinut
            $table->boolean('cena_na_upit')->default(false);
            $table->text('opis')->nullable();
            $table->string('temelj_url')->nullable();
            $table->timestamp('objavljen_at')->nullable();
            $table->timestamp('sinhronizovan_at')->nullable();
            $table->string('greska_sinhronizacije')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('oglas_slike', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oglas_id')->constrained('oglasi')->cascadeOnDelete();
            $table->string('putanja');
            $table->string('tip', 10)->default('slika');         // slika | tlocrt
            $table->unsignedSmallInteger('redosled')->default(0);
            $table->timestamps();
        });

        Schema::create('upiti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stan_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('temelj_id', 36)->unique();
            $table->string('ime');
            $table->string('email');
            $table->string('telefon', 50)->nullable();
            $table->text('poruka');
            $table->string('oglas_url')->nullable();
            $table->string('status')->default('novo');          // novo | u_kontaktu | zatvoreno
            $table->text('beleska')->nullable();
            $table->timestamp('primljeno_at');
            $table->timestamp('procitano_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upiti');
        Schema::dropIfExists('oglas_slike');
        Schema::dropIfExists('oglasi');
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('terasa_m2');
        });
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'adresa', 'spratnost', 'grejanje', 'lift', 'energetski_razred', 'parking',
                'dozvola_broj', 'dozvola_datum', 'dozvola_izdavalac', 'katastarska_parcela', 'prijava_radova_datum']);
        });
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['temelj_veza_status', 'temelj_veza_poruka', 'temelj_profil_url', 'temelj_veza_provereno_at']);
        });
    }
};
