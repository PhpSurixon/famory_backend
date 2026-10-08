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
        Schema::create('business_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('origin_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('type_of_tag', 50);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_tags');
    }
};
