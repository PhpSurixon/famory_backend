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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_url')->nullable()->after('waybill');
            $table->string('stripe_refund_id')->nullable()->after('tracking_url');
            $table->text('cancel_reason')->nullable()->after('stripe_refund_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_url', 'stripe_refund_id', 'cancel_reason']);
        });
    }
};
