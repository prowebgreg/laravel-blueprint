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
 * Auto-corrects EXIF orientation for rotated images.
 * For animated GIF/WebP files, extracts only the first frame.
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

        // Extract first frame if animated (GIF or WebP)
        $processedSourcePath = $this->extractFirstFrameIfAnimated($sourcePath, $outputDir);

        // Get original image dimensions (use processed source to get correct dimensions after EXIF correction)
        $imageInfo = @getimagesize($processedSourcePath);
        if ($imageInfo === false) {
            throw new RuntimeException("Failed to get image dimensions for: {$processedSourcePath}");
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

            // Generate variant using spatie/image with EXIF orientation correction
            try {
                Image::load($processedSourcePath)
                    ->orientation()
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

        // Generate original size variant with EXIF orientation correction
        $originalPath = $outputDir.'/'.$baseName.'-original.webp';

        try {
            Image::load($processedSourcePath)
                ->orientation()
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

        // Clean up extracted first frame if created
        if ($processedSourcePath !== $sourcePath && file_exists($processedSourcePath)) {
            @unlink($processedSourcePath);
        }

        return $variants;
    }

    /**
     * Extract first frame from animated GIF or WebP files.
     *
     * For animated images (GIF or WebP), we extract only the first frame
     * to prevent generating huge animated variant files. The extracted
     * first frame is saved as a static WebP in the same directory.
     *
     * @param  string  $sourcePath  Path to the source image file
     * @param  string  $outputDir  Directory to save extracted frame
     * @return string Path to the processed image (original or extracted frame)
     */
    private function extractFirstFrameIfAnimated(string $sourcePath, string $outputDir): string
    {
        // Get image info to determine format
        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            return $sourcePath;
        }

        $mimeType = $imageInfo['mime'];

        // Check if it's a GIF or WebP
        $isGif = $mimeType === 'image/gif';
        $isWebP = $mimeType === 'image/webp';

        if (! $isGif && ! $isWebP) {
            return $sourcePath;
        }

        // Detect if the image is animated
        $isAnimated = false;

        if ($isGif) {
            $isAnimated = $this->isAnimatedGif($sourcePath);
        } elseif ($isWebP) {
            $isAnimated = $this->isAnimatedWebP($sourcePath);
        }

        if (! $isAnimated) {
            return $sourcePath;
        }

        // Extract first frame to static WebP
        $firstFramePath = $outputDir.'/first-frame-'.uniqid().'.webp';

        try {
            // Spatie Image automatically extracts the first frame when converting to WebP
            Image::load($sourcePath)
                ->format('webp')
                ->save($firstFramePath);

            return $firstFramePath;
        } catch (\Throwable) {
            // Clean up temp file if extraction fails
            if (file_exists($firstFramePath)) {
                @unlink($firstFramePath);
            }

            // If extraction fails, return original path
            return $sourcePath;
        }
    }

    /**
     * Check if a GIF file is animated (contains multiple frames).
     *
     * @param  string  $filePath  Path to GIF file
     * @return bool True if animated, false otherwise
     */
    private function isAnimatedGif(string $filePath): bool
    {
        // Skip detection for very large files (> 50MB) to avoid memory issues
        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize > 50 * 1024 * 1024) {
            return false;
        }

        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            return false;
        }

        // Count number of frames by looking for graphic control extension blocks
        // Each frame in an animated GIF has a Graphic Control Extension (0x21 0xF9)
        $frameCount = substr_count($fileContents, "\x00\x21\xF9\x04");

        // Also check for multiple image descriptors (0x2C)
        if ($frameCount <= 1) {
            $frameCount = substr_count($fileContents, "\x2C");
        }

        // Animated GIF has more than 1 frame
        return $frameCount > 1;
    }

    /**
     * Check if a WebP file is animated (contains multiple frames).
     *
     * @param  string  $filePath  Path to WebP file
     * @return bool True if animated, false otherwise
     */
    private function isAnimatedWebP(string $filePath): bool
    {
        // Skip detection for very large files (> 50MB) to avoid memory issues
        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize > 50 * 1024 * 1024) {
            return false;
        }

        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            return false;
        }

        // Check for ANIM chunk in WebP file
        // Animated WebP files contain an "ANIM" chunk
        return str_contains($fileContents, 'ANIM');
    }
}
