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
        Schema::create('media_variants', function (Blueprint $table) {
            // Primary key
            $table->uuid('id')->primary();

            // Foreign key to parent media asset
            $table->uuid('media_asset_id');
            $table->foreign('media_asset_id')
                ->references('id')
                ->on('media_assets')
                ->onDelete('cascade');

            // Variant dimensions
            $table->integer('width');
            $table->integer('height');

            // Output format
            $table->string('format', 10)->default('webp');

            // File properties
            $table->bigInteger('file_size');

            // S3 and CloudFront URLs
            $table->string('s3_key', 512);
            $table->string('cloudfront_url', 512);

            // Timestamps (no updated_at - variants are immutable)
            $table->timestamp('created_at')->useCurrent();

            // Unique constraint - one variant per width per asset
            // Note: PostgreSQL automatically creates a B-tree index for this unique constraint
            // which efficiently handles queries like: WHERE media_asset_id = ? [AND width = ?]
            $table->unique(['media_asset_id', 'width'], 'media_variants_asset_width_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_variants');
    }
};
