<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Make user_id nullable for guest messages
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['user_id']);

            // Make column nullable
            $table->foreignId('user_id')->nullable()->change();

            // Re-add foreign key with nullOnDelete
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
