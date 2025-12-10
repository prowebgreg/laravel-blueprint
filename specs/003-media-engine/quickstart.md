# Quickstart: Media Engine & Asset Model

**Feature Branch**: `003-media-engine`
**Estimated Time**: 15 minutes

## Prerequisites

- [ ] Laravel Sail environment running (`./vendor/bin/sail up -d`)
- [ ] S3 bucket and CloudFront configured (Phase 1)
- [ ] content_relations table exists (Phase 2)
- [ ] Queue workers running (`./vendor/bin/sail artisan horizon`)

## Installation Steps

### 1. Install Required Packages

```bash
./vendor/bin/sail composer require spatie/image:^3.0 \
    spatie/laravel-image-optimizer:^1.7 \
    enshrined/svg-sanitize:^0.16
```

### 2. Run Migrations

```bash
./vendor/bin/sail artisan migrate
```

**Expected output**:
```
Migrating: 2025_12_10_000001_create_media_assets_table
Migrated:  2025_12_10_000001_create_media_assets_table (XXms)
Migrating: 2025_12_10_000002_create_media_variants_table
Migrated:  2025_12_10_000002_create_media_variants_table (XXms)
Migrating: 2025_12_10_000003_create_settings_table
Migrated:  2025_12_10_000003_create_settings_table (XXms)
Migrating: 2025_12_10_000004_modify_content_relations_for_uuid
Migrated:  2025_12_10_000004_modify_content_relations_for_uuid (XXms)
```

### 3. Seed Example Data

```bash
./vendor/bin/sail artisan db:seed --class=MediaSeeder
```

### 4. Verify Configuration

```bash
./vendor/bin/sail artisan config:show media
```

**Expected**: Configuration values for max upload size, variant widths, allowed MIME types.

## Verification Checklist

### Database Verification

```bash
./vendor/bin/sail artisan tinker
```

```php
// Check media_assets table exists with correct columns
\Schema::hasTable('media_assets'); // true
\Schema::hasColumn('media_assets', 'state'); // true
\Schema::hasColumn('media_assets', 'focal_point'); // true

// Check media_variants table exists
\Schema::hasTable('media_variants'); // true
\Schema::hasColumn('media_variants', 'media_asset_id'); // true

// Check settings table exists
\Schema::hasTable('settings'); // true

// Check content_relations supports string IDs
$columnType = collect(\DB::select("
    SELECT data_type
    FROM information_schema.columns
    WHERE table_name = 'content_relations'
    AND column_name = 'target_id'
"))->first();
$columnType->data_type; // 'character varying'

// Check seeded data exists
\App\Models\MediaAsset::count(); // >= 5
\App\Models\MediaVariant::count(); // >= 20
\App\Models\Setting::where('key', 'media.fallback_image_id')->exists(); // true
```

### Enum Verification

```php
// Verify enums work correctly
use App\Enums\MediaType;
use App\Enums\MediaState;
use App\Enums\MediaFolder;

MediaType::Image->value; // 'image'
MediaType::fromMimeType('image/jpeg'); // MediaType::Image
MediaFolder::forMediaType(MediaType::Svg); // MediaFolder::Svg
MediaState::Ready->isAccessible(); // true
MediaState::Processing->isProcessing(); // true
```

### Model Verification

```php
// Test factory states
$asset = \App\Models\MediaAsset::factory()->create();
$asset->state->value; // 'ready'

$failed = \App\Models\MediaAsset::factory()->failed()->create();
$failed->state->value; // 'failed'
$failed->error_message; // 'Processing failed: timeout'

// Test variant relationship
$withVariants = \App\Models\MediaAsset::factory()->withVariants()->create();
$withVariants->variants()->count(); // >= 7

// Test UUID primary key
strlen($asset->id); // 36 (UUID format)
```

### HasMedia Trait Verification

```php
use App\Models\Page;
use App\Models\MediaAsset;

// Create test page and media
$page = Page::factory()->published()->create();
$media = MediaAsset::factory()->withVariants()->create();

// Attach media
$page->attachMedia($media, 'page:test:hero:image');

// Retrieve media
$retrieved = $page->getMedia('page:test:hero:image');
$retrieved->id === $media->id; // true

// Get media URL
$url = $page->getMediaUrl('page:test:hero:image', 480);
str_contains($url, '-480.webp'); // true

// Get fallback when no media attached
$noMedia = Page::factory()->published()->create();
$fallbackUrl = $noMedia->getMediaUrl('page:test:missing:image');
$fallbackUrl !== null; // true (returns fallback)

// Detach media
$page->detachMedia('page:test:hero:image');
$page->getMedia('page:test:hero:image'); // null
```

### Action Verification

```php
use App\Actions\Media\SanitizeFilenameAction;
use App\Actions\Media\ValidateUploadAction;

// Test filename sanitization
$sanitize = new SanitizeFilenameAction();
$sanitize->execute('Hero Image (1).jpg'); // 'hero-image-1-{nanoid}'

// Test with various inputs
$sanitize->execute('___weird___file___.png'); // 'weird-file-{nanoid}'
$sanitize->execute('UPPERCASE.JPG'); // 'uppercase-{nanoid}'
```

### SVG Sanitization Verification

```php
use App\Actions\Media\SanitizeSvgAction;

$sanitize = new SanitizeSvgAction();

// Test malicious SVG
$malicious = '<svg><script>alert("xss")</script><circle r="10"/></svg>';
$clean = $sanitize->execute($malicious);
str_contains($clean, '<script>'); // false
str_contains($clean, '<circle'); // true

// Test event handlers
$eventHandler = '<svg onclick="alert(1)"><rect/></svg>';
$clean = $sanitize->execute($eventHandler);
str_contains($clean, 'onclick'); // false
```

### Service Verification

```php
use App\Services\Media\MediaUsageService;
use App\Models\Page;
use App\Models\MediaAsset;

// Test usage tracking
$media = MediaAsset::factory()->withVariants()->create();
$page = Page::factory()->published()->create();
$page->attachMedia($media, 'og_image');

$usageService = app(MediaUsageService::class);
$usages = $usageService->findUsages($media);
$usages->isNotEmpty(); // true

// Test deletion blocking
$isBlocked = $usageService->isBlockedByPublicContent($media);
$isBlocked; // true (used by Page)

// Test with only content resource usage
$faq = \App\Models\Faq::factory()->published()->create();
$faqMedia = MediaAsset::factory()->create();
$faq->attachMedia($faqMedia, 'faq:image');
$usageService->isBlockedByPublicContent($faqMedia); // false
```

### Fallback Service Verification

```php
use App\Services\Media\MediaFallbackService;

$fallbackService = app(MediaFallbackService::class);

// Get fallback URL
$url = $fallbackService->getFallbackUrl();
$url !== null; // true

// Get fallback with specific width
$url480 = $fallbackService->getFallbackUrl(480);
str_contains($url480, '-480.webp') || str_contains($url480, 'original'); // true
```

## Test Suite Verification

```bash
# Run all media-related tests
./vendor/bin/sail artisan test --filter=Media

# Run specific test files
./vendor/bin/sail artisan test tests/Unit/Actions/Media/
./vendor/bin/sail artisan test tests/Feature/MediaUploadTest.php
./vendor/bin/sail artisan test tests/Feature/HasMediaTraitTest.php
```

**Expected**: All tests pass with no failures.

## Queue Verification

```bash
# Check Horizon is running with media queue
./vendor/bin/sail artisan horizon:status
```

In Tinker:
```php
// Dispatch a test job (if ProcessMediaVariantsJob exists)
// Note: This requires actual file handling, use mock for verification
\App\Jobs\Media\ProcessMediaVariantsJob::dispatch(
    \App\Models\MediaAsset::factory()->processing()->create()
);

// Check job was queued
\Illuminate\Support\Facades\Queue::size('media'); // >= 1
```

## Scheduled Jobs Verification

```bash
# List scheduled commands
./vendor/bin/sail artisan schedule:list
```

**Expected output includes**:
```
CleanupFailedMediaJob .............. daily at 3:00
SyncOrphanedFilesJob ............... weekly on Sunday at 4:00
```

## Common Issues

### Issue: "Class MediaAsset not found"
**Solution**: Clear composer autoload cache:
```bash
./vendor/bin/sail composer dump-autoload
```

### Issue: "Undefined type MediaState"
**Solution**: Ensure enums are created:
```bash
ls app/Enums/MediaState.php app/Enums/MediaType.php app/Enums/MediaFolder.php
```

### Issue: "S3 connection refused"
**Solution**: Verify AWS credentials in `.env`:
```bash
./vendor/bin/sail artisan config:show filesystems.disks.s3
```

### Issue: "content_relations column type mismatch"
**Solution**: Run the migration to modify column types:
```bash
./vendor/bin/sail artisan migrate:status
./vendor/bin/sail artisan migrate
```

### Issue: "Fallback image returns null"
**Solution**: Seed the fallback image setting:
```bash
./vendor/bin/sail artisan db:seed --class=MediaSeeder
```

## Next Steps

After verifying all checks pass:

1. Phase 4: Global Settings Admin (includes fallback image picker UI)
2. Phase 5: Admin Interface (Filament media library, upload modal)
3. Phase 6: Content Blocks with Media (integrate media into block system)
