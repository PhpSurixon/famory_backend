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
            $table->foreignId('business_id')->nullable()->after('business_tag_id')
                ->constrained('businesses')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable()->after('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_tag_codes', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn(['business_id', 'assigned_at']);
        });
    }
};
