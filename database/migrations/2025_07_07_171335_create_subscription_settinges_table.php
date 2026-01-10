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
        Schema::create('subscription_settinges', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('plans')->nullable();
            $table->string('benefits')->nullable();
            $table->string('plan_id_ios')->nullable();
            $table->string('plan_id_android')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_settinges');
    }
};
