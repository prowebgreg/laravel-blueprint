<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds relation_type column to support multiple attachments of the same
     * media asset with different type identifiers (e.g., 'hero_image', 'og_image').
     */
    public function up(): void
    {
        Schema::table('content_relations', function (Blueprint $table) {
            // Drop existing unique constraint
            $table->dropUnique('content_relations_unique_relationship');
        });

        // PostgreSQL type casting: no custom operators needed
        // UUID-to-varchar comparison handled via explicit CAST in application code

        Schema::table('content_relations', function (Blueprint $table) {
            // Add relation_type column for type identifiers
            $table->string('relation_type', 255)->nullable()->after('target_id');

            // Recreate unique constraint including relation_type
            // This allows same media to be attached multiple times with different types
            $table->unique(
                ['source_type', 'source_id', 'target_type', 'target_id', 'relation_type'],
                'content_relations_unique_typed_relationship'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_relations', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('content_relations_unique_typed_relationship');

            // Drop relation_type column
            $table->dropColumn('relation_type');
        });

        // No custom operators to drop

        Schema::table('content_relations', function (Blueprint $table) {
            // Recreate original unique constraint
            $table->unique(
                ['source_type', 'source_id', 'target_type', 'target_id'],
                'content_relations_unique_relationship'
            );
        });
    }
};
