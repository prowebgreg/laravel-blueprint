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
        // PostgreSQL optimization: Combine index drops in single operation to minimize locks
        Schema::table('content_relations', function (Blueprint $table) {
            // Drop existing indexes that include source_id or target_id
            $table->dropIndex('content_relations_source_order_idx');
            $table->dropIndex('content_relations_target_idx');
            $table->dropUnique('content_relations_unique_relationship');
        });

        // Change column types: bigint → varchar(36) for UUID support
        // PostgreSQL handles this efficiently with empty table
        // For production with data, consider: ALTER TABLE ... USING source_id::varchar(36)
        Schema::table('content_relations', function (Blueprint $table) {
            $table->string('source_id', 36)->change();
            $table->string('target_id', 36)->change();
        });

        // Recreate indexes optimized for PostgreSQL varchar(36) columns
        // Note: varchar(36) indexes are ~4.6x larger than bigint but acceptable for CMS scale
        Schema::table('content_relations', function (Blueprint $table) {
            // Primary query pattern: fetch targets for source with ordering
            // Index order optimized for: WHERE source_type = ? AND source_id = ? ORDER BY order
            $table->index(['source_type', 'source_id', 'order'], 'content_relations_source_order_idx');

            // Reverse lookup: find all sources pointing to a target
            // Covers: WHERE target_type = ? AND target_id = ?
            $table->index(['target_type', 'target_id'], 'content_relations_target_idx');

            // Uniqueness constraint prevents duplicate relationships
            // Also serves as covering index for duplicate checks during INSERT
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
        Schema::table('content_relations', function (Blueprint $table) {
            // Drop indexes created in up()
            $table->dropIndex('content_relations_source_order_idx');
            $table->dropIndex('content_relations_target_idx');
            $table->dropUnique('content_relations_unique_relationship');
        });

        // PostgreSQL requires USING clause to convert varchar → bigint
        // Laravel Schema Builder doesn't support USING, so use raw SQL
        DB::statement('ALTER TABLE content_relations ALTER COLUMN source_id TYPE bigint USING source_id::bigint');
        DB::statement('ALTER TABLE content_relations ALTER COLUMN target_id TYPE bigint USING target_id::bigint');

        Schema::table('content_relations', function (Blueprint $table) {
            // Recreate original indexes with bigint columns
            $table->index(['source_type', 'source_id', 'order'], 'content_relations_source_order_idx');
            $table->index(['target_type', 'target_id'], 'content_relations_target_idx');
            $table->unique(
                ['source_type', 'source_id', 'target_type', 'target_id'],
                'content_relations_unique_relationship'
            );
        });
    }
};
