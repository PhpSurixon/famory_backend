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
        Schema::create('buy_new_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_id');
            $table->integer('buyer_user_id')->nullable();
            $table->string('buyer_user_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_new_tags');
    }
};
