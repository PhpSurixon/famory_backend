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
        Schema::create('assgin_user_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id');
            $table->integer('user_id');
            $table->integer('user_group_id');
            $table->boolean('is_add')->default(1);
            $table->string('is_notify')->default('false');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assgin_user_groups');
    }
};
