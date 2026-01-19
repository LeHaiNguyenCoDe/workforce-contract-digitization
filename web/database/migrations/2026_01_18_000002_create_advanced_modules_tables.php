<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Shipping Partners
        Schema::create('shipping_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // ghn, ghtk, viettel_post
            $table->string('logo')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('api_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('config')->nullable();
            $table->json('supported_services')->nullable();
            $table->timestamps();
        });

        // Employees
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->date('join_date')->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamps();
        });

        // Attendance
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status')->default('present'); // present, absent, late, half_day
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        // Tasks
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        // Appointments/Calendar
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('type')->default('meeting'); // meeting, call, visit
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled
            $table->string('location')->nullable();
            $table->timestamps();
        });

        // Warranties
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('warranty_code')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'claimed', 'expired', 'void'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Warranty Claims
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained()->cascadeOnDelete();
            $table->text('issue_description');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('resolution')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // API Logs
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('endpoint');
            $table->integer('status_code');
            $table->integer('duration_ms');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('request_body')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'status_code']);
        });

        // Seed shipping partners
        \DB::table('shipping_partners')->insert([
            [
                'name' => 'Giao Hàng Nhanh',
                'code' => 'ghn',
                'is_active' => false,
                'api_url' => 'https://online-gateway.ghn.vn',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Giao Hàng Tiết Kiệm',
                'code' => 'ghtk',
                'is_active' => false,
                'api_url' => 'https://services.giaohangtietkiem.vn',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Viettel Post',
                'code' => 'viettel_post',
                'is_active' => false,
                'api_url' => 'https://partner.viettelpost.vn',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('shipping_partners');
    }
};
