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
        Schema::create('tag_collaborators', function (Blueprint $table) {
            $table->increments('id'); // int(10) UNSIGNED AUTO_INCREMENT
            $table->string('family_tag_id', 10);
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('invited_by')->nullable();
            $table->string('email', 100)->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'removed'])->default('pending');
            $table->enum('request_type', ['invitation', 'access_request'])->default('invitation');
            $table->text('request_message')->nullable();
            $table->timestamps();
            $table->softDeletes(); // deleted_at TIMESTAMP NULL
            $table->enum('permissions_level', ['view', 'add', 'edit'])->default('view');
            $table->string('avatar', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_collaborators');
    }
};
