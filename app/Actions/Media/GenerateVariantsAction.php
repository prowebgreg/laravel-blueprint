<?php

declare(strict_types=1);

namespace App\Actions\Media;

use RuntimeException;
use Spatie\Image\Image;

/**
 * Generate responsive image variants in WebP format.
 *
 * Creates multiple width-specific variants for responsive images,
 * converts all formats to WebP, and preserves aspect ratio.
 * Skips variants larger than the original image width (no upscaling).
 *
 * @internal This Action is called by ProcessMediaVariantsJob which controls all input paths.
 */
class GenerateVariantsAction
{
    /**
     * Generate responsive image variants in WebP format.
     *
     * @param  string  $sourcePath  Path to the source image file
     * @param  string  $outputDir  Directory to save generated variants
     * @param  string  $baseName  Base filename for variants (without extension)
     * @return array<int|string, array{width: int, height: int, path: string, file_size: int}>
     *
     * @throws RuntimeException If source file is not readable or image dimensions cannot be determined
     * @throws RuntimeException If variant generation fails
     */
    public function execute(string $sourcePath, string $outputDir, string $baseName): array
    {
        // Validate source file exists and is readable
        if (! file_exists($sourcePath) || ! is_readable($sourcePath)) {
            throw new RuntimeException("Source file does not exist or is not readable: {$sourcePath}");
        }

        // Create output directory if it doesn't exist
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Get original image dimensions
        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new RuntimeException("Failed to get image dimensions for: {$sourcePath}");
        }

        [$originalWidth, $originalHeight] = $imageInfo;

        // Validate dimensions are positive
        if ($originalWidth <= 0 || $originalHeight <= 0) {
            throw new RuntimeException("Invalid image dimensions: {$originalWidth}x{$originalHeight}");
        }

        // Get variant widths from config
        $variantWidths = config('media.variant_widths', [480, 640, 720, 960, 1168, 1440, 1920]);
        $quality = config('media.variant_quality', 85);

        $variants = [];

        // Generate responsive width variants
        foreach ($variantWidths as $targetWidth) {
            // Skip variants larger than original width (no upscaling)
            if ($targetWidth > $originalWidth) {
                continue;
            }

            // Calculate height maintaining aspect ratio
            $targetHeight = (int) round(($targetWidth / $originalWidth) * $originalHeight);

            // Generate variant path
            $variantPath = $outputDir.'/'.$baseName.'-'.$targetWidth.'w.webp';

            // Generate variant using spatie/image
            try {
                Image::load($sourcePath)
                    ->width($targetWidth)
                    ->quality($quality)
                    ->save($variantPath);
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    "Failed to generate variant at {$targetWidth}w: {$e->getMessage()}",
                    previous: $e
                );
            }

            // Get file size
            $fileSize = filesize($variantPath);

            $variants[$targetWidth] = [
                'width' => $targetWidth,
                'height' => $targetHeight,
                'path' => $variantPath,
                'file_size' => $fileSize !== false ? $fileSize : 0,
            ];
        }

        // Generate original size variant
        $originalPath = $outputDir.'/'.$baseName.'-original.webp';

        try {
            Image::load($sourcePath)
                ->quality($quality)
                ->save($originalPath);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                "Failed to generate original variant: {$e->getMessage()}",
                previous: $e
            );
        }

        $originalFileSize = filesize($originalPath);

        $variants['original'] = [
            'width' => $originalWidth,
            'height' => $originalHeight,
            'path' => $originalPath,
            'file_size' => $originalFileSize !== false ? $originalFileSize : 0,
        ];

        return $variants;
    }
}
