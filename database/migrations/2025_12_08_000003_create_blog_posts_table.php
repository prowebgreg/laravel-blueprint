<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('status', 20)->default('draft');
            $table->jsonb('content_blocks')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_author', 255)->nullable();
            $table->boolean('meta_robots')->default(true);
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_type', 20)->default('article');
            $table->string('og_image', 2048)->nullable();
            $table->string('twitter_title', 255)->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image', 2048)->nullable();
            $table->jsonb('breadcrumbs')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique('slug', 'blog_posts_slug_unique');
            $table->index('status', 'blog_posts_status_index');
            $table->index('deleted_at', 'blog_posts_deleted_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
