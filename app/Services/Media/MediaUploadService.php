<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Actions\Media\SanitizeFilenameAction;
use App\Actions\Media\SanitizeSvgAction;
use App\Actions\Media\UploadToS3Action;
use App\Actions\Media\ValidateUploadAction;
use App\Enums\MediaFolder;
use App\Enums\MediaState;
use App\Enums\MediaType;
use App\Jobs\Media\ProcessMediaVariantsJob;
use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Media Upload Service
 *
 * Orchestrates the complete media upload flow:
 * 1. Validate upload (security, dimensions, MIME type)
 * 2. Sanitize filename with nanoid
 * 3. Sanitize SVG content (SVGs only - removes XSS vectors)
 * 4. Upload original/sanitized file to S3
 * 5. Create MediaAsset record
 * 6. Dispatch variant generation job (images only)
 *
 * State Machine:
 * - Images: Created with state=Processing → Job transitions to Ready/Failed
 * - Videos: Created with state=Ready (no variants needed)
 * - SVGs: Created with state=Ready (no variants needed, sanitized before upload)
 *
 * Error Handling:
 * - Validation failures throw InvalidArgumentException
 * - SVG sanitization failures throw InvalidArgumentException
 * - S3 upload failures throw RuntimeException (no MediaAsset created)
 * - All errors are logged
 */
class MediaUploadService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly ValidateUploadAction $validateUploadAction,
        private readonly SanitizeFilenameAction $sanitizeFilenameAction,
        private readonly SanitizeSvgAction $sanitizeSvgAction,
        private readonly UploadToS3Action $uploadToS3Action,
    ) {}

    /**
     * Upload and process a media file.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string|null  $altText  Accessibility alt text
     * @param  string|null  $title  Media title
     * @param  string|null  $caption  Extended description/caption
     * @return MediaAsset The created media asset record
     *
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If S3 upload fails
     */
    public function upload(
        UploadedFile $file,
        ?string $altText = null,
        ?string $title = null,
        ?string $caption = null
    ): MediaAsset {
        Log::info('Starting media upload', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        // Step 1: Validate the upload
        $validationResult = $this->validateUploadAction->execute($file);

        if (! $validationResult->valid) {
            Log::warning('Upload validation failed', [
                'original_name' => $file->getClientOriginalName(),
                'error' => $validationResult->error,
            ]);

            throw new \InvalidArgumentException($validationResult->error);
        }

        $mediaType = $validationResult->mediaType;
        $dimensions = $validationResult->dimensions;

        // Step 2: Sanitize the filename
        $sanitizedFilename = $this->sanitizeFilenameAction->execute(
            $file->getClientOriginalName()
        );

        // Step 3: Sanitize SVG content if needed
        $fileToUpload = $file;
        if ($mediaType === MediaType::Svg) {
            try {
                Log::info('Sanitizing SVG content', [
                    'original_name' => $file->getClientOriginalName(),
                ]);

                $svgContent = file_get_contents($file->getRealPath());
                if ($svgContent === false) {
                    throw new \InvalidArgumentException('Failed to read SVG file content');
                }

                $sanitizedContent = $this->sanitizeSvgAction->execute($svgContent);

                // Create temporary file with sanitized content
                $tempPath = sys_get_temp_dir().'/'.uniqid('sanitized_svg_', true).'.svg';
                if (file_put_contents($tempPath, $sanitizedContent) === false) {
                    throw new \RuntimeException('Failed to create temporary file for sanitized SVG');
                }

                // Create a new UploadedFile from the sanitized content
                $fileToUpload = new UploadedFile(
                    path: $tempPath,
                    originalName: $file->getClientOriginalName(),
                    mimeType: $file->getMimeType(),
                    error: null,
                    test: true // Mark as test to prevent move operation from failing
                );

                Log::info('SVG content sanitized successfully', [
                    'original_name' => $file->getClientOriginalName(),
                    'original_size' => strlen($svgContent),
                    'sanitized_size' => strlen($sanitizedContent),
                ]);
            } catch (\Exception $e) {
                // Clean up temp file if it was created
                if (isset($tempPath) && file_exists($tempPath)) {
                    @unlink($tempPath);
                }

                Log::error('SVG sanitization failed', [
                    'original_name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);

                throw new \InvalidArgumentException(
                    'SVG sanitization failed: '.$e->getMessage(),
                    0,
                    $e
                );
            }
        }

        // Step 4: Determine folder from media type
        $folder = MediaFolder::forMediaType($mediaType);

        // Step 5: Upload original/sanitized file to S3
        try {
            $uploadResult = $this->uploadToS3Action->execute(
                file: $fileToUpload,
                sanitizedFilename: pathinfo($sanitizedFilename, PATHINFO_FILENAME),
                folder: $folder
            );

            // Clean up temporary SVG file if it was created
            if ($mediaType === MediaType::Svg && isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        } catch (\Exception $e) {
            // Clean up temporary SVG file if it was created
            if ($mediaType === MediaType::Svg && isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }

            Log::error('S3 upload failed', [
                'original_name' => $file->getClientOriginalName(),
                'sanitized_filename' => $sanitizedFilename,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Failed to upload file to S3: '.$e->getMessage(),
                0,
                $e
            );
        }

        // Step 6: Determine initial state based on media type
        $initialState = match ($mediaType) {
            MediaType::Image => MediaState::Processing,
            MediaType::Video, MediaType::Svg => MediaState::Ready,
        };

        // Step 7: Create MediaAsset record
        $asset = MediaAsset::create([
            'filename' => $sanitizedFilename,
            'original_name' => $file->getClientOriginalName(),
            'media_type' => $mediaType,
            'folder' => $folder,
            'file_size' => $file->getSize(),
            'dimensions' => $dimensions,
            'mime_type' => $file->getMimeType(),
            'state' => $initialState,
            'error_message' => null,
            's3_key_original' => $uploadResult->s3Key,
            'cloudfront_url_original' => $uploadResult->cloudfrontUrl,
            'alt_text' => $altText,
            'title' => $title,
            'caption' => $caption,
            'focal_point' => null,
            'tags' => [],
        ]);

        Log::info('MediaAsset created', [
            'asset_id' => $asset->id,
            'media_type' => $mediaType->value,
            'state' => $initialState->value,
            's3_key' => $uploadResult->s3Key,
        ]);

        // Step 8: Dispatch variant generation job for images only
        if ($mediaType === MediaType::Image) {
            ProcessMediaVariantsJob::dispatch($asset);

            Log::info('Dispatched variant generation job', [
                'asset_id' => $asset->id,
            ]);
        }

        return $asset;
    }
}
