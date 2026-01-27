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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // e.g., 'friend_request', 'new_message', 'group_invite'
            $table->string('notifiable_type')->nullable(); // Polymorphic type
            $table->unsignedBigInteger('notifiable_id')->nullable(); // Polymorphic ID
            $table->json('data'); // Additional notification data
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Index for fetching user's unread notifications
            $table->index(['user_id', 'read_at', 'created_at'], 'idx_notifications_user_unread');
            
            // Index for polymorphic queries
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
