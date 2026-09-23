<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zgrada_id')->constrained('buildings')->cascadeOnDelete();
            $table->string('tip_checkliste'); // Upotrebna_dozvola, Uknjizba, Paket_za_banku (fiksno)
            $table->timestamps();
            $table->unique(['zgrada_id', 'tip_checkliste']);
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained()->cascadeOnDelete();
            $table->string('naziv_stavke');
            $table->string('povezani_tip_dokumenta')->nullable();
            $table->boolean('zavrseno')->default(false); // rucno se stiklira (PRD 2.3 / 6.6)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklists');
    }
};
