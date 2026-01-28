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
        if (Schema::hasTable('leads')) {
            return;
        }
        // Leads - Khách hàng tiềm năng
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 100);
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('company', 100)->nullable();
            $table->enum('source', [
                'website', 'facebook', 'google', 'instagram', 'tiktok',
                'referral', 'event', 'cold_call', 'landing_page', 'other'
            ])->default('website');
            $table->string('source_detail', 255)->nullable()->comment('Campaign name, referrer name, etc');

            // Lead qualification
            $table->integer('score')->default(0)->comment('Lead score 0-100');
            $table->enum('status', [
                'new', 'contacted', 'qualified', 'proposal', 'negotiation',
                'converted', 'lost', 'disqualified'
            ])->default('new');
            $table->enum('temperature', ['cold', 'warm', 'hot'])->default('cold');

            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            // Conversion
            $table->foreignId('converted_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();

            // Tracking
            $table->timestamp('last_contacted_at')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->decimal('estimated_value', 15, 2)->nullable();

            // Additional info
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_to']);
            $table->index(['score', 'temperature']);
            $table->index('email');
            $table->index('phone');
        });

        // Lead Activities - Lịch sử tương tác
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->enum('type', [
                'email_sent', 'email_opened', 'email_clicked',
                'call_made', 'call_received', 'meeting_scheduled', 'meeting_completed',
                'note_added', 'status_changed', 'score_changed',
                'sms_sent', 'form_filled', 'page_visited'
            ]);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at');

            $table->index(['lead_id', 'created_at']);
            $table->index('type');
        });

        // Lead Score History
        Schema::create('lead_score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->integer('score_before');
            $table->integer('score_after');
            $table->integer('score_change');
            $table->string('reason', 255);
            $table->timestamp('changed_at');

            $table->index(['lead_id', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_score_histories');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
