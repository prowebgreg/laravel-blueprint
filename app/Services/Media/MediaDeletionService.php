<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Actions\Media\DeleteFromS3Action;
use App\Exceptions\MediaDeletionBlockedException;
use App\Models\MediaAsset;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Media Deletion Service
 *
 * Orchestrates safe media deletion with usage protection:
 * 1. Check if media is the designated fallback image (block deletion)
 * 2. Check if media is used by published public content (block deletion)
 * 3. Soft-delete the MediaAsset record
 * 4. Remove all relationships from content_relations table
 * 5. Delete original file and all variant files from S3
 *
 * Deletion Blocking Rules:
 * - Block if media is the fallback image (set in system settings)
 * - Block if used by published Page, Service, or BlogPost
 * - Allow if only used by draft content
 * - Allow if only used by soft-deleted content
 * - Allow if only used by internal resources (Faq, Testimonial)
 *
 * Error Handling:
 * - Throws MediaDeletionBlockedException if deletion is blocked
 * - S3 cleanup failures are logged but don't prevent soft-delete
 */
class MediaDeletionService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly MediaUsageService $mediaUsageService,
        private readonly DeleteFromS3Action $deleteFromS3Action,
    ) {}

    /**
     * Delete a media asset with usage protection.
     *
     * @param  MediaAsset  $media  The media asset to delete
     *
     * @throws MediaDeletionBlockedException If deletion is blocked
     */
    public function delete(MediaAsset $media): void
    {
        Log::info('Starting media deletion', [
            'media_id' => $media->id,
            'original_name' => $media->original_name,
        ]);

        // Step 1: Check if this is the fallback image
        $fallbackId = Setting::get('media.fallback_image_id');
        if ($fallbackId !== null && $fallbackId === $media->id) {
            Log::warning('Attempted to delete fallback image', [
                'media_id' => $media->id,
            ]);

            throw new MediaDeletionBlockedException(
                media: $media,
                usages: [],
                isFallbackImage: true
            );
        }

        // Step 2: Check if media is used by published public content
        if ($this->mediaUsageService->isBlockedByPublicContent($media)) {
            $usages = $this->mediaUsageService->findUsages($media);

            Log::warning('Media deletion blocked by published content', [
                'media_id' => $media->id,
                'usage_count' => $usages->count(),
            ]);

            throw new MediaDeletionBlockedException(
                media: $media,
                usages: $usages->toArray()
            );
        }

        // Step 3: Soft-delete the MediaAsset record
        $media->delete();

        Log::info('Media asset soft-deleted', [
            'media_id' => $media->id,
        ]);

        // Step 4: Remove all relationships from content_relations table
        $deletedRelations = DB::table('content_relations')
            ->where('target_type', MediaAsset::class)
            ->where('target_id', $media->id)
            ->delete();

        Log::info('Removed content relationships', [
            'media_id' => $media->id,
            'deleted_count' => $deletedRelations,
        ]);

        // Step 5: Delete original file and all variant files from S3
        $this->cleanupS3Files($media);
    }

    /**
     * Delete all S3 files for a media asset (original + variants).
     *
     * S3 cleanup failures are logged but don't throw exceptions to prevent
     * the soft-delete from failing if S3 is temporarily unavailable.
     *
     * @param  MediaAsset  $media  The media asset with files to delete
     */
    private function cleanupS3Files(MediaAsset $media): void
    {
        // Collect all S3 keys to delete
        $s3Keys = [];

        // Add original file
        if (! empty($media->s3_key_original)) {
            $s3Keys[] = $media->s3_key_original;
        }

        // Add all variant files
        $variants = $media->variants()->get();
        foreach ($variants as $variant) {
            if (! empty($variant->s3_key)) {
                $s3Keys[] = $variant->s3_key;
            }
        }

        if (empty($s3Keys)) {
            Log::info('No S3 files to delete', [
                'media_id' => $media->id,
            ]);

            return;
        }

        Log::info('Deleting S3 files', [
            'media_id' => $media->id,
            'file_count' => count($s3Keys),
        ]);

        // Execute S3 deletion
        try {
            $result = $this->deleteFromS3Action->execute($s3Keys);

            if ($result->success) {
                Log::info('S3 files deleted successfully', [
                    'media_id' => $media->id,
                    'deleted_count' => $result->deletedCount,
                ]);
            } else {
                Log::error('S3 file deletion failed', [
                    'media_id' => $media->id,
                    'deleted_count' => $result->deletedCount,
                    'failed_count' => count($result->failedKeys),
                    'failed_keys' => $result->failedKeys,
                    'error' => $result->errorMessage,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('S3 cleanup exception', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);

            // Don't throw - we already soft-deleted the record
        }
    }
}
