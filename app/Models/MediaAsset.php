<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaFolder;
use App\Enums\MediaState;
use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MediaAsset Model
 *
 * Represents an uploaded media file in the system.
 * All media is processed, optimized, and offloaded to S3/CloudFront.
 *
 * @property string $id UUID primary key
 * @property string $filename Sanitized filename with nanoid
 * @property string $original_name Original uploaded filename
 * @property MediaType $media_type Type: image, video, or svg
 * @property MediaFolder $folder Storage folder path
 * @property int $file_size File size in bytes
 * @property array|null $dimensions {width: int, height: int}
 * @property string $mime_type MIME type
 * @property MediaState $state Processing state
 * @property string|null $error_message Error details if failed
 * @property string $s3_key_original S3 object key for original file
 * @property string $cloudfront_url_original CDN URL for original
 * @property string|null $alt_text Accessibility alt text
 * @property string|null $title Media title
 * @property string|null $caption Extended description
 * @property array|null $focal_point {x: 0-1, y: 0-1} for smart cropping
 * @property array $tags Array of tag strings
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * Soft Delete Usage Examples:
 *
 * // Get only soft-deleted assets (for "Trash" view in admin panel)
 * MediaAsset::onlyTrashed()->get();
 * MediaAsset::onlyTrashed()->where('deleted_at', '>=', now()->subDays(30))->get();
 *
 * // Get all assets including soft-deleted (for audit/admin queries)
 * MediaAsset::withTrashed()->find($id);
 * MediaAsset::withTrashed()->where('media_type', MediaType::Image)->get();
 *
 * // Semantic alias for admin contexts (same as withTrashed())
 * MediaAsset::withDeletedMedia()->get();
 * MediaAsset::withDeletedMedia()->where('media_type', MediaType::Image)->get();
 *
 * // Restore a soft-deleted asset (within 30-day retention window)
 * $asset = MediaAsset::onlyTrashed()->find($id);
 * $asset->restore();
 *
 * // Permanently delete an asset (force delete bypasses soft delete)
 * $asset = MediaAsset::find($id);
 * $asset->forceDelete();
 *
 * // Check if an asset is soft-deleted
 * $asset->trashed(); // returns bool
 *
 * // Query deleted assets with relationships
 * MediaAsset::onlyTrashed()->with('variants')->get();
 */
class MediaAsset extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'filename',
        'original_name',
        'media_type',
        'folder',
        'file_size',
        'dimensions',
        'mime_type',
        'state',
        'error_message',
        's3_key_original',
        'cloudfront_url_original',
        'alt_text',
        'title',
        'caption',
        'focal_point',
        'tags',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'media_type' => MediaType::class,
            'folder' => MediaFolder::class,
            'state' => MediaState::class,
            'dimensions' => 'array',
            'focal_point' => 'array',
            'tags' => 'array',
            'file_size' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get all variants for this media asset.
     *
     * @return HasMany<MediaVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    /**
     * Scope query to include soft-deleted media assets.
     *
     * Semantic alias for withTrashed() that provides clearer context
     * when querying media in admin panels or audit views.
     *
     * @param  Builder<MediaAsset>  $query
     * @return Builder<MediaAsset>
     */
    public function scopeWithDeletedMedia(Builder $query): Builder
    {
        return $query->withTrashed();
    }

    /**
     * Get the CDN URL for this media asset with optional variant width selection.
     *
     * Width selection logic:
     * - If no width specified: return original URL
     * - If exact width exists: return that variant URL
     * - If exact width doesn't exist: return next larger variant
     * - If requested width is larger than all variants: return largest variant
     * - If no variants exist: return original URL
     * - SVG and video: always return original URL (no variants)
     * - Zero or negative width: return original URL
     *
     * @param  int|null  $width  Optional variant width in pixels
     * @return string The CDN URL for the media or selected variant
     */
    public function getUrl(?int $width = null): string
    {
        // Return original URL if no width specified or invalid width
        if ($width === null || $width <= 0) {
            return $this->cloudfront_url_original;
        }

        // SVG and video don't have variants - always return original
        if ($this->media_type !== MediaType::Image) {
            return $this->cloudfront_url_original;
        }

        // Load variants sorted by width (reuse if already loaded to avoid N+1)
        $variants = $this->relationLoaded('variants')
            ? $this->variants->sortBy('width')->values()
            : $this->variants()->orderBy('width')->get();

        // If no variants exist, return original
        if ($variants->isEmpty()) {
            return $this->cloudfront_url_original;
        }

        // Try to find exact width match
        $exact = $variants->firstWhere('width', $width);
        if ($exact !== null) {
            return $exact->cloudfront_url;
        }

        // Find next larger variant
        $nextLarger = $variants->where('width', '>', $width)->first();
        if ($nextLarger !== null) {
            return $nextLarger->cloudfront_url;
        }

        // Requested width is larger than all variants - return largest
        $largest = $variants->last();

        return $largest->cloudfront_url;
    }
}
