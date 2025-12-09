<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait HasContentBlocks
 *
 * Provides JSONB handling for content_blocks column with helper methods
 * for managing structured content blocks. Blocks are stored as JSON in
 * PostgreSQL and automatically cast to/from arrays in PHP.
 *
 * Requirements:
 * - Model must have a 'content_blocks' JSON/JSONB column (nullable)
 * - Each block must have 'type' and 'data' keys
 * - Block types should have corresponding classes in app/Blocks/
 *
 * Content Block Structure:
 * [
 *   {
 *     "type": "hero",
 *     "data": {
 *       "heading": "Welcome",
 *       "lede": "Description",
 *       "image": "https://...",
 *       "cta_button_text": "Get Started"
 *     }
 *   },
 *   {
 *     "type": "cta",
 *     "data": {
 *       "heading": "Ready to Begin?",
 *       "lede": "Contact us today.",
 *       "cta_button_text": "Contact Us"
 *     }
 *   }
 * ]
 *
 * Note: This trait does NOT perform validation. Use the ValidContentBlocks
 * validation rule in Form Requests to validate block structure and types.
 */
trait HasContentBlocks
{
    /**
     * Get the content_blocks attribute as an array.
     *
     * Decodes the JSONB column from database JSON string to PHP array.
     * Returns null if the column value is null.
     *
     * @param  mixed  $value  The raw value from database (string for JSON or already array)
     * @return array<int, array{type: string, data: array<string, mixed>}>|null Decoded array of blocks or null
     */
    public function getContentBlocksAttribute(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Set the content_blocks attribute from an array.
     *
     * Encodes the PHP array to JSON string for storage in JSONB column.
     * Accepts null to clear the content_blocks column.
     *
     * @param  mixed  $value  Array of blocks, JSON string, or null
     */
    public function setContentBlocksAttribute(mixed $value): void
    {
        if ($value === null) {
            $this->attributes['content_blocks'] = null;

            return;
        }

        if (is_string($value)) {
            $this->attributes['content_blocks'] = $value;

            return;
        }

        $this->attributes['content_blocks'] = json_encode($value);
    }

    /**
     * Add a new block to the end of the content_blocks array.
     *
     * Appends the block to the existing blocks array, or creates a new
     * array if content_blocks is currently null. Does not validate the
     * block structure - use ValidContentBlocks rule in Form Request.
     *
     * @param  array{type: string, data: array<string, mixed>}  $block  The block to add
     */
    public function addBlock(array $block): void
    {
        $blocks = $this->content_blocks ?? [];
        $blocks[] = $block;
        $this->content_blocks = $blocks;
    }

    /**
     * Remove a block at the specified index.
     *
     * Removes the block at the given array index and reindexes the array
     * to maintain sequential numeric keys (0, 1, 2...). If the index does
     * not exist, the array remains unchanged. Sets content_blocks to null
     * if removing the last block.
     *
     * @param  int  $index  The zero-based index of the block to remove
     */
    public function removeBlock(int $index): void
    {
        $blocks = $this->content_blocks ?? [];

        if (! isset($blocks[$index])) {
            return;
        }

        unset($blocks[$index]);
        $blocks = array_values($blocks); // reindex to sequential keys

        // Set to null if array is now empty, otherwise set the reindexed array
        $this->content_blocks = empty($blocks) ? null : $blocks;
    }

    /**
     * Reorder blocks based on an index mapping array.
     *
     * Accepts an array of old indices in the desired new order. For example,
     * passing [2, 0, 1] will move the block at index 2 to position 0, the
     * block at index 0 to position 1, and the block at index 1 to position 2.
     *
     * Invalid or non-existent indices in the mapping are silently skipped.
     * If the mapping array is empty, content_blocks is set to null.
     *
     * Example:
     *   Original blocks: ['A', 'B', 'C']
     *   Call: reorderBlocks([2, 0, 1])
     *   Result: ['C', 'A', 'B']
     *
     * @param  array<int, int>  $order  Array of old indices in desired new order
     */
    public function reorderBlocks(array $order): void
    {
        $blocks = $this->content_blocks ?? [];
        $reordered = [];

        foreach ($order as $oldIndex) {
            if (isset($blocks[$oldIndex])) {
                $reordered[] = $blocks[$oldIndex];
            }
        }

        // Set to null if reordered array is empty, otherwise set the reordered array
        $this->content_blocks = empty($reordered) ? null : $reordered;
    }
}
