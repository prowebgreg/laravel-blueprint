<?php

declare(strict_types=1);

namespace App\Blocks;

use App\Blocks\Contracts\BlockInterface;
use Closure;

/**
 * Hero block for prominent page sections with heading, lede, image, and CTA.
 *
 * Typically used at the top of landing pages or major sections to capture
 * attention with a compelling headline, supporting text, visual element,
 * and call-to-action button.
 *
 * Example data structure:
 * {
 *   "type": "hero",
 *   "data": {
 *     "heading": "Welcome to Our Site",
 *     "lede": "We help businesses grow online.",
 *     "image": "https://cdn.example.com/hero.webp",
 *     "cta_button_text": "Get Started"
 *   }
 * }
 */
class HeroBlock implements BlockInterface
{
    /**
     * Get the unique type identifier for this block.
     *
     * @return string The block type identifier
     */
    public static function type(): string
    {
        return 'hero';
    }

    /**
     * Get the schema definition for this block's data fields.
     *
     * All fields are optional - a hero block can be partially populated.
     *
     * @return array<string, string> Field name => data type mapping
     */
    public static function schema(): array
    {
        return [
            'heading' => 'string',
            'lede' => 'string',
            'image' => 'string',
            'cta_button_text' => 'string',
        ];
    }

    /**
     * Validate block data against the schema definition.
     *
     * Ensures all provided fields match their expected types. All fields
     * are optional, so missing fields do not trigger validation errors.
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
                $fail("content_blocks.{$index}.data.{$field}", "Unknown field '{$field}' for hero block.");

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
