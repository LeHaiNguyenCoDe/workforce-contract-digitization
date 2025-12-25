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
        Schema::create('quality_checks', function (Blueprint $header) {
            $header->id();
            $header->string('batch_number')->unique();
            $header->foreignId('product_id')->constrained();
            $header->foreignId('supplier_id')->constrained();
            $header->foreignId('inspector_id')->constrained('users');
            $header->date('check_date');
            $header->enum('status', ['passed', 'failed', 'pending'])->default('pending');
            $header->integer('score')->default(0);
            $header->text('notes')->nullable();
            $header->json('issues')->nullable();
            $header->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_checks');
    }
};
