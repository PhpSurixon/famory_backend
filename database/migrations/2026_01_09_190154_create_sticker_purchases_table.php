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
        Schema::create('sticker_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 255);
            $table->integer('product_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->foreignId('ad_address_id')->nullable();
            $table->string('order_status', 25)->default('waiting');
            $table->string('shipping_tracking_number')->nullable();
            $table->string('payment_intent_id', 255)->nullable();
            $table->string('charge_id', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sticker_purchases');
    }
};
