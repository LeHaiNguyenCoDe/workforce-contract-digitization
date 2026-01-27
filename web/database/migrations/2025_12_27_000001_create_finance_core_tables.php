<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finance Core Tables Migration
 * 
 * BR-FIN-01: Thu tiền bán hàng
 * BR-FIN-02: Chi tiền
 * BR-FIN-03: Không cho âm quỹ
 * BR-DEBT-01: Công nợ phải thu
 * BR-DEBT-02: Công nợ phải trả
 */
return new class extends Migration {
    public function up(): void
    {
        // Quỹ tiền (BR-FIN-03)
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Tiền mặt, Ngân hàng ABC...
            $table->string('code', 50)->unique();
            $table->enum('type', ['cash', 'bank', 'other'])->default('cash');
            $table->decimal('balance', 20, 2)->default(0);
            $table->decimal('initial_balance', 20, 2)->default(0);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Finance Transaction - Thu/Chi tiền thực tế (BR-FIN-01, BR-FIN-02)
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 50)->unique();
            $table->foreignId('fund_id')->constrained('funds')->onDelete('cascade');
            $table->enum('type', ['receipt', 'payment']); // Thu / Chi
            $table->decimal('amount', 20, 2);
            $table->decimal('balance_before', 20, 2);
            $table->decimal('balance_after', 20, 2);
            $table->date('transaction_date');

            // Reference to source document
            $table->string('reference_type', 50)->nullable(); // order, expense, purchase, etc
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fund_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Account Receivable - Công nợ phải thu (BR-DEBT-01)
        Schema::create('account_receivables', function (Blueprint $table) {
            $table->id();
            $table->string('ar_code', 50)->unique();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_amount', 20, 2); // Tổng tiền phải thu
            $table->decimal('paid_amount', 20, 2)->default(0); // Đã thu
            $table->decimal('remaining_amount', 20, 2); // Còn phải thu
            $table->date('due_date')->nullable();
            $table->enum('status', ['open', 'partial', 'paid', 'overdue', 'written_off'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['due_date', 'status']);
        });

        // Account Payable - Công nợ phải trả (BR-DEBT-02)
        Schema::create('account_payables', function (Blueprint $table) {
            $table->id();
            $table->string('ap_code', 50)->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // Reference to source (purchase request, inbound batch, etc)
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->decimal('total_amount', 20, 2); // Tổng tiền phải trả
            $table->decimal('paid_amount', 20, 2)->default(0); // Đã trả
            $table->decimal('remaining_amount', 20, 2); // Còn phải trả
            $table->date('due_date')->nullable();
            $table->enum('status', ['open', 'partial', 'paid', 'overdue'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Payment History for AR/AP
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->enum('debt_type', ['ar', 'ap']); // Receivable or Payable
            $table->unsignedBigInteger('debt_id');
            $table->foreignId('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
            $table->decimal('amount', 20, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['debt_type', 'debt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('account_payables');
        Schema::dropIfExists('account_receivables');
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('funds');
    }
};
