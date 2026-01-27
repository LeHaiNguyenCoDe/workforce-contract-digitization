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
        // Conversations table - optimized with indexes
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(); // Group name (null for private chat)
            $table->enum('type', ['private', 'group'])->default('private');
            $table->string('avatar')->nullable(); // Group avatar
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });

        // Pivot table for conversation participants
        Schema::create('conversation_user', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['member', 'admin'])->default('member');
            $table->timestamp('last_read_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->primary(['conversation_id', 'user_id']);
            $table->index(['user_id', 'is_pinned', 'updated_at']); // For listing user's conversations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_user');
        Schema::dropIfExists('conversations');
    }
};
