<?php

declare(strict_types=1);

use App\Http\Requests\UploadMediaRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

test('validates allowed image file types', function () {
    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    foreach ($allowedImageTypes as $type) {
        $file = UploadedFile::fake()->image("test.{$type}");
        $request = new UploadMediaRequest;

        $validator = Validator::make(['file' => $file], $request->rules());

        expect($validator->passes())->toBeTrue("Failed for type: {$type}");
    }
});

test('validates allowed svg file type', function () {
    $file = UploadedFile::fake()->create('test.svg', 100, 'image/svg+xml');
    $request = new UploadMediaRequest;

    $validator = Validator::make(['file' => $file], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('validates allowed pdf file type', function () {
    $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
    $request = new UploadMediaRequest;

    $validator = Validator::make(['file' => $file], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('validates allowed video file types', function () {
    $allowedVideoTypes = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
    ];

    foreach ($allowedVideoTypes as $ext => $mime) {
        $file = UploadedFile::fake()->create("test.{$ext}", 100, $mime);
        $request = new UploadMediaRequest;

        $validator = Validator::make(['file' => $file], $request->rules());

        expect($validator->passes())->toBeTrue("Failed for type: {$ext}");
    }
});

test('rejects disallowed file types', function () {
    $disallowedTypes = [
        'exe' => 'application/x-msdownload',
        'zip' => 'application/zip',
        'doc' => 'application/msword',
    ];

    foreach ($disallowedTypes as $ext => $mime) {
        $file = UploadedFile::fake()->create("test.{$ext}", 100, $mime);
        $request = new UploadMediaRequest;

        $validator = Validator::make(['file' => $file], $request->rules(), $request->messages());

        expect($validator->fails())->toBeTrue("Should fail for type: {$ext}");
    }
});

test('rejects files exceeding 10MB size limit', function () {
    // Create a file larger than 10MB (10241 KB)
    $file = UploadedFile::fake()->create('test.jpg', 10241);
    $request = new UploadMediaRequest;

    $validator = Validator::make(['file' => $file], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('file'))->toBe('File size exceeds maximum allowed size of 10MB');
});

test('accepts files within 10MB size limit', function () {
    // Create a file exactly at 10MB (10240 KB)
    $file = UploadedFile::fake()->create('test.jpg', 10240);
    $request = new UploadMediaRequest;

    $validator = Validator::make(['file' => $file], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('requires file field', function () {
    $request = new UploadMediaRequest;

    $validator = Validator::make([], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('file'))->toBe('A file is required for upload.');
});

test('returns custom error message for invalid file type', function () {
    $file = UploadedFile::fake()->create('test.exe', 100, 'application/x-msdownload');
    $request = new UploadMediaRequest;

    $validator = Validator::make(['file' => $file], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue();
    $error = $validator->errors()->first('file');
    expect($error)->toContain('File type not allowed');
});

test('rejects invalid file type with accepted types list in message', function () {
    $invalidFileTypes = [
        'exe' => 'application/x-msdownload',
        'zip' => 'application/zip',
        'doc' => 'application/msword',
    ];

    foreach ($invalidFileTypes as $ext => $mime) {
        $file = UploadedFile::fake()->create("test.{$ext}", 100, $mime);
        $request = new UploadMediaRequest;

        $validator = Validator::make(['file' => $file], $request->rules(), $request->messages());

        expect($validator->fails())->toBeTrue("Should fail for type: {$ext}");

        $error = $validator->errors()->first('file');
        $expectedMessage = 'File type not allowed. Accepted types: jpg, jpeg, png, gif, webp, svg, pdf, mp4, webm';

        expect($error)->toBe($expectedMessage, "Failed for type: {$ext}");
    }
});

test('media config returns correct allowed extensions', function () {
    $extensions = config('media.helpers.extensions')();

    expect($extensions)->toBeArray()
        ->toContain('jpg')
        ->toContain('jpeg')
        ->toContain('png')
        ->toContain('gif')
        ->toContain('webp')
        ->toContain('svg')
        ->toContain('pdf')
        ->toContain('mp4')
        ->toContain('webm');
});

test('media config returns correct allowed mime types', function () {
    $mimeTypes = config('media.helpers.mime_types')();

    expect($mimeTypes)->toBeArray()
        ->toContain('image/jpeg')
        ->toContain('image/png')
        ->toContain('image/gif')
        ->toContain('image/webp')
        ->toContain('image/svg+xml')
        ->toContain('application/pdf')
        ->toContain('video/mp4')
        ->toContain('video/webm');
});

test('media config max file size is 10240 KB', function () {
    expect(config('media.max_file_size'))->toBe(10240);
});
