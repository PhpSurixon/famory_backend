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
        Schema::create('scheduling_posts', function (Blueprint $table) {
            $table->id(); // bigint unsigned
            $table->integer('post_id')->nullable();
            $table->string('schedule_type', 20)->default('now');
            $table->string('schedule_time', 20)->nullable();
            $table->date('schedule_date')->nullable();
            $table->string('reoccurring_type', 5)->default('no');
            $table->string('reoccurring_time', 20)->nullable();
            $table->boolean('is_post')->default(false);
            $table->string('timezone', 250)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduling_posts');
    }
};
