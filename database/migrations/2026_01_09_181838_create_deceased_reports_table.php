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
        Schema::create('deceased_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('deceased_by')->nullable();
            $table->integer('report_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deceased_reports');
    }
};
