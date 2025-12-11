<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaFolder;
use App\Enums\MediaState;
use App\Enums\MediaType;
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
        // TODO: Implement getUrl() method in T052
        // Implementation steps:
        // 1. If width is null, <= 0, or media is SVG/video: return cloudfront_url_original
        // 2. Load variants: $variants = $this->variants()->orderBy('width')->get()
        // 3. If no variants: return cloudfront_url_original
        // 4. Find exact match: $exact = $variants->firstWhere('width', $width)
        // 5. If exact: return $exact->cloudfront_url
        // 6. Find next larger: $nextLarger = $variants->where('width', '>', $width)->first()
        // 7. If next larger: return $nextLarger->cloudfront_url
        // 8. Otherwise: return largest variant cloudfront_url
        //
        // Parameter used in implementation:
        // - $width: variant width for selection logic (null/0/negative returns original)

        /** @phpstan-ignore-next-line TDD placeholder - parameter used in implementation */
        throw new \BadMethodCallException(
            'getUrl('.($width ?? 'null').') not yet implemented - see T052'
        );
    }
}
