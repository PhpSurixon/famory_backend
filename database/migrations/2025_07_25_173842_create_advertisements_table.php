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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ad_name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiration')->nullable();
            $table->date('renew_date')->nullable();
            $table->boolean('cancel_status')->default(0);
            $table->string('zip_code')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longtitude')->nullable();
            $table->string('action_button_text')->nullable();
            $table->string('action_button_link')->nullable();
            $table->boolean('is_national')->default(0);
            $table->string('full_screen_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->boolean('is_archieved')->default(0);
            $table->boolean('payment_status')->default(0);
            $table->string('card_id')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->string('charge_id')->nullable();
            $table->boolean('reminder_email_sent')->default(0);
            $table->date('free_expiration_date')->nullable();
            $table->boolean('show_ads_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
