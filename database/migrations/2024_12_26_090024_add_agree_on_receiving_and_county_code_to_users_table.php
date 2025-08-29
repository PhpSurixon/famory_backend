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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'agree_on_receiving')) {
                 $table->tinyInteger('agree_on_receiving')->default(0)->comment('Agreement on receiving notifications, 0 = No, 1 = Yes');
            }
            if (!Schema::hasColumn('users', 'country_code')) {
                $table->string('country_code', 10)->default('')->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['agree_on_receiving', 'country_code']);
        });
    }
};
