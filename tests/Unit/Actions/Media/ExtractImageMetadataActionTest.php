<?php

declare(strict_types=1);

use App\Actions\Media\ExtractImageMetadataAction;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->action = new ExtractImageMetadataAction;
});

describe('JPEG image metadata extraction', function () {
    it('extracts dimensions from valid jpeg image', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['width', 'height'])
            ->and($result['width'])->toBe(1920)
            ->and($result['height'])->toBe(1080)
            ->and($result['width'])->toBeInt()
            ->and($result['height'])->toBeInt();
    });

    it('extracts dimensions from jpeg with jpg extension', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(800)
            ->and($result['height'])->toBe(600);
    });

    it('extracts dimensions from jpeg with jpeg extension', function () {
        $file = UploadedFile::fake()->image('photo.jpeg', 1024, 768);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(1024)
            ->and($result['height'])->toBe(768);
    });
});

describe('PNG image metadata extraction', function () {
    it('extracts dimensions from valid png image', function () {
        $file = UploadedFile::fake()->image('test.png', 640, 480);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['width', 'height'])
            ->and($result['width'])->toBe(640)
            ->and($result['height'])->toBe(480)
            ->and($result['width'])->toBeInt()
            ->and($result['height'])->toBeInt();
    });

    it('extracts dimensions from transparent png', function () {
        $file = UploadedFile::fake()->image('transparent.png', 1200, 900);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(1200)
            ->and($result['height'])->toBe(900);
    });
});

describe('GIF image metadata extraction', function () {
    it('extracts dimensions from valid gif image', function () {
        $file = UploadedFile::fake()->image('animation.gif', 500, 300);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['width', 'height'])
            ->and($result['width'])->toBe(500)
            ->and($result['height'])->toBe(300)
            ->and($result['width'])->toBeInt()
            ->and($result['height'])->toBeInt();
    });

    it('extracts dimensions from static gif', function () {
        $file = UploadedFile::fake()->image('static.gif', 256, 256);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(256)
            ->and($result['height'])->toBe(256);
    });
});

describe('WebP image metadata extraction', function () {
    it('extracts dimensions from valid webp image', function () {
        $file = UploadedFile::fake()->image('modern.webp', 1440, 900);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['width', 'height'])
            ->and($result['width'])->toBe(1440)
            ->and($result['height'])->toBe(900)
            ->and($result['width'])->toBeInt()
            ->and($result['height'])->toBeInt();
    });

    it('extracts dimensions from webp with transparency', function () {
        $file = UploadedFile::fake()->image('transparent.webp', 2000, 1500);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(2000)
            ->and($result['height'])->toBe(1500);
    });
});

describe('various image dimensions', function () {
    it('handles small images (50x50)', function () {
        $file = UploadedFile::fake()->image('tiny.jpg', 50, 50);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(50)
            ->and($result['height'])->toBe(50);
    });

    it('handles large images (16000x16000)', function () {
        $file = UploadedFile::fake()->image('huge.jpg', 16000, 16000);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(16000)
            ->and($result['height'])->toBe(16000);
    });

    it('handles portrait orientation images', function (int $width, int $height) {
        $file = UploadedFile::fake()->image('portrait.jpg', $width, $height);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe($width)
            ->and($result['height'])->toBe($height)
            ->and($result['height'])->toBeGreaterThan($result['width']);
    })->with([
        'mobile portrait' => [1080, 1920],
        'tall banner' => [800, 3000],
        'vertical poster' => [1200, 2400],
    ]);

    it('handles landscape orientation images', function (int $width, int $height) {
        $file = UploadedFile::fake()->image('landscape.jpg', $width, $height);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe($width)
            ->and($result['height'])->toBe($height)
            ->and($result['width'])->toBeGreaterThan($result['height']);
    })->with([
        'standard HD' => [1920, 1080],
        'wide banner' => [3000, 800],
        'ultra-wide' => [3440, 1440],
    ]);

    it('handles square images', function (int $dimension) {
        $file = UploadedFile::fake()->image('square.jpg', $dimension, $dimension);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe($dimension)
            ->and($result['height'])->toBe($dimension)
            ->and($result['width'])->toBe($result['height']);
    })->with([
        'tiny square' => 100,
        'profile pic' => 512,
        'large square' => 2048,
        'max square' => 5000,
    ]);

    it('handles extreme aspect ratios', function (int $width, int $height) {
        $file = UploadedFile::fake()->image('extreme.jpg', $width, $height);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe($width)
            ->and($result['height'])->toBe($height);
    })->with([
        'very wide' => [5000, 100],
        'very tall' => [100, 5000],
        'cinema wide' => [3840, 1600],
    ]);
});

describe('non-image file handling', function () {
    it('returns null for mp4 video files', function () {
        $file = UploadedFile::fake()->create('video.mp4', 10000, 'video/mp4');

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    });

    it('returns null for webm video files', function () {
        $file = UploadedFile::fake()->create('video.webm', 8000, 'video/webm');

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    });

    it('returns null for mov video files', function () {
        $file = UploadedFile::fake()->create('video.mov', 12000, 'video/quicktime');

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    });

    it('returns null for svg files', function () {
        $file = UploadedFile::fake()->create('icon.svg', 100, 'image/svg+xml');

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    });

    it('returns null for all video formats', function (string $filename, string $mimeType) {
        $file = UploadedFile::fake()->create($filename, 5000, $mimeType);

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    })->with([
        'mp4' => ['test.mp4', 'video/mp4'],
        'webm' => ['test.webm', 'video/webm'],
        'quicktime' => ['test.mov', 'video/quicktime'],
        'avi' => ['test.avi', 'video/x-msvideo'],
    ]);
});

describe('corrupted and invalid file handling', function () {
    it('handles empty files gracefully', function () {
        $file = UploadedFile::fake()->create('empty.jpg', 0, 'image/jpeg');

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    });

    it('handles files with image extension but invalid content', function () {
        $fakeContent = str_repeat("\x00", 100);
        $file = UploadedFile::fake()->createWithContent('fake.jpg', $fakeContent);

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    });

    it('handles corrupted jpeg files', function () {
        // JPEG magic bytes but corrupted structure
        $corruptedContent = "\xFF\xD8\xFF".str_repeat("\x00", 100);
        $file = UploadedFile::fake()->createWithContent('corrupted.jpg', $corruptedContent);

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    });

    it('handles corrupted png files', function () {
        // PNG magic bytes but corrupted structure
        $corruptedContent = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A".str_repeat("\x00", 100);
        $file = UploadedFile::fake()->createWithContent('corrupted.png', $corruptedContent);

        $result = $this->action->execute($file);

        expect($result)->toBeNull();
    });

    it('handles files with mismatched extension and content', function () {
        // PNG content with JPEG extension
        $pngMagicBytes = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
        $file = UploadedFile::fake()->createWithContent('mismatch.jpg', $pngMagicBytes.str_repeat("\x00", 100));

        $result = $this->action->execute($file);

        // Should attempt to read and either succeed (if GD can handle) or return null
        expect($result)->toBeNull();
    });
});

describe('edge cases', function () {
    it('handles 1x1 pixel images', function () {
        $file = UploadedFile::fake()->image('single-pixel.jpg', 1, 1);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(1)
            ->and($result['height'])->toBe(1);
    });

    it('handles extremely wide images (panorama)', function () {
        $file = UploadedFile::fake()->image('panorama.jpg', 10000, 500);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(10000)
            ->and($result['height'])->toBe(500);
    });

    it('handles extremely tall images (vertical panorama)', function () {
        $file = UploadedFile::fake()->image('vertical-pano.jpg', 500, 10000);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(500)
            ->and($result['height'])->toBe(10000);
    });

    it('validates both dimensions are positive integers', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBeInt()
            ->and($result['height'])->toBeInt()
            ->and($result['width'])->toBeGreaterThan(0)
            ->and($result['height'])->toBeGreaterThan(0);
    });

    it('handles files with uppercase extensions', function () {
        $file = UploadedFile::fake()->image('TEST.JPG', 800, 600);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(800)
            ->and($result['height'])->toBe(600);
    });

    it('handles files with mixed case extensions', function () {
        $file = UploadedFile::fake()->image('test.PnG', 1024, 768);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe(1024)
            ->and($result['height'])->toBe(768);
    });
});

describe('return value structure', function () {
    it('returns array with exactly two keys for valid images', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['width', 'height'])
            ->and($result)->toHaveCount(2);
    });

    it('returns null for non-images not an empty array', function () {
        $file = UploadedFile::fake()->create('video.mp4', 5000, 'video/mp4');

        $result = $this->action->execute($file);

        expect($result)->toBeNull()
            ->and($result)->not->toBeArray();
    });

    it('dimensions are integers not strings', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBeInt()
            ->and($result['height'])->toBeInt()
            ->and($result['width'])->not->toBeString()
            ->and($result['height'])->not->toBeString();
    });

    it('returns consistent structure across multiple calls', function () {
        $file1 = UploadedFile::fake()->image('test1.jpg', 800, 600);
        $file2 = UploadedFile::fake()->image('test2.png', 1024, 768);
        $file3 = UploadedFile::fake()->image('test3.webp', 1920, 1080);

        $result1 = $this->action->execute($file1);
        $result2 = $this->action->execute($file2);
        $result3 = $this->action->execute($file3);

        expect($result1)->toHaveKeys(['width', 'height'])
            ->and($result2)->toHaveKeys(['width', 'height'])
            ->and($result3)->toHaveKeys(['width', 'height'])
            ->and($result1)->toHaveCount(2)
            ->and($result2)->toHaveCount(2)
            ->and($result3)->toHaveCount(2);
    });
});

describe('all supported formats comprehensive test', function () {
    it('extracts metadata from all supported image formats', function (string $filename, string $mimeType, int $width, int $height) {
        $file = UploadedFile::fake()->image($filename, $width, $height)->mimeType($mimeType);

        $result = $this->action->execute($file);

        expect($result)->toBeArray()
            ->and($result['width'])->toBe($width)
            ->and($result['height'])->toBe($height)
            ->and($result['width'])->toBeInt()
            ->and($result['height'])->toBeInt();
    })->with([
        'jpeg standard' => ['photo.jpg', 'image/jpeg', 1920, 1080],
        'jpeg alternate ext' => ['photo.jpeg', 'image/jpeg', 1024, 768],
        'png' => ['graphic.png', 'image/png', 800, 600],
        'gif' => ['animation.gif', 'image/gif', 640, 480],
        'webp' => ['modern.webp', 'image/webp', 1440, 900],
    ]);
});
