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
        Schema::create('info_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_name')->nullable();
            $table->string('page_url')->nullable();
            $table->longText('page_content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_pages');
    }
};
