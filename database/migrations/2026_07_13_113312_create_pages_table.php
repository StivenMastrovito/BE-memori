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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theme_id')->nullable()->constrained('themes')->nullOnDelete();

            $table->string('slug')->unique();
            $table->string('title');
            $table->string('occasion'); // anniversary, birthday, wedding, graduation, birth, love, travel, memorial, pet

            $table->json('custom_colors')->nullable();
            $table->json('custom_fonts')->nullable();

            $table->decimal('base_price', 8, 2)->default(0);   // snapshot prezzo pubblicazione base
            $table->decimal('total_amount', 8, 2)->default(0); // base_price + sezioni a pagamento + addon

            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->string('cover_image_url')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
