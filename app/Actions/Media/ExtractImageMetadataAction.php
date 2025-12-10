<?php

declare(strict_types=1);

namespace App\Actions\Media;

use Illuminate\Http\UploadedFile;

/**
 * Extracts image metadata (width and height) using GD library.
 *
 * Supports JPEG, PNG, GIF, and WebP formats.
 * Returns null for non-image files (videos, SVGs) or corrupted files.
 */
class ExtractImageMetadataAction
{
    /**
     * Extract width and height from an image file using GD library.
     *
     * @param  UploadedFile  $file  The uploaded file to extract metadata from
     * @return array{width: int, height: int}|null Array with width/height or null on failure
     */
    public function execute(UploadedFile $file): ?array
    {
        try {
            $path = $file->getRealPath();

            if ($path === false || ! file_exists($path)) {
                return null;
            }

            // Use getimagesize() to extract dimensions
            // This function returns false for non-image files or corrupted images
            $imageInfo = @getimagesize($path);

            if ($imageInfo === false) {
                return null;
            }

            [$width, $height] = $imageInfo;

            // Validate dimensions are positive integers
            if (! is_int($width) || ! is_int($height) || $width <= 0 || $height <= 0) {
                return null;
            }

            return [
                'width' => $width,
                'height' => $height,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
