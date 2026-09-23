<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zgrada_id')->constrained('buildings')->cascadeOnDelete();
            $table->string('oznaka');
            $table->string('sprat')->nullable();
            $table->decimal('kvadratura', 8, 2)->nullable();
            $table->unsignedTinyInteger('broj_soba')->nullable();
            $table->decimal('cena', 14, 2)->nullable(); // interno polje, samo u admin prikazu (PRD 6.4)
            $table->foreignId('kupac_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('status')->default('Za_prodaju');
            $table->boolean('arhiviran')->default(false);
            $table->timestamps();

            $table->unique(['zgrada_id', 'oznaka']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
