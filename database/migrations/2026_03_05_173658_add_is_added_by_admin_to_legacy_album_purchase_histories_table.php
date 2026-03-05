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
        Schema::table('legacy_album_purchase_histories', function (Blueprint $table) {
            $table->boolean('is_added_by_admin')->default(false)->after('payment_id');
        });

        Schema::table('tags_purchase_histories', function (Blueprint $table) {
            $table->boolean('is_added_by_admin')->default(false)->after('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legacy_album_purchase_histories', function (Blueprint $table) {
            $table->dropColumn('is_added_by_admin');
        });
        
        Schema::table('tags_purchase_histories', function (Blueprint $table) {
            $table->dropColumn('is_added_by_admin');
        });
    }
};
