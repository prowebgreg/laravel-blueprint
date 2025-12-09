<?php

declare(strict_types=1);

namespace App\Rules;

use App\Blocks\Contracts\BlockInterface;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

use function class_exists;
use function is_array;
use function is_string;
use function is_subclass_of;

/**
 * Validates content blocks structure and data types.
 *
 * Ensures content_blocks field contains valid JSON array with properly
 * structured blocks. Each block must have a 'type' field mapping to a
 * valid block class in App\Blocks namespace, and optional 'data' field
 * conforming to the block's schema.
 *
 * Validation rules:
 * - NULL values are allowed (nullable)
 * - Empty arrays [] are allowed
 * - Each block must have 'type' field (string)
 * - Block type must correspond to existing class in App\Blocks\
 * - 'data' field is optional but must be an array if present
 * - Data field values must match block schema types when provided
 * - No fields within blocks are required
 */
class ValidContentBlocks implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow NULL values (nullable)
        if ($value === null) {
            return;
        }

        // Value must be an array
        if (! is_array($value)) {
            $fail("The {$attribute} must be an array.");

            return;
        }

        // Allow empty arrays
        if (empty($value)) {
            return;
        }

        // Validate each block
        foreach ($value as $index => $block) {
            $this->validateBlock($block, $index, $fail);
        }
    }

    /**
     * Validate an individual content block.
     *
     * @param  mixed  $block  The block to validate
     * @param  int  $index  The block's index in the content_blocks array
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail  Closure to call with validation error messages
     */
    protected function validateBlock(mixed $block, int $index, Closure $fail): void
    {
        // Block must be an array
        if (! is_array($block)) {
            $fail("Block at index {$index} must be an array.");

            return;
        }

        // Block must have 'type' field
        if (! isset($block['type'])) {
            $fail("Block at index {$index} is missing required 'type' field.");

            return;
        }

        // 'type' field must be a string
        if (! is_string($block['type'])) {
            $fail("Block at index {$index}: 'type' field must be a string.");

            return;
        }

        $blockType = $block['type'];

        // Block type cannot be empty
        if ($blockType === '') {
            $fail("Block at index {$index}: 'type' field cannot be empty.");

            return;
        }

        // Resolve block class from type
        $blockClass = $this->resolveBlockClass($blockType);

        if ($blockClass === null) {
            $fail("Block type '{$blockType}' is not defined.");

            return;
        }

        // Validate 'data' field if present
        if (isset($block['data'])) {
            // 'data' must be an array
            if (! is_array($block['data'])) {
                $fail("Block at index {$index}: 'data' field must be an array.");

                return;
            }

            // Validate data against block schema
            $blockClass::validate($block['data'], $fail, $index);
        }
    }

    /**
     * Resolve block class from block type identifier.
     *
     * Converts block type (e.g., 'hero', 'hero_banner') to fully qualified class name
     * (e.g., App\Blocks\HeroBlock, App\Blocks\HeroBannerBlock) and verifies the class
     * exists and implements BlockInterface.
     *
     * @param  string  $type  The block type identifier
     * @return class-string<BlockInterface>|null The block class or null if not found
     */
    protected function resolveBlockClass(string $type): ?string
    {
        // Convert type to class name: 'hero' -> 'HeroBlock', 'hero_banner' -> 'HeroBannerBlock'
        $className = Str::studly($type).'Block';
        $fullyQualifiedClass = "App\\Blocks\\{$className}";

        // Check if class exists and implements BlockInterface
        if (! class_exists($fullyQualifiedClass)) {
            return null;
        }

        if (! is_subclass_of($fullyQualifiedClass, BlockInterface::class, true)) {
            return null;
        }

        /** @var class-string<BlockInterface> */
        return $fullyQualifiedClass;
    }
}
