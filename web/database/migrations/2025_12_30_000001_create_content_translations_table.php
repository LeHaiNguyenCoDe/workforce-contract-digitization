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
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type'); // App\Models\Product, App\Models\Category, etc.
            $table->unsignedBigInteger('translatable_id');
            $table->string('field'); // name, description, content, etc.
            $table->string('locale', 10); // vi, en, ja, ko, etc.
            $table->text('value'); // Translated text
            $table->boolean('is_auto_translated')->default(true);
            $table->timestamps();

            // Composite unique constraint
            $table->unique(['translatable_type', 'translatable_id', 'field', 'locale'], 'content_trans_unique');
            
            // Indexes for faster lookups
            $table->index(['translatable_type', 'translatable_id']);
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_translations');
    }
};
