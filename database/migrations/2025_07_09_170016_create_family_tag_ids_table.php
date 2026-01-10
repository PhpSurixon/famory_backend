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
        Schema::create('family_tag_ids', function (Blueprint $table) {
            $table->id();
            $table->integer('created_user_id')->nullable();
            $table->string('family_tag_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->enum('privacy_type', ['Public', 'Private'])->default('Public');
            $table->longText('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_tag_ids');
    }
};
