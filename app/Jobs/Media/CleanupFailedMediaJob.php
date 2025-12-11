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
 * Automatically cleans up failed media assets that are older than the configured
 * retention period (default 24 hours). This prevents the database and S3 from
 * accumulating orphaned failed uploads.
 *
 * Cleanup Actions:
 * - Permanently deletes (forceDelete) failed assets older than retention threshold
 * - Deletes S3 original file
 * - Deletes S3 variant files (if any exist)
 * - Handles missing S3 files gracefully
 *
 * Assets Affected:
 * - Only MediaState::Failed assets
 * - Only assets created_at > configured retention hours (default 24)
 * - Assets without variants (video, svg) are handled correctly
 *
 * Retention Logic:
 * - Recent failed assets (< 24 hours) are preserved for debugging
 * - Old failed assets (>= 24 hours) have no recovery value and are purged
 * - Retention period is configurable via config('media.failed_retention_hours')
 *
 * Note: Uses forceDelete() for permanent removal since failed assets older than
 * the retention window have no recovery value.
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
}
