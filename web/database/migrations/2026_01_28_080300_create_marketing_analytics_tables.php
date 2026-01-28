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
        if (Schema::hasTable('marketing_analytics')) {
            return;
        }
        // Marketing KPIs - Daily aggregation
        Schema::create('marketing_kpis', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('channel', ['email', 'sms', 'push', 'social', 'all'])->default('all');
            $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->nullOnDelete();

            // Sending metrics
            $table->integer('total_sends')->default(0);
            $table->integer('total_deliveries')->default(0);
            $table->integer('total_bounces')->default(0);

            // Engagement metrics
            $table->integer('total_opens')->default(0);
            $table->integer('total_clicks')->default(0);
            $table->integer('total_unsubscribes')->default(0);

            // Conversion metrics
            $table->integer('total_conversions')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);

            // Cost metrics
            $table->decimal('total_cost', 15, 2)->default(0);

            // Calculated metrics
            $table->decimal('open_rate', 5, 2)->default(0)->comment('%');
            $table->decimal('click_rate', 5, 2)->default(0)->comment('%');
            $table->decimal('conversion_rate', 5, 2)->default(0)->comment('%');
            $table->decimal('roi', 10, 2)->default(0)->comment('Return on Investment');

            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['date', 'channel', 'campaign_id']);
            $table->index('date');
        });

        // Customer Value Tracking - CLV calculation
        Schema::create('customer_value_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Order metrics
            $table->date('first_order_date')->nullable();
            $table->date('last_order_date')->nullable();
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->decimal('average_order_value', 15, 2)->default(0);

            // Frequency
            $table->integer('days_since_first_order')->default(0);
            $table->integer('days_since_last_order')->default(0);
            $table->decimal('purchase_frequency', 10, 2)->default(0)->comment('Orders per month');

            // Value
            $table->decimal('clv_actual', 15, 2)->default(0)->comment('Actual Customer Lifetime Value');
            $table->decimal('clv_predicted', 15, 2)->default(0)->comment('Predicted CLV');

            // Churn
            $table->boolean('is_churned')->default(false);
            $table->decimal('churn_probability', 5, 2)->default(0)->comment('%');

            // Acquisition
            $table->string('acquisition_source', 100)->nullable();
            $table->decimal('acquisition_cost', 15, 2)->default(0);

            $table->timestamp('last_calculated_at');
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['clv_predicted', 'is_churned']);
        });

        // Attribution Touchpoints
        Schema::create('attribution_touchpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 100);
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            // Source tracking
            $table->string('source', 100)->nullable();
            $table->string('medium', 100)->nullable();
            $table->string('campaign', 255)->nullable();
            $table->string('content', 255)->nullable();
            $table->string('term', 255)->nullable();

            // Position in journey
            $table->enum('touchpoint_position', ['first', 'middle', 'last'])->nullable();
            $table->integer('position_order')->default(0);

            // Attribution value
            $table->decimal('revenue_attributed', 15, 2)->default(0);
            $table->string('attribution_model', 50)->nullable()->comment('last_click, first_click, linear, etc');

            // Context
            $table->string('referrer_url', 500)->nullable();
            $table->string('landing_page', 500)->nullable();
            $table->string('device_type', 50)->nullable();

            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
            $table->index(['order_id', 'touchpoint_position']);
            $table->index(['source', 'medium', 'campaign']);
        });

        // Marketing Sources - Aggregated performance by source
        Schema::create('marketing_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['paid', 'organic', 'referral', 'direct', 'social', 'email'])->default('organic');

            // UTM parameters
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 255)->nullable();

            // Performance metrics
            $table->integer('total_sessions')->default(0);
            $table->integer('total_users')->default(0);
            $table->integer('total_conversions')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);

            // Cost
            $table->decimal('cost_spent', 15, 2)->default(0);

            // Calculated
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('roi', 10, 2)->default(0);
            $table->decimal('cpa', 15, 2)->default(0)->comment('Cost Per Acquisition');

            $table->timestamp('last_updated_at');
            $table->timestamps();

            $table->index(['utm_source', 'utm_medium', 'utm_campaign']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_sources');
        Schema::dropIfExists('attribution_touchpoints');
        Schema::dropIfExists('customer_value_tracking');
        Schema::dropIfExists('marketing_kpis');
    }
};
