<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MediaVariant Model
 *
 * Represents a responsive size variant of an image asset.
 * Each variant is a specific width/height version of the parent MediaAsset,
 * typically in WebP format with optimized file size for web delivery.
 *
 * Variants are immutable - once created, they are never updated (no updated_at).
 *
 * @property string $id UUID primary key
 * @property string $media_asset_id UUID foreign key to media_assets
 * @property int $width Variant width in pixels
 * @property int $height Variant height in pixels
 * @property string $format Output format (typically 'webp')
 * @property int $file_size Variant file size in bytes
 * @property string $s3_key S3 object key for this variant
 * @property string $cloudfront_url CDN URL for delivery
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property-read MediaAsset $mediaAsset Parent media asset
 */
class MediaVariant extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_variants';

    /**
     * Indicates that the model does not have an updated_at timestamp.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'media_asset_id',
        'width',
        'height',
        'format',
        'file_size',
        's3_key',
        'cloudfront_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'file_size' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the parent media asset for this variant.
     *
     * @return BelongsTo<MediaAsset, $this>
     */
    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }
}
