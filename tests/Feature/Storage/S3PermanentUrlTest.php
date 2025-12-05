<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

it('returns CloudFront URL when AWS_URL is configured', function () {
    // Set CloudFront URL in config
    $cloudFrontUrl = 'https://dxrnpyjkukgbc.cloudfront.net';
    Config::set('filesystems.disks.s3.url', $cloudFrontUrl);

    // Create the s3-permanent disk which is scoped to the s3 disk
    $testPath = '2025/12/test.jpg';

    // Get the URL from the s3-permanent disk
    $generatedUrl = Storage::disk('s3-permanent')->url($testPath);

    // Expected URL should include the CloudFront domain and the permanent prefix
    // According to the contract: https://dxrnpyjkukgbc.cloudfront.net/permanent/2025/12/550e8400.jpg
    $expectedUrl = $cloudFrontUrl.'/permanent/'.$testPath;

    expect($generatedUrl)->toBe($expectedUrl);
});

it('returns CloudFront URL with file path preserved', function () {
    $cloudFrontUrl = 'https://dxrnpyjkukgbc.cloudfront.net';
    Config::set('filesystems.disks.s3.url', $cloudFrontUrl);

    $testPath = '2025/12/uuid-abc123.jpg';

    $generatedUrl = Storage::disk('s3-permanent')->url($testPath);

    expect($generatedUrl)->toStartWith($cloudFrontUrl)
        ->and($generatedUrl)->toContain('/permanent/')
        ->and($generatedUrl)->toContain($testPath);
});

it('includes permanent prefix in URL for scoped disk', function () {
    // Set CloudFront URL in config
    $cloudFrontUrl = 'https://dxrnpyjkukgbc.cloudfront.net';
    Config::set('filesystems.disks.s3.url', $cloudFrontUrl);

    // Simple test path
    $testPath = 'test.jpg';

    // Get the URL from the s3-permanent disk
    $generatedUrl = Storage::disk('s3-permanent')->url($testPath);

    // URL must include the permanent/ prefix since it's a scoped disk
    expect($generatedUrl)->toContain('/permanent/')
        ->and($generatedUrl)->toStartWith($cloudFrontUrl);
});

it('generates different URLs for s3-temp and s3-permanent disks', function () {
    // Set CloudFront URL in config
    $cloudFrontUrl = 'https://dxrnpyjkukgbc.cloudfront.net';
    Config::set('filesystems.disks.s3.url', $cloudFrontUrl);

    // Same file path on different disks
    $testPath = 'test.jpg';

    // Get URLs from both disks
    $permanentUrl = Storage::disk('s3-permanent')->url($testPath);
    $tempUrl = Storage::disk('s3-temp')->url($testPath);

    // URLs should be different because they're scoped to different prefixes
    expect($permanentUrl)->not->toBe($tempUrl)
        ->and($permanentUrl)->toContain('/permanent/')
        ->and($tempUrl)->toContain('/temp/');
});

it('verifies s3-permanent disk configuration for URL generation', function () {
    // Get the disk configuration
    $config = config('filesystems.disks.s3-permanent');

    // Verify the disk is properly configured as a scoped disk
    expect($config)->toHaveKey('driver', 'scoped')
        ->and($config)->toHaveKey('disk', 's3')
        ->and($config)->toHaveKey('prefix', 'permanent')
        ->and($config)->toHaveKey('visibility', 'public');

    // Verify the base s3 disk can have a URL configured
    $s3Config = config('filesystems.disks.s3');
    expect($s3Config)->toHaveKey('url');
});

it('generates correct URL format matching contract specification', function () {
    // Set CloudFront URL as specified in the contract
    $cloudFrontUrl = 'https://dxrnpyjkukgbc.cloudfront.net';
    Config::set('filesystems.disks.s3.url', $cloudFrontUrl);

    // Use the exact path from the contract specification
    $testPath = '2025/12/550e8400.jpg';

    // Get the URL from the s3-permanent disk
    $generatedUrl = Storage::disk('s3-permanent')->url($testPath);

    // Expected URL from contract: https://dxrnpyjkukgbc.cloudfront.net/permanent/2025/12/550e8400.jpg
    $expectedUrl = 'https://dxrnpyjkukgbc.cloudfront.net/permanent/2025/12/550e8400.jpg';

    expect($generatedUrl)->toBe($expectedUrl);
});
