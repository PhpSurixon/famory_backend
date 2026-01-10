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
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('password_resets')) {
            Schema::table('password_resets', function (Blueprint $table) {
                if (!Schema::hasColumn('password_resets', 'type')) {
                    $table->enum('type', ['forgot_pass', 'signup'])
                    ->default('forgot_pass')
                    ->after('otp');
                }
            });
        }
    }
};
