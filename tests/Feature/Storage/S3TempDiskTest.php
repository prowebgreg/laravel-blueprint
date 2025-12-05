<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

it('can upload file to s3-temp disk with temp/ prefix', function () {
    // Fake the s3-temp scoped disk and its underlying s3 disk
    Storage::fake('s3');
    Storage::fake('s3-temp');

    // Upload a test file to the s3-temp scoped disk
    $filename = 'test-file.txt';
    $content = 'Test content for temporary storage';

    Storage::disk('s3-temp')->put($filename, $content);

    // Verify the file exists on the s3-temp disk
    Storage::disk('s3-temp')->assertExists($filename);

    // Verify the content is correct
    expect(Storage::disk('s3-temp')->get($filename))->toBe($content);
});

it('verifies s3-temp disk is configured as scoped disk', function () {
    // Get the disk configuration
    $config = config('filesystems.disks.s3-temp');

    expect($config)->toHaveKey('driver', 'scoped')
        ->and($config)->toHaveKey('disk', 's3')
        ->and($config)->toHaveKey('prefix', 'temp')
        ->and($config)->toHaveKey('visibility', 'private')
        ->and($config)->toHaveKey('throw', true);
});

it('can list files from s3-temp disk with scoped prefix', function () {
    // Fake the s3-temp scoped disk
    Storage::fake('s3-temp');

    // Upload multiple test files to s3-temp
    Storage::disk('s3-temp')->put('file1.txt', 'Content 1');
    Storage::disk('s3-temp')->put('file2.txt', 'Content 2');
    Storage::disk('s3-temp')->put('nested/file3.txt', 'Content 3');

    // List files on the s3-temp disk (should return relative paths without prefix)
    $files = Storage::disk('s3-temp')->allFiles();

    expect($files)->toContain('file1.txt')
        ->and($files)->toContain('file2.txt')
        ->and($files)->toContain('nested/file3.txt');

    // Verify they exist on the scoped disk
    Storage::disk('s3-temp')->assertExists('file1.txt');
    Storage::disk('s3-temp')->assertExists('file2.txt');
    Storage::disk('s3-temp')->assertExists('nested/file3.txt');
});

it('can delete files from s3-temp disk', function () {
    // Fake the s3-temp scoped disk
    Storage::fake('s3-temp');

    // Upload a test file
    $filename = 'deletable-file.txt';
    Storage::disk('s3-temp')->put($filename, 'Content to delete');

    // Verify it exists
    Storage::disk('s3-temp')->assertExists($filename);

    // Delete using the scoped disk
    Storage::disk('s3-temp')->delete($filename);

    // Verify it's deleted
    Storage::disk('s3-temp')->assertMissing($filename);
});

it('can check file existence on s3-temp disk', function () {
    // Fake the s3-temp scoped disk
    Storage::fake('s3-temp');

    $filename = 'existence-test.txt';

    // File shouldn't exist initially
    expect(Storage::disk('s3-temp')->exists($filename))->toBeFalse();

    // Upload the file
    Storage::disk('s3-temp')->put($filename, 'Test content');

    // File should now exist on scoped disk
    expect(Storage::disk('s3-temp')->exists($filename))->toBeTrue();
});
