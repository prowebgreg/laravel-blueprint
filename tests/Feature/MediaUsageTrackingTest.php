<?php

declare(strict_types=1);

/**
 * Feature Tests for Media Usage Tracking (User Story 6)
 *
 * Tests the MediaUsageService functionality for tracking media usage across content models
 * and determining if media deletion should be blocked by published public content.
 *
 * User Story 6: Delete Media with Usage Protection
 * - System identifies all content using a specific media asset (FR-040)
 * - System blocks deletion when used by published Page, Service, or BlogPost (FR-041)
 * - System allows deletion when only used by internal content resources (FR-042)
 * - System allows deletion when only used by soft-deleted content (FR-043)
 *
 * Public Content Models (block deletion when published):
 * - Page (/{slug})
 * - Service (/services/{slug})
 * - BlogPost (/blog/{slug})
 *
 * Internal Content Resources (don't block deletion):
 * - Faq (no public URL)
 * - Testimonial (no public URL)
 *
 * @see /specs/003-media-engine/spec.md
 * @see /specs/003-media-engine/tasks.md
 */

use App\Enums\ContentStatus;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Service;
use App\Models\Testimonial;
use App\Services\Media\MediaUsageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('findUsages()', function () {
    it('returns empty collection when media has no usages', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();

        $usages = $service->findUsages($media);

        expect($usages)->toBeInstanceOf(\Illuminate\Support\Collection::class)
            ->and($usages)->toBeEmpty();
    });

    it('returns usage when media is attached to a Page', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->create(['slug' => 'home']);

        $page->attachMedia($media, 'page:home:hero:image');

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(1)
            ->and($usages->first())->toMatchArray([
                'model_type' => Page::class,
                'model_id' => (string) $page->id,
                'relation_type' => 'page:home:hero:image',
            ]);
    });

    it('returns usage when media is attached to a Service', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $serviceModel = Service::factory()->create(['slug' => 'web-development']);

        $serviceModel->attachMedia($media, 'service:hero:image');

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(1)
            ->and($usages->first())->toMatchArray([
                'model_type' => Service::class,
                'model_id' => (string) $serviceModel->id,
                'relation_type' => 'service:hero:image',
            ]);
    });

    it('returns usage when media is attached to a BlogPost', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $blogPost = BlogPost::factory()->create();

        $blogPost->attachMedia($media, 'blog:hero:image');

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(1)
            ->and($usages->first())->toMatchArray([
                'model_type' => BlogPost::class,
                'model_id' => (string) $blogPost->id,
                'relation_type' => 'blog:hero:image',
            ]);
    });

    it('returns usage when media is attached to a Faq', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $faq = Faq::factory()->create();

        $faq->attachMedia($media, 'faq:icon');

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(1)
            ->and($usages->first())->toMatchArray([
                'model_type' => Faq::class,
                'model_id' => (string) $faq->id,
                'relation_type' => 'faq:icon',
            ]);
    });

    it('returns usage when media is attached to a Testimonial', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $testimonial = Testimonial::factory()->create();

        $testimonial->attachMedia($media, 'testimonial:avatar');

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(1)
            ->and($usages->first())->toMatchArray([
                'model_type' => Testimonial::class,
                'model_id' => (string) $testimonial->id,
                'relation_type' => 'testimonial:avatar',
            ]);
    });

    it('returns all usages when media is attached to multiple content items', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->create(['slug' => 'home']);
        $serviceModel = Service::factory()->create(['slug' => 'consulting']);
        $faq = Faq::factory()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $serviceModel->attachMedia($media, 'service:hero:image');
        $faq->attachMedia($media, 'faq:icon');

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(3);

        $modelTypes = $usages->pluck('model_type')->toArray();
        expect($modelTypes)->toContain(Page::class, Service::class, Faq::class);
    });

    it('includes multiple usages from same model with different relation types', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->create(['slug' => 'home']);

        $page->attachMedia($media, 'page:home:hero:image');
        $page->attachMedia($media, 'og_image');

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(2);

        $relationTypes = $usages->pluck('relation_type')->toArray();
        expect($relationTypes)->toContain('page:home:hero:image', 'og_image');
    });

    it('excludes soft-deleted content items from usage results', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->create(['slug' => 'home']);

        $page->attachMedia($media, 'page:home:hero:image');
        $page->delete(); // Soft delete

        $usages = $service->findUsages($media);

        expect($usages)->toBeEmpty();
    });

    it('includes non-deleted content when some content is soft-deleted', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page1 = Page::factory()->create(['slug' => 'home']);
        $page2 = Page::factory()->create(['slug' => 'about']);

        $page1->attachMedia($media, 'page:home:hero:image');
        $page2->attachMedia($media, 'page:about:hero:image');

        $page1->delete(); // Soft delete first page

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(1)
            ->and($usages->first())->toMatchArray([
                'model_type' => Page::class,
                'model_id' => (string) $page2->id,
            ]);
    });
});

describe('isBlockedByPublicContent()', function () {
    it('returns false when media has no usages', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns true when used by published Page', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);

        $page->attachMedia($media, 'page:home:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeTrue();
    });

    it('returns true when used by published Service', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $serviceModel = Service::factory()->published()->create(['slug' => 'web-development']);

        $serviceModel->attachMedia($media, 'service:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeTrue();
    });

    it('returns true when used by published BlogPost', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $blogPost = BlogPost::factory()->published()->create();

        $blogPost->attachMedia($media, 'blog:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeTrue();
    });

    it('returns false when only used by draft Page', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'home']);

        $page->attachMedia($media, 'page:home:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns false when only used by draft Service', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $serviceModel = Service::factory()->draft()->create(['slug' => 'consulting']);

        $serviceModel->attachMedia($media, 'service:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns false when only used by draft BlogPost', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $blogPost = BlogPost::factory()->draft()->create();

        $blogPost->attachMedia($media, 'blog:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns false when only used by soft-deleted Page', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);

        $page->attachMedia($media, 'page:home:hero:image');
        $page->delete(); // Soft delete

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns false when only used by soft-deleted Service', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $serviceModel = Service::factory()->published()->create(['slug' => 'consulting']);

        $serviceModel->attachMedia($media, 'service:hero:image');
        $serviceModel->delete(); // Soft delete

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns false when only used by soft-deleted BlogPost', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $blogPost = BlogPost::factory()->published()->create();

        $blogPost->attachMedia($media, 'blog:hero:image');
        $blogPost->delete(); // Soft delete

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns false when only used by Faq content resource', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $faq = Faq::factory()->published()->create();

        $faq->attachMedia($media, 'faq:icon');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns false when only used by Testimonial content resource', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $testimonial = Testimonial::factory()->published()->create();

        $testimonial->attachMedia($media, 'testimonial:avatar');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns false when used by multiple Faq and Testimonial records', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $faq1 = Faq::factory()->published()->create();
        $faq2 = Faq::factory()->published()->create();
        $testimonial = Testimonial::factory()->published()->create();

        $faq1->attachMedia($media, 'faq:icon');
        $faq2->attachMedia($media, 'faq:icon');
        $testimonial->attachMedia($media, 'testimonial:avatar');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns true when used by both published Page and Faq', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);
        $faq = Faq::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $faq->attachMedia($media, 'faq:icon');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeTrue();
    });

    it('returns true when used by both published Service and Testimonial', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $serviceModel = Service::factory()->published()->create(['slug' => 'consulting']);
        $testimonial = Testimonial::factory()->published()->create();

        $serviceModel->attachMedia($media, 'service:hero:image');
        $testimonial->attachMedia($media, 'testimonial:avatar');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeTrue();
    });

    it('returns true when used by published BlogPost regardless of draft Service', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $blogPost = BlogPost::factory()->published()->create();
        $serviceModel = Service::factory()->draft()->create(['slug' => 'consulting']);

        $blogPost->attachMedia($media, 'blog:hero:image');
        $serviceModel->attachMedia($media, 'service:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeTrue();
    });

    it('returns false when used by draft Page and published Faq', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'home']);
        $faq = Faq::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $faq->attachMedia($media, 'faq:icon');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });

    it('returns true when used by multiple published public content models', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);
        $serviceModel = Service::factory()->published()->create(['slug' => 'consulting']);
        $blogPost = BlogPost::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $serviceModel->attachMedia($media, 'service:hero:image');
        $blogPost->attachMedia($media, 'blog:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeTrue();
    });

    it('returns false when used by mix of draft public content and published internal resources', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'home']);
        $serviceModel = Service::factory()->draft()->create(['slug' => 'consulting']);
        $faq = Faq::factory()->published()->create();
        $testimonial = Testimonial::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $serviceModel->attachMedia($media, 'service:hero:image');
        $faq->attachMedia($media, 'faq:icon');
        $testimonial->attachMedia($media, 'testimonial:avatar');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeFalse();
    });
});

describe('edge cases and validation', function () {
    it('handles media attached with multiple relation types to same published Page', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);

        $page->attachMedia($media, 'page:home:hero:image');
        $page->attachMedia($media, 'og_image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($isBlocked)->toBeTrue();
    });

    it('handles ContentStatus enum correctly for published status', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();

        // Explicitly verify ContentStatus::Published enum
        $page = Page::factory()->create([
            'slug' => 'home',
            'status' => ContentStatus::Published,
        ]);

        $page->attachMedia($media, 'page:home:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($page->status)->toBe(ContentStatus::Published)
            ->and($isBlocked)->toBeTrue();
    });

    it('handles ContentStatus enum correctly for draft status', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();

        // Explicitly verify ContentStatus::Draft enum
        $page = Page::factory()->create([
            'slug' => 'home',
            'status' => ContentStatus::Draft,
        ]);

        $page->attachMedia($media, 'page:home:hero:image');

        $isBlocked = $service->isBlockedByPublicContent($media);

        expect($page->status)->toBe(ContentStatus::Draft)
            ->and($isBlocked)->toBeFalse();
    });

    it('returns correct usage count for complex multi-model scenario', function () {
        $service = app(MediaUsageService::class);
        $media = MediaAsset::factory()->create();

        // Create multiple content items
        $page1 = Page::factory()->published()->create(['slug' => 'home']);
        $page2 = Page::factory()->draft()->create(['slug' => 'about']);
        $serviceModel = Service::factory()->published()->create(['slug' => 'consulting']);
        $blogPost = BlogPost::factory()->draft()->create();
        $faq = Faq::factory()->published()->create();
        $testimonial = Testimonial::factory()->published()->create();

        // Attach media to all
        $page1->attachMedia($media, 'page:home:hero:image');
        $page2->attachMedia($media, 'page:about:hero:image');
        $serviceModel->attachMedia($media, 'service:hero:image');
        $blogPost->attachMedia($media, 'blog:hero:image');
        $faq->attachMedia($media, 'faq:icon');
        $testimonial->attachMedia($media, 'testimonial:avatar');

        $usages = $service->findUsages($media);

        expect($usages)->toHaveCount(6);
    });
});
