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
        Schema::table('album_users', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'accepted', 'rejected'])->after('role')->default('pending');
        });

        // Delete column in another table
        Schema::table('albums', function (Blueprint $table) {
            if (Schema::hasColumn('albums', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('album_users', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
