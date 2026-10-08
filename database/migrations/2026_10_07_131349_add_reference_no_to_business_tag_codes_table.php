<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('business_tag_codes', function (Blueprint $table) {
            $table->string('reference_no', 20)->nullable()->after('tag_code');
        });

        // Backfill existing codes with a unique reference number, then enforce uniqueness.
        $used = [];
        DB::table('business_tag_codes')->orderBy('id')->select('id')->get()->each(function ($row) use (&$used) {
            do {
                $ref = 'REF' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            } while (isset($used[$ref]));
            $used[$ref] = true;

            DB::table('business_tag_codes')->where('id', $row->id)->update(['reference_no' => $ref]);
        });

        Schema::table('business_tag_codes', function (Blueprint $table) {
            $table->unique('reference_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_tag_codes', function (Blueprint $table) {
            $table->dropUnique(['reference_no']);
            $table->dropColumn('reference_no');
        });
    }
};
