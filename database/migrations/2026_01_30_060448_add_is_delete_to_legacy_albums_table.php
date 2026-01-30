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
        Schema::table('legacy_albums', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(0)->after('type');
        });

        Schema::table('family_tag_ids', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(0)->after('qrimage');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('remaining_lagecy_count')->default(0)->after('is_bot');
            $table->integer('remaining_tag_count')->default(0)->after('remaining_lagecy_count');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legacy_albums', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });
        Schema::table('family_tag_ids', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('remaining_lagecy_count');
            $table->dropColumn('remaining_tag_count');
        });
    }
};
