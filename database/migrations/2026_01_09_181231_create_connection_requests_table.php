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
        Schema::create('connection_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('group_id')->nullable();
            $table->boolean('is_verify')->default(0);
            $table->text('msg')->nullable();
            $table->string('status')->nullable();
            $table->string('guest_email')->nullable();
            $table->boolean('sender_delete')->default(0);
            $table->boolean('user_delete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connection_requests');
    }
};
