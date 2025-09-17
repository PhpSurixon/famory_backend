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
        Schema::create('death_confirmations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');        // which user's death is to be confirmed
            $table->unsignedBigInteger('trusted_user_id'); // confirmed by user_id
            $table->enum('status', ['pending', 'confirmed', 'not_confirmed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('death_confirmations');
    }
};
