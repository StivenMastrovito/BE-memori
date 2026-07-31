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
        Schema::create('feature_addons', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // base_page, timeline, playlist, countdown, video, letter, password_protection, qr_code, custom_domain, remove_watermark, analytics
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['base', 'section', 'feature']);
            $table->decimal('price', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_addons');
    }
};
