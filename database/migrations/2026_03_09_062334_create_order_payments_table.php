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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->default(0);
            $table->string('payment_intent_id')->nullable();
            $table->string('stripe_transaction_id')->nullable();
            $table->double('amount',10,2)->default(0);
            $table->integer('payment_status')->default(0)->comment('0=>pending,1=>Paid,2=>failed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
