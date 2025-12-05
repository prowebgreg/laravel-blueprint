<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

it('can delete file from s3-permanent disk', function () {
    // Fake the s3-permanent scoped disk
    Storage::fake('s3');
    Storage::fake('s3-permanent');

    // Upload a test file to the s3-permanent disk
    $filename = '2025/12/test-file-to-delete.txt';
    $content = 'Test content for permanent storage deletion';

    Storage::disk('s3-permanent')->put($filename, $content);

    // Verify the file exists
    Storage::disk('s3-permanent')->assertExists($filename);

    // Delete the file from s3-permanent disk
    $deleted = Storage::disk('s3-permanent')->delete($filename);

    // Verify the delete operation succeeded
    expect($deleted)->toBeTrue();

    // Verify the file no longer exists
    Storage::disk('s3-permanent')->assertMissing($filename);
});

it('can delete multiple files from s3-permanent disk', function () {
    // Fake the s3-permanent scoped disk
    Storage::fake('s3');
    Storage::fake('s3-permanent');

    // Upload multiple test files to s3-permanent
    $files = [
        '2025/12/file1.txt' => 'Content 1',
        '2025/12/file2.txt' => 'Content 2',
        '2025/12/nested/file3.txt' => 'Content 3',
    ];

    foreach ($files as $path => $content) {
        Storage::disk('s3-permanent')->put($path, $content);
        Storage::disk('s3-permanent')->assertExists($path);
    }

    // Delete all files
    $deleted = Storage::disk('s3-permanent')->delete(array_keys($files));

    // Verify all files are deleted
    expect($deleted)->toBeTrue();

    foreach (array_keys($files) as $path) {
        Storage::disk('s3-permanent')->assertMissing($path);
    }
});

it('handles deletion of non-existent file on s3-permanent disk', function () {
    // Fake the s3-permanent scoped disk
    Storage::fake('s3');
    Storage::fake('s3-permanent');

    $nonExistentFile = '2025/12/non-existent-file.txt';

    // Verify file doesn't exist
    Storage::disk('s3-permanent')->assertMissing($nonExistentFile);

    // Attempt to delete non-existent file - should not throw exception
    Storage::disk('s3-permanent')->delete($nonExistentFile);

    // File should still be missing after delete attempt
    Storage::disk('s3-permanent')->assertMissing($nonExistentFile);
});

it('can delete and recreate file with same path on s3-permanent disk', function () {
    // Fake the s3-permanent scoped disk
    Storage::fake('s3');
    Storage::fake('s3-permanent');

    $filename = '2025/12/reusable-path.txt';
    $originalContent = 'Original content';
    $newContent = 'New content after deletion';

    // Upload original file
    Storage::disk('s3-permanent')->put($filename, $originalContent);
    Storage::disk('s3-permanent')->assertExists($filename);
    expect(Storage::disk('s3-permanent')->get($filename))->toBe($originalContent);

    // Delete the file
    Storage::disk('s3-permanent')->delete($filename);
    Storage::disk('s3-permanent')->assertMissing($filename);

    // Upload new file with same path
    Storage::disk('s3-permanent')->put($filename, $newContent);
    Storage::disk('s3-permanent')->assertExists($filename);

    // Verify new content is different
    expect(Storage::disk('s3-permanent')->get($filename))->toBe($newContent)
        ->and(Storage::disk('s3-permanent')->get($filename))->not->toBe($originalContent);
});

it('verifies file existence before and after deletion on s3-permanent disk', function () {
    // Fake the s3-permanent scoped disk
    Storage::fake('s3');
    Storage::fake('s3-permanent');

    $filename = '2025/12/existence-check.txt';

    // File shouldn't exist initially
    expect(Storage::disk('s3-permanent')->exists($filename))->toBeFalse();

    // Upload the file
    Storage::disk('s3-permanent')->put($filename, 'Test content');

    // File should now exist
    expect(Storage::disk('s3-permanent')->exists($filename))->toBeTrue();

    // Delete the file
    Storage::disk('s3-permanent')->delete($filename);

    // File should no longer exist
    expect(Storage::disk('s3-permanent')->exists($filename))->toBeFalse();
});

it('can delete files from nested directories on s3-permanent disk', function () {
    // Fake the s3-permanent scoped disk
    Storage::fake('s3');
    Storage::fake('s3-permanent');

    // Upload files in nested directories
    $nestedFiles = [
        '2025/12/01/image1.jpg' => 'Image content 1',
        '2025/12/02/documents/file.pdf' => 'PDF content',
        '2025/12/03/media/videos/clip.mp4' => 'Video content',
    ];

    foreach ($nestedFiles as $path => $content) {
        Storage::disk('s3-permanent')->put($path, $content);
        Storage::disk('s3-permanent')->assertExists($path);
    }

    // Delete each nested file
    foreach (array_keys($nestedFiles) as $path) {
        $deleted = Storage::disk('s3-permanent')->delete($path);
        expect($deleted)->toBeTrue();
        Storage::disk('s3-permanent')->assertMissing($path);
    }
});
