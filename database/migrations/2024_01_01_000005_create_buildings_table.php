<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('projekat_id')->constrained('projects')->cascadeOnDelete();
            $table->string('naziv');
            $table->unsignedInteger('broj_stanova')->nullable();
            $table->string('status')->default('U izgradnji');
            $table->text('napomena')->nullable();
            $table->boolean('arhiviran')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // dodeljene_zgrade - relevantno samo za ulogu Nadzor/izvodjac (PRD 6.8)
        Schema::create('building_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'building_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_user');
        Schema::dropIfExists('buildings');
    }
};
