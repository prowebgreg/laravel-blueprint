<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Actions\Media\DeleteFromS3Action;
use App\Enums\MediaState;
use App\Models\MediaAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Cleanup Failed Media Job
 *
 * Automatically cleans up two categories of media assets:
 *
 * 1. Failed Assets (default 24 hours):
 *    - Permanently deletes failed assets older than configured retention period
 *    - Recent failed assets preserved for debugging
 *
 * 2. Soft-Deleted Assets (default 30 days):
 *    - Permanently purges soft-deleted assets older than configured retention period
 *    - Provides recovery window for accidental deletions
 *
 * Cleanup Actions:
 * - Permanently deletes (forceDelete) eligible assets
 * - Deletes S3 original file
 * - Deletes S3 variant files (if any exist)
 * - Handles missing S3 files gracefully
 *
 * Failed Assets Cleanup:
 * - Only MediaState::Failed assets
 * - Only assets created_at > configured retention hours (default 24)
 * - Retention period: config('media.failed_retention_hours')
 *
 * Soft-Deleted Assets Cleanup:
 * - Only soft-deleted assets (deleted_at is not null)
 * - Only assets deleted_at > configured retention days (default 30)
 * - Retention period: config('media.soft_delete_retention_days')
 * - Provides 30-day recovery window before permanent deletion
 *
 * Note: Uses forceDelete() for permanent removal since assets outside
 * retention windows have no recovery value.
 */
class CleanupFailedMediaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Set queue from config
        $this->onQueue(config('media.processing_queue', 'media'));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Resolve dependencies from container
        $deleteFromS3Action = app(DeleteFromS3Action::class);

        // Phase 1: Cleanup failed assets
        $this->cleanupFailedAssets($deleteFromS3Action);

        // Phase 2: Purge soft-deleted assets
        $this->purgeSoftDeletedAssets($deleteFromS3Action);
    }

    /**
     * Cleanup failed assets older than configured retention period.
     */
    private function cleanupFailedAssets(DeleteFromS3Action $deleteFromS3Action): void
    {
        // Get retention hours from config (default 24 hours)
        $retentionHours = config('media.failed_retention_hours', 24);

        // Calculate threshold timestamp
        $threshold = now()->subHours($retentionHours);

        Log::info('Starting cleanup of failed media assets', [
            'retention_hours' => $retentionHours,
            'threshold' => $threshold->toDateTimeString(),
        ]);

        // Query failed assets older than threshold
        // Eager load variants to avoid N+1 queries when cleaning up S3 files
        $oldFailedAssets = MediaAsset::query()
            ->where('state', MediaState::Failed)
            ->where('created_at', '<', $threshold)
            ->with('variants')
            ->get();

        if ($oldFailedAssets->isEmpty()) {
            Log::info('No old failed assets to cleanup');

            return;
        }

        $deletedCount = 0;
        $s3FilesDeleted = 0;

        foreach ($oldFailedAssets as $asset) {
            try {
                // Collect S3 keys to delete (original + all variants)
                $s3Keys = [$asset->s3_key_original];

                // Add variant S3 keys if they exist
                foreach ($asset->variants as $variant) {
                    $s3Keys[] = $variant->s3_key;
                }

                // Delete S3 files (handles missing files gracefully)
                $result = $deleteFromS3Action->execute($s3Keys);
                $s3FilesDeleted += $result->deletedCount;

                if (! $result->success) {
                    Log::warning('Some S3 files failed to delete during cleanup', [
                        'asset_id' => $asset->id,
                        'failed_keys' => $result->failedKeys,
                        'error' => $result->errorMessage,
                    ]);
                }

                // Permanently delete the asset (variants cascade via DB foreign key)
                $asset->forceDelete();
                $deletedCount++;

                Log::debug('Deleted failed asset', [
                    'asset_id' => $asset->id,
                    'filename' => $asset->filename,
                    's3_files_deleted' => \count($s3Keys),
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to cleanup asset', [
                    'asset_id' => $asset->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with next asset instead of failing entire job
            }
        }

        Log::info('Completed cleanup of failed media assets', [
            'assets_deleted' => $deletedCount,
            's3_files_deleted' => $s3FilesDeleted,
        ]);
    }

    /**
     * Purge soft-deleted assets older than configured retention period.
     */
    private function purgeSoftDeletedAssets(DeleteFromS3Action $deleteFromS3Action): void
    {
        // Get retention days from config (default 30 days)
        $retentionDays = config('media.soft_delete_retention_days', 30);

        // Calculate threshold timestamp
        $threshold = now()->subDays($retentionDays);

        Log::info('Starting purge of soft-deleted media assets', [
            'retention_days' => $retentionDays,
            'threshold' => $threshold->toDateTimeString(),
        ]);

        // Query soft-deleted assets older than threshold
        // Eager load variants to avoid N+1 queries when cleaning up S3 files
        $oldSoftDeletedAssets = MediaAsset::onlyTrashed()
            ->where('deleted_at', '<', $threshold)
            ->with('variants')
            ->get();

        if ($oldSoftDeletedAssets->isEmpty()) {
            Log::info('No old soft-deleted assets to purge');

            return;
        }

        $purgedCount = 0;
        $s3FilesDeleted = 0;

        foreach ($oldSoftDeletedAssets as $asset) {
            try {
                // Collect S3 keys to delete (original + all variants)
                $s3Keys = [$asset->s3_key_original];

                // Add variant S3 keys if they exist
                foreach ($asset->variants as $variant) {
                    $s3Keys[] = $variant->s3_key;
                }

                // Delete S3 files (handles missing files gracefully)
                $result = $deleteFromS3Action->execute($s3Keys);
                $s3FilesDeleted += $result->deletedCount;

                if (! $result->success) {
                    Log::warning('Some S3 files failed to delete during purge', [
                        'asset_id' => $asset->id,
                        'failed_keys' => $result->failedKeys,
                        'error' => $result->errorMessage,
                    ]);
                }

                // Permanently delete the asset (variants cascade via DB foreign key)
                $asset->forceDelete();
                $purgedCount++;

                Log::debug('Purged soft-deleted asset', [
                    'asset_id' => $asset->id,
                    'filename' => $asset->filename,
                    'deleted_at' => $asset->deleted_at->toDateTimeString(),
                    's3_files_deleted' => \count($s3Keys),
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to purge soft-deleted asset', [
                    'asset_id' => $asset->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with next asset instead of failing entire job
            }
        }

        Log::info('Completed purge of soft-deleted media assets', [
            'assets_purged' => $purgedCount,
            's3_files_deleted' => $s3FilesDeleted,
        ]);
    }
}
