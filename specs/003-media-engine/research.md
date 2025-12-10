# Research: Media Engine & Asset Model

**Feature Branch**: `003-media-engine`
**Date**: 2025-12-10

## Package Research

### spatie/image ^3.0

**Decision**: Use Spatie Image for image manipulation

**Rationale**:
- Native WebP conversion with simple API: `Image::load($path)->width(480)->save('image.webp')`
- Aspect ratio preservation built-in with `->scale()` method
- Supports GD and Imagick drivers (project uses GD per constitution)
- Clean API for resize operations without manual calculations
- First-frame extraction from GIFs handled automatically by image drivers

**Usage Pattern**:
```php
use Spatie\Image\Image;
use Spatie\Image\Enums\ImageDriver;

Image::useImageDriver(ImageDriver::Gd)
    ->loadFile($sourcePath)
    ->width($targetWidth)
    ->format('webp')
    ->quality(85)
    ->save($destinationPath);
```

**Alternatives Rejected**:
- Intervention Image v3: Also viable but Spatie Image offers simpler API for our use case (resize + format conversion only)
- Raw GD functions: Too low-level, requires manual aspect ratio calculations

### spatie/laravel-image-optimizer ^1.7

**Decision**: Use for optimization pipeline after variant generation

**Rationale**:
- Integrates with Laravel's service container
- Supports cwebp (WebP optimization), jpegoptim, optipng
- Middleware available but we'll use programmatic optimization in Jobs
- Chainable with Spatie Image workflow

**Usage Pattern**:
```php
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

// After Spatie Image generates the WebP
ImageOptimizer::optimize($webpPath);
```

**Configuration Required**:
```php
// config/image-optimizer.php
'optimizers' => [
    Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
        '-q 85',
        '-mt', // Multi-threading
    ],
],
```

**Alternatives Rejected**:
- Manual cwebp shell calls: Spatie package handles binary detection and error handling
- TinyPNG API: External dependency, rate limits, cost

### enshrined/svg-sanitize ^0.16

**Decision**: Use for SVG XSS protection before storage

**Rationale**:
- Purpose-built for SVG sanitization
- Removes `<script>` tags, event handlers (onclick, onload, etc.), external references
- Preserves valid SVG structure and visual elements
- Used by WordPress and other major CMS platforms

**Usage Pattern**:
```php
use enshrined\svgSanitize\Sanitizer;

$sanitizer = new Sanitizer();
$sanitizer->removeRemoteReferences(true); // Remove external xlink:href
$sanitizer->minify(false); // Preserve formatting for debugging

$cleanSvg = $sanitizer->sanitize($dirtySvg);

if ($cleanSvg === false) {
    throw new InvalidSvgException('SVG sanitization failed');
}
```

**XSS Vectors Handled**:
- `<script>` tags embedded in SVG
- Event handlers: `onclick`, `onload`, `onerror`, `onmouseover`, etc.
- JavaScript URLs: `href="javascript:..."`
- External references: `xlink:href="http://..."`
- Data URIs with scripts

**Alternatives Rejected**:
- DOMPurify (JS): Server-side PHP needed
- Manual regex stripping: Insufficient, SVG attack vectors are complex

## Design Decisions

### Decision 1: UUID vs Auto-increment for MediaAsset

**Decision**: Use UUID primary keys for MediaAsset and MediaVariant models

**Rationale**:
- Prevents ID enumeration attacks on media URLs
- Enables distributed generation without coordination
- Safer for public-facing CDN URLs
- Spec explicitly requires UUID

**Implementation**:
- MediaAsset and MediaVariant use `$table->uuid('id')->primary()`
- HasMedia trait will use a separate relationship approach (not content_relations)
- The existing content_relations table uses `unsignedBigInteger` IDs, incompatible with UUIDs

### Decision 2: Media Relationships via content_relations

**Decision**: Extend content_relations table to support UUID targets

**Rationale**:
- Spec FR-029 explicitly states: "System MUST use the existing content_relations table for media-to-content relationships"
- Constitution mandates using Relationship Engine for all content linking
- Avoids creating a custom media_content pivot table

**Implementation Challenge**:
- Current content_relations uses `unsignedBigInteger` for target_id
- MediaAsset uses UUID primary key
- Solution: Store UUID as string in existing column (36 chars fits)

**Migration Approach**:
```php
// Modify column type to accommodate both integers and UUIDs
$table->string('target_id', 36)->change();
$table->string('source_id', 36)->change();
```

**Alternative Considered but Rejected**:
- Separate media_attachments table: Violates constitution's Relationship Engine rule
- Keep integer IDs for MediaAsset: Loses UUID security benefits

### Decision 3: State Machine Implementation

**Decision**: Use simple enum-based state with model events, not a state machine package

**Rationale**:
- Only 4 states: uploading → processing → ready/failed
- Transitions are linear and simple
- Model observers handle state changes
- No complex branching or guard conditions

**States**:
```php
enum MediaState: string
{
    case Uploading = 'uploading';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
}
```

**Transition Rules**:
- `uploading` → `processing` (after S3 upload succeeds)
- `processing` → `ready` (after variants generated)
- `processing` → `failed` (on any error)
- `uploading` → `failed` (on S3 upload failure)

### Decision 4: S3 Key Naming Strategy

**Decision**: Use flat folder structure with filename-nanoid-width pattern

**Rationale**:
- Spec defines: `media/images/{filename}-{nanoid}-{width}.webp`
- No nested date folders (simplifies cleanup and organization)
- 8-character nanoid prevents collisions while keeping URLs short

**Pattern**:
```
media/
├── images/
│   ├── hero-image-x7k9m2p4-original.webp
│   ├── hero-image-x7k9m2p4-480.webp
│   ├── hero-image-x7k9m2p4-640.webp
│   └── ...
├── videos/
│   └── demo-video-a1b2c3d4.mp4
└── svg/
    └── logo-icon-e5f6g7h8.svg
```

### Decision 5: Variant Generation Strategy

**Decision**: Generate only variants smaller than or equal to original width

**Rationale**:
- FR-005a: "System MUST NOT upscale images"
- Skip variants where `targetWidth > originalWidth`
- Original stored as `-original.webp` (converted to WebP, not resized)

**Width Hierarchy**: 480, 640, 720, 960, 1168, 1440, 1920, original

**Example**: 1200px original generates: 480, 640, 720, 960, 1168, original (skips 1440, 1920)

### Decision 6: Fallback Image Resolution

**Decision**: Store fallback media asset ID in settings table

**Rationale**:
- Settings table provides key-value storage for system configuration
- Fallback can be any existing MediaAsset in ready state
- FR-051 requires blocking deletion of fallback image

**Implementation**:
```php
// Setting key: 'media.fallback_image_id'
// Value: UUID of the fallback MediaAsset

// MediaFallbackService
public function getFallbackUrl(?int $width = null): ?string
{
    $fallbackId = Setting::get('media.fallback_image_id');
    if (!$fallbackId) return null;

    $asset = MediaAsset::find($fallbackId);
    return $asset?->getUrl($width);
}
```

### Decision 7: Usage Tracking Scope

**Decision**: Query content_relations for media usage, check model type for blocking

**Rationale**:
- FR-041: Block deletion when used by Page, Service, BlogPost (public content)
- FR-042: Allow deletion when only used by Faq, Testimonial (content resources)
- FR-043: Ignore soft-deleted content

**Public Models (block deletion)**:
- `App\Models\Page`
- `App\Models\Service`
- `App\Models\BlogPost`

**Content Resources (allow deletion)**:
- `App\Models\Faq`
- `App\Models\Testimonial`
- `App\Models\TeamMember` (future)

### Decision 8: Queue Configuration

**Decision**: Use dedicated 'media' queue for processing jobs

**Rationale**:
- Horizon configuration already exists in Phase 1
- Media processing is resource-intensive (image manipulation)
- Separate queue prevents blocking other jobs
- Configurable timeout per job type

**Job Configuration**:
```php
// ProcessMediaVariantsJob
public int $timeout = 180;
public string $queue = 'media';
public int $tries = 1; // Rollback handles failures

// CleanupFailedMediaJob
public int $timeout = 60;
public string $queue = 'media';

// SyncOrphanedFilesJob
public int $timeout = 300;
public string $queue = 'media';
```

### Decision 9: Error Handling and Rollback

**Decision**: Transaction + S3 cleanup on failure

**Rationale**:
- FR-021: "Roll back all successfully uploaded variants if any variant upload fails"
- Database wrapped in transaction
- S3 uploads tracked for cleanup on failure

**Pattern**:
```php
$uploadedKeys = [];
DB::beginTransaction();
try {
    foreach ($variants as $variant) {
        $key = $this->uploadToS3Action->execute($variant);
        $uploadedKeys[] = $key;
    }
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    $this->deleteFromS3Action->executeBatch($uploadedKeys);
    throw $e;
}
```

### Decision 10: Filename Sanitization Rules

**Decision**: Strict sanitization with nanoid suffix

**Rationale**:
- FR-016: lowercase, hyphens for spaces, remove special characters
- FR-017: 8-character unique identifier

**Rules**:
1. Convert to lowercase
2. Replace spaces and underscores with hyphens
3. Remove all non-alphanumeric except hyphens
4. Collapse multiple hyphens to single
5. Trim hyphens from start/end
6. Append `-{nanoid8}`

**Examples**:
- `Hero Image (1).jpg` → `hero-image-1-x7k9m2p4.webp`
- `___weird___file___.png` → `weird-file-a1b2c3d4.webp`
- `Ünïcödé Fîlé.gif` → `unicode-file-e5f6g7h8.webp`

## Infrastructure Decisions

### Test Fixtures

**Decision**: Create minimal test images using PHP GD in test setup

**Rationale**:
- Avoid binary files in git repository
- Generate fixtures with known dimensions for assertions
- Create valid/invalid variants programmatically

**Fixture Generation**:
```php
// tests/TestCase.php or dedicated trait
protected function createTestImage(int $width, int $height, string $format = 'jpg'): string
{
    $image = imagecreatetruecolor($width, $height);
    $path = storage_path("framework/testing/test-{$width}x{$height}.{$format}");

    match ($format) {
        'jpg', 'jpeg' => imagejpeg($image, $path, 90),
        'png' => imagepng($image, $path),
        'gif' => imagegif($image, $path),
        'webp' => imagewebp($image, $path, 90),
    };

    imagedestroy($image);
    return $path;
}
```

**SVG Fixtures**: Hardcoded strings in tests for valid/malicious SVG content.

### MinIO for Integration Tests

**Decision**: Use Storage::fake() for unit tests, real S3 for integration tests only

**Rationale**:
- Unit tests should be fast and isolated
- Integration tests verify actual S3 behavior
- MinIO can be added to docker-compose.yml for local integration testing

**Configuration**:
```php
// phpunit.xml
<env name="FILESYSTEM_DISK" value="testing"/>

// config/filesystems.php (testing disk)
'testing' => [
    'driver' => 'local',
    'root' => storage_path('framework/testing'),
],
```

### Scheduled Jobs

**Decision**: Register cleanup jobs in Console Kernel

**Rationale**:
- CleanupFailedMediaJob: daily at 3 AM
- SyncOrphanedFilesJob: weekly on Sunday at 4 AM
- Purge soft-deleted: daily at 3:30 AM

**Registration**:
```php
// routes/console.php (Laravel 12 style)
Schedule::job(new CleanupFailedMediaJob)->dailyAt('03:00');
Schedule::job(new SyncOrphanedFilesJob)->weeklyOn(0, '04:00');
```

## Unknowns Resolved

| Original Unknown | Resolution |
|------------------|------------|
| How to handle UUID with content_relations? | Modify column types to string(36) to accommodate both integer and UUID |
| State machine package needed? | No - simple enum with 4 states is sufficient |
| Where to store fallback image reference? | settings table with key 'media.fallback_image_id' |
| How to generate test fixtures? | PHP GD in test setup, not binary files in repo |
| Queue configuration for media jobs? | Dedicated 'media' queue, configurable timeouts per job |
