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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('unique_order_id')->unique();
            $table->string('invoice_no')->nullable();
            $table->bigInteger('user_id')->default(0);
            $table->bigInteger('user_address_id')->default(0);
            $table->longText('address_data')->nullable();
            $table->timestamp('order_datetime')->nullable();
            $table->bigInteger('last_status_id')->default(1);
            $table->integer('payment_mode')->default(1)->comment('1->COD,2->Online');
            $table->double('subtotal_amount',10,2)->default(0);
            $table->double('shipping_amount',10,2)->default(0);
            $table->double('payable_amount',10,2)->default(0);
            $table->string('payment_intent_id')->nullable();
            $table->string('waybill')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
