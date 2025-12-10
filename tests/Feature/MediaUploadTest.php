<?php

declare(strict_types=1);

/**
 * Feature Tests for Complete Media Upload Flow
 *
 * Tests end-to-end upload functionality:
 * - Complete upload flow: validate → sanitize → extract metadata → create asset → dispatch job
 * - State transitions: processing → ready
 * - Variant generation for images
 * - SVG and video handling
 * - Validation errors
 *
 * @see /specs/003-media-engine/plan.md
 * @see /specs/003-media-engine/data-model.md
 */

use App\Enums\MediaState;
use App\Enums\MediaType;
use App\Jobs\Media\ProcessMediaVariantsJob;
use App\Models\MediaAsset;
use App\Services\Media\MediaUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('complete upload flow', function () {
    it('creates MediaAsset with processing state initially for image', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('hero-image.jpg', 1920, 1080);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset)->toBeInstanceOf(MediaAsset::class)
            ->and($asset->state)->toBe(MediaState::Processing)
            ->and($asset->media_type)->toBe(MediaType::Image);
    });

    it('sanitizes filename with nanoid suffix', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('My Hero Image!.jpg', 1920, 1080);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->filename)->toMatch('/^my-hero-image-[a-z0-9]{8}\.jpg$/')
            ->and($asset->original_name)->toBe('My Hero Image!.jpg');
    });

    it('extracts metadata dimensions for images', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->dimensions)->toBeArray()
            ->and($asset->dimensions['width'])->toBe(1920)
            ->and($asset->dimensions['height'])->toBe(1080);
    });

    it('uploads to S3 permanent folder', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        Storage::disk('s3-permanent')->assertExists($asset->s3_key_original);
    });

    it('dispatches ProcessMediaVariantsJob for images', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        Queue::assertPushed(ProcessMediaVariantsJob::class, function ($job) use ($asset) {
            return $job->asset->id === $asset->id;
        });
    });

    it('stores correct mime type', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('test.png', 800, 600)->mimeType('image/png');
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->mime_type)->toBe('image/png');
    });

    it('stores file size', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->file_size)->toBeGreaterThan(0);
    });
});

describe('SVG upload handling', function () {
    it('stores SVG without variants', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->create('logo.svg', 100, 'image/svg+xml');
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->media_type)->toBe(MediaType::Svg)
            ->and($asset->variants)->toHaveCount(0);

        Queue::assertNotPushed(ProcessMediaVariantsJob::class);
    });

    it('marks SVG ready immediately', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->create('logo.svg', 100, 'image/svg+xml');
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->state)->toBe(MediaState::Ready);
    });

    it('stores SVG in svg folder', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->create('logo.svg', 100, 'image/svg+xml');
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->s3_key_original)->toContain('media/svg/');
    });
});

describe('video upload handling', function () {
    it('stores video without processing', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->create('video.mp4', 5000, 'video/mp4');
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->media_type)->toBe(MediaType::Video)
            ->and($asset->dimensions)->toBeNull()
            ->and($asset->variants)->toHaveCount(0);

        Queue::assertNotPushed(ProcessMediaVariantsJob::class);
    });

    it('marks video ready immediately', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->create('video.mp4', 5000, 'video/mp4');
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->state)->toBe(MediaState::Ready);
    });

    it('stores video in videos folder', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->create('video.mp4', 5000, 'video/mp4');
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->s3_key_original)->toContain('media/videos/');
    });
});

describe('validation errors', function () {
    it('rejects invalid file type', function () {
        Storage::fake('s3-permanent');

        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');
        $service = app(MediaUploadService::class);

        expect(fn () => $service->upload($file))
            ->toThrow(\InvalidArgumentException::class);

        expect(MediaAsset::count())->toBe(0);
    });

    it('rejects file too large', function () {
        Storage::fake('s3-permanent');

        $file = UploadedFile::fake()->create('large.jpg', 25000, 'image/jpeg'); // 25MB
        $service = app(MediaUploadService::class);

        expect(fn () => $service->upload($file))
            ->toThrow(\InvalidArgumentException::class);

        expect(MediaAsset::count())->toBe(0);
    });

    it('rejects image with dimensions too small', function () {
        Storage::fake('s3-permanent');

        $file = UploadedFile::fake()->image('tiny.jpg', 40, 40); // Below 50px minimum
        $service = app(MediaUploadService::class);

        expect(fn () => $service->upload($file))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('rejects image with dimensions too large', function () {
        Storage::fake('s3-permanent');

        $file = UploadedFile::fake()->image('huge.jpg', 17000, 1000); // Above 16000px maximum
        $service = app(MediaUploadService::class);

        expect(fn () => $service->upload($file))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('does not create MediaAsset on validation failure', function () {
        Storage::fake('s3-permanent');

        $file = UploadedFile::fake()->create('bad.exe', 100, 'application/x-msdownload');
        $service = app(MediaUploadService::class);

        try {
            $service->upload($file);
        } catch (\Exception $e) {
            // Expected
        }

        expect(MediaAsset::count())->toBe(0);
    });

    it('does not upload to S3 on validation failure', function () {
        Storage::fake('s3-permanent');

        $file = UploadedFile::fake()->create('bad.exe', 100, 'application/x-msdownload');
        $service = app(MediaUploadService::class);

        try {
            $service->upload($file);
        } catch (\Exception $e) {
            // Expected
        }

        // Check that no files were uploaded
        expect(Storage::disk('s3-permanent')->allFiles())->toHaveCount(0);
    });
});

describe('filename sanitization', function () {
    it('converts to lowercase', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('UPPERCASE.jpg', 800, 600);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->filename)->toMatch('/^[a-z0-9-]+\.[a-z]+$/');
    });

    it('replaces spaces with hyphens', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('my hero image.jpg', 800, 600);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->filename)->toContain('my-hero-image');
    });

    it('removes special characters', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('file@#$%name!.jpg', 800, 600);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->filename)->toMatch('/^[a-z0-9-]+\.[a-z]+$/');
    });

    it('appends 8-character nanoid suffix', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        $service = app(MediaUploadService::class);

        $asset = $service->upload($file);

        expect($asset->filename)->toMatch('/^test-[a-z0-9]{8}\.jpg$/');
    });
});

describe('edge cases', function () {
    it('handles concurrent uploads with same original filename', function () {
        Storage::fake('s3-permanent');
        Queue::fake();

        $file1 = UploadedFile::fake()->image('test.jpg', 800, 600);
        $file2 = UploadedFile::fake()->image('test.jpg', 1024, 768);
        $service = app(MediaUploadService::class);

        $asset1 = $service->upload($file1);
        $asset2 = $service->upload($file2);

        expect($asset1->filename)->not->toBe($asset2->filename);
    });
});
