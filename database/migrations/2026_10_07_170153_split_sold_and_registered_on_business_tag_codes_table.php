<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * "Sold" and "registered" are now two separate things:
     *  - sold_at / sold_marked_by : the shop sold the tag (an admin marks it on the business's behalf,
     *                               or it is set automatically when a user registers the tag).
     *  - registered_user_id / registered_at : a user scanned and registered the tag in the app.
     * What used to be sold_user_id is the registered user, so it is renamed. Existing rows keep
     * their sold_at, and their registered_at is back-filled from it.
     */
    public function up(): void
    {
        $table = 'business_tag_codes';

        if (Schema::hasColumn($table, 'sold_user_id') && !Schema::hasColumn($table, 'registered_user_id')) {
            $indexes = array_column(Schema::getIndexes($table), 'name');
            if (in_array('business_tag_codes_sold_user_id_index', $indexes, true)) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropIndex('business_tag_codes_sold_user_id_index');
                });
            }

            Schema::table($table, function (Blueprint $t) {
                $t->renameColumn('sold_user_id', 'registered_user_id');
            });
        }

        Schema::table($table, function (Blueprint $t) use ($table) {
            if (!Schema::hasColumn($table, 'registered_at')) {
                $t->timestamp('registered_at')->nullable()->after('registered_user_id');
            }
            if (!Schema::hasColumn($table, 'sold_marked_by')) {
                // The admin who marked the tag sold on the business's behalf (null when set by a registration).
                $t->unsignedBigInteger('sold_marked_by')->nullable()->after('sold_at');
            }
        });

        DB::table($table)
            ->whereNotNull('registered_user_id')
            ->whereNull('registered_at')
            ->update(['registered_at' => DB::raw('sold_at')]);

        $indexes = array_column(Schema::getIndexes($table), 'name');
        if (!in_array('business_tag_codes_registered_user_id_index', $indexes, true)) {
            Schema::table($table, function (Blueprint $t) {
                $t->index('registered_user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = 'business_tag_codes';

        $indexes = array_column(Schema::getIndexes($table), 'name');
        if (in_array('business_tag_codes_registered_user_id_index', $indexes, true)) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex('business_tag_codes_registered_user_id_index');
            });
        }

        $drop = array_values(array_filter(
            ['registered_at', 'sold_marked_by'],
            fn ($c) => Schema::hasColumn($table, $c)
        ));
        if ($drop) {
            Schema::table($table, function (Blueprint $t) use ($drop) {
                $t->dropColumn($drop);
            });
        }

        if (Schema::hasColumn($table, 'registered_user_id') && !Schema::hasColumn($table, 'sold_user_id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->renameColumn('registered_user_id', 'sold_user_id');
            });
            Schema::table($table, function (Blueprint $t) {
                $t->index('sold_user_id');
            });
        }
    }
};
