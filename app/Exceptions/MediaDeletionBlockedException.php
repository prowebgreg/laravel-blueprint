<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\MediaAsset;
use Exception;

/**
 * MediaDeletionBlockedException
 *
 * Thrown when attempting to delete a media asset that cannot be deleted.
 *
 * Blocking conditions:
 * - Media is the designated fallback image (system setting)
 * - Media is used by published public content
 *
 * Public content models (block deletion when published):
 * - Page (/{slug})
 * - Service (/services/{slug})
 * - BlogPost (/blog/{slug})
 *
 * Internal content resources (don't block deletion):
 * - Faq (no public URL)
 * - Testimonial (no public URL)
 */
class MediaDeletionBlockedException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param  MediaAsset  $media  The media asset that cannot be deleted
     * @param  array<int, array{model_type: string, model_id: string, relation_type: string}>  $usages  List of blocking content usages
     * @param  bool  $isFallbackImage  Whether deletion is blocked because this is the fallback image
     */
    public function __construct(
        public readonly MediaAsset $media,
        public readonly array $usages,
        public readonly bool $isFallbackImage = false,
    ) {
        $message = $this->buildMessage();

        parent::__construct($message);
    }

    /**
     * Build the exception message based on the blocking reason.
     */
    private function buildMessage(): string
    {
        if ($this->isFallbackImage) {
            return sprintf(
                'Cannot delete media "%s" (ID: %s) - it is the designated fallback image',
                $this->media->original_name,
                $this->media->id
            );
        }

        $count = count($this->usages);

        return sprintf(
            'Cannot delete media "%s" (ID: %s) - used by %d published content %s',
            $this->media->original_name,
            $this->media->id,
            $count,
            $count === 1 ? 'item' : 'items'
        );
    }

    /**
     * Check if deletion is blocked because media is the fallback image.
     */
    public function isBlockedAsFallbackImage(): bool
    {
        return $this->isFallbackImage;
    }
}
