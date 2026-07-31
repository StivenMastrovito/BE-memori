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
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_addon_id')->nullable()->constrained('feature_addons')->nullOnDelete();

            $table->string('type'); // text, photo, song, timeline, playlist, countdown, video, letter
            $table->unsignedInteger('order')->default(0);
            $table->json('content');
            $table->decimal('price', 8, 2)->default(0); // 0 se sezione base gratuita (text/photo/song)
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
