<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\MediaAsset;
use Exception;

/**
 * MediaDeletionBlockedException
 *
 * Thrown when attempting to delete a media asset that is used by published public content.
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
     */
    public function __construct(
        public readonly MediaAsset $media,
        public readonly array $usages,
    ) {
        $count = count($usages);
        $message = sprintf(
            'Cannot delete media "%s" (ID: %s) - used by %d published content %s',
            $media->original_name,
            $media->id,
            $count,
            $count === 1 ? 'item' : 'items'
        );

        parent::__construct($message);
    }
}
