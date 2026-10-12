<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * What a Business Tag costs Famory. Existing tags start at 0.00 until it is filled in.
     */
    public function up(): void
    {
        Schema::table('business_tags', function (Blueprint $table) {
            if (!Schema::hasColumn('business_tags', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->default(5)->after('origin_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_tags', function (Blueprint $table) {
            if (Schema::hasColumn('business_tags', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
        });
    }
};
