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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('reseller_price', 10, 2)->nullable();
            $table->integer('count')->nullable();
            $table->integer('total_purchased')->default(0);
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->string('type_of_tag')->nullable();
            $table->text('tag_purpose')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_favourite')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
