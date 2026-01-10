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
        Schema::create('ads_sees', function (Blueprint $table) {
            $table->id();
            $table->integer('ads_id')->nullable();
            $table->integer('view')->nullable();
            $table->integer('click_to_open')->nullable();
            $table->integer('click_to_website')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_sees');
    }
};
