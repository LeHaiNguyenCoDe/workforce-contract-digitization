<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index(); // general, payment, shipping, seo
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, float, json
            $table->timestamps();

            $table->unique(['group', 'key']);
        });

        // Seed default settings
        $defaultSettings = [
            // General
            ['group' => 'general', 'key' => 'store_name', 'value' => 'Ceramic ERP', 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_email', 'value' => 'contact@ceramic-erp.com', 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_phone', 'value' => '0123456789', 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_address', 'value' => '', 'type' => 'string'],
            ['group' => 'general', 'key' => 'store_logo', 'value' => '', 'type' => 'string'],
            ['group' => 'general', 'key' => 'currency', 'value' => 'VND', 'type' => 'string'],
            ['group' => 'general', 'key' => 'timezone', 'value' => 'Asia/Ho_Chi_Minh', 'type' => 'string'],

            // Payment
            ['group' => 'payment', 'key' => 'cod_enabled', 'value' => 'true', 'type' => 'boolean'],
            ['group' => 'payment', 'key' => 'bank_transfer_enabled', 'value' => 'true', 'type' => 'boolean'],
            ['group' => 'payment', 'key' => 'vnpay_enabled', 'value' => 'false', 'type' => 'boolean'],
            ['group' => 'payment', 'key' => 'momo_enabled', 'value' => 'false', 'type' => 'boolean'],
            ['group' => 'payment', 'key' => 'bank_name', 'value' => '', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'bank_account', 'value' => '', 'type' => 'string'],
            ['group' => 'payment', 'key' => 'bank_owner', 'value' => '', 'type' => 'string'],

            // Shipping
            ['group' => 'shipping', 'key' => 'free_shipping_threshold', 'value' => '500000', 'type' => 'integer'],
            ['group' => 'shipping', 'key' => 'default_shipping_fee', 'value' => '30000', 'type' => 'integer'],
            ['group' => 'shipping', 'key' => 'ghn_enabled', 'value' => 'false', 'type' => 'boolean'],
            ['group' => 'shipping', 'key' => 'ghtk_enabled', 'value' => 'false', 'type' => 'boolean'],

            // SEO
            ['group' => 'seo', 'key' => 'meta_title', 'value' => 'Ceramic ERP - Hệ thống quản lý', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'meta_description', 'value' => '', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'meta_keywords', 'value' => '', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'google_analytics', 'value' => '', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'facebook_pixel', 'value' => '', 'type' => 'string'],
        ];

        foreach ($defaultSettings as $setting) {
            $setting['created_at'] = now();
            $setting['updated_at'] = now();
            \DB::table('settings')->insert($setting);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
