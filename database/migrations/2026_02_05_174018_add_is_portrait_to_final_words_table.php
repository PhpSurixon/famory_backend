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
        Schema::table('final_words', function (Blueprint $table) {
             $table->boolean('isPotrait')->default(true)->after('video_formats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_words', function (Blueprint $table) {
            $table->dropColumn('isPotrait');
        });
    }
};
