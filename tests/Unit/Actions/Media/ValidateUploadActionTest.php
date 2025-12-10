<?php

declare(strict_types=1);

use App\Actions\Media\ValidateUploadAction;
use App\DTOs\ValidationResult;
use App\Enums\MediaType;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->action = new ValidateUploadAction;
});

describe('MIME type validation', function () {
    it('validates a valid jpeg upload', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080)->mimeType('image/jpeg');

        $result = $this->action->execute($file);

        expect($result)->toBeInstanceOf(ValidationResult::class)
            ->and($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Image)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions)->toBeArray()
            ->and($result->dimensions['width'])->toBe(1920)
            ->and($result->dimensions['height'])->toBe(1080);
    });

    it('validates a valid png upload', function () {
        $file = UploadedFile::fake()->image('test.png', 800, 600)->mimeType('image/png');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Image)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions['width'])->toBe(800)
            ->and($result->dimensions['height'])->toBe(600);
    });

    it('validates a valid gif upload', function () {
        $file = UploadedFile::fake()->image('test.gif', 640, 480)->mimeType('image/gif');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Image)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions['width'])->toBe(640)
            ->and($result->dimensions['height'])->toBe(480);
    });

    it('validates a valid webp upload', function () {
        $file = UploadedFile::fake()->image('test.webp', 1440, 900)->mimeType('image/webp');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Image)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions['width'])->toBe(1440)
            ->and($result->dimensions['height'])->toBe(900);
    });

    it('validates a valid mp4 video upload', function () {
        $file = UploadedFile::fake()->create('test.mp4', 10000, 'video/mp4');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Video)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions)->toBeNull();
    });

    it('validates a valid webm video upload', function () {
        $file = UploadedFile::fake()->create('test.webm', 8000, 'video/webm');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Video)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions)->toBeNull();
    });

    it('validates a valid quicktime video upload', function () {
        $file = UploadedFile::fake()->create('test.mov', 12000, 'video/quicktime');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Video)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions)->toBeNull();
    });

    it('validates a valid svg upload', function () {
        $file = UploadedFile::fake()->create('test.svg', 100, 'image/svg+xml');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Svg)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions)->toBeNull();
    });

    it('rejects files with invalid mime types', function (string $mimeType) {
        $file = UploadedFile::fake()->create('test.exe', 100, $mimeType);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->mediaType)->toBeNull()
            ->and($result->error)->toContain('MIME type')
            ->and($result->dimensions)->toBeNull();
    })->with([
        'executable' => 'application/x-msdownload',
        'zip archive' => 'application/zip',
        'word document' => 'application/msword',
        'pdf document' => 'application/pdf',
        'text file' => 'text/plain',
        'json file' => 'application/json',
    ]);
});

describe('file size validation', function () {
    it('rejects files exceeding 20MB limit', function () {
        // 20MB + 1 byte
        $file = UploadedFile::fake()->create('test.jpg', 20481, 'image/jpeg');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->toMatch('/size|20MB|exceed/i');
    });

    it('accepts files at exactly 20MB limit', function () {
        // Exactly 20MB (20971520 bytes = 20480 KB)
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080)->size(20480);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->error)->toBeNull();
    });

    it('accepts files below 20MB limit', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080)->size(10240);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->error)->toBeNull();
    });

    it('rejects empty files (0 bytes)', function () {
        $file = UploadedFile::fake()->create('test.jpg', 0, 'image/jpeg');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->not->toBeNull();
    });
});

describe('image dimension validation', function () {
    it('rejects images with width below 50px', function () {
        $file = UploadedFile::fake()->image('test.jpg', 49, 1080);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->toMatch('/width|dimension|50|minimum/i');
    });

    it('rejects images with height below 50px', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 49);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->toMatch('/height|dimension|50|minimum/i');
    });

    it('rejects images with width above 16000px', function () {
        $file = UploadedFile::fake()->image('test.jpg', 16001, 1080);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->toMatch('/width|dimension|16000|maximum/i');
    });

    it('rejects images with height above 16000px', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 16001);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->toMatch('/height|dimension|16000|maximum/i');
    });

    it('accepts images at minimum dimensions (50x50)', function () {
        $file = UploadedFile::fake()->image('test.jpg', 50, 50);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->error)->toBeNull()
            ->and($result->dimensions['width'])->toBe(50)
            ->and($result->dimensions['height'])->toBe(50);
    });

    it('accepts images at maximum dimensions (16000x16000)', function () {
        $file = UploadedFile::fake()->image('test.jpg', 16000, 16000);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->error)->toBeNull()
            ->and($result->dimensions['width'])->toBe(16000)
            ->and($result->dimensions['height'])->toBe(16000);
    });

    it('accepts images within valid dimension range', function (int $width, int $height) {
        $file = UploadedFile::fake()->image('test.jpg', $width, $height);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->error)->toBeNull()
            ->and($result->dimensions['width'])->toBe($width)
            ->and($result->dimensions['height'])->toBe($height);
    })->with([
        'small image' => [100, 100],
        'typical photo' => [1920, 1080],
        'portrait' => [1080, 1920],
        'wide banner' => [3000, 800],
        'tall banner' => [800, 3000],
        'square large' => [5000, 5000],
    ]);

    it('validates dimensions are integers', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->dimensions['width'])->toBeInt()
            ->and($result->dimensions['height'])->toBeInt();
    });
});

describe('magic bytes validation', function () {
    it('rejects files with mismatched magic bytes for jpg extension', function () {
        // Create a file with PNG magic bytes but .jpg extension
        $pngMagicBytes = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
        $fakeContent = $pngMagicBytes.str_repeat("\x00", 100);

        $file = UploadedFile::fake()->createWithContent('test.jpg', $fakeContent);

        $result = $this->action->execute($file);

        // Should fail because magic bytes don't match claimed MIME type
        expect($result->valid)->toBeFalse()
            ->and($result->error)->toMatch('/magic bytes|file type|mismatch|invalid/i');
    });

    it('rejects files with mismatched magic bytes for png extension', function () {
        // Create a file with JPEG magic bytes but .png extension
        $jpegMagicBytes = "\xFF\xD8\xFF";
        $fakeContent = $jpegMagicBytes.str_repeat("\x00", 100);

        $file = UploadedFile::fake()->createWithContent('test.png', $fakeContent);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->toMatch('/magic bytes|file type|mismatch|invalid/i');
    });

    it('validates correct magic bytes for jpeg files', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->error)->toBeNull();
    });

    it('validates correct magic bytes for png files', function () {
        $file = UploadedFile::fake()->image('test.png', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->error)->toBeNull();
    });
});

describe('edge cases', function () {
    it('rejects both dimensions invalid simultaneously', function () {
        $file = UploadedFile::fake()->image('test.jpg', 20, 30);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->not->toBeNull();
    });

    it('handles very small valid images', function () {
        $file = UploadedFile::fake()->image('test.jpg', 51, 52);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->dimensions['width'])->toBe(51)
            ->and($result->dimensions['height'])->toBe(52);
    });

    it('handles very large valid images', function () {
        $file = UploadedFile::fake()->image('test.jpg', 15999, 15998);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->dimensions['width'])->toBe(15999)
            ->and($result->dimensions['height'])->toBe(15998);
    });

    it('validates video files do not check dimensions', function () {
        $file = UploadedFile::fake()->create('test.mp4', 5000, 'video/mp4');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Video)
            ->and($result->dimensions)->toBeNull();
    });

    it('validates svg files do not check dimensions', function () {
        $file = UploadedFile::fake()->create('test.svg', 100, 'image/svg+xml');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue()
            ->and($result->mediaType)->toBe(MediaType::Svg)
            ->and($result->dimensions)->toBeNull();
    });

    it('handles multiple validation failures and reports first error', function () {
        // Too large file size AND invalid dimensions
        $file = UploadedFile::fake()->image('test.jpg', 20, 30)->size(25000);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse()
            ->and($result->error)->not->toBeNull()
            ->and($result->error)->toBeString();
    });
});

describe('ValidationResult DTO', function () {
    it('returns ValidationResult with all required fields for valid upload', function () {
        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result)->toHaveProperties(['valid', 'mediaType', 'error', 'dimensions'])
            ->and($result->valid)->toBeBool()
            ->and($result->mediaType)->toBeInstanceOf(MediaType::class)
            ->and($result->error)->toBeNull()
            ->and($result->dimensions)->toBeArray();
    });

    it('returns ValidationResult with all required fields for invalid upload', function () {
        $file = UploadedFile::fake()->create('test.exe', 100, 'application/x-msdownload');

        $result = $this->action->execute($file);

        expect($result)->toHaveProperties(['valid', 'mediaType', 'error', 'dimensions'])
            ->and($result->valid)->toBeBool()
            ->and($result->mediaType)->toBeNull()
            ->and($result->error)->toBeString()
            ->and($result->dimensions)->toBeNull();
    });
});

describe('configuration integration', function () {
    it('uses max_upload_size from config', function () {
        config(['media.max_upload_size' => 20971520]);

        $file = UploadedFile::fake()->create('test.jpg', 20481, 'image/jpeg');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse();
    });

    it('uses min_dimensions from config', function () {
        config(['media.min_dimensions' => 50]);

        $file = UploadedFile::fake()->image('test.jpg', 49, 100);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse();
    });

    it('uses max_dimensions from config', function () {
        config(['media.max_dimensions' => 16000]);

        $file = UploadedFile::fake()->image('test.jpg', 16001, 1000);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeFalse();
    });

    it('uses allowed_image_mimes from config', function () {
        config(['media.allowed_image_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']]);

        $file = UploadedFile::fake()->image('test.jpg', 1920, 1080);

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue();
    });

    it('uses allowed_video_mimes from config', function () {
        config(['media.allowed_video_mimes' => ['video/mp4', 'video/webm', 'video/quicktime']]);

        $file = UploadedFile::fake()->create('test.mp4', 5000, 'video/mp4');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue();
    });

    it('uses allowed_svg_mimes from config', function () {
        config(['media.allowed_svg_mimes' => ['image/svg+xml']]);

        $file = UploadedFile::fake()->create('test.svg', 100, 'image/svg+xml');

        $result = $this->action->execute($file);

        expect($result->valid)->toBeTrue();
    });
});
