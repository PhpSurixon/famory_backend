<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * These are plain indexed columns, not foreign keys: the legacy users.id is a signed INT,
     * which a bigint foreign key cannot reference. Each step checks first, so the migration can
     * be re-run safely if an earlier attempt stopped part-way.
     */
    public function up(): void
    {
        Schema::table('business_tag_codes', function (Blueprint $table) {
            // The app user who bought the tag offline and registered it in the app.
            if (!Schema::hasColumn('business_tag_codes', 'sold_user_id')) {
                $table->unsignedBigInteger('sold_user_id')->nullable()->after('qr_downloaded_at');
            }
        });

        Schema::table('business_tag_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('business_tag_codes', 'sold_at')) {
                $table->timestamp('sold_at')->nullable()->after('sold_user_id');
            }
            // family_tag_ids.id of the tag the user created from this code.
            if (!Schema::hasColumn('business_tag_codes', 'family_tag_id_ref')) {
                $table->unsignedBigInteger('family_tag_id_ref')->nullable()->after('sold_at');
            }
        });

        $indexes = array_column(Schema::getIndexes('business_tag_codes'), 'name');

        Schema::table('business_tag_codes', function (Blueprint $table) use ($indexes) {
            if (!in_array('business_tag_codes_sold_user_id_index', $indexes, true)) {
                $table->index('sold_user_id');
            }
            if (!in_array('business_tag_codes_family_tag_id_ref_index', $indexes, true)) {
                $table->index('family_tag_id_ref');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = array_column(Schema::getIndexes('business_tag_codes'), 'name');

        Schema::table('business_tag_codes', function (Blueprint $table) use ($indexes) {
            if (in_array('business_tag_codes_sold_user_id_index', $indexes, true)) {
                $table->dropIndex('business_tag_codes_sold_user_id_index');
            }
            if (in_array('business_tag_codes_family_tag_id_ref_index', $indexes, true)) {
                $table->dropIndex('business_tag_codes_family_tag_id_ref_index');
            }
        });

        Schema::table('business_tag_codes', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['sold_user_id', 'sold_at', 'family_tag_id_ref'],
                fn ($c) => Schema::hasColumn('business_tag_codes', $c)
            ));
            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};
