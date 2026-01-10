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
        Schema::create('ad_address', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('house_number')->nullable();
            $table->string('road_name')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_address');
    }
};
