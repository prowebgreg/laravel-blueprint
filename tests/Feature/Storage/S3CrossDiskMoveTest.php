<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

it('can move file from s3-temp to s3-permanent using copy-then-delete', function () {
    // Fake the s3-temp and s3-permanent scoped disks
    Storage::fake('s3');
    Storage::fake('s3-temp');
    Storage::fake('s3-permanent');

    // Upload a test file to s3-temp disk
    $tempPath = 'abc-123-uuid/original.txt';
    $content = 'Test content for moving between disks';
    Storage::disk('s3-temp')->put($tempPath, $content);

    // Verify file exists on s3-temp
    Storage::disk('s3-temp')->assertExists($tempPath);

    // Move file from s3-temp to s3-permanent using copy-then-delete pattern
    $permanentPath = '2025/12/abc-123-uuid.txt';
    $contents = Storage::disk('s3-temp')->get($tempPath);
    Storage::disk('s3-permanent')->put($permanentPath, $contents);
    Storage::disk('s3-temp')->delete($tempPath);

    // Verify file no longer exists on s3-temp
    Storage::disk('s3-temp')->assertMissing($tempPath);

    // Verify file exists on s3-permanent
    Storage::disk('s3-permanent')->assertExists($permanentPath);

    // Verify content is preserved
    expect(Storage::disk('s3-permanent')->get($permanentPath))->toBe($content);
});

it('can move file using stream-based approach for large files', function () {
    // Fake the s3-temp and s3-permanent scoped disks
    Storage::fake('s3');
    Storage::fake('s3-temp');
    Storage::fake('s3-permanent');

    // Upload a larger test file to s3-temp disk
    $tempPath = 'def-456-uuid/large-original.bin';
    $content = str_repeat('Large file content chunk. ', 1000); // Simulate larger file
    Storage::disk('s3-temp')->put($tempPath, $content);

    // Verify file exists on s3-temp
    Storage::disk('s3-temp')->assertExists($tempPath);

    // Move file using stream-based approach
    $permanentPath = '2025/12/def-456-uuid.bin';
    $stream = Storage::disk('s3-temp')->readStream($tempPath);
    Storage::disk('s3-permanent')->writeStream($permanentPath, $stream);
    Storage::disk('s3-temp')->delete($tempPath);

    // Verify file no longer exists on s3-temp
    Storage::disk('s3-temp')->assertMissing($tempPath);

    // Verify file exists on s3-permanent
    Storage::disk('s3-permanent')->assertExists($permanentPath);

    // Verify content is preserved
    expect(Storage::disk('s3-permanent')->get($permanentPath))->toBe($content);
});

it('preserves file content after cross-disk move', function () {
    // Fake the s3-temp and s3-permanent scoped disks
    Storage::fake('s3');
    Storage::fake('s3-temp');
    Storage::fake('s3-permanent');

    // Upload a file with specific content
    $tempPath = 'ghi-789-uuid/document.pdf';
    $content = 'PDF binary content simulation with special characters: éàü©®™';
    Storage::disk('s3-temp')->put($tempPath, $content);

    // Move the file
    $permanentPath = '2025/12/ghi-789-uuid.pdf';
    $contents = Storage::disk('s3-temp')->get($tempPath);
    Storage::disk('s3-permanent')->put($permanentPath, $contents);
    Storage::disk('s3-temp')->delete($tempPath);

    // Verify exact content match
    $movedContent = Storage::disk('s3-permanent')->get($permanentPath);
    expect($movedContent)->toBe($content)
        ->and(strlen($movedContent))->toBe(strlen($content));
});

it('verifies s3-permanent disk is configured as scoped disk with public visibility', function () {
    // Get the disk configuration
    $config = config('filesystems.disks.s3-permanent');

    expect($config)->toHaveKey('driver', 'scoped')
        ->and($config)->toHaveKey('disk', 's3')
        ->and($config)->toHaveKey('prefix', 'permanent')
        ->and($config)->toHaveKey('visibility', 'public')
        ->and($config)->toHaveKey('throw', true);
});

it('verifies file existence before attempting move operation', function () {
    // Fake the s3-temp and s3-permanent scoped disks
    Storage::fake('s3');
    Storage::fake('s3-temp');
    Storage::fake('s3-permanent');

    $tempPath = 'non-existent-uuid/missing.txt';

    // Verify file doesn't exist on s3-temp
    expect(Storage::disk('s3-temp')->exists($tempPath))->toBeFalse();

    // Verify assertMissing works correctly
    Storage::disk('s3-temp')->assertMissing($tempPath);
});

it('can move multiple files from s3-temp to s3-permanent in batch', function () {
    // Fake the s3-temp and s3-permanent scoped disks
    Storage::fake('s3');
    Storage::fake('s3-temp');
    Storage::fake('s3-permanent');

    // Upload multiple test files to s3-temp
    $files = [
        'uuid-1/image1.jpg' => 'Image 1 content',
        'uuid-2/image2.jpg' => 'Image 2 content',
        'uuid-3/document.pdf' => 'PDF content',
    ];

    foreach ($files as $tempPath => $content) {
        Storage::disk('s3-temp')->put($tempPath, $content);
        Storage::disk('s3-temp')->assertExists($tempPath);
    }

    // Move all files to permanent storage
    $moves = [
        'uuid-1/image1.jpg' => '2025/12/uuid-1.jpg',
        'uuid-2/image2.jpg' => '2025/12/uuid-2.jpg',
        'uuid-3/document.pdf' => '2025/12/uuid-3.pdf',
    ];

    foreach ($moves as $tempPath => $permanentPath) {
        $contents = Storage::disk('s3-temp')->get($tempPath);
        Storage::disk('s3-permanent')->put($permanentPath, $contents);
        Storage::disk('s3-temp')->delete($tempPath);
    }

    // Verify all files are moved
    foreach ($moves as $tempPath => $permanentPath) {
        Storage::disk('s3-temp')->assertMissing($tempPath);
        Storage::disk('s3-permanent')->assertExists($permanentPath);
        expect(Storage::disk('s3-permanent')->get($permanentPath))
            ->toBe($files[$tempPath]);
    }
});
