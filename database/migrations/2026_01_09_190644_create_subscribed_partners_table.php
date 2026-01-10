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
        Schema::create('subscribed_partners', function (Blueprint $table) {
            $table->id();
            $table->string('trusted_partner_id', 20);
            $table->string('user_id', 100);
            $table->string('payment_indent_id', 255);
            $table->string('charge_id', 255);
            $table->string('source', 100);
            $table->string('source_type', 100);
            $table->string('amount', 10);
            $table->string('type', 100);
            $table->string('subscription_type', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribed_partners');
    }
};
