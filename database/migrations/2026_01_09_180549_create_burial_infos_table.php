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
        Schema::create('burial_infos', function (Blueprint $table) {
            $table->id();
            $table->string('funeral_home')->nullable();
            $table->string('address')->nullable();
            $table->string('plot_number')->nullable();
            $table->unsignedBigInteger('contact')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('family_member_id')->nullable();
            $table->longText('notes')->nullable();
            $table->string('burial_pdf_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('burial_infos');
    }
};
