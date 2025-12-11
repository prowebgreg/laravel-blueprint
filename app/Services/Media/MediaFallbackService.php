<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\Setting;

/**
 * Media Fallback Service
 *
 * Provides fallback image retrieval and URL generation when media is missing,
 * deleted, or not in a ready state.
 *
 * Usage:
 * - Get fallback image ID from settings (media.fallback_image_id)
 * - Retrieve the fallback MediaAsset (must be Ready state, not deleted)
 * - Get fallback URL with optional width selection for responsive variants
 *
 * Returns null if no fallback is configured or fallback asset is unavailable.
 */
class MediaFallbackService
{
    /**
     * Get the fallback image ID from settings.
     *
     * @return string|null The UUID of the fallback MediaAsset or null if not configured
     */
    public function getFallbackImageId(): ?string
    {
        $fallbackId = Setting::get('media.fallback_image_id');

        return $fallbackId !== null ? (string) $fallbackId : null;
    }

    /**
     * Get the fallback MediaAsset if configured and available.
     *
     * Only returns the fallback if:
     * - It exists in the database
     * - It is not soft-deleted
     * - It is in Ready state
     *
     * @return MediaAsset|null The fallback MediaAsset or null if unavailable
     */
    public function getFallbackAsset(): ?MediaAsset
    {
        $fallbackId = $this->getFallbackImageId();

        if ($fallbackId === null) {
            return null;
        }

        $fallback = MediaAsset::find($fallbackId);

        if ($fallback === null) {
            return null;
        }

        if (! $fallback->state->isAccessible()) {
            return null;
        }

        return $fallback;
    }

    /**
     * Get the fallback URL with optional width selection.
     *
     * Delegates to MediaAsset::getUrl() for variant selection logic.
     *
     * @param  int|null  $width  Optional variant width in pixels
     * @return string|null The CDN URL for the fallback or null if unavailable
     */
    public function getFallbackUrl(?int $width = null): ?string
    {
        $fallback = $this->getFallbackAsset();

        if ($fallback === null) {
            return null;
        }

        return $fallback->getUrl($width);
    }
}
