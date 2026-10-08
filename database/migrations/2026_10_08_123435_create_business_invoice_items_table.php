<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One line per Business Tag on an invoice. The tag name and the price per code are copied
     * here, so an issued invoice never changes if the tag or its Business Price is edited later.
     */
    public function up(): void
    {
        Schema::create('business_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_invoice_id')->constrained('business_invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('business_tag_id')->nullable()->index();
            $table->string('tag_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_invoice_items');
    }
};
