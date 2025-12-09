<?php

declare(strict_types=1);

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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 20)->default('draft');
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status', 'faqs_status_index');
            $table->index('deleted_at', 'faqs_deleted_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
