<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

/**
 * Feature Tests for SyncOrphanedFilesJob
 *
 * Tests automated orphan detection and sync reporting between S3 and database:
 * - Detects orphaned S3 files (files in S3 without corresponding DB records)
 * - Marks MediaAsset as failed when S3 file is missing (DB record without S3 file)
 * - Generates comprehensive report of orphaned and missing files
 * - Handles multiple media folders (images, videos, svg)
 * - Processes files in subdirectories and variant paths
 * - Queue configuration (media queue)
 * - Edge cases: empty bucket, empty database, mixed scenarios
 *
 * Job Purpose:
 * - Runs weekly (Sunday 4:00 AM) to detect S3/DB inconsistencies
 * - Orphaned S3 files: Files uploaded but DB record never created or deleted
 * - Missing S3 files: DB record exists but file was manually deleted from S3
 * - Provides actionable report for manual cleanup/investigation
 *
 * @see /specs/003-media-engine/plan.md
 * @see /specs/003-media-engine/data-model.md
 */

use App\Enums\MediaState;
use App\Jobs\Media\SyncOrphanedFilesJob;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('orphan detection (S3 files without DB records)', function () {
    it('detects orphaned files in S3', function () {
        Storage::fake('s3-permanent');

        // Create valid MediaAsset with S3 file
        $validAsset = MediaAsset::factory()->create();
        Storage::disk('s3-permanent')->put($validAsset->s3_key_original, 'valid content');

        // Create orphaned S3 file (no DB record)
        $orphanedKey = 'media/images/orphaned-file-abc123.webp';
        Storage::disk('s3-permanent')->put($orphanedKey, 'orphaned content');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // Report should include orphaned file
        expect($report)->toHaveKey('orphaned_files')
            ->and($report['orphaned_files'])->toContain($orphanedKey)
            ->and($report['orphaned_files'])->not->toContain($validAsset->s3_key_original);
    });

    it('detects multiple orphaned files', function () {
        Storage::fake('s3-permanent');

        // Create 3 orphaned S3 files in different folders
        $orphan1 = 'media/images/orphan-1-xyz789.webp';
        $orphan2 = 'media/videos/orphan-2-abc456.mp4';
        $orphan3 = 'media/svg/orphan-3-def123.svg';

        Storage::disk('s3-permanent')->put($orphan1, 'orphan 1');
        Storage::disk('s3-permanent')->put($orphan2, 'orphan 2');
        Storage::disk('s3-permanent')->put($orphan3, 'orphan 3');

        // Create one valid asset
        $validAsset = MediaAsset::factory()->create();
        Storage::disk('s3-permanent')->put($validAsset->s3_key_original, 'valid');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['orphaned_files'])->toHaveCount(3)
            ->and($report['orphaned_files'])->toContain($orphan1)
            ->and($report['orphaned_files'])->toContain($orphan2)
            ->and($report['orphaned_files'])->toContain($orphan3)
            ->and($report['orphaned_files'])->not->toContain($validAsset->s3_key_original);
    });

    it('ignores files that have matching MediaAsset records', function () {
        Storage::fake('s3-permanent');

        // Create 3 valid assets with S3 files
        $asset1 = MediaAsset::factory()->create();
        $asset2 = MediaAsset::factory()->create();
        $asset3 = MediaAsset::factory()->create();

        Storage::disk('s3-permanent')->put($asset1->s3_key_original, 'content 1');
        Storage::disk('s3-permanent')->put($asset2->s3_key_original, 'content 2');
        Storage::disk('s3-permanent')->put($asset3->s3_key_original, 'content 3');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // No orphans should be detected
        expect($report['orphaned_files'])->toBeEmpty();
    });

    it('reports orphaned files in return data', function () {
        Storage::fake('s3-permanent');

        $orphan1 = 'media/images/orphan-abc.webp';
        $orphan2 = 'media/images/orphan-xyz.webp';
        Storage::disk('s3-permanent')->put($orphan1, 'orphan 1');
        Storage::disk('s3-permanent')->put($orphan2, 'orphan 2');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report)->toBeArray()
            ->and($report)->toHaveKeys(['orphaned_files', 'missing_files', 'summary'])
            ->and($report['orphaned_files'])->toHaveCount(2);
    });

    it('detects orphaned variant files', function () {
        Storage::fake('s3-permanent');

        // Create orphaned variant files (parent MediaAsset doesn't exist)
        $orphanVariant1 = 'media/images/variants/orphan-480w.webp';
        $orphanVariant2 = 'media/images/variants/orphan-720w.webp';

        Storage::disk('s3-permanent')->put($orphanVariant1, 'variant 1');
        Storage::disk('s3-permanent')->put($orphanVariant2, 'variant 2');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['orphaned_files'])->toContain($orphanVariant1)
            ->and($report['orphaned_files'])->toContain($orphanVariant2);
    });
});

describe('missing file detection (DB records without S3 files)', function () {
    it('marks MediaAsset as failed when S3 file is missing', function () {
        Storage::fake('s3-permanent');

        // Create MediaAsset but don't create S3 file
        $asset = MediaAsset::factory()->create([
            'state' => MediaState::Ready,
        ]);

        // Verify S3 file doesn't exist
        expect(Storage::disk('s3-permanent')->exists($asset->s3_key_original))->toBeFalse();

        $job = new SyncOrphanedFilesJob;
        $job->handle();

        // Asset should be marked as failed
        $asset->refresh();
        expect($asset->state)->toBe(MediaState::Failed)
            ->and($asset->error_message)->toContain('S3 file missing');
    });

    it('preserves error message with reason for failure', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->create([
            'state' => MediaState::Ready,
            'error_message' => null,
        ]);

        $job = new SyncOrphanedFilesJob;
        $job->handle();

        $asset->refresh();
        expect($asset->error_message)->not->toBeNull()
            ->and($asset->error_message)->toContain('S3 file missing')
            ->and($asset->error_message)->toContain('SyncOrphanedFilesJob');
    });

    it('handles multiple missing files', function () {
        Storage::fake('s3-permanent');

        // Create 3 assets without S3 files
        $asset1 = MediaAsset::factory()->create(['state' => MediaState::Ready]);
        $asset2 = MediaAsset::factory()->create(['state' => MediaState::Ready]);
        $asset3 = MediaAsset::factory()->create(['state' => MediaState::Ready]);

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // All should be marked as failed
        expect(MediaAsset::where('state', MediaState::Failed)->count())->toBe(3)
            ->and($report['missing_files'])->toHaveCount(3);
    });

    it('ignores MediaAsset records that have matching S3 files', function () {
        Storage::fake('s3-permanent');

        // Create assets with S3 files
        $asset1 = MediaAsset::factory()->create(['state' => MediaState::Ready]);
        $asset2 = MediaAsset::factory()->create(['state' => MediaState::Ready]);
        $asset3 = MediaAsset::factory()->create(['state' => MediaState::Ready]);

        Storage::disk('s3-permanent')->put($asset1->s3_key_original, 'content 1');
        Storage::disk('s3-permanent')->put($asset2->s3_key_original, 'content 2');
        Storage::disk('s3-permanent')->put($asset3->s3_key_original, 'content 3');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // No files should be marked as missing
        expect($report['missing_files'])->toBeEmpty()
            ->and(MediaAsset::where('state', MediaState::Failed)->count())->toBe(0);
    });

    it('does not re-mark already failed assets', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->failed()->create([
            'error_message' => 'Original error: Processing timeout',
        ]);

        // File is missing from S3 (never created)
        expect(Storage::disk('s3-permanent')->exists($asset->s3_key_original))->toBeFalse();

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        $asset->refresh();

        // Should preserve original error message, not overwrite
        expect($asset->state)->toBe(MediaState::Failed)
            ->and($asset->error_message)->toBe('Original error: Processing timeout');
    });

    it('includes missing file paths in report', function () {
        Storage::fake('s3-permanent');

        $asset1 = MediaAsset::factory()->create(['state' => MediaState::Ready]);
        $asset2 = MediaAsset::factory()->create(['state' => MediaState::Ready]);

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['missing_files'])->toHaveCount(2)
            ->and($report['missing_files'])->toContain($asset1->s3_key_original)
            ->and($report['missing_files'])->toContain($asset2->s3_key_original);
    });
});

describe('report generation', function () {
    it('returns report with orphaned and missing files', function () {
        Storage::fake('s3-permanent');

        // Create orphaned S3 file
        $orphanKey = 'media/images/orphan.webp';
        Storage::disk('s3-permanent')->put($orphanKey, 'orphan');

        // Create asset without S3 file
        $missingAsset = MediaAsset::factory()->create(['state' => MediaState::Ready]);

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report)->toHaveKeys(['orphaned_files', 'missing_files', 'summary'])
            ->and($report['orphaned_files'])->toContain($orphanKey)
            ->and($report['missing_files'])->toContain($missingAsset->s3_key_original);
    });

    it('reports zero findings when everything is in sync', function () {
        Storage::fake('s3-permanent');

        // Create 3 valid assets with S3 files
        $assets = MediaAsset::factory()->count(3)->create();
        foreach ($assets as $asset) {
            Storage::disk('s3-permanent')->put($asset->s3_key_original, 'content');
        }

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['orphaned_files'])->toBeEmpty()
            ->and($report['missing_files'])->toBeEmpty()
            ->and($report['summary']['orphaned_count'])->toBe(0)
            ->and($report['summary']['missing_count'])->toBe(0)
            ->and($report['summary']['status'])->toBe('in_sync');
    });

    it('includes summary statistics in report', function () {
        Storage::fake('s3-permanent');

        // Create 2 orphaned files
        Storage::disk('s3-permanent')->put('media/images/orphan1.webp', 'orphan 1');
        Storage::disk('s3-permanent')->put('media/images/orphan2.webp', 'orphan 2');

        // Create 3 assets without S3 files
        MediaAsset::factory()->count(3)->create(['state' => MediaState::Ready]);

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['summary'])->toHaveKeys(['orphaned_count', 'missing_count', 'status'])
            ->and($report['summary']['orphaned_count'])->toBe(2)
            ->and($report['summary']['missing_count'])->toBe(3)
            ->and($report['summary']['status'])->toBe('issues_found');
    });

    it('includes timestamp in report', function () {
        Storage::fake('s3-permanent');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report)->toHaveKey('timestamp')
            ->and($report['timestamp'])->toBeString();
    });
});

describe('queue configuration', function () {
    it('runs on media queue', function () {
        Queue::fake();

        SyncOrphanedFilesJob::dispatch();

        Queue::assertPushedOn('media', SyncOrphanedFilesJob::class);
    });
});

describe('edge cases', function () {
    it('handles empty S3 bucket gracefully', function () {
        Storage::fake('s3-permanent');

        // Create DB records but no S3 files
        MediaAsset::factory()->count(3)->create();

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // Should detect missing files, no orphans
        expect($report['orphaned_files'])->toBeEmpty()
            ->and($report['missing_files'])->toHaveCount(3);
    });

    it('handles empty database gracefully', function () {
        Storage::fake('s3-permanent');

        // Create S3 files but no DB records
        Storage::disk('s3-permanent')->put('media/images/file1.webp', 'content 1');
        Storage::disk('s3-permanent')->put('media/images/file2.webp', 'content 2');
        Storage::disk('s3-permanent')->put('media/videos/file3.mp4', 'content 3');

        expect(MediaAsset::count())->toBe(0);

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // All S3 files should be detected as orphans
        expect($report['orphaned_files'])->toHaveCount(3)
            ->and($report['missing_files'])->toBeEmpty();
    });

    it('handles mixed scenario (some orphaned, some missing, some valid)', function () {
        Storage::fake('s3-permanent');

        // 1. Valid asset with S3 file
        $validAsset = MediaAsset::factory()->create();
        Storage::disk('s3-permanent')->put($validAsset->s3_key_original, 'valid');

        // 2. Orphaned S3 file
        $orphanKey = 'media/images/orphan.webp';
        Storage::disk('s3-permanent')->put($orphanKey, 'orphan');

        // 3. Asset without S3 file
        $missingAsset = MediaAsset::factory()->create(['state' => MediaState::Ready]);

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['orphaned_files'])->toHaveCount(1)
            ->and($report['orphaned_files'])->toContain($orphanKey)
            ->and($report['missing_files'])->toHaveCount(1)
            ->and($report['missing_files'])->toContain($missingAsset->s3_key_original);
    });

    it('processes files in all media folders (images, videos, svg)', function () {
        Storage::fake('s3-permanent');

        // Create orphans in each folder
        Storage::disk('s3-permanent')->put('media/images/orphan.webp', 'image');
        Storage::disk('s3-permanent')->put('media/videos/orphan.mp4', 'video');
        Storage::disk('s3-permanent')->put('media/svg/orphan.svg', 'svg');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['orphaned_files'])->toHaveCount(3);
    });

    it('handles files in subdirectories and variant paths', function () {
        Storage::fake('s3-permanent');

        // Create orphaned files in subdirectories
        Storage::disk('s3-permanent')->put('media/images/2025/12/orphan.webp', 'image in subdir');
        Storage::disk('s3-permanent')->put('media/images/variants/orphan-480w.webp', 'variant');
        Storage::disk('s3-permanent')->put('media/videos/archive/orphan.mp4', 'video in archive');

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['orphaned_files'])->toHaveCount(3)
            ->and($report['orphaned_files'])->toContain('media/images/2025/12/orphan.webp')
            ->and($report['orphaned_files'])->toContain('media/images/variants/orphan-480w.webp')
            ->and($report['orphaned_files'])->toContain('media/videos/archive/orphan.mp4');
    });

    it('handles soft-deleted assets correctly', function () {
        Storage::fake('s3-permanent');

        // Create soft-deleted asset
        $softDeleted = MediaAsset::factory()->create(['state' => MediaState::Ready]);
        Storage::disk('s3-permanent')->put($softDeleted->s3_key_original, 'content');
        $softDeleted->delete(); // Soft delete

        // S3 file still exists but asset is soft-deleted
        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // Should be detected as orphaned S3 file (no active DB record)
        expect($report['orphaned_files'])->toContain($softDeleted->s3_key_original);
    });

    it('handles assets with variants', function () {
        Storage::fake('s3-permanent');

        $asset = MediaAsset::factory()->withVariants()->create();

        // Create S3 file for original
        Storage::disk('s3-permanent')->put($asset->s3_key_original, 'original');

        // Create S3 files for some variants but not all
        $variants = $asset->variants;
        Storage::disk('s3-permanent')->put($variants[0]->s3_key, 'variant 1');
        // Don't create S3 file for variant 2

        // Verify variant records exist in database
        $variantCount = MediaVariant::where('media_asset_id', $asset->id)->count();
        expect($variantCount)->toBeGreaterThan(1);

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // Should detect missing variant file
        expect($report['missing_files'])->toContain($variants[1]->s3_key)
            ->and($report['missing_files'])->not->toContain($variants[0]->s3_key);
    });

    it('handles large number of files efficiently', function () {
        Storage::fake('s3-permanent');

        // Create 100 orphaned files
        for ($i = 0; $i < 100; $i++) {
            Storage::disk('s3-permanent')->put("media/images/orphan-{$i}.webp", "content {$i}");
        }

        // Create 50 valid assets
        $assets = MediaAsset::factory()->count(50)->create();
        foreach ($assets as $asset) {
            Storage::disk('s3-permanent')->put($asset->s3_key_original, 'valid');
        }

        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        expect($report['orphaned_files'])->toHaveCount(100)
            ->and($report['missing_files'])->toBeEmpty()
            ->and($report['summary']['orphaned_count'])->toBe(100);
    });

    it('does not mark uploading or processing assets as failed', function () {
        Storage::fake('s3-permanent');

        $uploading = MediaAsset::factory()->uploading()->create();
        $processing = MediaAsset::factory()->processing()->create();

        // Files don't exist in S3 yet (still being processed)
        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // Should not mark as failed (they're in transitional states)
        $uploading->refresh();
        $processing->refresh();

        expect($uploading->state)->toBe(MediaState::Uploading)
            ->and($processing->state)->toBe(MediaState::Processing)
            ->and($report['missing_files'])->toBeEmpty();
    });

    it('only checks ready state assets for missing files', function () {
        Storage::fake('s3-permanent');

        $ready = MediaAsset::factory()->create(['state' => MediaState::Ready]);
        $uploading = MediaAsset::factory()->uploading()->create();
        $processing = MediaAsset::factory()->processing()->create();
        $failed = MediaAsset::factory()->failed()->create();

        // No S3 files exist
        $job = new SyncOrphanedFilesJob;
        $report = $job->handle();

        // Only ready asset should be marked as failed
        $ready->refresh();
        expect($ready->state)->toBe(MediaState::Failed)
            ->and($report['missing_files'])->toHaveCount(1)
            ->and($report['missing_files'])->toContain($ready->s3_key_original);
    });
});
