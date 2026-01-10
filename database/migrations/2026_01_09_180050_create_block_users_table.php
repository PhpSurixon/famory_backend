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
        Schema::create('block_users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('marked_user_id');
            $table->boolean('is_live');
            $table->boolean('block');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('block_users');
    }
};
