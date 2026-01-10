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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->boolean('isSeen')->default(0)->comment('0=>Not Seen ,1=>Seen');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->integer('group_id')->nullable();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->integer('marked_user_id')->nullable();
            $table->boolean('has_actioned')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
