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
        Schema::create('order_details', function (Blueprint $table) 
        {
            $table->id();
            $table->bigInteger('order_id')->default(0);
            $table->bigInteger('product_id')->default(0);
            $table->bigInteger('cart_id')->nullable();
            $table->bigInteger('buy_quantity')->default(0);
            $table->double('product_unit_price',10,2)->default(0);
            $table->longText('product_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
