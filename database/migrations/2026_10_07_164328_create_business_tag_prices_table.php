<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The price a business sells a Business Tag for. One row per business + tag.
     * It is filled with the tag's selling price by default and can be changed when
     * the tag codes are assigned to the business.
     */
    public function up(): void
    {
        Schema::create('business_tag_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('business_tag_id')->constrained('business_tags')->cascadeOnDelete();
            $table->decimal('business_price', 10, 2);
            $table->timestamps();

            $table->unique(['business_id', 'business_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_tag_prices');
    }
};
