<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Enums\ContentStatus;
use App\Models\BlogPost;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Media Usage Service
 *
 * Tracks media usage across content models and determines deletion protection.
 *
 * Usage:
 * - findUsages(): Get all content items using a specific media asset
 * - isBlockedByPublicContent(): Check if deletion should be blocked
 *
 * Public Content Models (block deletion when published):
 * - Page (/{slug})
 * - Service (/services/{slug})
 * - BlogPost (/blog/{slug})
 *
 * Internal Content Resources (don't block deletion):
 * - Faq (no public URL)
 * - Testimonial (no public URL)
 */
class MediaUsageService
{
    /**
     * List of public content model classes that block deletion when published.
     *
     * @var array<int, class-string>
     */
    private const PUBLIC_CONTENT_MODELS = [
        Page::class,
        Service::class,
        BlogPost::class,
    ];

    /**
     * Find all content items using a specific media asset.
     *
     * Excludes soft-deleted content from results.
     *
     * @param  MediaAsset  $media  The media asset to find usages for
     * @return Collection<int, array{model_type: string, model_id: string, relation_type: string}>
     */
    public function findUsages(MediaAsset $media): Collection
    {
        // Query content_relations table for all usages
        $relations = DB::table('content_relations')
            ->where('target_type', MediaAsset::class)
            ->where('target_id', $media->id)
            ->get(['source_type', 'source_id', 'relation_type']);

        // Group relations by source_type for batch loading
        $relationsByType = $relations->groupBy('source_type');

        $usages = collect();

        foreach ($relationsByType as $modelClass => $typeRelations) {
            // Verify model class exists
            if (! class_exists($modelClass)) {
                continue;
            }

            // Batch load all models of this type (including trashed)
            $ids = $typeRelations->pluck('source_id')->unique();
            $models = $modelClass::withTrashed()->whereIn('id', $ids)->get()->keyBy('id');

            // Process each relation with its loaded model
            foreach ($typeRelations as $relation) {
                $model = $models->get($relation->source_id);

                if ($model === null) {
                    continue;
                }

                // Skip soft-deleted models
                if (method_exists($model, 'trashed') && $model->trashed()) {
                    continue;
                }

                $usages->push([
                    'model_type' => $relation->source_type,
                    'model_id' => (string) $relation->source_id,
                    'relation_type' => $relation->relation_type,
                ]);
            }
        }

        return $usages;
    }

    /**
     * Check if media deletion should be blocked by published public content.
     *
     * Returns true if the media is used by any published Page, Service, or BlogPost
     * that is not soft-deleted.
     *
     * Returns false if only used by:
     * - Draft content (ContentStatus::Draft)
     * - Soft-deleted content
     * - Internal content resources (Faq, Testimonial)
     *
     * @param  MediaAsset  $media  The media asset to check
     * @return bool True if deletion should be blocked
     */
    public function isBlockedByPublicContent(MediaAsset $media): bool
    {
        $usages = $this->findUsages($media);

        // Filter usages to only public content models
        $publicUsages = $usages->filter(
            fn (array $usage): bool => \in_array($usage['model_type'], self::PUBLIC_CONTENT_MODELS, true)
        );

        if ($publicUsages->isEmpty()) {
            return false;
        }

        // Group public usages by model type for batch loading
        $usagesByType = $publicUsages->groupBy('model_type');

        foreach ($usagesByType as $modelClass => $typeUsages) {
            // Batch load all models of this type
            $ids = $typeUsages->pluck('model_id');
            $models = $modelClass::whereIn('id', $ids)->get()->keyBy('id');

            // Check if any model is published
            foreach ($models as $model) {
                if (isset($model->status) && $model->status === ContentStatus::Published) {
                    return true;
                }
            }
        }

        return false;
    }
}
