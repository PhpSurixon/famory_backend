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
        if (!Schema::hasTable('user_contacts')) {
            Schema::create('user_contacts', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Name of the contact
                $table->string('email')->nullable(); // Email of the contact, must be unique
                $table->string('phone'); // Phone number of the contact
                $table->unsignedBigInteger('user_id'); // Foreign key to associate the contact with the user
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
         }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_contacts');
    }
};
