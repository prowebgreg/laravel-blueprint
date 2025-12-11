<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

/**
 * Feature Tests for CleanupFailedMediaJob
 *
 * Tests automated cleanup of two categories of media assets:
 *
 * 1. Failed Assets Cleanup:
 * - Force deletes (permanent removal) failed assets older than configured retention period (24 hours default)
 * - Cleans up S3 files (original + variants) for deleted failed assets
 * - Preserves recent failed assets (newer than retention threshold)
 * - Preserves assets in other states (Ready, Processing, Uploading)
 *
 * 2. Soft-Deleted Assets Purging:
 * - Permanently purges soft-deleted assets older than configured retention period (30 days default)
 * - Cleans up S3 files (original + variants) for purged soft-deleted assets
 * - Preserves recently soft-deleted assets within recovery window
 * - Respects configured retention periods from config
 *
 * Additional tests:
 * - Queue configuration (media queue)
 * - Edge cases: empty result sets, multiple failures, assets with/without variants
 * - Both cleanup phases in single job execution
 *
 * Note: The job uses forceDelete() for permanent removal since assets outside
 * their retention windows have no recovery value.
 *
 * @see /specs/003-media-engine/plan.md
 * @see /specs/003-media-engine/data-model.md
 */

use App\Enums\MediaState;
use App\Jobs\Media\CleanupFailedMediaJob;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('24-hour threshold cleanup', function () {
    it('force deletes failed assets older than 24 hours', function () {
        Storage::fake('s3-permanent');

        // Create failed asset older than 24 hours
        $oldFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subHours(25),
        ]);

        // Create S3 file for the old failed asset
        Storage::disk('s3-permanent')->put($oldFailed->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should be permanently deleted (not soft deleted)
        expect(MediaAsset::find($oldFailed->id))->toBeNull()
            ->and(MediaAsset::withTrashed()->find($oldFailed->id))->toBeNull()
            ->and(Storage::disk('s3-permanent')->exists($oldFailed->s3_key_original))->toBeFalse();
    });

    it('deletes multiple failed assets older than threshold', function () {
        Storage::fake('s3-permanent');

        // Create 3 old failed assets
        $oldFailed1 = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(25)]);
        $oldFailed2 = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(48)]);
        $oldFailed3 = MediaAsset::factory()->failed()->create(['created_at' => now()->subDays(7)]);

        // Create S3 files
        Storage::disk('s3-permanent')->put($oldFailed1->s3_key_original, 'content 1');
        Storage::disk('s3-permanent')->put($oldFailed2->s3_key_original, 'content 2');
        Storage::disk('s3-permanent')->put($oldFailed3->s3_key_original, 'content 3');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // All old failed assets should be deleted
        expect(MediaAsset::find($oldFailed1->id))->toBeNull()
            ->and(MediaAsset::find($oldFailed2->id))->toBeNull()
            ->and(MediaAsset::find($oldFailed3->id))->toBeNull();
    });

    it('uses configured retention hours from config', function () {
        Storage::fake('s3-permanent');

        // Override config to 48 hours
        config(['media.failed_retention_hours' => 48]);

        // Create failed asset that's 25 hours old (would be deleted with 24h config, but not with 48h)
        $recentFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subHours(25),
        ]);

        // Create S3 file
        Storage::disk('s3-permanent')->put($recentFailed->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist (25 hours < 48 hours)
        expect(MediaAsset::find($recentFailed->id))->not->toBeNull();

        // Now create asset older than 48 hours
        $oldFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subHours(49),
        ]);
        Storage::disk('s3-permanent')->put($oldFailed->s3_key_original, 'old content');

        $job->handle();

        // Old asset should be deleted
        expect(MediaAsset::find($oldFailed->id))->toBeNull();
    });

    it('defaults to 24 hours when config is not set', function () {
        Storage::fake('s3-permanent');

        // Unset config to test default behavior
        config(['media.failed_retention_hours' => null]);

        // Create failed asset older than 24 hours (default)
        $oldFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subHours(25),
        ]);

        // Create S3 file
        Storage::disk('s3-permanent')->put($oldFailed->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should be deleted using 24-hour default
        expect(MediaAsset::find($oldFailed->id))->toBeNull();
    });
});

describe('recent failed assets preservation', function () {
    it('does not delete failed assets newer than 24 hours', function () {
        Storage::fake('s3-permanent');

        // Create failed asset that's only 23 hours old
        $recentFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subHours(23),
        ]);

        // Create S3 file
        Storage::disk('s3-permanent')->put($recentFailed->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist
        expect(MediaAsset::find($recentFailed->id))->not->toBeNull()
            ->and(Storage::disk('s3-permanent')->exists($recentFailed->s3_key_original))->toBeTrue();
    });

    it('does not delete failed assets created exactly at threshold', function () {
        Storage::fake('s3-permanent');

        // Create failed asset exactly 24 hours old
        $thresholdFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subHours(24),
        ]);

        Storage::disk('s3-permanent')->put($thresholdFailed->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist (not older than, just equal to)
        expect(MediaAsset::find($thresholdFailed->id))->not->toBeNull();
    });

    it('preserves very recent failed assets', function () {
        Storage::fake('s3-permanent');

        // Create failed assets at various recent times
        $justFailed = MediaAsset::factory()->failed()->create(['created_at' => now()->subMinutes(5)]);
        $oneHourOld = MediaAsset::factory()->failed()->create(['created_at' => now()->subHour()]);
        $twelveHoursOld = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(12)]);

        // Create S3 files
        Storage::disk('s3-permanent')->put($justFailed->s3_key_original, 'content 1');
        Storage::disk('s3-permanent')->put($oneHourOld->s3_key_original, 'content 2');
        Storage::disk('s3-permanent')->put($twelveHoursOld->s3_key_original, 'content 3');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // All recent failed assets should still exist
        expect(MediaAsset::find($justFailed->id))->not->toBeNull()
            ->and(MediaAsset::find($oneHourOld->id))->not->toBeNull()
            ->and(MediaAsset::find($twelveHoursOld->id))->not->toBeNull();
    });
});

describe('state-based filtering', function () {
    it('does not delete ready assets', function () {
        Storage::fake('s3-permanent');

        // Create ready asset older than 24 hours
        $oldReady = MediaAsset::factory()->create([
            'state' => MediaState::Ready,
            'created_at' => now()->subDays(30),
        ]);

        Storage::disk('s3-permanent')->put($oldReady->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist
        expect(MediaAsset::find($oldReady->id))->not->toBeNull();
    });

    it('does not delete processing assets', function () {
        Storage::fake('s3-permanent');

        // Create processing asset older than 24 hours
        $oldProcessing = MediaAsset::factory()->processing()->create([
            'created_at' => now()->subDays(2),
        ]);

        Storage::disk('s3-permanent')->put($oldProcessing->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist
        expect(MediaAsset::find($oldProcessing->id))->not->toBeNull();
    });

    it('does not delete uploading assets', function () {
        Storage::fake('s3-permanent');

        // Create uploading asset older than 24 hours
        $oldUploading = MediaAsset::factory()->uploading()->create([
            'created_at' => now()->subDays(2),
        ]);

        Storage::disk('s3-permanent')->put($oldUploading->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist
        expect(MediaAsset::find($oldUploading->id))->not->toBeNull();
    });

    it('only deletes failed state assets', function () {
        Storage::fake('s3-permanent');

        // Create multiple old assets in different states
        $oldFailed = MediaAsset::factory()->failed()->create(['created_at' => now()->subDays(2)]);
        $oldReady = MediaAsset::factory()->create(['state' => MediaState::Ready, 'created_at' => now()->subDays(2)]);
        $oldProcessing = MediaAsset::factory()->processing()->create(['created_at' => now()->subDays(2)]);
        $oldUploading = MediaAsset::factory()->uploading()->create(['created_at' => now()->subDays(2)]);

        // Create S3 files
        foreach ([$oldFailed, $oldReady, $oldProcessing, $oldUploading] as $asset) {
            Storage::disk('s3-permanent')->put($asset->s3_key_original, 'content');
        }

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Only failed asset should be deleted
        expect(MediaAsset::find($oldFailed->id))->toBeNull()
            ->and(MediaAsset::find($oldReady->id))->not->toBeNull()
            ->and(MediaAsset::find($oldProcessing->id))->not->toBeNull()
            ->and(MediaAsset::find($oldUploading->id))->not->toBeNull();
    });
});

describe('S3 file cleanup', function () {
    it('deletes S3 original file when deleting failed asset', function () {
        Storage::fake('s3-permanent');

        $oldFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subHours(25),
        ]);

        // Create S3 file
        Storage::disk('s3-permanent')->put($oldFailed->s3_key_original, 'test content');
        expect(Storage::disk('s3-permanent')->exists($oldFailed->s3_key_original))->toBeTrue();

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // S3 file should be deleted
        expect(Storage::disk('s3-permanent')->exists($oldFailed->s3_key_original))->toBeFalse();
    });

    it('deletes S3 variant files when deleting failed asset with variants', function () {
        Storage::fake('s3-permanent');

        $oldFailed = MediaAsset::factory()->failed()->withVariants()->create([
            'created_at' => now()->subHours(25),
        ]);

        // Create S3 files for original and variants
        Storage::disk('s3-permanent')->put($oldFailed->s3_key_original, 'original content');

        $variants = $oldFailed->variants;
        foreach ($variants as $variant) {
            Storage::disk('s3-permanent')->put($variant->s3_key, "variant {$variant->width}");
        }

        // Verify files exist before cleanup
        expect(Storage::disk('s3-permanent')->exists($oldFailed->s3_key_original))->toBeTrue();
        foreach ($variants as $variant) {
            expect(Storage::disk('s3-permanent')->exists($variant->s3_key))->toBeTrue();
        }

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // All S3 files should be deleted
        expect(Storage::disk('s3-permanent')->exists($oldFailed->s3_key_original))->toBeFalse();
        foreach ($variants as $variant) {
            expect(Storage::disk('s3-permanent')->exists($variant->s3_key))->toBeFalse();
        }
    });

    it('handles missing S3 files gracefully', function () {
        Storage::fake('s3-permanent');

        $oldFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subHours(25),
        ]);

        // Don't create S3 file (simulates already-deleted or missing file)
        expect(Storage::disk('s3-permanent')->exists($oldFailed->s3_key_original))->toBeFalse();

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Should complete without error, asset should be deleted
        expect(MediaAsset::find($oldFailed->id))->toBeNull();
    });

    it('deletes multiple S3 files for multiple failed assets', function () {
        Storage::fake('s3-permanent');

        $oldFailed1 = MediaAsset::factory()->failed()->withVariants()->create(['created_at' => now()->subDays(2)]);
        $oldFailed2 = MediaAsset::factory()->failed()->withVariants()->create(['created_at' => now()->subDays(3)]);

        // Create S3 files for both assets and their variants
        Storage::disk('s3-permanent')->put($oldFailed1->s3_key_original, 'content 1');
        Storage::disk('s3-permanent')->put($oldFailed2->s3_key_original, 'content 2');

        foreach ($oldFailed1->variants as $variant) {
            Storage::disk('s3-permanent')->put($variant->s3_key, 'variant 1');
        }
        foreach ($oldFailed2->variants as $variant) {
            Storage::disk('s3-permanent')->put($variant->s3_key, 'variant 2');
        }

        $totalFiles = 2 + $oldFailed1->variants->count() + $oldFailed2->variants->count();
        expect(Storage::disk('s3-permanent')->allFiles())->toHaveCount($totalFiles);

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // All files should be deleted
        expect(Storage::disk('s3-permanent')->allFiles())->toBeEmpty();
    });
});

describe('variant cleanup', function () {
    it('deletes variant database records when deleting failed asset', function () {
        Storage::fake('s3-permanent');

        $oldFailed = MediaAsset::factory()->failed()->withVariants()->create([
            'created_at' => now()->subHours(25),
        ]);

        $variantCount = $oldFailed->variants()->count();
        expect($variantCount)->toBeGreaterThan(0);

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Variants should be deleted (cascade delete or explicit cleanup)
        expect(MediaVariant::where('media_asset_id', $oldFailed->id)->count())->toBe(0);
    });

    it('handles failed assets without variants', function () {
        Storage::fake('s3-permanent');

        // Video and SVG assets don't have variants
        $oldFailedVideo = MediaAsset::factory()->video()->failed()->create(['created_at' => now()->subDays(2)]);
        $oldFailedSvg = MediaAsset::factory()->svg()->failed()->create(['created_at' => now()->subDays(2)]);

        Storage::disk('s3-permanent')->put($oldFailedVideo->s3_key_original, 'video content');
        Storage::disk('s3-permanent')->put($oldFailedSvg->s3_key_original, 'svg content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Assets should be deleted
        expect(MediaAsset::find($oldFailedVideo->id))->toBeNull()
            ->and(MediaAsset::find($oldFailedSvg->id))->toBeNull()
            ->and(Storage::disk('s3-permanent')->exists($oldFailedVideo->s3_key_original))->toBeFalse()
            ->and(Storage::disk('s3-permanent')->exists($oldFailedSvg->s3_key_original))->toBeFalse();
    });
});

describe('queue configuration', function () {
    it('runs on media queue', function () {
        Queue::fake();

        CleanupFailedMediaJob::dispatch();

        Queue::assertPushedOn('media', CleanupFailedMediaJob::class);
    });
});

describe('soft-deleted asset purging', function () {
    it('purges soft-deleted assets older than 30 days', function () {
        Storage::fake('s3-permanent');

        // Create soft-deleted asset older than 30 days
        $oldSoftDeleted = MediaAsset::factory()->create([
            'created_at' => now()->subDays(60),
        ]);
        $oldSoftDeleted->delete(); // Soft delete
        $oldSoftDeleted->deleted_at = now()->subDays(31);
        $oldSoftDeleted->save();

        // Create S3 file for the old soft-deleted asset
        Storage::disk('s3-permanent')->put($oldSoftDeleted->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should be permanently deleted (not in trash anymore)
        expect(MediaAsset::find($oldSoftDeleted->id))->toBeNull()
            ->and(MediaAsset::withTrashed()->find($oldSoftDeleted->id))->toBeNull()
            ->and(Storage::disk('s3-permanent')->exists($oldSoftDeleted->s3_key_original))->toBeFalse();
    });

    it('preserves soft-deleted assets newer than 30 days', function () {
        Storage::fake('s3-permanent');

        // Create soft-deleted asset that's only 29 days old
        $recentSoftDeleted = MediaAsset::factory()->create([
            'created_at' => now()->subDays(40),
        ]);
        $recentSoftDeleted->delete(); // Soft delete
        $recentSoftDeleted->deleted_at = now()->subDays(29);
        $recentSoftDeleted->save();

        // Create S3 file
        Storage::disk('s3-permanent')->put($recentSoftDeleted->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist in trash
        expect(MediaAsset::withTrashed()->find($recentSoftDeleted->id))->not->toBeNull()
            ->and(Storage::disk('s3-permanent')->exists($recentSoftDeleted->s3_key_original))->toBeTrue();
    });

    it('uses configured retention days from config', function () {
        Storage::fake('s3-permanent');

        // Override config to 45 days
        config(['media.soft_delete_retention_days' => 45]);

        // Create soft-deleted asset that's 31 days old (would be purged with 30d config, but not with 45d)
        $recentSoftDeleted = MediaAsset::factory()->create();
        $recentSoftDeleted->delete();
        $recentSoftDeleted->deleted_at = now()->subDays(31);
        $recentSoftDeleted->save();

        // Create S3 file
        Storage::disk('s3-permanent')->put($recentSoftDeleted->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist (31 days < 45 days)
        expect(MediaAsset::withTrashed()->find($recentSoftDeleted->id))->not->toBeNull();

        // Now create asset older than 45 days
        $oldSoftDeleted = MediaAsset::factory()->create();
        $oldSoftDeleted->delete();
        $oldSoftDeleted->deleted_at = now()->subDays(46);
        $oldSoftDeleted->save();
        Storage::disk('s3-permanent')->put($oldSoftDeleted->s3_key_original, 'old content');

        $job->handle();

        // Old asset should be purged
        expect(MediaAsset::withTrashed()->find($oldSoftDeleted->id))->toBeNull();
    });

    it('defaults to 30 days when config is not set', function () {
        Storage::fake('s3-permanent');

        // Unset config to test default behavior
        config(['media.soft_delete_retention_days' => null]);

        // Create soft-deleted asset older than 30 days (default)
        $oldSoftDeleted = MediaAsset::factory()->create();
        $oldSoftDeleted->delete();
        $oldSoftDeleted->deleted_at = now()->subDays(31);
        $oldSoftDeleted->save();

        // Create S3 file
        Storage::disk('s3-permanent')->put($oldSoftDeleted->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should be purged using 30-day default
        expect(MediaAsset::withTrashed()->find($oldSoftDeleted->id))->toBeNull();
    });

    it('deletes S3 files for purged soft-deleted assets', function () {
        Storage::fake('s3-permanent');

        $oldSoftDeleted = MediaAsset::factory()->create();
        $oldSoftDeleted->delete();
        $oldSoftDeleted->deleted_at = now()->subDays(31);
        $oldSoftDeleted->save();

        // Create S3 file
        Storage::disk('s3-permanent')->put($oldSoftDeleted->s3_key_original, 'test content');
        expect(Storage::disk('s3-permanent')->exists($oldSoftDeleted->s3_key_original))->toBeTrue();

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // S3 file should be deleted
        expect(Storage::disk('s3-permanent')->exists($oldSoftDeleted->s3_key_original))->toBeFalse();
    });

    it('deletes S3 variant files for purged soft-deleted assets with variants', function () {
        Storage::fake('s3-permanent');

        $oldSoftDeleted = MediaAsset::factory()->withVariants()->create();
        $oldSoftDeleted->delete();
        $oldSoftDeleted->deleted_at = now()->subDays(31);
        $oldSoftDeleted->save();

        // Create S3 files for original and variants
        Storage::disk('s3-permanent')->put($oldSoftDeleted->s3_key_original, 'original content');

        $variants = $oldSoftDeleted->variants;
        foreach ($variants as $variant) {
            Storage::disk('s3-permanent')->put($variant->s3_key, "variant {$variant->width}");
        }

        // Verify files exist before cleanup
        expect(Storage::disk('s3-permanent')->exists($oldSoftDeleted->s3_key_original))->toBeTrue();
        foreach ($variants as $variant) {
            expect(Storage::disk('s3-permanent')->exists($variant->s3_key))->toBeTrue();
        }

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // All S3 files should be deleted
        expect(Storage::disk('s3-permanent')->exists($oldSoftDeleted->s3_key_original))->toBeFalse();
        foreach ($variants as $variant) {
            expect(Storage::disk('s3-permanent')->exists($variant->s3_key))->toBeFalse();
        }
    });

    it('does not purge assets that are not soft-deleted', function () {
        Storage::fake('s3-permanent');

        // Create ready asset (not soft-deleted)
        $activeAsset = MediaAsset::factory()->create([
            'state' => MediaState::Ready,
            'created_at' => now()->subDays(60),
        ]);

        Storage::disk('s3-permanent')->put($activeAsset->s3_key_original, 'test content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset should still exist
        expect(MediaAsset::find($activeAsset->id))->not->toBeNull();
    });

    it('purges multiple soft-deleted assets older than threshold', function () {
        Storage::fake('s3-permanent');

        // Create 3 old soft-deleted assets
        $oldSoftDeleted1 = MediaAsset::factory()->create();
        $oldSoftDeleted1->delete();
        $oldSoftDeleted1->deleted_at = now()->subDays(31);
        $oldSoftDeleted1->save();

        $oldSoftDeleted2 = MediaAsset::factory()->create();
        $oldSoftDeleted2->delete();
        $oldSoftDeleted2->deleted_at = now()->subDays(60);
        $oldSoftDeleted2->save();

        $oldSoftDeleted3 = MediaAsset::factory()->create();
        $oldSoftDeleted3->delete();
        $oldSoftDeleted3->deleted_at = now()->subDays(90);
        $oldSoftDeleted3->save();

        // Create S3 files
        Storage::disk('s3-permanent')->put($oldSoftDeleted1->s3_key_original, 'content 1');
        Storage::disk('s3-permanent')->put($oldSoftDeleted2->s3_key_original, 'content 2');
        Storage::disk('s3-permanent')->put($oldSoftDeleted3->s3_key_original, 'content 3');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // All old soft-deleted assets should be purged
        expect(MediaAsset::withTrashed()->find($oldSoftDeleted1->id))->toBeNull()
            ->and(MediaAsset::withTrashed()->find($oldSoftDeleted2->id))->toBeNull()
            ->and(MediaAsset::withTrashed()->find($oldSoftDeleted3->id))->toBeNull();
    });

    it('handles mixed scenario with old and recent soft-deleted assets', function () {
        Storage::fake('s3-permanent');

        $oldSoftDeleted1 = MediaAsset::factory()->create();
        $oldSoftDeleted1->delete();
        $oldSoftDeleted1->deleted_at = now()->subDays(60);
        $oldSoftDeleted1->save();

        $oldSoftDeleted2 = MediaAsset::factory()->create();
        $oldSoftDeleted2->delete();
        $oldSoftDeleted2->deleted_at = now()->subDays(31);
        $oldSoftDeleted2->save();

        $recentSoftDeleted1 = MediaAsset::factory()->create();
        $recentSoftDeleted1->delete();
        $recentSoftDeleted1->deleted_at = now()->subDays(29);
        $recentSoftDeleted1->save();

        $recentSoftDeleted2 = MediaAsset::factory()->create();
        $recentSoftDeleted2->delete();
        $recentSoftDeleted2->deleted_at = now()->subDays(5);
        $recentSoftDeleted2->save();

        foreach ([$oldSoftDeleted1, $oldSoftDeleted2, $recentSoftDeleted1, $recentSoftDeleted2] as $asset) {
            Storage::disk('s3-permanent')->put($asset->s3_key_original, 'content');
        }

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Old soft-deleted assets purged, recent ones preserved
        expect(MediaAsset::withTrashed()->find($oldSoftDeleted1->id))->toBeNull()
            ->and(MediaAsset::withTrashed()->find($oldSoftDeleted2->id))->toBeNull()
            ->and(MediaAsset::withTrashed()->find($recentSoftDeleted1->id))->not->toBeNull()
            ->and(MediaAsset::withTrashed()->find($recentSoftDeleted2->id))->not->toBeNull();
    });

    it('handles both failed and soft-deleted asset cleanup in single job execution', function () {
        Storage::fake('s3-permanent');

        // Create old failed asset
        $oldFailed = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(25)]);
        Storage::disk('s3-permanent')->put($oldFailed->s3_key_original, 'failed content');

        // Create old soft-deleted asset
        $oldSoftDeleted = MediaAsset::factory()->create();
        $oldSoftDeleted->delete();
        $oldSoftDeleted->deleted_at = now()->subDays(31);
        $oldSoftDeleted->save();
        Storage::disk('s3-permanent')->put($oldSoftDeleted->s3_key_original, 'soft-deleted content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Both should be permanently deleted
        expect(MediaAsset::withTrashed()->find($oldFailed->id))->toBeNull()
            ->and(MediaAsset::withTrashed()->find($oldSoftDeleted->id))->toBeNull()
            ->and(Storage::disk('s3-permanent')->exists($oldFailed->s3_key_original))->toBeFalse()
            ->and(Storage::disk('s3-permanent')->exists($oldSoftDeleted->s3_key_original))->toBeFalse();
    });
});

describe('edge cases', function () {
    it('handles empty result set gracefully', function () {
        Storage::fake('s3-permanent');

        // No failed assets exist
        expect(MediaAsset::where('state', MediaState::Failed)->count())->toBe(0);

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Should complete without error
        expect(true)->toBeTrue();
    });

    it('handles no old failed assets', function () {
        Storage::fake('s3-permanent');

        // Create only recent failed assets
        $recentFailed1 = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(1)]);
        $recentFailed2 = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(12)]);

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // All assets should still exist
        expect(MediaAsset::find($recentFailed1->id))->not->toBeNull()
            ->and(MediaAsset::find($recentFailed2->id))->not->toBeNull();
    });

    it('handles mixed scenario with old and recent failed assets', function () {
        Storage::fake('s3-permanent');

        $oldFailed1 = MediaAsset::factory()->failed()->create(['created_at' => now()->subDays(2)]);
        $oldFailed2 = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(30)]);
        $recentFailed1 = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(23)]);
        $recentFailed2 = MediaAsset::factory()->failed()->create(['created_at' => now()->subHours(5)]);

        foreach ([$oldFailed1, $oldFailed2, $recentFailed1, $recentFailed2] as $asset) {
            Storage::disk('s3-permanent')->put($asset->s3_key_original, 'content');
        }

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Old failed assets deleted, recent ones preserved
        expect(MediaAsset::find($oldFailed1->id))->toBeNull()
            ->and(MediaAsset::find($oldFailed2->id))->toBeNull()
            ->and(MediaAsset::find($recentFailed1->id))->not->toBeNull()
            ->and(MediaAsset::find($recentFailed2->id))->not->toBeNull();
    });

    it('handles large batch of failed assets', function () {
        Storage::fake('s3-permanent');

        // Create 50 old failed assets
        $oldFailedAssets = MediaAsset::factory()->failed()->count(50)->create([
            'created_at' => now()->subDays(5),
        ]);

        foreach ($oldFailedAssets as $asset) {
            Storage::disk('s3-permanent')->put($asset->s3_key_original, 'content');
        }

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // All should be deleted
        expect(MediaAsset::where('state', MediaState::Failed)
            ->where('created_at', '<=', now()->subHours(24))
            ->count())->toBe(0);
    });

    it('preserves error_message field before deletion', function () {
        Storage::fake('s3-permanent');

        $oldFailed = MediaAsset::factory()->failed()->create([
            'created_at' => now()->subDays(2),
            'error_message' => 'Original error: File processing timeout',
        ]);

        // Capture error message before deletion
        $errorMessage = $oldFailed->error_message;
        expect($errorMessage)->toBe('Original error: File processing timeout');

        Storage::disk('s3-permanent')->put($oldFailed->s3_key_original, 'content');

        $job = new CleanupFailedMediaJob;
        $job->handle();

        // Asset deleted but we captured the error message
        expect(MediaAsset::find($oldFailed->id))->toBeNull();
    });
});
