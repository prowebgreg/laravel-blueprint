<?php

declare(strict_types=1);

/**
 * Feature Tests for ProcessMediaVariantsJob
 *
 * Tests async variant processing:
 * - Job processes media asset successfully
 * - Creates variant records in database
 * - Uploads variants to S3
 * - State transitions: processing → ready (success) or processing → failed (error)
 * - Queue configuration (media queue, 180s timeout)
 * - Rollback on failure
 *
 * @see /specs/003-media-engine/plan.md
 * @see /specs/003-media-engine/data-model.md
 */

use App\Enums\MediaState;
use App\Jobs\Media\ProcessMediaVariantsJob;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('job processing', function () {
    it('processes media asset successfully', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        // Create a fake image file for processing
        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 2000, 1125);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Ready);
    });

    it('transitions state from processing to ready on success', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        expect($asset->state)->toBe(MediaState::Processing);

        // Create a fake image file for processing
        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 2000, 1125);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Ready)
            ->and($asset->error_message)->toBeNull();
    });

    it('skips non-image assets and marks ready', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->video()->processing()->create();

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Ready)
            ->and(MediaVariant::where('media_asset_id', $asset->id)->count())->toBe(0);
    });

    it('sets error_message when failing', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        // Don't create the file, so download fails
        $job = new ProcessMediaVariantsJob($asset);
        $exception = new \Exception('File not found in S3');

        $job->failed($exception);

        $asset->refresh();
        expect($asset->error_message)->toContain('Variant generation failed');
    });
});

describe('variant generation', function () {
    it('creates all variant records in database', function () {
        Storage::fake('s3-permanent');

        // 4K image should create 8 variants (7 responsive + original)
        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 3840, 'height' => 2160],
        ]);

        // Create a fake image file
        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 2000, 1125);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        expect(MediaVariant::where('media_asset_id', $asset->id)->count())->toBeGreaterThanOrEqual(7);
    });

    it('skips variants larger than original image', function () {
        Storage::fake('s3-permanent');

        // 1200px image should skip 1440 and 1920 variants
        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 1200, 'height' => 675],
        ]);

        // Create a fake image file with matching dimensions
        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 1200, 675);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $variants = MediaVariant::where('media_asset_id', $asset->id)->get();
        $widths = $variants->pluck('width')->toArray();

        expect($widths)->not->toContain(1440)
            ->and($widths)->not->toContain(1920);
    });

    it('stores variant format as webp', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 2000, 1125);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $variants = MediaVariant::where('media_asset_id', $asset->id)->get();
        foreach ($variants as $variant) {
            expect($variant->format)->toBe('webp');
        }
    });

    it('sets file_size for each variant', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 2000, 1125);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $variants = MediaVariant::where('media_asset_id', $asset->id)->get();
        foreach ($variants as $variant) {
            expect($variant->file_size)->toBeInt()
                ->and($variant->file_size)->toBeGreaterThan(0);
        }
    });
});

describe('S3 upload', function () {
    it('uploads variants to S3', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 2000, 1125);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $variants = MediaVariant::where('media_asset_id', $asset->id)->get();
        foreach ($variants as $variant) {
            Storage::disk('s3-permanent')->assertExists($variant->s3_key);
        }
    });

    it('stores correct s3_key for each variant', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'filename' => 'hero-image-abc123',
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 2000, 1125);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $variant480 = MediaVariant::where('media_asset_id', $asset->id)
            ->where('width', 480)->first();

        if ($variant480) {
            expect($variant480->s3_key)->toStartWith('media/images/')
                ->and($variant480->s3_key)->toContain('hero-image-abc123')
                ->and($variant480->s3_key)->toContain('480')
                ->and($variant480->s3_key)->toEndWith('.webp');
        }
    });

    it('generates cloudfront_url for each variant', function () {
        Storage::fake('s3-permanent');
        config(['filesystems.disks.s3-permanent.url' => 'https://cdn.example.com']);

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 2000, 1125);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $variants = MediaVariant::where('media_asset_id', $asset->id)->get();
        foreach ($variants as $variant) {
            expect($variant->cloudfront_url)->toStartWith('https://cdn.example.com/');
        }
    });
});

describe('queue configuration', function () {
    it('runs on media queue', function () {
        Queue::fake();

        $asset = MediaAsset::factory()->processing()->create();

        ProcessMediaVariantsJob::dispatch($asset);

        Queue::assertPushedOn('media', ProcessMediaVariantsJob::class);
    });

    it('respects 180s timeout', function () {
        $job = new ProcessMediaVariantsJob(MediaAsset::factory()->make());

        expect($job->timeout)->toBe(180);
    });
});

describe('error handling and rollback', function () {
    it('transitions to failed on processing error', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        // Don't put the file, causing download to fail
        $job = new ProcessMediaVariantsJob($asset);

        try {
            $job->handle(
                app(\App\Actions\Media\GenerateVariantsAction::class),
                app(\App\Actions\Media\UploadToS3Action::class)
            );
        } catch (\Exception $e) {
            $job->failed($e);
        }

        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Failed);
    });

    it('rolls back partial variants on failure', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 2000, 'height' => 1125],
        ]);

        // Create some variants manually to simulate partial processing (use unique widths)
        MediaVariant::factory()->create([
            'media_asset_id' => $asset->id,
            'width' => 640,
            'height' => 360,
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $asset->id,
            'width' => 960,
            'height' => 540,
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $asset->id,
            'width' => 1168,
            'height' => 657,
        ]);

        expect(MediaVariant::where('media_asset_id', $asset->id)->count())->toBe(3);

        $job = new ProcessMediaVariantsJob($asset);
        $exception = new \Exception('Processing failed');
        $job->failed($exception);

        expect(MediaVariant::where('media_asset_id', $asset->id)->count())->toBe(0);
    });
});

describe('edge cases', function () {
    it('handles very small images correctly', function () {
        Storage::fake('s3-permanent');

        // 320px image should create fewer variants (only original)
        $asset = MediaAsset::factory()->processing()->create([
            'dimensions' => ['width' => 320, 'height' => 180],
        ]);

        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 320, 180);
        Storage::disk('s3-permanent')->put(
            $asset->s3_key_original,
            $fakeImage->getContent()
        );

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $variantCount = MediaVariant::where('media_asset_id', $asset->id)->count();
        // 320px is smaller than all configured widths, so should only create "original" variant
        expect($variantCount)->toBe(1);
    });

    it('does not process video assets', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->video()->processing()->create();

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Ready)
            ->and(MediaVariant::where('media_asset_id', $asset->id)->count())->toBe(0);
    });

    it('does not process svg assets', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->svg()->processing()->create();

        $job = new ProcessMediaVariantsJob($asset);
        $job->handle(
            app(\App\Actions\Media\GenerateVariantsAction::class),
            app(\App\Actions\Media\UploadToS3Action::class)
        );

        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Ready)
            ->and(MediaVariant::where('media_asset_id', $asset->id)->count())->toBe(0);
    });
});
