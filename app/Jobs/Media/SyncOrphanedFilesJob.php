<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Enums\MediaState;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Sync Orphaned Files Job
 *
 * Scans S3 storage and database to detect and report inconsistencies between
 * S3 files and database records. Provides actionable reports for manual cleanup.
 *
 * Detection Actions:
 * - Orphaned S3 files: Files exist in S3 but have no corresponding DB record
 * - Missing S3 files: DB records exist but S3 files are missing
 * - Marks MediaAsset as failed when S3 file is missing (MediaState::Ready only)
 * - Marks MediaVariant parent as failed when variant S3 file is missing
 * - Generates comprehensive report with orphaned/missing file counts
 *
 * Orphaned Files (S3 → No DB):
 * - Files uploaded but DB record never created or was deleted
 * - Includes soft-deleted assets (file exists but no active DB record)
 * - All media folders scanned: images, videos, svg
 * - Subdirectories and variant paths included
 *
 * Missing Files (DB → No S3):
 * - Only checks MediaState::Ready assets (not uploading, processing, or failed)
 * - Checks MediaVariant records for missing variant files
 * - Sets state to MediaState::Failed with error message
 * - Preserves existing error messages on already-failed assets
 *
 * Report Structure:
 * - orphaned_files: Array of S3 keys without DB records
 * - missing_files: Array of S3 keys that should exist but don't
 * - summary: {orphaned_count, missing_count, status}
 * - timestamp: Current timestamp string
 *
 * Scheduled: Weekly (Sunday 4:00 AM) via schedule:work
 * Queue: media
 * Timeout: 300 seconds (5 minutes)
 */
class SyncOrphanedFilesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Job timeout in seconds.
     */
    public int $timeout = 300;

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
     *
     * @return array{orphaned_files: array<int, string>, missing_files: array<int, string>, summary: array{orphaned_count: int, missing_count: int, status: string}, timestamp: string}
     */
    public function handle(): array
    {
        Log::info('Starting S3/DB orphan detection and sync check');

        // Step 1: List all S3 files under media/ prefix
        $s3Disk = Storage::disk('s3-permanent');
        $allS3Files = collect($s3Disk->allFiles('media/'));

        Log::debug('S3 files discovered', [
            'total_files' => $allS3Files->count(),
        ]);

        // Step 2: Collect all valid S3 keys from database
        // Only include non-trashed MediaAsset records (active DB records)
        $validAssetKeys = MediaAsset::query()
            ->pluck('s3_key_original')
            ->filter()
            ->values();

        $validVariantKeys = MediaVariant::query()
            ->pluck('s3_key')
            ->filter()
            ->values();

        $validDbKeys = $validAssetKeys->merge($validVariantKeys);

        Log::debug('Valid DB keys collected', [
            'asset_keys' => $validAssetKeys->count(),
            'variant_keys' => $validVariantKeys->count(),
            'total_valid_keys' => $validDbKeys->count(),
        ]);

        // Step 3: Detect orphaned S3 files (S3 files without DB records)
        $orphanedFiles = $allS3Files->diff($validDbKeys)->values()->toArray();

        Log::debug('Orphaned S3 files detected', [
            'count' => count($orphanedFiles),
        ]);

        // Step 4: Detect missing S3 files for MediaAsset records
        $missingAssetFiles = $this->detectMissingAssetFiles($s3Disk);

        // Step 5: Detect missing S3 files for MediaVariant records
        $missingVariantFiles = $this->detectMissingVariantFiles($s3Disk);

        // Merge missing files
        $missingFiles = array_merge($missingAssetFiles, $missingVariantFiles);

        Log::debug('Missing S3 files detected', [
            'asset_files' => count($missingAssetFiles),
            'variant_files' => count($missingVariantFiles),
            'total_missing' => count($missingFiles),
        ]);

        // Step 6: Generate report
        $report = [
            'orphaned_files' => $orphanedFiles,
            'missing_files' => $missingFiles,
            'summary' => [
                'orphaned_count' => count($orphanedFiles),
                'missing_count' => count($missingFiles),
                'status' => (count($orphanedFiles) === 0 && count($missingFiles) === 0)
                    ? 'in_sync'
                    : 'issues_found',
            ],
            'timestamp' => now()->toDateTimeString(),
        ];

        Log::info('Completed S3/DB orphan detection and sync check', [
            'orphaned_count' => $report['summary']['orphaned_count'],
            'missing_count' => $report['summary']['missing_count'],
            'status' => $report['summary']['status'],
        ]);

        return $report;
    }

    /**
     * Detect missing S3 files for MediaAsset records.
     * Marks MediaState::Ready assets as failed when S3 file is missing.
     *
     * @return array<int, string>
     */
    private function detectMissingAssetFiles(Filesystem $s3Disk): array
    {
        $missingFiles = [];

        // Only check Ready state assets (not uploading, processing, or already failed)
        $readyAssets = MediaAsset::query()
            ->where('state', MediaState::Ready)
            ->get();

        foreach ($readyAssets as $asset) {
            if (! $s3Disk->exists($asset->s3_key_original)) {
                $missingFiles[] = $asset->s3_key_original;

                // Mark asset as failed with descriptive error message
                $asset->update([
                    'state' => MediaState::Failed,
                    'error_message' => 'S3 file missing: detected by SyncOrphanedFilesJob',
                ]);

                Log::warning('MediaAsset marked as failed due to missing S3 file', [
                    'asset_id' => $asset->id,
                    's3_key' => $asset->s3_key_original,
                ]);
            }
        }

        return $missingFiles;
    }

    /**
     * Detect missing S3 files for MediaVariant records.
     * Marks parent MediaAsset as failed when variant S3 file is missing.
     *
     * @return array<int, string>
     */
    private function detectMissingVariantFiles(Filesystem $s3Disk): array
    {
        $missingFiles = [];

        // Query all variants with their parent MediaAsset
        $variants = MediaVariant::query()
            ->with('mediaAsset')
            ->get();

        foreach ($variants as $variant) {
            if (! $s3Disk->exists($variant->s3_key)) {
                $missingFiles[] = $variant->s3_key;

                // Mark parent asset as failed if not already failed
                $parentAsset = $variant->mediaAsset;
                if ($parentAsset && $parentAsset->state !== MediaState::Failed) {
                    $parentAsset->update([
                        'state' => MediaState::Failed,
                        'error_message' => "Variant S3 file missing: {$variant->s3_key} detected by SyncOrphanedFilesJob",
                    ]);

                    Log::warning('MediaAsset marked as failed due to missing variant S3 file', [
                        'asset_id' => $parentAsset->id,
                        'variant_id' => $variant->id,
                        'variant_s3_key' => $variant->s3_key,
                    ]);
                }
            }
        }

        return $missingFiles;
    }
}
