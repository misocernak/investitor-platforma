<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Osnovna fiksna lista + admin moze da doda nove tipove (PRD 6.5)
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('naziv')->unique();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('projekat_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('zgrada_id')->nullable()->constrained('buildings')->nullOnDelete();
            $table->foreignId('stan_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('tip');
            $table->string('naziv');
            $table->date('datum_izdavanja')->nullable(); // rucni unos
            $table->string('izdavalac')->nullable();
            $table->string('putanja_fajla')->nullable();
            $table->unsignedInteger('verzija')->default(1);
            $table->boolean('aktivna_verzija')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'tip']);
            $table->index(['zgrada_id', 'tip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_types');
    }
};
