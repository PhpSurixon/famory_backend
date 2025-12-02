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
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reporter_id');       // jisne report kiya
            $table->unsignedBigInteger('reported_user_id');  // jisko report kiya
            $table->string('reason')->nullable();            // eg. spam, abuse, nudity
            $table->text('description')->nullable();  
            $table->timestamps();
            $table->unique(['reporter_id', 'reported_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
