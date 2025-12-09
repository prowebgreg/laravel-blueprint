<?php

declare(strict_types=1);

namespace App\Blocks;

use App\Blocks\Contracts\BlockInterface;
use Closure;

/**
 * Call-to-Action (CTA) content block.
 *
 * Displays a heading, supporting text (lede), and a customizable button
 * to encourage user action. Commonly used for conversions, newsletter signups,
 * contact forms, or any primary action point on a page.
 */
class CtaBlock implements BlockInterface
{
    /**
     * Get the unique type identifier for this block.
     *
     * @return string The block type identifier
     */
    public static function type(): string
    {
        return 'cta';
    }

    /**
     * Get the schema definition for this block's data fields.
     *
     * All fields are optional - a CTA block can exist with any combination
     * of heading, supporting text, and button text.
     *
     * @return array<string, string> Field name => data type mapping
     */
    public static function schema(): array
    {
        return [
            'heading' => 'string',
            'lede' => 'string',
            'cta_button_text' => 'string',
        ];
    }

    /**
     * Validate block data against the schema definition.
     *
     * Ensures that all provided data fields match their expected string types.
     * Since all fields are optional, empty data is valid.
     *
     * @param  array<string, mixed>  $data  The block data to validate
     * @param  Closure  $fail  Closure to call with validation error messages
     * @param  int  $index  The block's position in the content_blocks array (for error messages)
     */
    public static function validate(array $data, Closure $fail, int $index): void
    {
        $schema = self::schema();

        foreach ($data as $field => $value) {
            // Check if field exists in schema
            if (! \array_key_exists($field, $schema)) {
                $fail("content_blocks.{$index}.data.{$field}", "Unknown field '{$field}' for block type 'cta'.");

                continue;
            }

            // Validate field type
            $expectedType = $schema[$field];
            $actualType = get_debug_type($value);

            if ($expectedType === 'string' && ! \is_string($value)) {
                $fail("content_blocks.{$index}.data.{$field}", "The {$field} field must be a string, {$actualType} given.");
            }
        }
    }
}
