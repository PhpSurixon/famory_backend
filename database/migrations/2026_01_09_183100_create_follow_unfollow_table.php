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
        Schema::create('follow_unfollow', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('following_id')->nullable();
            $table->boolean('status')->default(0)->comment('0 = View Top Posts, 1 = alredy view');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_unfollow');
    }
};
