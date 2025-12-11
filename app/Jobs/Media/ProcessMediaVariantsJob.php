<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Actions\Media\GenerateVariantsAction;
use App\Actions\Media\UploadToS3Action;
use App\Enums\MediaState;
use App\Enums\MediaType;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Process Media Variants Job
 *
 * Asynchronously generates responsive image variants for uploaded media assets.
 * Downloads original from S3, generates WebP variants at multiple widths,
 * uploads to S3, and creates MediaVariant records.
 *
 * State Transitions:
 * - Processing → Ready (success)
 * - Processing → Failed (failure with rollback)
 *
 * Edge Cases:
 * - Non-image assets (video, svg) are marked Ready immediately
 * - Assets with null dimensions are marked Ready immediately
 */
class ProcessMediaVariantsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Job timeout in seconds.
     */
    public int $timeout;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public MediaAsset $asset
    ) {
        // Set queue from config
        $this->onQueue(config('media.processing_queue', 'media'));

        // Set timeout from config
        $this->timeout = config('media.processing_timeout', 180);
    }

    /**
     * Execute the job.
     */
    public function handle(
        GenerateVariantsAction $generateVariantsAction,
        UploadToS3Action $uploadToS3Action
    ): void {
        $startTime = microtime(true);

        Log::info('Starting variant generation', [
            'asset_id' => $this->asset->id,
            'media_type' => $this->asset->media_type->value,
        ]);

        // Skip processing for non-image assets
        if ($this->asset->media_type !== MediaType::Image) {
            $this->markAsReady('Non-image asset, no variants needed');

            return;
        }

        // Skip processing if dimensions are null
        if ($this->asset->dimensions === null) {
            $this->markAsReady('Asset has null dimensions, no variants needed');

            return;
        }

        // Validate asset is in Processing state
        if ($this->asset->state !== MediaState::Processing) {
            Log::warning('Asset not in Processing state, skipping', [
                'asset_id' => $this->asset->id,
                'current_state' => $this->asset->state->value,
            ]);

            return;
        }

        $tempDir = null;
        $createdVariantIds = [];

        try {
            // Create temporary directory for processing
            $tempDir = sys_get_temp_dir().'/media-variants-'.$this->asset->id;
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Download original from S3
            $originalPath = $tempDir.'/original';
            $this->downloadFromS3($this->asset->s3_key_original, $originalPath);

            // Extract base filename (without extension)
            $baseName = pathinfo($this->asset->filename, PATHINFO_FILENAME);

            // Generate variants
            $variants = $generateVariantsAction->execute($originalPath, $tempDir, $baseName);

            Log::info('Generated variants', [
                'asset_id' => $this->asset->id,
                'variant_count' => count($variants),
            ]);

            // Upload each variant to S3 and create database records
            foreach ($variants as $variantData) {
                $variantPath = $variantData['path'];
                $variantFilename = basename($variantPath);

                // Upload to S3 (UploadToS3Action handles retries)
                $uploadResult = $uploadToS3Action->execute(
                    file: $variantPath,
                    sanitizedFilename: pathinfo($variantFilename, PATHINFO_FILENAME),
                    folder: $this->asset->folder
                );

                // Create MediaVariant record
                $variant = MediaVariant::create([
                    'media_asset_id' => $this->asset->id,
                    'width' => $variantData['width'],
                    'height' => $variantData['height'],
                    'format' => 'webp',
                    'file_size' => $variantData['file_size'],
                    's3_key' => $uploadResult->s3Key,
                    'cloudfront_url' => $uploadResult->cloudfrontUrl,
                ]);

                $createdVariantIds[] = $variant->id;

                Log::debug('Created variant', [
                    'asset_id' => $this->asset->id,
                    'variant_id' => $variant->id,
                    'width' => $variantData['width'],
                ]);
            }

            // Mark asset as Ready
            $this->asset->update([
                'state' => MediaState::Ready,
                'error_message' => null,
            ]);

            // Calculate total processing duration
            $duration = round(microtime(true) - $startTime, 2);
            $slowThreshold = config('media.slow_operation_threshold', 30);

            // Log warning if operation took longer than threshold
            if ($duration > $slowThreshold) {
                Log::warning('Slow variant generation detected', [
                    'asset_id' => $this->asset->id,
                    'duration_seconds' => $duration,
                    'variant_count' => count($createdVariantIds),
                    'file_size' => $this->asset->file_size,
                    'threshold_seconds' => $slowThreshold,
                ]);
            }

            Log::info('Variant generation completed successfully', [
                'asset_id' => $this->asset->id,
                'variants_created' => count($createdVariantIds),
                'duration_seconds' => $duration,
            ]);
        } catch (\Throwable $e) {
            // Rollback will be handled by failed() method
            Log::error('Variant generation failed', [
                'asset_id' => $this->asset->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            // Clean up temp directory
            if ($tempDir !== null && is_dir($tempDir)) {
                $this->cleanupTempDirectory($tempDir);
            }
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessMediaVariantsJob failed, rolling back', [
            'asset_id' => $this->asset->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Refresh asset to get latest state
        $this->asset->refresh();

        // Get variants before deleting (single query instead of 3)
        $variants = $this->asset->variants()->get();
        $deletedVariantCount = $variants->count();
        $s3KeysToDelete = $variants->pluck('s3_key')->toArray();

        // Delete variant records from database
        $this->asset->variants()->delete();

        // Delete variant files from S3
        if (! empty($s3KeysToDelete)) {
            try {
                Storage::disk('s3-permanent')->delete($s3KeysToDelete);
                Log::info('Deleted variant files from S3', [
                    'asset_id' => $this->asset->id,
                    'files_deleted' => count($s3KeysToDelete),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to delete variant files from S3 during rollback', [
                    'asset_id' => $this->asset->id,
                    's3_keys' => $s3KeysToDelete,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update asset state to Failed with error message
        $this->asset->update([
            'state' => MediaState::Failed,
            'error_message' => 'Variant generation failed: '.$exception->getMessage(),
        ]);

        Log::info('Rollback completed', [
            'asset_id' => $this->asset->id,
            'variants_deleted' => $deletedVariantCount,
        ]);
    }

    /**
     * Download file from S3 to local path.
     *
     * @throws \RuntimeException If file cannot be downloaded or written
     */
    private function downloadFromS3(string $s3Key, string $localPath): void
    {
        try {
            $contents = Storage::disk('s3-permanent')->get($s3Key);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                "Failed to download file from S3: {$s3Key}. Error: {$e->getMessage()}",
                0,
                $e
            );
        }

        if ($contents === null) {
            throw new \RuntimeException("File not found in S3: {$s3Key}");
        }

        $bytesWritten = file_put_contents($localPath, $contents);

        if ($bytesWritten === false || $bytesWritten === 0) {
            throw new \RuntimeException("Failed to write downloaded file to: {$localPath}");
        }

        Log::debug('Downloaded file from S3', [
            's3_key' => $s3Key,
            'local_path' => $localPath,
            'size' => $bytesWritten,
        ]);
    }

    /**
     * Clean up temporary directory and all its contents.
     */
    private function cleanupTempDirectory(string $tempDir): void
    {
        try {
            if (! is_dir($tempDir)) {
                return;
            }

            // Delete all files in directory
            $files = glob("{$tempDir}/*");
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            // Delete directory
            rmdir($tempDir);

            Log::debug('Cleaned up temp directory', ['temp_dir' => $tempDir]);
        } catch (\Throwable $e) {
            Log::warning('Failed to clean up temp directory', [
                'temp_dir' => $tempDir,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark asset as Ready without processing variants.
     */
    private function markAsReady(string $reason): void
    {
        $this->asset->update([
            'state' => MediaState::Ready,
            'error_message' => null,
        ]);

        Log::info('Marked asset as Ready', [
            'asset_id' => $this->asset->id,
            'reason' => $reason,
        ]);
    }
}
