# Data Model: Media Engine & Asset Model

**Feature Branch**: `003-media-engine`
**Date**: 2025-12-10

## Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                              MediaAsset                              │
├─────────────────────────────────────────────────────────────────────┤
│ id: UUID (PK)                                                        │
│ filename: string (sanitized-name-nanoid)                            │
│ original_name: string                                                │
│ media_type: MediaType (image|video|svg)                              │
│ folder: MediaFolder (images|videos|svg)                              │
│ file_size: bigint                                                    │
│ dimensions: jsonb {width, height}                                    │
│ mime_type: string                                                    │
│ state: MediaState (uploading|processing|ready|failed)               │
│ error_message: text                                                  │
│ s3_key_original: string                                              │
│ cloudfront_url_original: string                                      │
│ alt_text: string                                                     │
│ title: string                                                        │
│ caption: text                                                        │
│ focal_point: jsonb {x, y}                                            │
│ tags: jsonb []                                                       │
│ created_at: timestamp                                                │
│ updated_at: timestamp                                                │
│ deleted_at: timestamp                                                │
└─────────────────────────────────────────────────────────────────────┘
         │
         │ 1:N
         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                             MediaVariant                             │
├─────────────────────────────────────────────────────────────────────┤
│ id: UUID (PK)                                                        │
│ media_asset_id: UUID (FK → media_assets.id, CASCADE DELETE)         │
│ width: integer                                                       │
│ height: integer                                                      │
│ format: string (default: 'webp')                                     │
│ file_size: bigint                                                    │
│ s3_key: string                                                       │
│ cloudfront_url: string                                               │
│ created_at: timestamp                                                │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                              Setting                                 │
├─────────────────────────────────────────────────────────────────────┤
│ key: string (PK)                                                     │
│ value: text                                                          │
│ created_at: timestamp                                                │
│ updated_at: timestamp                                                │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                         content_relations                            │
│                    (existing table - modified)                       │
├─────────────────────────────────────────────────────────────────────┤
│ id: bigint (PK)                                                      │
│ source_type: string (255)                                            │
│ source_id: string (36) ← MODIFIED from unsignedBigInteger           │
│ target_type: string (255)                                            │
│ target_id: string (36) ← MODIFIED from unsignedBigInteger           │
│ order: smallint (default: 0)                                         │
│ created_at: timestamp                                                │
└─────────────────────────────────────────────────────────────────────┘
```

## Relationship Diagram

```
┌─────────────┐      content_relations       ┌─────────────────┐
│    Page     │ ──────────────────────────▶ │   MediaAsset    │
│   Service   │   source_type: App\Models\X │                 │
│  BlogPost   │   source_id: content.id     │   (UUID PK)     │
│    Faq      │   target_type: MediaAsset   │                 │
│ Testimonial │   target_id: media.uuid     │                 │
└─────────────┘                              └─────────────────┘
                                                     │
                                                     │ 1:N
                                                     ▼
                                             ┌─────────────────┐
                                             │  MediaVariant   │
                                             │   (UUID PK)     │
                                             │                 │
                                             │ 480, 640, 720   │
                                             │ 960, 1168, 1440 │
                                             │ 1920, original  │
                                             └─────────────────┘
```

## Entities

### MediaAsset

Primary entity representing an uploaded media file.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | uuid | PK | Unique identifier |
| filename | string(255) | NOT NULL | Sanitized filename with nanoid (e.g., `hero-image-x7k9m2p4`) |
| original_name | string(255) | NOT NULL | Original uploaded filename |
| media_type | enum | NOT NULL | `image`, `video`, or `svg` |
| folder | enum | NOT NULL | Storage folder: `images`, `videos`, `svg` |
| file_size | bigint | NOT NULL | File size in bytes |
| dimensions | jsonb | NULL | `{width: int, height: int}` - null for video/SVG |
| mime_type | string(127) | NOT NULL | MIME type (e.g., `image/webp`) |
| state | enum | NOT NULL, DEFAULT 'uploading' | Processing state |
| error_message | text | NULL | Error details if state is 'failed' |
| s3_key_original | string(512) | NOT NULL | S3 object key for original file |
| cloudfront_url_original | string(512) | NOT NULL | CDN URL for original file |
| alt_text | string(255) | NULL | Accessibility alt text |
| title | string(255) | NULL | Media title |
| caption | text | NULL | Extended description |
| focal_point | jsonb | NULL | `{x: 0-1, y: 0-1}` for smart cropping |
| tags | jsonb | DEFAULT '[]' | Array of tag strings |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

**Indexes**:
- PRIMARY KEY (`id`)
- INDEX (`state`) - for querying by processing state
- INDEX (`media_type`) - for filtering by type
- INDEX (`deleted_at`) - for soft delete queries
- INDEX (`created_at`) - for chronological listing

**Validation Rules**:
- `original_name`: required, max 255 characters
- `media_type`: required, must be valid enum value
- `file_size`: required, max 20971520 (20MB)
- `mime_type`: required, must be in allowed list
- `dimensions.width`: 50-16000 (for images only)
- `dimensions.height`: 50-16000 (for images only)
- `focal_point.x`: 0-1 if present
- `focal_point.y`: 0-1 if present

### MediaVariant

Represents a responsive size variant of an image asset.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | uuid | PK | Unique identifier |
| media_asset_id | uuid | FK, NOT NULL, CASCADE | Reference to parent MediaAsset |
| width | int | NOT NULL | Variant width in pixels |
| height | int | NOT NULL | Variant height in pixels |
| format | string(10) | NOT NULL, DEFAULT 'webp' | Output format |
| file_size | bigint | NOT NULL | Variant file size in bytes |
| s3_key | string(512) | NOT NULL | S3 object key |
| cloudfront_url | string(512) | NOT NULL | CDN URL |
| created_at | timestamp | NOT NULL | Creation timestamp |

**Indexes**:
- PRIMARY KEY (`id`)
- INDEX (`media_asset_id`) - for fetching variants of an asset
- UNIQUE (`media_asset_id`, `width`) - one variant per width per asset

**Validation Rules**:
- `width`: required, positive integer
- `height`: required, positive integer
- `format`: required, typically 'webp'
- `file_size`: required, positive integer

### Setting

Key-value configuration storage.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| key | string(255) | PK | Configuration key (dot notation) |
| value | text | NULL | Configuration value (nullable) |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Known Keys**:
- `media.fallback_image_id`: UUID of the default fallback MediaAsset

## Enums

### MediaType

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaType: string
{
    case Image = 'image';
    case Video = 'video';
    case Svg = 'svg';

    /**
     * Get allowed MIME types for this media type.
     *
     * @return array<string>
     */
    public function allowedMimeTypes(): array
    {
        return match ($this) {
            self::Image => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            self::Video => ['video/mp4', 'video/webm', 'video/quicktime'],
            self::Svg => ['image/svg+xml'],
        };
    }

    /**
     * Determine media type from MIME type.
     */
    public static function fromMimeType(string $mimeType): ?self
    {
        foreach (self::cases() as $type) {
            if (in_array($mimeType, $type->allowedMimeTypes(), true)) {
                return $type;
            }
        }
        return null;
    }
}
```

### MediaFolder

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaFolder: string
{
    case Images = 'images';
    case Videos = 'videos';
    case Svg = 'svg';

    /**
     * Get folder for media type.
     */
    public static function forMediaType(MediaType $type): self
    {
        return match ($type) {
            MediaType::Image => self::Images,
            MediaType::Video => self::Videos,
            MediaType::Svg => self::Svg,
        };
    }

    /**
     * Get full S3 prefix path.
     */
    public function s3Prefix(): string
    {
        return 'media/' . $this->value;
    }
}
```

### MediaState

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaState: string
{
    case Uploading = 'uploading';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';

    /**
     * Check if state allows variant access.
     */
    public function isAccessible(): bool
    {
        return $this === self::Ready;
    }

    /**
     * Check if state indicates a terminal failure.
     */
    public function isFailed(): bool
    {
        return $this === self::Failed;
    }

    /**
     * Check if state indicates processing is in progress.
     */
    public function isProcessing(): bool
    {
        return $this === self::Uploading || $this === self::Processing;
    }
}
```

## State Transitions

```
                     ┌───────────────────────────────┐
                     │                               │
                     ▼                               │
┌──────────┐    ┌────────────┐    ┌─────────┐    ┌──────┐
│ uploading│───▶│ processing │───▶│  ready  │    │failed│
└──────────┘    └────────────┘    └─────────┘    └──────┘
     │               │                               ▲
     │               │                               │
     └───────────────┴───────────────────────────────┘
           (on error at any step)
```

**Valid Transitions**:
1. `uploading` → `processing`: File uploaded to S3, processing begins
2. `processing` → `ready`: All variants generated and uploaded
3. `uploading` → `failed`: S3 upload failed after retries
4. `processing` → `failed`: Variant generation or upload failed

## Relationships

### MediaAsset → MediaVariant (One-to-Many)

```php
// MediaAsset.php
public function variants(): HasMany
{
    return $this->hasMany(MediaVariant::class);
}

// MediaVariant.php
public function mediaAsset(): BelongsTo
{
    return $this->belongsTo(MediaAsset::class);
}
```

### Content Models → MediaAsset (Many-to-Many via content_relations)

Uses HasMedia trait to provide relationship methods through the generic content_relations table.

```php
// Page.php, Service.php, BlogPost.php, Faq.php, Testimonial.php
use App\Traits\HasMedia;

class Page extends Model
{
    use HasMedia;
    // ...
}
```

## HasMedia Trait Contract

```php
<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Collection;

/**
 * Provides media attachment capabilities to any Eloquent model.
 *
 * Uses the content_relations table for polymorphic relationships,
 * storing the relationship type identifier in the pivot data.
 */
trait HasMedia
{
    /**
     * Get all media assets attached to this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<MediaAsset>
     */
    public function mediaAssets(): MorphToMany;

    /**
     * Attach a media asset with a relationship type identifier.
     *
     * @param MediaAsset $media The media asset to attach
     * @param string $type Relationship identifier (e.g., 'page:home:hero:image')
     * @param int|null $order Optional display order
     */
    public function attachMedia(MediaAsset $media, string $type, ?int $order = null): void;

    /**
     * Detach media for a specific relationship type.
     *
     * @param string $type Relationship identifier to detach
     */
    public function detachMedia(string $type): void;

    /**
     * Get media asset for a specific relationship type.
     *
     * @param string $type Relationship identifier
     * @return MediaAsset|null
     */
    public function getMedia(string $type): ?MediaAsset;

    /**
     * Get all attached media assets.
     *
     * @return Collection<int, MediaAsset>
     */
    public function getAllMedia(): Collection;

    /**
     * Get media URL for a relationship type, with optional width.
     *
     * Returns fallback URL if no media attached or media deleted.
     *
     * @param string $type Relationship identifier
     * @param int|null $width Desired variant width (null for original)
     * @return string|null CDN URL or fallback URL
     */
    public function getMediaUrl(string $type, ?int $width = null): ?string;
}
```

## Relationship Type Naming Convention

| Context | Pattern | Example |
|---------|---------|---------|
| Fixed field (all pages) | `{field_name}` | `og_image` |
| Static page | `page:{slug}:{section}:{field}` | `page:home:hero:image` |
| Custom page type | `{type}:{section}:{field}` | `service:hero:image` |
| Content resource | `{resource}:{field}` | `testimonial:avatar` |

## Migration Modifications

### Modify content_relations Table

The existing `content_relations` table must be modified to support UUID foreign keys:

```php
Schema::table('content_relations', function (Blueprint $table) {
    // Change column types to accommodate both integers and UUIDs
    $table->string('source_id', 36)->change();
    $table->string('target_id', 36)->change();
});
```

**Impact Analysis**:
- Existing relationships using integer IDs will continue to work (stored as strings)
- New MediaAsset relationships will use UUID strings
- No data migration needed; string columns accept both formats

## Factory Definitions

### MediaAssetFactory

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MediaFolder;
use App\Enums\MediaState;
use App\Enums\MediaType;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    protected $model = MediaAsset::class;

    public function definition(): array
    {
        $nanoid = Str::random(8);
        $name = fake()->slug(2);
        $filename = "{$name}-{$nanoid}";

        return [
            'id' => Str::uuid()->toString(),
            'filename' => $filename,
            'original_name' => "{$name}.jpg",
            'media_type' => MediaType::Image,
            'folder' => MediaFolder::Images,
            'file_size' => fake()->numberBetween(50000, 5000000),
            'dimensions' => ['width' => 1920, 'height' => 1080],
            'mime_type' => 'image/webp',
            'state' => MediaState::Ready,
            'error_message' => null,
            's3_key_original' => "media/images/{$filename}-original.webp",
            'cloudfront_url_original' => "https://cdn.example.com/media/images/{$filename}-original.webp",
            'alt_text' => null,
            'title' => null,
            'caption' => null,
            'focal_point' => null,
            'tags' => [],
        ];
    }

    public function uploading(): self
    {
        return $this->state(fn (array $attrs) => ['state' => MediaState::Uploading]);
    }

    public function processing(): self
    {
        return $this->state(fn (array $attrs) => ['state' => MediaState::Processing]);
    }

    public function failed(): self
    {
        return $this->state(fn (array $attrs) => [
            'state' => MediaState::Failed,
            'error_message' => 'Processing failed: timeout',
        ]);
    }

    public function video(): self
    {
        return $this->state(function (array $attrs) {
            $nanoid = Str::random(8);
            $name = fake()->slug(2);
            $filename = "{$name}-{$nanoid}";

            return [
                'filename' => $filename,
                'original_name' => "{$name}.mp4",
                'media_type' => MediaType::Video,
                'folder' => MediaFolder::Videos,
                'dimensions' => null,
                'mime_type' => 'video/mp4',
                's3_key_original' => "media/videos/{$filename}.mp4",
                'cloudfront_url_original' => "https://cdn.example.com/media/videos/{$filename}.mp4",
            ];
        });
    }

    public function svg(): self
    {
        return $this->state(function (array $attrs) {
            $nanoid = Str::random(8);
            $name = fake()->slug(2);
            $filename = "{$name}-{$nanoid}";

            return [
                'filename' => $filename,
                'original_name' => "{$name}.svg",
                'media_type' => MediaType::Svg,
                'folder' => MediaFolder::Svg,
                'dimensions' => null,
                'mime_type' => 'image/svg+xml',
                's3_key_original' => "media/svg/{$filename}.svg",
                'cloudfront_url_original' => "https://cdn.example.com/media/svg/{$filename}.svg",
            ];
        });
    }

    public function withMetadata(): self
    {
        return $this->state(fn (array $attrs) => [
            'alt_text' => fake()->sentence(3),
            'title' => fake()->words(3, true),
            'caption' => fake()->paragraph(),
            'focal_point' => ['x' => 0.5, 'y' => 0.3],
            'tags' => fake()->words(3),
        ]);
    }

    public function withVariants(): self
    {
        return $this->afterCreating(function (MediaAsset $asset) {
            if ($asset->media_type !== MediaType::Image) {
                return;
            }

            $widths = [480, 640, 720, 960, 1168, 1440, 1920];
            $originalWidth = $asset->dimensions['width'] ?? 1920;
            $originalHeight = $asset->dimensions['height'] ?? 1080;
            $aspectRatio = $originalHeight / $originalWidth;

            foreach ($widths as $width) {
                if ($width > $originalWidth) {
                    continue;
                }

                $height = (int) round($width * $aspectRatio);

                MediaVariant::factory()->create([
                    'media_asset_id' => $asset->id,
                    'width' => $width,
                    'height' => $height,
                ]);
            }
        });
    }
}
```

### MediaVariantFactory

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaVariant>
 */
class MediaVariantFactory extends Factory
{
    protected $model = MediaVariant::class;

    public function definition(): array
    {
        $width = fake()->randomElement([480, 640, 720, 960, 1168, 1440, 1920]);
        $height = (int) round($width * 9 / 16); // 16:9 aspect ratio

        return [
            'id' => Str::uuid()->toString(),
            'media_asset_id' => MediaAsset::factory(),
            'width' => $width,
            'height' => $height,
            'format' => 'webp',
            'file_size' => fake()->numberBetween(10000, 500000),
            's3_key' => "media/images/sample-" . Str::random(8) . "-{$width}.webp",
            'cloudfront_url' => "https://cdn.example.com/media/images/sample-" . Str::random(8) . "-{$width}.webp",
        ];
    }
}
```

## Seeder Definition

### MediaSeeder

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MediaAsset;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        // Create a few ready images with variants
        $heroImage = MediaAsset::factory()
            ->withMetadata()
            ->withVariants()
            ->create([
                'original_name' => 'hero-background.jpg',
                'alt_text' => 'Hero background image',
            ]);

        $logoImage = MediaAsset::factory()
            ->svg()
            ->create([
                'original_name' => 'company-logo.svg',
                'alt_text' => 'Company Logo',
            ]);

        $testimonialAvatar = MediaAsset::factory()
            ->withVariants()
            ->create([
                'original_name' => 'testimonial-avatar.jpg',
                'alt_text' => 'Customer photo',
            ]);

        // Create fallback image and set in settings
        $fallbackImage = MediaAsset::factory()
            ->withVariants()
            ->create([
                'original_name' => 'placeholder.jpg',
                'alt_text' => 'Placeholder image',
            ]);

        Setting::updateOrCreate(
            ['key' => 'media.fallback_image_id'],
            ['value' => $fallbackImage->id]
        );

        // Create some processing/failed examples
        MediaAsset::factory()
            ->processing()
            ->create(['original_name' => 'processing-example.jpg']);

        MediaAsset::factory()
            ->failed()
            ->create(['original_name' => 'failed-example.jpg']);
    }
}
```
