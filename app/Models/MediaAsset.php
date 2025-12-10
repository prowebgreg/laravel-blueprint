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
}
