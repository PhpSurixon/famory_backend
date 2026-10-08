<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Which invoice bills this code. A code can be on only one invoice, so it is never billed
     * twice. The link is cleared when the invoice is cancelled.
     */
    public function up(): void
    {
        Schema::table('business_tag_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('business_tag_codes', 'business_invoice_id')) {
                $table->foreignId('business_invoice_id')->nullable()->after('family_tag_id_ref')
                    ->constrained('business_invoices')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_tag_codes', function (Blueprint $table) {
            if (Schema::hasColumn('business_tag_codes', 'business_invoice_id')) {
                $table->dropForeign(['business_invoice_id']);
                $table->dropColumn('business_invoice_id');
            }
        });
    }
};
