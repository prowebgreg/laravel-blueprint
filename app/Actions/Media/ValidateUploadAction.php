<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\DTOs\ValidationResult;
use App\Enums\MediaType;
use Illuminate\Http\UploadedFile;

/**
 * Validates uploaded media files against security and dimension requirements.
 *
 * Validates:
 * - MIME type against allowed types
 * - File size (max 20MB)
 * - Image dimensions (50-16000px for width and height)
 * - Magic bytes match claimed MIME type
 */
class ValidateUploadAction
{
    /**
     * Execute validation on uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded file to validate
     * @return ValidationResult Validation result with media type and dimensions if valid
     */
    public function execute(UploadedFile $file): ValidationResult
    {
        // Check file size first
        if ($file->getSize() === 0) {
            return ValidationResult::failed('File is empty (0 bytes).');
        }

        $maxSize = config('media.max_upload_size');
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / 1024 / 1024;

            return ValidationResult::failed("File size exceeds maximum allowed size of {$maxSizeMB}MB.");
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        $mediaType = MediaType::fromMimeType($mimeType ?? '');

        if ($mediaType === null) {
            return ValidationResult::failed("Invalid MIME type: {$mimeType}. File type is not allowed.");
        }

        // Verify MIME type is in allowed list
        $allowedMimes = match ($mediaType) {
            MediaType::Image => config('media.allowed_image_mimes'),
            MediaType::Video => config('media.allowed_video_mimes'),
            MediaType::Svg => config('media.allowed_svg_mimes'),
        };

        if (! \in_array($mimeType, $allowedMimes, true)) {
            return ValidationResult::failed("Invalid MIME type: {$mimeType}. File type is not allowed.");
        }

        // Validate magic bytes for images
        if ($mediaType === MediaType::Image) {
            $magicBytesValid = $this->validateMagicBytes($file, $mimeType);
            if (! $magicBytesValid) {
                return ValidationResult::failed('File magic bytes do not match the declared MIME type. File may be invalid or corrupted.');
            }
        }

        // Extract and validate dimensions for images
        $dimensions = null;
        if ($mediaType === MediaType::Image) {
            $imageInfo = getimagesize($file->getRealPath());

            if ($imageInfo === false) {
                return ValidationResult::failed('Unable to read image dimensions. File may be corrupted.');
            }

            [$width, $height] = $imageInfo;
            $dimensions = ['width' => $width, 'height' => $height];

            $minDimensions = config('media.min_dimensions');
            $maxDimensions = config('media.max_dimensions');

            if ($width < $minDimensions) {
                return ValidationResult::failed("Image width ({$width}px) is below minimum required width of {$minDimensions}px.");
            }

            if ($height < $minDimensions) {
                return ValidationResult::failed("Image height ({$height}px) is below minimum required height of {$minDimensions}px.");
            }

            if ($width > $maxDimensions) {
                return ValidationResult::failed("Image width ({$width}px) exceeds maximum allowed width of {$maxDimensions}px.");
            }

            if ($height > $maxDimensions) {
                return ValidationResult::failed("Image height ({$height}px) exceeds maximum allowed height of {$maxDimensions}px.");
            }
        }

        return ValidationResult::success($mediaType, $dimensions);
    }

    /**
     * Validate that file magic bytes match the claimed MIME type.
     */
    private function validateMagicBytes(UploadedFile $file, string $mimeType): bool
    {
        $filePath = $file->getRealPath();
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            return false;
        }

        try {
            $magicBytes = fread($handle, 12);

            if ($magicBytes === false) {
                return false;
            }

            // RIFF format: 4 bytes header + 4 bytes file size + 4 bytes format identifier
            return match ($mimeType) {
                'image/jpeg' => str_starts_with($magicBytes, "\xFF\xD8\xFF"),
                'image/png' => str_starts_with($magicBytes, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"),
                'image/gif' => str_starts_with($magicBytes, 'GIF87a') || str_starts_with($magicBytes, 'GIF89a'),
                'image/webp' => str_starts_with($magicBytes, 'RIFF') && substr($magicBytes, 8, 4) === 'WEBP',
                default => true,
            };
        } finally {
            fclose($handle);
        }
    }
}
