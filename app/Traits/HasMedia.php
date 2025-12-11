<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\MediaAsset;
use App\Services\Media\MediaFallbackService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

/**
 * Provides media attachment capabilities to any Eloquent model.
 *
 * Uses the content_relations table for polymorphic relationships,
 * storing the relationship type identifier in the relation_type pivot column.
 *
 * Relationship Type Naming Convention:
 * - Fixed field: {field_name} e.g. "og_image"
 * - Static page: page:{slug}:{section}:{field} e.g. "page:home:hero:image"
 * - Custom page: {type}:{section}:{field} e.g. "service:hero:image"
 * - Content resource: {resource}:{field} e.g. "testimonial:avatar"
 */
trait HasMedia
{
    /**
     * Get all media assets attached to this model.
     *
     * Note: Due to PostgreSQL UUID-to-varchar join limitations, this relationship
     * is defined for metadata only. Use getMedia() and getAllMedia() methods for queries.
     *
     * @return MorphToMany<MediaAsset, $this>
     */
    public function mediaAssets(): MorphToMany
    {
        return $this->morphToMany(
            MediaAsset::class,
            'source',
            'content_relations',
            'source_id',
            'target_id',
            'id',
            'id'
        )
            ->withPivot('relation_type', 'order', 'created_at')
            ->orderBy('content_relations.order');
    }

    /**
     * Attach a media asset with a relationship type identifier.
     *
     * Prevents duplicate attachments for the same type identifier.
     *
     * @param  MediaAsset  $media  The media asset to attach
     * @param  string  $type  Relationship identifier (e.g., 'page:home:hero:image')
     * @param  int|null  $order  Optional display order (defaults to 0)
     */
    public function attachMedia(MediaAsset $media, string $type, ?int $order = null): void
    {
        // PostgreSQL: Cast columns to text to avoid UUID type inference
        $exists = DB::select(
            'SELECT EXISTS(
                SELECT 1 FROM content_relations
                WHERE source_type::text = ?::text
                AND source_id::text = ?::text
                AND target_type::text = ?::text
                AND target_id::text = ?::text
                AND relation_type::text = ?::text
            ) as exists',
            [
                static::class,
                (string) $this->getKey(),
                MediaAsset::class,
                (string) $media->id,
                $type,
            ]
        )[0]->exists ?? false;

        if ($exists) {
            return; // Silently ignore duplicate attachments
        }

        // Insert the relationship (target_id stored as string)
        DB::table('content_relations')->insert([
            'source_type' => static::class,
            'source_id' => (string) $this->getKey(),
            'target_type' => MediaAsset::class,
            'target_id' => (string) $media->id, // Cast UUID to string
            'relation_type' => $type,
            'order' => $order ?? 0,
            'created_at' => now(),
        ]);
    }

    /**
     * Detach media for a specific relationship type.
     *
     * Removes all relationships matching the type identifier.
     *
     * @param  string  $type  Relationship identifier to detach
     */
    public function detachMedia(string $type): void
    {
        // PostgreSQL: Use raw SQL with explicit text casting to avoid UUID type inference
        DB::delete(
            'DELETE FROM content_relations
            WHERE source_type::text = ?::text
            AND source_id::text = ?::text
            AND target_type::text = ?::text
            AND relation_type::text = ?::text',
            [
                static::class,
                (string) $this->getKey(),
                MediaAsset::class,
                $type,
            ]
        );
    }

    /**
     * Get media asset for a specific relationship type.
     *
     * Returns the first media asset if multiple exist for the same type.
     *
     * @param  string  $type  Relationship identifier
     */
    public function getMedia(string $type): ?MediaAsset
    {
        // PostgreSQL: Cast all columns to text to avoid UUID type inference
        $mediaIds = DB::select(
            'SELECT target_id FROM content_relations
            WHERE source_type::text = ?::text
            AND source_id::text = ?::text
            AND target_type::text = ?::text
            AND relation_type::text = ?::text
            ORDER BY "order" ASC',
            [
                static::class,
                (string) $this->getKey(),
                MediaAsset::class,
                $type,
            ]
        );

        if (empty($mediaIds)) {
            return null;
        }

        return MediaAsset::find($mediaIds[0]->target_id);
    }

    /**
     * Get all attached media assets.
     *
     * Returns each unique media asset only once, even if attached
     * with multiple type identifiers. Results are ordered by the
     * relationship order field.
     *
     * @return Collection<int, MediaAsset>
     */
    public function getAllMedia(): Collection
    {
        // PostgreSQL: Cast all columns to text to avoid UUID type inference
        // Use DISTINCT ON instead of DISTINCT to allow ORDER BY
        $mediaIds = DB::select(
            'SELECT DISTINCT ON (target_id) target_id, "order"
            FROM content_relations
            WHERE source_type::text = ?::text
            AND source_id::text = ?::text
            AND target_type::text = ?::text
            ORDER BY target_id, "order" ASC',
            [
                static::class,
                (string) $this->getKey(),
                MediaAsset::class,
            ]
        );

        if (empty($mediaIds)) {
            return new Collection;
        }

        // Sort by order from the relation
        usort($mediaIds, fn ($a, $b) => $a->order <=> $b->order);
        $ids = array_map(fn ($row) => $row->target_id, $mediaIds);

        // Maintain order from the relationship using parameterized query
        // Use query() to avoid static analysis warnings about whereIn()
        $placeholders = implode(',', array_fill(0, \count($ids), '?'));

        return MediaAsset::query()
            ->whereIn('id', $ids)
            ->orderByRaw("array_position(ARRAY[{$placeholders}]::text[], id::text)", $ids)
            ->get();
    }

    /**
     * Get CDN URL for attached media with optional width selection.
     *
     * Returns the appropriate variant URL for the requested width, or fallback
     * image if media is not attached, deleted, or not in 'ready' state.
     *
     * Implementation delegated to MediaAsset::getUrl() for variant selection logic.
     *
     * @param  string  $type  Relationship identifier
     * @param  int|null  $width  Optional variant width
     * @return string|null CDN URL or fallback URL or null if no fallback configured
     */
    public function getMediaUrl(string $type, ?int $width = null): ?string
    {
        // Get media for the specified relationship type
        $media = $this->getMedia($type);

        // Check if media exists and is in accessible (ready) state
        if ($media !== null && $media->state->isAccessible()) {
            return $media->getUrl($width);
        }

        // Media not available - use fallback service
        $fallbackService = app(MediaFallbackService::class);

        return $fallbackService->getFallbackUrl($width);
    }
}
