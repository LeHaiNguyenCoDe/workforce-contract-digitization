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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('remember_token');
            $table->foreignId('updated_by')->nullable()->after('created_by');
            $table->foreignId('deleted_by')->nullable()->after('updated_by');
            $table->timestamp('deleted_at')->nullable()->after('deleted_by');
            $table->boolean('active')->default(true)->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by', 'deleted_at', 'active']);
        });
    }
};
