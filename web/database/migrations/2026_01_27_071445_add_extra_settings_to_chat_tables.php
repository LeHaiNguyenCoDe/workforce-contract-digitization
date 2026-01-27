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
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('messaging_permissions')->default('all')->after('type'); // all, admin_only
            $table->integer('disappearing_messages_ttl')->nullable()->after('messaging_permissions'); // seconds, null = disabled
        });

        Schema::table('conversation_user', function (Blueprint $table) {
            $table->boolean('read_receipts_enabled')->default(true)->after('is_pinned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['messaging_permissions', 'disappearing_messages_ttl']);
        });

        Schema::table('conversation_user', function (Blueprint $table) {
            $table->dropColumn('read_receipts_enabled');
        });
    }
};
