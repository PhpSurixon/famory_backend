<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            // Check if the column 'verified_at' exists before adding it
            if (!Schema::hasColumn('password_resets', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            // Drop the columns if they exist
           
            if (Schema::hasColumn('password_resets', 'verified_at')) {
                $table->dropColumn('verified_at');
            }
        });
    }
};
