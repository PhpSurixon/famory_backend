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
        Schema::create('device_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('device_token')->nullable();
            $table->string('platform')->nullable();
            $table->string('uuid')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('is_user_loggedin')->default(0);
            $table->string('time_zone')->nullable();
            $table->string('is_prod')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_details');
    }
};
