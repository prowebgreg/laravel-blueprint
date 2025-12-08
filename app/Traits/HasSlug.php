<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Trait HasSlug
 *
 * Automatically generates unique URL-safe slugs from the 'name' attribute.
 * Handles duplicate slugs with numeric suffixes (-2, -3, etc.).
 * Prevents usage of reserved slugs defined in content.reserved_slugs config.
 *
 * Requirements:
 * - Model must have a 'name' string attribute
 * - Model must have a 'slug' string attribute (nullable recommended)
 * - Model may use soft deletes (trait will include trashed records in uniqueness check)
 *
 * @method static creating(\Closure $callback)
 */
trait HasSlug
{
    /**
     * Boot the HasSlug trait for a model.
     *
     * Registers a 'creating' event listener to auto-generate slugs from the name
     * attribute before the model is saved to the database for the first time.
     *
     * Note: Slugs are intentionally NOT auto-updated when the name changes.
     * This is a deliberate SEO best practice - changing URLs breaks links
     * and loses search engine ranking. To change a slug, explicitly set it
     * or delete and recreate the record.
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug($model->name);
            }
        });
    }

    /**
     * Generate a unique URL-safe slug from the given name.
     *
     * Algorithm:
     * 1. Convert name to URL-safe slug using Laravel's Str::slug()
     * 2. Handle empty slug generation (fallback to random string)
     * 3. Validate against reserved slugs list (throws ValidationException if reserved)
     * 4. Check for existing slugs (including soft-deleted records)
     * 5. If duplicate exists, append incrementing suffix: -2, -3, etc.
     * 6. Stop at max_slug_suffix_attempts (default 1000) to prevent infinite loops
     *
     * @param  string  $name  The name to generate slug from
     * @return string The unique generated slug
     *
     * @throws ValidationException If slug matches a reserved slug
     * @throws \RuntimeException If max suffix attempts exceeded or empty slug generated
     */
    public function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        // Handle empty slug generation (e.g., from special characters only)
        if ($baseSlug === '' || $baseSlug === null) {
            throw new \RuntimeException(
                "Unable to generate slug from name '{$name}'. Name contains no valid characters for slug generation."
            );
        }

        // Get reserved slugs config
        $reservedSlugs = config('content.reserved_slugs', []);

        // Check if base slug is reserved
        $this->validateSlugNotReserved($baseSlug, $reservedSlugs);

        $slug = $baseSlug;
        $maxAttempts = config('content.max_slug_suffix_attempts', 1000);
        $suffix = 1;

        // Check for existing slugs (including soft-deleted records)
        while ($this->slugExists($slug)) {
            $suffix++;

            if ($suffix > $maxAttempts) {
                throw new \RuntimeException(
                    "Unable to generate unique slug for '{$name}'. Maximum attempts ({$maxAttempts}) exceeded."
                );
            }

            $slug = "{$baseSlug}-{$suffix}";

            // Also check if the suffixed slug is reserved
            $this->validateSlugNotReserved($slug, $reservedSlugs);
        }

        return $slug;
    }

    /**
     * Validate that a slug is not in the reserved slugs list.
     *
     * @param  string  $slug  The slug to validate
     * @param  array<int, string>  $reservedSlugs  List of reserved slugs
     *
     * @throws ValidationException If slug is reserved
     */
    protected function validateSlugNotReserved(string $slug, array $reservedSlugs): void
    {
        if (\in_array($slug, $reservedSlugs, true)) {
            throw ValidationException::withMessages([
                'name' => "The slug '{$slug}' is reserved and cannot be used.",
            ]);
        }
    }

    /**
     * Check if the given slug already exists in the database.
     *
     * Includes soft-deleted records in the check to prevent slug reuse
     * and ensure true uniqueness across all records. Excludes the current
     * model from the check when updating existing records.
     *
     * @param  string  $slug  The slug to check
     * @return bool True if slug exists, false otherwise
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);

        // Exclude current model if it exists (has an ID) - prevents false positives on updates
        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }

        // Include soft-deleted records in the check if model uses soft deletes
        $traits = class_uses_recursive(static::class);
        if (\is_array($traits) && \in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits, true)) {
            $query->withTrashed();
        }

        return $query->exists();
    }
}
