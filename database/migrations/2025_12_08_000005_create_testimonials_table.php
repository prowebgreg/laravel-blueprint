<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 20)->default('draft');
            $table->string('author_name', 255)->nullable();
            $table->string('author_title', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->text('quote')->nullable();
            $table->smallInteger('rating')->nullable();
            $table->string('avatar', 2048)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status', 'testimonials_status_index');
            $table->index('deleted_at', 'testimonials_deleted_at_index');
        });

        // Add CHECK constraint for rating (1-5)
        DB::statement('ALTER TABLE testimonials ADD CONSTRAINT testimonials_rating_check CHECK (rating >= 1 AND rating <= 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
