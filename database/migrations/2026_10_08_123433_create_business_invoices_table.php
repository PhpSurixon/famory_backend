<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * An invoice billed to a business for the Business Tags its shop sold. Status moves
     * draft -> issued -> paid, and draft/issued -> cancelled.
     */
    public function up(): void
    {
        Schema::create('business_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();              // BINV-2026-00001
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->string('status', 20)->default('draft')->index(); // draft, issued, paid, cancelled
            $table->date('invoice_date');

            // Snapshot of who was billed, so later edits to the business never change an invoice
            $table->string('bill_to_name');
            $table->string('bill_to_email')->nullable();
            $table->string('bill_to_mobile', 30)->nullable();
            $table->text('bill_to_address')->nullable();            // typed in when the invoice is generated
            $table->text('notes')->nullable();

            $table->unsignedInteger('total_codes')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamp('issued_at')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();

            $table->date('paid_date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_invoices');
    }
};
