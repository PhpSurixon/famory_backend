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
        Schema::create('trusted_partners', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('company_name', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->string('lat', 100)->nullable();
            $table->string('lng', 100)->nullable();
            $table->string('phone', 12)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('featured_partner', 10)->default('0');
            $table->string('renewal_date', 10)->nullable();
            $table->integer('featured_company_price_id')->nullable();
            $table->string('cancel_status', 10)->default('0');
            $table->string('cancel_at', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusted_partners');
    }
};
