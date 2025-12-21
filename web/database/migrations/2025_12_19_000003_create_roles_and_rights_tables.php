<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 70);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rights', function (Blueprint $table) {
            $table->id();
            $table->string('name', 70);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('user_right', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('right_id')->constrained()->cascadeOnDelete();
            $table->boolean('suppress')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'right_id']);
        });

        Schema::create('role_right', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('right_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'right_id']);
        });

        Schema::create('user_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('access_date');
            $table->boolean('is_successful_login')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_accesses');
        Schema::dropIfExists('role_right');
        Schema::dropIfExists('user_right');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('rights');
        Schema::dropIfExists('roles');
    }
};


