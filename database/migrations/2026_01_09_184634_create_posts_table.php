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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('file')->nullable();
            $table->mediumText('video_formats')->nullable();
            $table->enum('post_type', ['public', 'private','family','album'])->default('public');
            $table->string('tag_id')->nullable();
            $table->integer('album_id')->nullable();
            $table->string('media_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
