<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('naziv');
            $table->string('pib');
            $table->string('maticni_broj')->nullable();
            $table->string('adresa')->nullable();
            $table->string('grad')->nullable();
            $table->string('kontakt_osoba')->nullable();
            $table->string('telefon')->nullable();
            $table->string('email');
            $table->string('logo')->nullable();
            $table->text('napomena')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
