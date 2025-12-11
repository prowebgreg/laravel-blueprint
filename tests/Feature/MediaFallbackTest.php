<?php

declare(strict_types=1);

/**
 * Feature Tests for Media Fallback and Display
 *
 * Tests media URL retrieval with fallback handling:
 * - getMediaUrl() - get CDN URL for media with variant width selection
 * - MediaAsset::getUrl() - direct URL retrieval with width selection
 * - Fallback behavior for missing, deleted, or processing media
 * - Width selection logic: exact match, next larger, or largest available
 * - Edge cases: SVG, video, processing state
 *
 * User Story 5: Display Media on Frontend with Fallback
 * - Developer requests media by relationship type
 * - System returns appropriate variant URL for display
 * - Fallback image returned when media missing or deleted
 * - No broken image icons on frontend
 *
 * IMPORTANT: These tests use TDD approach - they define expected behavior
 * for getMediaUrl() and getUrl() methods that will be implemented in future tasks.
 * Tests will FAIL until implementation is complete.
 *
 * Acceptance Scenarios from spec.md:
 * 1. Content has media attached + width specified → CDN URL for appropriate variant
 * 2. No media attached → fallback image URL
 * 3. Media attached but deleted → fallback image URL
 * 4. Request specific width, exact doesn't exist → next larger variant returned
 *
 * @see /specs/003-media-engine/spec.md
 * @see /specs/003-media-engine/data-model.md
 */

use App\Enums\MediaState;
use App\Enums\MediaType;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('getMediaUrl() with attached media', function () {
    it('returns CDN URL for variant when width specified', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create(['dimensions' => ['width' => 1920, 'height' => 1080]]);

        // Create a 960px variant
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 960,
            'height' => 540,
            'cloudfront_url' => 'https://cdn.example.com/media/images/hero-abc123-960.webp',
        ]);

        $page->attachMedia($media, 'page:home:hero:image');

        $url = $page->getMediaUrl('page:home:hero:image', 960);

        expect($url)->toBe('https://cdn.example.com/media/images/hero-abc123-960.webp');
    });

    it('returns original URL when no width specified', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/media/images/hero-abc123-original.webp',
        ]);

        $page->attachMedia($media, 'page:home:hero:image');

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/media/images/hero-abc123-original.webp');
    });

    it('returns correct variant based on requested width', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();

        // Create multiple variants
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 480,
            'cloudfront_url' => 'https://cdn.example.com/test-480.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 720,
            'cloudfront_url' => 'https://cdn.example.com/test-720.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 1440,
            'cloudfront_url' => 'https://cdn.example.com/test-1440.webp',
        ]);

        $page->attachMedia($media, 'page:home:hero:image');

        // Request 720px width - should get exact match
        $url = $page->getMediaUrl('page:home:hero:image', 720);

        expect($url)->toBe('https://cdn.example.com/test-720.webp');
    });

    it('passes through to MediaAsset getUrl method', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/test-original.webp',
        ]);

        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 960,
            'cloudfront_url' => 'https://cdn.example.com/test-960.webp',
        ]);

        $page->attachMedia($media, 'og_image');

        // Both should return the same URL (delegated to MediaAsset::getUrl)
        $urlFromTrait = $page->getMediaUrl('og_image', 960);
        $urlDirect = $media->getUrl(960);

        expect($urlFromTrait)->toBe($urlDirect)
            ->and($urlFromTrait)->toBe('https://cdn.example.com/test-960.webp');
    });
});

describe('getMediaUrl() with missing media', function () {
    it('returns fallback URL when no media attached', function () {
        $page = Page::factory()->create(['slug' => 'home']);

        // Create and configure fallback image
        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/fallback.webp');
    });

    it('returns fallback URL when type identifier not found', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'page:home:hero:image');

        // Configure fallback
        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        // Request different type identifier that doesn't exist
        $url = $page->getMediaUrl('page:home:banner:image');

        expect($url)->toBe('https://cdn.example.com/fallback.webp');
    });

    it('returns fallback URL with width selection when specified', function () {
        $page = Page::factory()->create(['slug' => 'home']);

        // Create fallback with variants
        $fallback = MediaAsset::factory()->create();
        MediaVariant::factory()->create([
            'media_asset_id' => $fallback->id,
            'width' => 960,
            'cloudfront_url' => 'https://cdn.example.com/fallback-960.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        $url = $page->getMediaUrl('non_existent_type', 960);

        expect($url)->toBe('https://cdn.example.com/fallback-960.webp');
    });
});

describe('getMediaUrl() with deleted media', function () {
    it('returns fallback URL when media soft-deleted', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'page:home:hero:image');

        // Configure fallback
        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        // Soft delete the media
        $media->delete();

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/fallback.webp');
    });

    it('returns fallback URL when media hard-deleted', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();
        $mediaId = $media->id;
        $page->attachMedia($media, 'page:home:hero:image');

        // Configure fallback
        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        // Force delete (hard delete)
        $media->forceDelete();

        // Verify media is gone
        expect(MediaAsset::find($mediaId))->toBeNull();

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/fallback.webp');
    });

    it('returns fallback with width selection for deleted media', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'og_image');

        // Configure fallback with variants
        $fallback = MediaAsset::factory()->create();
        MediaVariant::factory()->create([
            'media_asset_id' => $fallback->id,
            'width' => 720,
            'cloudfront_url' => 'https://cdn.example.com/fallback-720.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        $media->delete();

        $url = $page->getMediaUrl('og_image', 720);

        expect($url)->toBe('https://cdn.example.com/fallback-720.webp');
    });
});

describe('MediaAsset getUrl() width selection logic', function () {
    it('returns exact width variant when available', function () {
        $media = MediaAsset::factory()->create();

        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 480,
            'cloudfront_url' => 'https://cdn.example.com/test-480.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 960,
            'cloudfront_url' => 'https://cdn.example.com/test-960.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 1440,
            'cloudfront_url' => 'https://cdn.example.com/test-1440.webp',
        ]);

        $url = $media->getUrl(960);

        expect($url)->toBe('https://cdn.example.com/test-960.webp');
    });

    it('returns next larger variant when exact width not available', function () {
        $media = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/test-original.webp',
        ]);

        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 480,
            'cloudfront_url' => 'https://cdn.example.com/test-480.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 960,
            'cloudfront_url' => 'https://cdn.example.com/test-960.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 1440,
            'cloudfront_url' => 'https://cdn.example.com/test-1440.webp',
        ]);

        // Request 800px, should get 960px (next larger)
        $url = $media->getUrl(800);

        expect($url)->toBe('https://cdn.example.com/test-960.webp');
    });

    it('returns largest available variant when requested width is larger', function () {
        $media = MediaAsset::factory()->create([
            'dimensions' => ['width' => 1920, 'height' => 1080],
            'cloudfront_url_original' => 'https://cdn.example.com/test-original.webp',
        ]);

        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 960,
            'cloudfront_url' => 'https://cdn.example.com/test-960.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 1440,
            'cloudfront_url' => 'https://cdn.example.com/test-1440.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 1920,
            'cloudfront_url' => 'https://cdn.example.com/test-1920.webp',
        ]);

        // Request 2560px (larger than any variant), should get 1920px (largest)
        $url = $media->getUrl(2560);

        expect($url)->toBe('https://cdn.example.com/test-1920.webp');
    });

    it('returns original when no variants exist', function () {
        $media = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/test-original.webp',
        ]);

        // Request specific width but no variants available
        $url = $media->getUrl(960);

        expect($url)->toBe('https://cdn.example.com/test-original.webp');
    });

    it('returns original when width not specified', function () {
        $media = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/test-original.webp',
        ]);

        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 960,
            'cloudfront_url' => 'https://cdn.example.com/test-960.webp',
        ]);

        $url = $media->getUrl();

        expect($url)->toBe('https://cdn.example.com/test-original.webp');
    });

    it('orders variants correctly for next larger selection', function () {
        $media = MediaAsset::factory()->create();

        // Create variants in random order
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 1440,
            'cloudfront_url' => 'https://cdn.example.com/test-1440.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 480,
            'cloudfront_url' => 'https://cdn.example.com/test-480.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 960,
            'cloudfront_url' => 'https://cdn.example.com/test-960.webp',
        ]);

        // Request 500px, should get 960px (not 480px)
        $url = $media->getUrl(500);

        expect($url)->toBe('https://cdn.example.com/test-960.webp');
    });
});

describe('fallback configuration', function () {
    it('returns null when no fallback configured and no media attached', function () {
        $page = Page::factory()->create(['slug' => 'home']);

        // Ensure no fallback is configured
        $setting = Setting::find('media.fallback_image_id');
        if ($setting) {
            $setting->delete();
        }

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBeNull();
    });

    it('returns fallback URL when fallback configured and no media attached', function () {
        $page = Page::factory()->create(['slug' => 'home']);

        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/fallback.webp');
    });

    it('handles missing fallback image gracefully', function () {
        $page = Page::factory()->create(['slug' => 'home']);

        // Set fallback to non-existent media ID (valid UUID format but doesn't exist)
        Setting::set('media.fallback_image_id', '00000000-0000-0000-0000-000000000000');

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBeNull();
    });

    it('handles deleted fallback image gracefully', function () {
        $page = Page::factory()->create(['slug' => 'home']);

        // Create and delete fallback
        $fallback = MediaAsset::factory()->create();
        Setting::set('media.fallback_image_id', $fallback->id);
        $fallback->delete();

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBeNull();
    });

    it('uses fallback variant selection when width specified', function () {
        $page = Page::factory()->create(['slug' => 'home']);

        $fallback = MediaAsset::factory()->create();
        MediaVariant::factory()->create([
            'media_asset_id' => $fallback->id,
            'width' => 640,
            'cloudfront_url' => 'https://cdn.example.com/fallback-640.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $fallback->id,
            'width' => 1168,
            'cloudfront_url' => 'https://cdn.example.com/fallback-1168.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        // Request width between two variants
        $url = $page->getMediaUrl('non_existent', 800);

        // Should get next larger (1168)
        expect($url)->toBe('https://cdn.example.com/fallback-1168.webp');
    });
});

describe('edge cases', function () {
    it('SVG media returns original URL regardless of width', function () {
        $media = MediaAsset::factory()->svg()->create([
            'media_type' => MediaType::Svg,
            'cloudfront_url_original' => 'https://cdn.example.com/logo.svg',
        ]);

        // SVG has no variants, should always return original
        $urlNoWidth = $media->getUrl();
        $urlWithWidth = $media->getUrl(960);

        expect($urlNoWidth)->toBe('https://cdn.example.com/logo.svg')
            ->and($urlWithWidth)->toBe('https://cdn.example.com/logo.svg');
    });

    it('video media returns original URL regardless of width', function () {
        $media = MediaAsset::factory()->video()->create([
            'media_type' => MediaType::Video,
            'cloudfront_url_original' => 'https://cdn.example.com/intro.mp4',
        ]);

        // Video has no variants, should always return original
        $urlNoWidth = $media->getUrl();
        $urlWithWidth = $media->getUrl(1920);

        expect($urlNoWidth)->toBe('https://cdn.example.com/intro.mp4')
            ->and($urlWithWidth)->toBe('https://cdn.example.com/intro.mp4');
    });

    it('processing media returns fallback URL', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->processing()->create([
            'state' => MediaState::Processing,
        ]);
        $page->attachMedia($media, 'page:home:hero:image');

        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/fallback.webp');
    });

    it('uploading media returns fallback URL', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->uploading()->create([
            'state' => MediaState::Uploading,
        ]);
        $page->attachMedia($media, 'page:home:hero:image');

        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/fallback.webp');
    });

    it('failed media returns fallback URL', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->failed()->create([
            'state' => MediaState::Failed,
            'error_message' => 'Processing timeout',
        ]);
        $page->attachMedia($media, 'page:home:hero:image');

        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/fallback.webp');
    });

    it('only ready media returns actual media URL', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create([
            'state' => MediaState::Ready,
            'cloudfront_url_original' => 'https://cdn.example.com/hero.webp',
        ]);
        $page->attachMedia($media, 'page:home:hero:image');

        $url = $page->getMediaUrl('page:home:hero:image');

        expect($url)->toBe('https://cdn.example.com/hero.webp');
    });

    it('handles zero width request gracefully', function () {
        $media = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/test-original.webp',
        ]);

        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 480,
            'cloudfront_url' => 'https://cdn.example.com/test-480.webp',
        ]);

        // Width 0 should return original
        $url = $media->getUrl(0);

        expect($url)->toBe('https://cdn.example.com/test-original.webp');
    });

    it('handles negative width request gracefully', function () {
        $media = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/test-original.webp',
        ]);

        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 480,
            'cloudfront_url' => 'https://cdn.example.com/test-480.webp',
        ]);

        // Negative width should return original
        $url = $media->getUrl(-100);

        expect($url)->toBe('https://cdn.example.com/test-original.webp');
    });

    it('handles very large width request', function () {
        $media = MediaAsset::factory()->create([
            'dimensions' => ['width' => 1920, 'height' => 1080],
        ]);

        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 1920,
            'cloudfront_url' => 'https://cdn.example.com/test-1920.webp',
        ]);

        // Request 99999px, should get largest (1920)
        $url = $media->getUrl(99999);

        expect($url)->toBe('https://cdn.example.com/test-1920.webp');
    });

    it('returns correct URL for image with only small variants', function () {
        $media = MediaAsset::factory()->create([
            'dimensions' => ['width' => 800, 'height' => 600],
            'cloudfront_url_original' => 'https://cdn.example.com/small-original.webp',
        ]);

        // Only create variants smaller than original
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 480,
            'cloudfront_url' => 'https://cdn.example.com/small-480.webp',
        ]);
        MediaVariant::factory()->create([
            'media_asset_id' => $media->id,
            'width' => 640,
            'cloudfront_url' => 'https://cdn.example.com/small-640.webp',
        ]);

        // Request 1920px, should get largest variant (640)
        $url = $media->getUrl(1920);

        expect($url)->toBe('https://cdn.example.com/small-640.webp');
    });
});

describe('multiple content types with media fallback', function () {
    it('works with Service model', function () {
        $service = \App\Models\Service::factory()->create(['slug' => 'web-dev']);
        $media = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/service-hero.webp',
        ]);
        $service->attachMedia($media, 'service:hero:image');

        $url = $service->getMediaUrl('service:hero:image');

        expect($url)->toBe('https://cdn.example.com/service-hero.webp');
    });

    it('works with Testimonial model', function () {
        $testimonial = \App\Models\Testimonial::factory()->create();
        $avatar = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/avatar.webp',
        ]);
        $testimonial->attachMedia($avatar, 'testimonial:avatar');

        $url = $testimonial->getMediaUrl('testimonial:avatar');

        expect($url)->toBe('https://cdn.example.com/avatar.webp');
    });

    it('works with Faq model using SVG', function () {
        $faq = \App\Models\Faq::factory()->create();
        $icon = MediaAsset::factory()->svg()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/faq-icon.svg',
        ]);
        $faq->attachMedia($icon, 'faq:icon');

        $url = $faq->getMediaUrl('faq:icon');

        expect($url)->toBe('https://cdn.example.com/faq-icon.svg');
    });

    it('fallback works across different content models', function () {
        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/fallback.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        $page = Page::factory()->create();
        $service = \App\Models\Service::factory()->create();
        $testimonial = \App\Models\Testimonial::factory()->create();

        // All should return fallback for non-existent media
        expect($page->getMediaUrl('og_image'))->toBe('https://cdn.example.com/fallback.webp')
            ->and($service->getMediaUrl('service:hero:image'))->toBe('https://cdn.example.com/fallback.webp')
            ->and($testimonial->getMediaUrl('testimonial:avatar'))->toBe('https://cdn.example.com/fallback.webp');
    });
});

describe('real-world usage scenarios', function () {
    it('hero image on homepage with responsive variants', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $hero = MediaAsset::factory()->create();

        // Create typical responsive breakpoint variants
        $breakpoints = [
            480 => 'https://cdn.example.com/hero-480.webp',
            640 => 'https://cdn.example.com/hero-640.webp',
            720 => 'https://cdn.example.com/hero-720.webp',
            960 => 'https://cdn.example.com/hero-960.webp',
            1168 => 'https://cdn.example.com/hero-1168.webp',
            1440 => 'https://cdn.example.com/hero-1440.webp',
            1920 => 'https://cdn.example.com/hero-1920.webp',
        ];

        foreach ($breakpoints as $width => $url) {
            MediaVariant::factory()->create([
                'media_asset_id' => $hero->id,
                'width' => $width,
                'cloudfront_url' => $url,
            ]);
        }

        $page->attachMedia($hero, 'page:home:hero:image');

        // Test various viewport widths
        expect($page->getMediaUrl('page:home:hero:image', 480))->toBe('https://cdn.example.com/hero-480.webp')
            ->and($page->getMediaUrl('page:home:hero:image', 640))->toBe('https://cdn.example.com/hero-640.webp')
            ->and($page->getMediaUrl('page:home:hero:image', 1440))->toBe('https://cdn.example.com/hero-1440.webp')
            ->and($page->getMediaUrl('page:home:hero:image', 1920))->toBe('https://cdn.example.com/hero-1920.webp');
    });

    it('OG image shared across multiple pages', function () {
        $ogImage = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/og-default.webp',
        ]);

        $homePage = Page::factory()->create(['slug' => 'home']);
        $aboutPage = Page::factory()->create(['slug' => 'about']);
        $contactPage = Page::factory()->create(['slug' => 'contact']);

        $homePage->attachMedia($ogImage, 'og_image');
        $aboutPage->attachMedia($ogImage, 'og_image');
        $contactPage->attachMedia($ogImage, 'og_image');

        // All pages should return same OG image
        expect($homePage->getMediaUrl('og_image'))->toBe('https://cdn.example.com/og-default.webp')
            ->and($aboutPage->getMediaUrl('og_image'))->toBe('https://cdn.example.com/og-default.webp')
            ->and($contactPage->getMediaUrl('og_image'))->toBe('https://cdn.example.com/og-default.webp');
    });

    it('testimonial avatar with fallback when user deletes photo', function () {
        $testimonial = \App\Models\Testimonial::factory()->create();
        $avatar = MediaAsset::factory()->create();
        $testimonial->attachMedia($avatar, 'testimonial:avatar');

        // Configure generic avatar as fallback
        $fallback = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/generic-avatar.webp',
        ]);
        Setting::set('media.fallback_image_id', $fallback->id);

        // Initially shows user's avatar
        expect($testimonial->getMediaUrl('testimonial:avatar'))->toContain($avatar->cloudfront_url_original);

        // User requests deletion
        $avatar->delete();

        // Now shows generic avatar fallback
        expect($testimonial->getMediaUrl('testimonial:avatar'))->toBe('https://cdn.example.com/generic-avatar.webp');
    });

    it('service page with multiple media types', function () {
        $service = \App\Models\Service::factory()->create(['slug' => 'consulting']);

        $heroImage = MediaAsset::factory()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/consulting-hero.webp',
        ]);
        $logoSvg = MediaAsset::factory()->svg()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/consulting-logo.svg',
        ]);
        $introVideo = MediaAsset::factory()->video()->create([
            'cloudfront_url_original' => 'https://cdn.example.com/consulting-intro.mp4',
        ]);

        $service->attachMedia($heroImage, 'service:hero:image');
        $service->attachMedia($logoSvg, 'service:logo:svg');
        $service->attachMedia($introVideo, 'service:intro:video');

        // Each media type returns correct URL
        expect($service->getMediaUrl('service:hero:image'))->toBe('https://cdn.example.com/consulting-hero.webp')
            ->and($service->getMediaUrl('service:logo:svg'))->toBe('https://cdn.example.com/consulting-logo.svg')
            ->and($service->getMediaUrl('service:intro:video'))->toBe('https://cdn.example.com/consulting-intro.mp4');
    });
});
