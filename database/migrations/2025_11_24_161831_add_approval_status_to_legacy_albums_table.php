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
            $table->enum('approval_status', ['pending', 'accepted', 'rejected'])->after('type')->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'unpaid'])->after('approval_status')->default('pending');
            $table->string('payment_id')->after('payment_status')->nullable();
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'accepted', 'rejected'])->after('album_cover')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legacy_albums', function (Blueprint $table) {
            $table->dropColumn('approval_status');
            $table->dropColumn('payment_status');
            $table->dropColumn('payment_id');
        });
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
