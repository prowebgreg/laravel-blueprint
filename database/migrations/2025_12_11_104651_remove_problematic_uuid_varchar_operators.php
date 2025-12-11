<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remove problematic custom PostgreSQL operators for UUID/varchar comparisons.
 *
 * These operators were causing issues where PostgreSQL would try to cast
 * varchar parameters to UUID when comparing with varchar columns,
 * resulting in "invalid input syntax for type uuid" errors.
 *
 * The operators override the default varchar equality comparison behavior
 * and are not needed - PostgreSQL handles UUID to varchar comparisons
 * correctly without custom operators when columns are properly typed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the custom operators first (they depend on the functions)
        DB::statement('DROP OPERATOR IF EXISTS = (uuid, character varying)');
        DB::statement('DROP OPERATOR IF EXISTS = (character varying, uuid)');

        // Drop the custom functions
        DB::statement('DROP FUNCTION IF EXISTS uuid_varchar_eq(uuid, character varying)');
        DB::statement('DROP FUNCTION IF EXISTS varchar_uuid_eq(character varying, uuid)');
    }

    /**
     * Reverse the migrations.
     *
     * Note: Recreating these operators is not recommended as they cause
     * type casting issues with PDO parameter binding.
     */
    public function down(): void
    {
        // Intentionally not recreating the problematic operators
        // If needed for specific use cases, they should be recreated
        // with proper type handling that doesn't interfere with PDO bindings
    }
};
