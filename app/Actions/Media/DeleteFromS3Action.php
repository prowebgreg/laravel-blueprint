<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\DTOs\Media\S3DeleteResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteFromS3Action
{
    /**
     * Delete single file or batch of files from S3.
     *
     * This action is idempotent - deleting non-existent files is considered
     * successful (404 is not an error). This allows safe retry logic and
     * prevents failures when cleaning up already-deleted files.
     *
     * @param  string|array<string>  $s3Keys  Single S3 key or array of keys
     * @return S3DeleteResult Object containing success, deletedCount, failedKeys, and errorMessage
     */
    public function execute(string|array $s3Keys): S3DeleteResult
    {
        // Normalize input to array
        $keys = $this->normalizeInput($s3Keys);

        // Handle empty input
        if (empty($keys)) {
            return new S3DeleteResult(
                success: true,
                deletedCount: 0,
                failedKeys: [],
                errorMessage: null,
            );
        }

        $deletedCount = 0;
        $failedKeys = [];
        $errorMessage = null;

        try {
            $disk = Storage::disk('s3-permanent');

            // Delete each file
            foreach ($keys as $key) {
                try {
                    // Storage::delete() returns true/false, but doesn't throw on non-existent files
                    // This makes the operation idempotent - we treat 404 as success
                    $disk->delete($key);
                    $deletedCount++;
                } catch (\Exception $e) {
                    // Individual file deletion failed
                    $failedKeys[] = $key;
                    $errorMessage = $errorMessage ?? $e->getMessage();

                    Log::warning('Failed to delete S3 file', [
                        's3_key' => $key,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // General failure (e.g., couldn't connect to S3)
            Log::error('S3 deletion failed', [
                'error' => $e->getMessage(),
            ]);

            return new S3DeleteResult(
                success: false,
                deletedCount: 0,
                failedKeys: is_array($s3Keys) ? $s3Keys : [$s3Keys],
                errorMessage: $e->getMessage(),
            );
        }

        // Success if no failures occurred
        $success = empty($failedKeys);

        return new S3DeleteResult(
            success: $success,
            deletedCount: $deletedCount,
            failedKeys: $failedKeys,
            errorMessage: $errorMessage,
        );
    }

    /**
     * Normalize input to array of keys, filtering out empty strings.
     *
     * @param  string|array<string>  $input
     * @return array<string>
     */
    private function normalizeInput(string|array $input): array
    {
        // Convert single string to array
        if (is_string($input)) {
            // Filter out empty strings
            return $input === '' ? [] : [$input];
        }

        // Return array as-is (already an array)
        return $input;
    }
}
