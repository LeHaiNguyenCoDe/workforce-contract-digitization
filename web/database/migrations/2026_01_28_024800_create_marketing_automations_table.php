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
        if (Schema::hasTable('marketing_automations')) {
            return;
        }
        Schema::create('marketing_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('trigger_type', [
                'order_placed',
                'order_completed',
                'order_cancelled',
                'customer_registered',
                'cart_abandoned',
                'product_viewed',
                'review_submitted'
            ])->default('order_placed');
            $table->enum('action_type', [
                'email',
                'sms',
                'notification',
                'webhook'
            ])->default('email');
            $table->integer('delay_days')->default(0);
            $table->integer('delay_hours')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable();
            $table->unsignedBigInteger('email_template_id')->nullable();
            $table->timestamps();

            $table->index('trigger_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_automations');
    }
};
