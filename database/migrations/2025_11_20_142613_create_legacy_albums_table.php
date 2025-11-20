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
        Schema::create('legacy_albums', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');          // creator
            $table->unsignedBigInteger('shared_with_id');   // selected user
            $table->string('title')->nullable();
            $table->string('conver_image')->nullable();
            $table->enum('type', ['legacy', 'normal'])->default('legacy');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_albums');
    }
};
