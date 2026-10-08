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
        Schema::table('business_tag_codes', function (Blueprint $table) {
            $table->timestamp('qr_downloaded_at')->nullable()->after('assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_tag_codes', function (Blueprint $table) {
            $table->dropColumn('qr_downloaded_at');
        });
    }
};
