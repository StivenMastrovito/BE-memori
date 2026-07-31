<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('page_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_addon_id')->constrained('feature_addons');
            $table->decimal('price', 8, 2); // snapshot al momento dell'attivazione

            $table->timestamps();

            $table->unique(['page_id', 'feature_addon_id']); // evita doppie attivazioni della stessa feature
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_addons');
    }
};
