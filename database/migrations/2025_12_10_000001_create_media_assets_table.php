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
        Schema::create('media_assets', function (Blueprint $table) {
            // Primary key
            $table->uuid('id')->primary();

            // File identification
            $table->string('filename', 255);
            $table->string('original_name', 255);

            // Type and storage
            $table->string('media_type', 50);
            $table->string('folder', 50);

            // File properties
            $table->bigInteger('file_size');
            $table->jsonb('dimensions')->nullable();
            $table->string('mime_type', 127);

            // Processing state
            $table->string('state', 50)->default('uploading');
            $table->text('error_message')->nullable();

            // S3 and CloudFront URLs
            $table->string('s3_key_original', 512);
            $table->string('cloudfront_url_original', 512);

            // Metadata
            $table->string('alt_text', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('caption')->nullable();

            // Advanced features
            $table->jsonb('focal_point')->nullable();
            $table->jsonb('tags')->default('[]');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common query patterns
            $table->index('state', 'media_assets_state_idx');
            $table->index('media_type', 'media_assets_media_type_idx');
            $table->index('created_at', 'media_assets_created_at_idx');

            // Composite index for common filtering + sorting pattern
            $table->index(['state', 'created_at'], 'media_assets_state_created_at_idx');
            $table->index(['media_type', 'created_at'], 'media_assets_media_type_created_at_idx');
        });

        // PostgreSQL-specific indexes (must be created after table exists)
        // Partial index for soft deletes (more efficient than indexing all rows)
        DB::statement('CREATE INDEX media_assets_deleted_at_idx ON media_assets (deleted_at) WHERE deleted_at IS NOT NULL');

        // GIN index on tags JSONB column for array containment queries (@> operator)
        DB::statement('CREATE INDEX media_assets_tags_gin_idx ON media_assets USING GIN (tags)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
