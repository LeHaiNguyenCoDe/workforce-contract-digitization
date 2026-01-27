<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guest_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_token', 64)->unique();
            $table->string('guest_name');
            $table->string('guest_contact'); // email or phone
            $table->enum('contact_type', ['email', 'phone'])->default('email');
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_chat_sessions');
    }
};
