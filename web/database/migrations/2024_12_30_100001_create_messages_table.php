<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Optimized for high-volume messaging with proper indexes
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->text('content')->nullable(); // Nullable for file-only messages
            $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
            $table->json('metadata')->nullable(); // For file info, reactions, etc.
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('deleted_at')->nullable(); // Soft delete for messages
            $table->timestamps();

            // CRITICAL: Composite index for fetching messages in a conversation
            // Used for: Loading chat history with pagination
            $table->index(['conversation_id', 'created_at', 'id'], 'idx_messages_conv_created');
            
            // Index for user's message lookup
            $table->index(['user_id', 'created_at']);
        });

        // Message attachments - separate table for better scalability
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->unsignedBigInteger('file_size');
            $table->string('thumbnail_path')->nullable(); // For images
            $table->timestamps();

            $table->index('message_id');
        });

        // Message read receipts - for tracking who has read what
        Schema::create('message_reads', function (Blueprint $table) {
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');

            $table->primary(['message_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('messages');
    }
};
