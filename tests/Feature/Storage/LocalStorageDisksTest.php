<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

it('can upload file to local-temp disk with temp/ prefix', function () {
    // Fake the local-temp scoped disk and its underlying local disk
    Storage::fake('local');
    Storage::fake('local-temp');

    // Upload a test file to the local-temp scoped disk
    $filename = 'test-file.txt';
    $content = 'Test content for temporary storage';

    Storage::disk('local-temp')->put($filename, $content);

    // Verify the file exists on the local-temp disk
    Storage::disk('local-temp')->assertExists($filename);

    // Verify the content is correct
    expect(Storage::disk('local-temp')->get($filename))->toBe($content);
});

it('verifies local-temp disk is configured as scoped disk', function () {
    // Get the disk configuration
    $config = config('filesystems.disks.local-temp');

    expect($config)->toHaveKey('driver', 'scoped')
        ->and($config)->toHaveKey('disk', 'local')
        ->and($config)->toHaveKey('prefix', 'temp')
        ->and($config)->toHaveKey('visibility', 'private')
        ->and($config)->toHaveKey('throw', true);
});

it('can upload file to local-permanent disk with permanent/ prefix', function () {
    // Fake the local-permanent scoped disk and its underlying local disk
    Storage::fake('local');
    Storage::fake('local-permanent');

    // Upload a test file to the local-permanent scoped disk
    $filename = 'permanent-file.txt';
    $content = 'Test content for permanent storage';

    Storage::disk('local-permanent')->put($filename, $content);

    // Verify the file exists on the local-permanent disk
    Storage::disk('local-permanent')->assertExists($filename);

    // Verify the content is correct
    expect(Storage::disk('local-permanent')->get($filename))->toBe($content);
});

it('verifies local-permanent disk is configured as scoped disk with public visibility', function () {
    // Get the disk configuration
    $config = config('filesystems.disks.local-permanent');

    expect($config)->toHaveKey('driver', 'scoped')
        ->and($config)->toHaveKey('disk', 'local')
        ->and($config)->toHaveKey('prefix', 'permanent')
        ->and($config)->toHaveKey('visibility', 'public')
        ->and($config)->toHaveKey('throw', true);
});

it('can move file from local-temp to local-permanent using copy-then-delete', function () {
    // Fake the local-temp and local-permanent scoped disks
    Storage::fake('local');
    Storage::fake('local-temp');
    Storage::fake('local-permanent');

    // Upload a test file to local-temp disk
    $tempPath = 'abc-123-uuid/original.txt';
    $content = 'Test content for moving between disks';
    Storage::disk('local-temp')->put($tempPath, $content);

    // Verify file exists on local-temp
    Storage::disk('local-temp')->assertExists($tempPath);

    // Move file from local-temp to local-permanent using copy-then-delete pattern
    $permanentPath = '2025/12/abc-123-uuid.txt';
    $contents = Storage::disk('local-temp')->get($tempPath);
    Storage::disk('local-permanent')->put($permanentPath, $contents);
    Storage::disk('local-temp')->delete($tempPath);

    // Verify file no longer exists on local-temp
    Storage::disk('local-temp')->assertMissing($tempPath);

    // Verify file exists on local-permanent
    Storage::disk('local-permanent')->assertExists($permanentPath);

    // Verify content is preserved
    expect(Storage::disk('local-permanent')->get($permanentPath))->toBe($content);
});

it('can move file using stream-based approach for large files', function () {
    // Fake the local-temp and local-permanent scoped disks
    Storage::fake('local');
    Storage::fake('local-temp');
    Storage::fake('local-permanent');

    // Upload a larger test file to local-temp disk
    $tempPath = 'def-456-uuid/large-original.bin';
    $content = str_repeat('Large file content chunk. ', 1000); // Simulate larger file
    Storage::disk('local-temp')->put($tempPath, $content);

    // Verify file exists on local-temp
    Storage::disk('local-temp')->assertExists($tempPath);

    // Move file using stream-based approach
    $permanentPath = '2025/12/def-456-uuid.bin';
    $stream = Storage::disk('local-temp')->readStream($tempPath);
    Storage::disk('local-permanent')->writeStream($permanentPath, $stream);
    Storage::disk('local-temp')->delete($tempPath);

    // Verify file no longer exists on local-temp
    Storage::disk('local-temp')->assertMissing($tempPath);

    // Verify file exists on local-permanent
    Storage::disk('local-permanent')->assertExists($permanentPath);

    // Verify content is preserved
    expect(Storage::disk('local-permanent')->get($permanentPath))->toBe($content);
});

it('can get URL from local-permanent disk', function () {
    // Fake the local-permanent scoped disk
    Storage::fake('local');
    Storage::fake('local-permanent');

    // Upload a test file to local-permanent disk
    $filename = '2025/12/test-image.jpg';
    $content = 'Test image content';
    Storage::disk('local-permanent')->put($filename, $content);

    // Verify file exists
    Storage::disk('local-permanent')->assertExists($filename);

    // Get the URL (local disk returns relative path)
    $url = Storage::disk('local-permanent')->url($filename);

    // Verify URL is returned
    expect($url)->toBeString()
        ->and($url)->not()->toBeEmpty();
});

it('can delete files from local-temp disk', function () {
    // Fake the local-temp scoped disk
    Storage::fake('local');
    Storage::fake('local-temp');

    // Upload a test file
    $filename = 'deletable-file.txt';
    Storage::disk('local-temp')->put($filename, 'Content to delete');

    // Verify it exists
    Storage::disk('local-temp')->assertExists($filename);

    // Delete using the scoped disk
    Storage::disk('local-temp')->delete($filename);

    // Verify it's deleted
    Storage::disk('local-temp')->assertMissing($filename);
});

it('can delete files from local-permanent disk', function () {
    // Fake the local-permanent scoped disk
    Storage::fake('local');
    Storage::fake('local-permanent');

    // Upload a test file
    $filename = '2025/12/deletable-permanent.txt';
    Storage::disk('local-permanent')->put($filename, 'Permanent content to delete');

    // Verify it exists
    Storage::disk('local-permanent')->assertExists($filename);

    // Delete using the scoped disk
    Storage::disk('local-permanent')->delete($filename);

    // Verify it's deleted
    Storage::disk('local-permanent')->assertMissing($filename);
});

it('can list files from local-temp disk with scoped prefix', function () {
    // Fake the local-temp scoped disk
    Storage::fake('local');
    Storage::fake('local-temp');

    // Upload multiple test files to local-temp
    Storage::disk('local-temp')->put('file1.txt', 'Content 1');
    Storage::disk('local-temp')->put('file2.txt', 'Content 2');
    Storage::disk('local-temp')->put('nested/file3.txt', 'Content 3');

    // List files on the local-temp disk (should return relative paths without prefix)
    $files = Storage::disk('local-temp')->allFiles();

    expect($files)->toContain('file1.txt')
        ->and($files)->toContain('file2.txt')
        ->and($files)->toContain('nested/file3.txt');

    // Verify they exist on the scoped disk
    Storage::disk('local-temp')->assertExists('file1.txt');
    Storage::disk('local-temp')->assertExists('file2.txt');
    Storage::disk('local-temp')->assertExists('nested/file3.txt');
});

it('can list files from local-permanent disk with scoped prefix', function () {
    // Fake the local-permanent scoped disk
    Storage::fake('local');
    Storage::fake('local-permanent');

    // Upload multiple test files to local-permanent
    Storage::disk('local-permanent')->put('2025/12/file1.jpg', 'Image 1');
    Storage::disk('local-permanent')->put('2025/12/file2.jpg', 'Image 2');
    Storage::disk('local-permanent')->put('2025/11/file3.pdf', 'Document 3');

    // List files on the local-permanent disk
    $files = Storage::disk('local-permanent')->allFiles();

    expect($files)->toContain('2025/12/file1.jpg')
        ->and($files)->toContain('2025/12/file2.jpg')
        ->and($files)->toContain('2025/11/file3.pdf');

    // Verify they exist on the scoped disk
    Storage::disk('local-permanent')->assertExists('2025/12/file1.jpg');
    Storage::disk('local-permanent')->assertExists('2025/12/file2.jpg');
    Storage::disk('local-permanent')->assertExists('2025/11/file3.pdf');
});

it('preserves file content after cross-disk move', function () {
    // Fake the local-temp and local-permanent scoped disks
    Storage::fake('local');
    Storage::fake('local-temp');
    Storage::fake('local-permanent');

    // Upload a file with specific content including special characters
    $tempPath = 'ghi-789-uuid/document.pdf';
    $content = 'PDF binary content simulation with special characters: éàü©®™';
    Storage::disk('local-temp')->put($tempPath, $content);

    // Move the file
    $permanentPath = '2025/12/ghi-789-uuid.pdf';
    $contents = Storage::disk('local-temp')->get($tempPath);
    Storage::disk('local-permanent')->put($permanentPath, $contents);
    Storage::disk('local-temp')->delete($tempPath);

    // Verify exact content match
    $movedContent = Storage::disk('local-permanent')->get($permanentPath);
    expect($movedContent)->toBe($content)
        ->and(strlen($movedContent))->toBe(strlen($content));
});

it('can move multiple files from local-temp to local-permanent in batch', function () {
    // Fake the local-temp and local-permanent scoped disks
    Storage::fake('local');
    Storage::fake('local-temp');
    Storage::fake('local-permanent');

    // Upload multiple test files to local-temp
    $files = [
        'uuid-1/image1.jpg' => 'Image 1 content',
        'uuid-2/image2.jpg' => 'Image 2 content',
        'uuid-3/document.pdf' => 'PDF content',
    ];

    foreach ($files as $tempPath => $content) {
        Storage::disk('local-temp')->put($tempPath, $content);
        Storage::disk('local-temp')->assertExists($tempPath);
    }

    // Move all files to permanent storage
    $moves = [
        'uuid-1/image1.jpg' => '2025/12/uuid-1.jpg',
        'uuid-2/image2.jpg' => '2025/12/uuid-2.jpg',
        'uuid-3/document.pdf' => '2025/12/uuid-3.pdf',
    ];

    foreach ($moves as $tempPath => $permanentPath) {
        $contents = Storage::disk('local-temp')->get($tempPath);
        Storage::disk('local-permanent')->put($permanentPath, $contents);
        Storage::disk('local-temp')->delete($tempPath);
    }

    // Verify all files are moved
    foreach ($moves as $tempPath => $permanentPath) {
        Storage::disk('local-temp')->assertMissing($tempPath);
        Storage::disk('local-permanent')->assertExists($permanentPath);
        expect(Storage::disk('local-permanent')->get($permanentPath))
            ->toBe($files[$tempPath]);
    }
});

it('can check file existence on local-temp disk', function () {
    // Fake the local-temp scoped disk
    Storage::fake('local');
    Storage::fake('local-temp');

    $filename = 'existence-test.txt';

    // File shouldn't exist initially
    expect(Storage::disk('local-temp')->exists($filename))->toBeFalse();

    // Upload the file
    Storage::disk('local-temp')->put($filename, 'Test content');

    // File should now exist on scoped disk
    expect(Storage::disk('local-temp')->exists($filename))->toBeTrue();
});

it('can check file existence on local-permanent disk', function () {
    // Fake the local-permanent scoped disk
    Storage::fake('local');
    Storage::fake('local-permanent');

    $filename = '2025/12/existence-test.jpg';

    // File shouldn't exist initially
    expect(Storage::disk('local-permanent')->exists($filename))->toBeFalse();

    // Upload the file
    Storage::disk('local-permanent')->put($filename, 'Test image');

    // File should now exist on scoped disk
    expect(Storage::disk('local-permanent')->exists($filename))->toBeTrue();
});

it('verifies file existence before attempting move operation', function () {
    // Fake the local-temp and local-permanent scoped disks
    Storage::fake('local');
    Storage::fake('local-temp');
    Storage::fake('local-permanent');

    $tempPath = 'non-existent-uuid/missing.txt';

    // Verify file doesn't exist on local-temp
    expect(Storage::disk('local-temp')->exists($tempPath))->toBeFalse();

    // Verify assertMissing works correctly
    Storage::disk('local-temp')->assertMissing($tempPath);
});
