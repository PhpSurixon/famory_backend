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
        Schema::table('subscription_settinges', function (Blueprint $table) {
            $table->enum('subscription_type', ['Consumable', 'Subscription'])->default('Subscription')->after('plan_id_android');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_settinges', function (Blueprint $table) {
           $table->dropColumn('subscription_type');
        });
    }
};
