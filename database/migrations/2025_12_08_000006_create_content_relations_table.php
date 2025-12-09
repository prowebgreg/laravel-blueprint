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
        Schema::create('content_relations', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 255);
            $table->unsignedBigInteger('source_id');
            $table->string('target_type', 255);
            $table->unsignedBigInteger('target_id');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            // Composite index for primary query pattern: fetch targets for source with ordering
            // Covers: WHERE source_type = ? AND source_id = ? ORDER BY order
            $table->index(['source_type', 'source_id', 'order'], 'content_relations_source_order_idx');

            // Index for reverse lookups: find all sources pointing to a target
            // Covers: WHERE target_type = ? AND target_id = ?
            $table->index(['target_type', 'target_id'], 'content_relations_target_idx');

            // Unique constraint prevents duplicate relationships
            // Also serves as an index for uniqueness checks during INSERT/UPDATE
            $table->unique(
                ['source_type', 'source_id', 'target_type', 'target_id'],
                'content_relations_unique_relationship'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_relations');
    }
};
