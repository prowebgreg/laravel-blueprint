<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\DTOs\Media\S3UploadResult;
use App\Enums\MediaFolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadToS3Action
{
    private const MAX_ATTEMPTS = 3;

    private const BACKOFF_DELAYS = [1000000, 2000000, 4000000]; // 1s, 2s, 4s in microseconds

    /**
     * Upload file to S3 with retry logic and exponential backoff.
     *
     * @param  UploadedFile|string  $file  File to upload (UploadedFile or local path)
     * @param  string  $sanitizedFilename  Sanitized filename with nanoid (without extension)
     * @param  MediaFolder  $folder  Target folder (Images, Videos, Svg)
     * @return S3UploadResult Object containing s3Key and cloudfrontUrl
     *
     * @throws \Exception When upload fails after all retry attempts
     */
    public function execute(
        UploadedFile|string $file,
        string $sanitizedFilename,
        MediaFolder $folder
    ): S3UploadResult {
        // Extract file extension
        $extension = $this->getFileExtension($file);

        // Build S3 key using MediaFolder::s3Prefix()
        $s3Key = $folder->s3Prefix().'/'.$sanitizedFilename.'.'.$extension;

        $lastException = null;
        $uploadSucceeded = false;

        // Attempt upload with retry logic
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            try {
                // Apply backoff delay before retry attempts (not on first attempt)
                if ($attempt > 0) {
                    usleep(self::BACKOFF_DELAYS[$attempt - 1]);
                    Log::info("Retrying S3 upload (attempt {$attempt}/{self::MAX_ATTEMPTS})", [
                        's3_key' => $s3Key,
                    ]);
                }

                // Upload to S3
                $this->uploadFile($file, $s3Key);

                $uploadSucceeded = true;
                break; // Success - exit retry loop
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("S3 upload attempt failed (attempt {$attempt}/{self::MAX_ATTEMPTS})", [
                    's3_key' => $s3Key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Handle final failure after all retries
        if (! $uploadSucceeded) {
            $this->rollbackUpload($s3Key);

            throw new \Exception(
                'Failed to upload file to S3 after '.self::MAX_ATTEMPTS.' attempts: '.$lastException->getMessage(),
                0,
                $lastException
            );
        }

        // Build CloudFront URL
        $cloudfrontUrl = $this->buildCloudfrontUrl($s3Key);

        return new S3UploadResult(
            s3Key: $s3Key,
            cloudfrontUrl: $cloudfrontUrl,
        );
    }

    /**
     * Get file extension from UploadedFile or file path.
     */
    private function getFileExtension(UploadedFile|string $file): string
    {
        if ($file instanceof UploadedFile) {
            return $file->getClientOriginalExtension();
        }

        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * Upload file to S3 using Storage facade.
     */
    private function uploadFile(UploadedFile|string $file, string $s3Key): void
    {
        $disk = Storage::disk('s3-permanent');

        if ($file instanceof UploadedFile) {
            $disk->putFileAs('', $file, $s3Key);
        } else {
            $disk->put($s3Key, file_get_contents($file));
        }
    }

    /**
     * Attempt to delete partial upload on failure.
     * Silently catches any delete errors to preserve original upload error.
     */
    private function rollbackUpload(string $s3Key): void
    {
        try {
            if (Storage::disk('s3-permanent')->exists($s3Key)) {
                Storage::disk('s3-permanent')->delete($s3Key);
                Log::info('Rolled back partial S3 upload', ['s3_key' => $s3Key]);
            }
        } catch (\Exception $e) {
            // Log but don't throw - preserve original upload error
            Log::warning('Failed to rollback S3 upload', [
                's3_key' => $s3Key,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build CloudFront URL from S3 key.
     */
    private function buildCloudfrontUrl(string $s3Key): string
    {
        $baseUrl = config('filesystems.disks.s3-permanent.url')
            ?? config('filesystems.disks.s3.url');

        return rtrim($baseUrl, '/').'/'.$s3Key;
    }
}
