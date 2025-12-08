<?php

declare(strict_types=1);

namespace App\Blocks\Contracts;

use Closure;

/**
 * Contract for all content block type classes.
 *
 * Content blocks are stored as JSONB arrays in the database, with each block
 * containing a 'type' identifier and optional 'data' fields. Block classes
 * implementing this interface define the schema for their data and provide
 * type validation.
 *
 * All methods are static since validation and schema definition operate at
 * the class level rather than on instances.
 */
interface BlockInterface
{
    /**
     * Get the unique type identifier for this block.
     *
     * This identifier is stored in the 'type' field of content blocks
     * and is used to resolve which block class handles the data.
     *
     * @return string The block type identifier (e.g., 'hero', 'cta', 'faq_section')
     */
    public static function type(): string;

    /**
     * Get the schema definition for this block's data fields.
     *
     * Returns an array mapping field names to their expected data types.
     * Used for validation and documentation purposes.
     *
     * Example return value:
     * [
     *     'heading' => 'string',
     *     'lede' => 'string',
     *     'image' => 'string',
     *     'cta_button_text' => 'string',
     * ]
     *
     * Note: No fields are required - blocks can have empty data objects.
     *
     * @return array<string, string> Field name => data type mapping
     */
    public static function schema(): array;

    /**
     * Validate block data against the schema definition.
     *
     * Checks that all provided data fields match their expected types
     * according to the schema. Calls the $fail closure with error messages
     * if validation fails.
     *
     * @param  array<string, mixed>  $data  The block data to validate
     * @param  Closure  $fail  Closure to call with validation error messages
     * @param  int  $index  The block's position in the content_blocks array (for error messages)
     */
    public static function validate(array $data, Closure $fail, int $index): void;
}
