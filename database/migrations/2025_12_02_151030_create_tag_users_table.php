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
        Schema::create('tag_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->index();
            $table->foreignId('user_id')->index();
            $table->enum('role', ['collaborator', 'viewer'])->default('viewer');
            $table->enum('approval_status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_users');
    }
};
