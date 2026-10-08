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
        Schema::create('business_tag_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_tag_id')->constrained('business_tags')->cascadeOnDelete();
            $table->string('tag_code', 20)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_tag_codes');
    }
};
