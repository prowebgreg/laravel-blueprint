<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait HasSeo
 *
 * Provides SEO field mirroring for Open Graph and Twitter Card metadata.
 * Automatically falls back to meta_title and meta_description when
 * og_* or twitter_* fields are NULL in the database.
 *
 * Mirroring Behavior:
 * - NULL in database = mirror from meta field
 * - Any non-NULL value = independent (mirroring broken)
 *
 * Requirements:
 * - Model must have SEO-related columns: meta_title, meta_description,
 *   og_title, og_description, twitter_title, twitter_description
 */
trait HasSeo
{
    /**
     * Get the Open Graph title.
     *
     * Returns og_title if explicitly set, otherwise falls back to meta_title.
     *
     * @param  string|null  $value  The raw database value
     * @return string|null The resolved title
     */
    public function getOgTitleAttribute(?string $value): ?string
    {
        return $value ?? $this->attributes['meta_title'] ?? null;
    }

    /**
     * Get the Open Graph description.
     *
     * Returns og_description if explicitly set, otherwise falls back to meta_description.
     *
     * @param  string|null  $value  The raw database value
     * @return string|null The resolved description
     */
    public function getOgDescriptionAttribute(?string $value): ?string
    {
        return $value ?? $this->attributes['meta_description'] ?? null;
    }

    /**
     * Get the Twitter Card title.
     *
     * Returns twitter_title if explicitly set, otherwise falls back to meta_title.
     *
     * @param  string|null  $value  The raw database value
     * @return string|null The resolved title
     */
    public function getTwitterTitleAttribute(?string $value): ?string
    {
        return $value ?? $this->attributes['meta_title'] ?? null;
    }

    /**
     * Get the Twitter Card description.
     *
     * Returns twitter_description if explicitly set, otherwise falls back to meta_description.
     *
     * @param  string|null  $value  The raw database value
     * @return string|null The resolved description
     */
    public function getTwitterDescriptionAttribute(?string $value): ?string
    {
        return $value ?? $this->attributes['meta_description'] ?? null;
    }
}
