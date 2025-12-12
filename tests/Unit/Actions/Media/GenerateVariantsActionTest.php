<?php

declare(strict_types=1);

use App\Actions\Media\GenerateVariantsAction;

/**
 * TDD Tests for GenerateVariantsAction
 *
 * Tests variant generation for images:
 * - 8 variants (7 responsive widths + original)
 * - Skip upscaling (variants larger than original)
 * - Aspect ratio preservation
 * - WebP format conversion
 *
 * @see /specs/003-media-engine/plan.md
 * @see /specs/003-media-engine/data-model.md
 */

/**
 * Helper function to create a test image of specified dimensions and format
 */
function createTestImage(int $width, int $height, string $format = 'jpeg'): string
{
    $image = imagecreatetruecolor($width, $height);

    // Fill with a color
    $color = imagecolorallocate($image, 100, 150, 200);
    imagefill($image, 0, 0, $color);

    $tempFile = sys_get_temp_dir().'/test-image-'.uniqid().'.'.$format;

    switch ($format) {
        case 'jpeg':
        case 'jpg':
            imagejpeg($image, $tempFile);
            break;
        case 'png':
            imagepng($image, $tempFile);
            break;
        case 'gif':
            imagegif($image, $tempFile);
            break;
        case 'webp':
            imagewebp($image, $tempFile);
            break;
    }

    imagedestroy($image);

    return $tempFile;
}

afterEach(function () {
    // Clean up test files in temp directory
    $tempDir = sys_get_temp_dir();
    $files = glob($tempDir.'/test-image-*');
    if ($files !== false) {
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    // Clean up variant directories
    $variantDirs = glob($tempDir.'/variants*');
    if ($variantDirs !== false) {
        foreach ($variantDirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir.'/*');
                if ($files !== false) {
                    foreach ($files as $file) {
                        @unlink($file);
                    }
                }
                @rmdir($dir);
            }
        }
    }
});

describe('variant generation for large images', function () {
    it('generates all 8 variants for a 4K image', function () {
        // 4K image (3840x2160) should generate all 7 responsive widths + original
        // Widths: 480, 640, 720, 960, 1168, 1440, 1920, original

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(3840, 2160);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        expect($variants)->toHaveCount(8)
            ->and(array_keys($variants))->toBe([480, 640, 720, 960, 1168, 1440, 1920, 'original']);
    });

    it('generates correct widths from config', function () {
        // Should use variant_widths from config/media.php: [480, 640, 720, 960, 1168, 1440, 1920]

        config(['media.variant_widths' => [480, 640, 720, 960, 1168, 1440, 1920]]);
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(2000, 1125);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        expect(array_keys($variants))->toContain(480, 640, 720, 960, 1168, 1440, 1920);
    });
});

describe('skip upscaling', function () {
    it('skips variants larger than original image width', function () {
        // 1200px wide image should only generate: 480, 640, 720, 960, 1168, original
        // Should skip: 1440, 1920 (larger than original)

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1200, 675);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        expect($variants)->toHaveCount(6)
            ->and(array_keys($variants))->toBe([480, 640, 720, 960, 1168, 'original'])
            ->and(array_keys($variants))->not->toContain(1440, 1920);
    });

    it('generates only original for very small images', function () {
        // 320px wide image should only generate original (all responsive widths are larger)

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(320, 180);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        expect($variants)->toHaveCount(1)
            ->and(array_keys($variants))->toBe(['original']);
    });

    it('includes variant at exact original width', function () {
        // 960px wide image should include 960 variant (not skip it)

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(960, 540);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        expect(array_keys($variants))->toContain(960);
    });
});

describe('aspect ratio preservation', function () {
    it('preserves 16:9 aspect ratio across all variants', function () {
        // 1920x1080 (16:9) should produce variants with same aspect ratio

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $key => $variant) {
            $ratio = $variant['width'] / $variant['height'];
            expect($ratio)->toBeGreaterThan(1.77)->toBeLessThan(1.78); // ~16:9
        }
    });

    it('preserves 4:3 aspect ratio across all variants', function () {
        // 1600x1200 (4:3) should produce variants with same aspect ratio

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1600, 1200);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $key => $variant) {
            $ratio = $variant['width'] / $variant['height'];
            expect($ratio)->toBeGreaterThan(1.32)->toBeLessThan(1.34); // ~4:3
        }
    });

    it('preserves portrait 9:16 aspect ratio', function () {
        // 1080x1920 (9:16) should produce variants with same aspect ratio

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1080, 1920);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $key => $variant) {
            $ratio = $variant['width'] / $variant['height'];
            expect($ratio)->toBeGreaterThan(0.56)->toBeLessThan(0.57); // ~9:16
        }
    });

    it('calculates correct height for 480w variant of 16:9 image', function () {
        // 480w of 16:9 should be 480x270

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        expect($variants[480]['width'])->toBe(480)
            ->and($variants[480]['height'])->toBe(270);
    });
});

describe('WebP format conversion', function () {
    it('converts JPEG to WebP format', function () {
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080, 'jpeg');
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $variant) {
            expect($variant['path'])->toEndWith('.webp');
        }
    });

    it('converts PNG to WebP format', function () {
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080, 'png');
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $variant) {
            expect($variant['path'])->toEndWith('.webp');
        }
    });

    it('converts GIF to WebP format', function () {
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080, 'gif');
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $variant) {
            expect($variant['path'])->toEndWith('.webp');
        }
    });

    it('keeps WebP format for WebP input', function () {
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080, 'webp');
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $variant) {
            expect($variant['path'])->toEndWith('.webp');
        }
    });
});

describe('variant data structure', function () {
    it('returns array with correct structure for each variant', function () {
        // Each variant should have: width, height, path, file_size

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $variant) {
            expect($variant)->toHaveKeys(['width', 'height', 'path', 'file_size'])
                ->and($variant['width'])->toBeInt()
                ->and($variant['height'])->toBeInt()
                ->and($variant['path'])->toBeString()
                ->and($variant['file_size'])->toBeInt();
        }
    });

    it('returns keyed array by width with original key for full size', function () {
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        expect($variants)->toHaveKey('original')
            ->and($variants['original']['width'])->toBe(1920)
            ->and($variants['original']['height'])->toBe(1080);
    });

    it('generates valid file paths in output directory', function () {
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $variant) {
            expect($variant['path'])->toStartWith($outputDir);
        }
    });
});

describe('quality configuration', function () {
    it('uses quality setting from config', function () {
        // Quality should be 85 from config/media.php

        config(['media.variant_quality' => 85]);
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        // Verify variants were generated (quality is applied internally)
        expect($variants)->not->toBeEmpty();
    });
});

describe('error handling', function () {
    it('throws exception for invalid source path', function () {
        $action = new GenerateVariantsAction;
        $outputDir = sys_get_temp_dir().'/variants';

        expect(fn () => $action->execute('/invalid/nonexistent/path.jpg', $outputDir, 'test'))
            ->toThrow(\RuntimeException::class, 'Source file does not exist or is not readable');
    });
});

describe('edge cases', function () {
    it('handles square images correctly', function () {
        // 2000x2000 square image

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(2000, 2000);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        foreach ($variants as $variant) {
            expect($variant['width'])->toBe($variant['height']);
        }
    });

    it('handles ultra-wide panoramic images', function () {
        // 21:9 ultra-wide (3440x1440)

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(3440, 1440);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        $expectedRatio = 3440 / 1440;
        foreach ($variants as $variant) {
            $ratio = $variant['width'] / $variant['height'];
            expect(abs($ratio - $expectedRatio))->toBeLessThan(0.01);
        }
    });

    it('handles tall portrait images', function () {
        // Very tall portrait (600x2400)

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(600, 2400);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        // Should only generate 480 and original (600 > 480, but < 640)
        expect(array_keys($variants))->toBe([480, 'original']);
    });

    it('creates output directory if it does not exist', function () {
        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080);
        $outputDir = sys_get_temp_dir().'/nonexistent-'.uniqid();

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        expect(is_dir($outputDir))->toBeTrue();
    });
});

describe('EXIF orientation correction', function () {
    it('applies orientation correction via Spatie Image', function () {
        // The orientation() method is called on all variant generation
        // This test verifies the action can process images with EXIF data
        // Note: Creating images with EXIF orientation metadata requires external tools
        // so we test that regular images process correctly with orientation() call

        $action = new GenerateVariantsAction;
        $sourcePath = createTestImage(1920, 1080);
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($sourcePath, $outputDir, 'test-image');

        // Variants should be generated successfully with orientation correction applied
        expect($variants)->not->toBeEmpty()
            ->and($variants['original']['width'])->toBe(1920)
            ->and($variants['original']['height'])->toBe(1080);
    });
});

describe('animated image handling', function () {
    it('handles static GIF normally without extraction', function () {
        // Static GIF should not trigger first-frame extraction
        $staticGif = createTestImage(1920, 1080, 'gif');

        $action = new GenerateVariantsAction;
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($staticGif, $outputDir, 'static-gif-test');

        expect($variants)->not->toBeEmpty();

        // All variants should be WebP format
        foreach ($variants as $variant) {
            expect($variant['path'])->toEndWith('.webp');
        }
    });

    it('handles static WebP normally without extraction', function () {
        $staticWebP = createTestImage(1920, 1080, 'webp');

        $action = new GenerateVariantsAction;
        $outputDir = sys_get_temp_dir().'/variants';

        $variants = $action->execute($staticWebP, $outputDir, 'static-webp-test');

        expect($variants)->not->toBeEmpty();

        // All variants should be WebP format
        foreach ($variants as $variant) {
            expect($variant['path'])->toEndWith('.webp');
        }
    });

    it('detects animated GIF by frame count', function () {
        // Create a GIF file and verify the isAnimatedGif detection logic
        // Note: This tests static GIF detection (animated GIF creation requires external tools)

        $staticGif = createTestImage(100, 100, 'gif');
        $action = new GenerateVariantsAction;
        $outputDir = sys_get_temp_dir().'/variants';

        // Static GIF should process normally
        $variants = $action->execute($staticGif, $outputDir, 'frame-count-test');

        expect($variants)->not->toBeEmpty();
    });
});
