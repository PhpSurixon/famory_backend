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
        Schema::create('user_live_statuses', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->boolean('is_alive')->default(1)->comment('0 = died, 1 = live');
            $table->integer('deceased_by')->nullable();
            $table->integer('alive_by')->nullable();
            $table->boolean('notify')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_live_statuses');
    }
};
