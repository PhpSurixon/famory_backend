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
        Schema::create('stripe_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->longText('res_detail')->nullable();
            $table->integer('card_number')->nullable();
            $table->string('stripe_card_id', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_details');
    }
};
