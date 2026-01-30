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
        Schema::create('tags_purchase_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('tag_count')->default(0);
            $table->string('package_name')->nullable();
            $table->string('amount')->nullable();
            $table->date('date')->nullable();
            $table->enum('status',['success','failed'])->nullable();
            $table->string('payment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags_purchase_histories');
    }
};
