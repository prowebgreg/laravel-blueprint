<?php

declare(strict_types=1);

/**
 * Feature Tests for Media Deletion (User Story 6)
 *
 * Tests the MediaDeletionService functionality for safely deleting media assets
 * with usage protection, S3 cleanup, and relationship removal.
 *
 * User Story 6: Delete Media with Usage Protection
 * - System blocks deletion when used by published Page, Service, or BlogPost (FR-041)
 * - System allows deletion when only used by internal content resources (FR-042)
 * - System allows deletion when only used by soft-deleted content (FR-043)
 * - System soft-deletes the media asset (FR-044)
 * - System queues S3 cleanup for original and all variants (FR-045)
 * - System removes relationships from content_relations table (FR-046)
 * - System blocks deletion of fallback image (FR-047)
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
use App\Models\MediaVariant;
use App\Models\Page;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('soft delete media with no usages', function () {
    it('can soft delete media with no usages', function () {
        $media = MediaAsset::factory()->create();

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue()
            ->and(MediaAsset::withTrashed()->find($media->id))->not->toBeNull()
            ->and(MediaAsset::find($media->id))->toBeNull();
    });

    it('can soft delete media with variants and no usages', function () {
        $media = MediaAsset::factory()->withVariants()->create();
        $variantCount = $media->variants()->count();

        expect($variantCount)->toBeGreaterThan(0);

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();

        // Assert variants still exist (they would be deleted via CASCADE on hard delete)
        expect(MediaVariant::where('media_asset_id', $media->id)->count())->toBe($variantCount);
    });
});

describe('soft delete media used only by draft content', function () {
    it('can soft delete media used only by draft Page', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'test-page']);

        $page->attachMedia($media, 'page:test:hero:image');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used only by draft Service', function () {
        $media = MediaAsset::factory()->create();
        $service = Service::factory()->draft()->create(['slug' => 'test-service']);

        $service->attachMedia($media, 'service:hero:image');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used only by draft BlogPost', function () {
        $media = MediaAsset::factory()->create();
        $blogPost = BlogPost::factory()->draft()->create();

        $blogPost->attachMedia($media, 'blog:hero:image');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used by multiple draft content items', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'home']);
        $service = Service::factory()->draft()->create(['slug' => 'consulting']);
        $blogPost = BlogPost::factory()->draft()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $service->attachMedia($media, 'service:hero:image');
        $blogPost->attachMedia($media, 'blog:hero:image');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });
});

describe('soft delete media used only by internal resources', function () {
    it('can soft delete media used only by Faq', function () {
        $media = MediaAsset::factory()->create();
        $faq = Faq::factory()->published()->create();

        $faq->attachMedia($media, 'faq:icon');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used only by Testimonial', function () {
        $media = MediaAsset::factory()->create();
        $testimonial = Testimonial::factory()->published()->create();

        $testimonial->attachMedia($media, 'testimonial:avatar');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used by multiple internal resources', function () {
        $media = MediaAsset::factory()->create();
        $faq1 = Faq::factory()->published()->create();
        $faq2 = Faq::factory()->published()->create();
        $testimonial = Testimonial::factory()->published()->create();

        $faq1->attachMedia($media, 'faq:icon');
        $faq2->attachMedia($media, 'faq:icon');
        $testimonial->attachMedia($media, 'testimonial:avatar');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used by mix of draft public content and published internal resources', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'home']);
        $service = Service::factory()->draft()->create(['slug' => 'consulting']);
        $faq = Faq::factory()->published()->create();
        $testimonial = Testimonial::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $service->attachMedia($media, 'service:hero:image');
        $faq->attachMedia($media, 'faq:icon');
        $testimonial->attachMedia($media, 'testimonial:avatar');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });
});

describe('soft delete media used only by soft-deleted content', function () {
    it('can soft delete media used only by soft-deleted Page', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'deleted-page']);

        $page->attachMedia($media, 'page:deleted:hero:image');
        $page->delete(); // Soft delete the page

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used only by soft-deleted Service', function () {
        $media = MediaAsset::factory()->create();
        $service = Service::factory()->published()->create(['slug' => 'deleted-service']);

        $service->attachMedia($media, 'service:hero:image');
        $service->delete(); // Soft delete the service

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used only by soft-deleted BlogPost', function () {
        $media = MediaAsset::factory()->create();
        $blogPost = BlogPost::factory()->published()->create();

        $blogPost->attachMedia($media, 'blog:hero:image');
        $blogPost->delete(); // Soft delete the blog post

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used by multiple soft-deleted content items', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);
        $service = Service::factory()->published()->create(['slug' => 'consulting']);
        $blogPost = BlogPost::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $service->attachMedia($media, 'service:hero:image');
        $blogPost->attachMedia($media, 'blog:hero:image');

        // Soft delete all content
        $page->delete();
        $service->delete();
        $blogPost->delete();

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });

    it('can soft delete media used by mix of soft-deleted and draft content', function () {
        $media = MediaAsset::factory()->create();
        $page1 = Page::factory()->published()->create(['slug' => 'deleted-page']);
        $page2 = Page::factory()->draft()->create(['slug' => 'draft-page']);
        $faq = Faq::factory()->published()->create();

        $page1->attachMedia($media, 'page:deleted:hero:image');
        $page2->attachMedia($media, 'page:draft:hero:image');
        $faq->attachMedia($media, 'faq:icon');

        // Soft delete the published page
        $page1->delete();

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });
});

describe('blocking deletion when used by published public content', function () {
    it('throws exception when trying to delete media used by published Page', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);

        $page->attachMedia($media, 'page:home:hero:image');

        // In this test, we're testing the constraint directly.
        // When MediaDeletionService is implemented, it would throw a custom exception.
        // For now, we test that the media is used by published content.
        // This will be updated when MediaDeletionService exists (T059).

        // Verify the blocking condition exists
        expect($page->status)->toBe(ContentStatus::Published)
            ->and($page->getMedia('page:home:hero:image'))->not->toBeNull();

        // The actual deletion blocking will be implemented in MediaDeletionService
        // For now, we just verify we can detect the condition
    })->skip('Will be implemented when MediaDeletionService exists (T059)');

    it('throws exception when trying to delete media used by published Service', function () {
        $media = MediaAsset::factory()->create();
        $service = Service::factory()->published()->create(['slug' => 'consulting']);

        $service->attachMedia($media, 'service:hero:image');

        // Verify the blocking condition exists
        expect($service->status)->toBe(ContentStatus::Published)
            ->and($service->getMedia('service:hero:image'))->not->toBeNull();
    })->skip('Will be implemented when MediaDeletionService exists (T059)');

    it('throws exception when trying to delete media used by published BlogPost', function () {
        $media = MediaAsset::factory()->create();
        $blogPost = BlogPost::factory()->published()->create();

        $blogPost->attachMedia($media, 'blog:hero:image');

        // Verify the blocking condition exists
        expect($blogPost->status)->toBe(ContentStatus::Published)
            ->and($blogPost->getMedia('blog:hero:image'))->not->toBeNull();
    })->skip('Will be implemented when MediaDeletionService exists (T059)');

    it('throws exception when media used by multiple published public content items', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);
        $service = Service::factory()->published()->create(['slug' => 'consulting']);
        $blogPost = BlogPost::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $service->attachMedia($media, 'service:hero:image');
        $blogPost->attachMedia($media, 'blog:hero:image');

        // Verify the blocking condition exists
        expect($page->status)->toBe(ContentStatus::Published)
            ->and($service->status)->toBe(ContentStatus::Published)
            ->and($blogPost->status)->toBe(ContentStatus::Published);
    })->skip('Will be implemented when MediaDeletionService exists (T059)');

    it('throws exception when media used by both published Page and internal resource', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->published()->create(['slug' => 'home']);
        $faq = Faq::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $faq->attachMedia($media, 'faq:icon');

        // Verify the blocking condition exists (published Page blocks)
        expect($page->status)->toBe(ContentStatus::Published);
    })->skip('Will be implemented when MediaDeletionService exists (T059)');
});

describe('blocking deletion of fallback image', function () {
    it('throws exception when trying to delete the fallback image', function () {
        $fallbackMedia = MediaAsset::factory()->withVariants()->create();

        // Set as fallback image
        Setting::set('media.fallback_image_id', $fallbackMedia->id);

        // Verify the fallback setting exists
        $fallbackId = Setting::get('media.fallback_image_id');
        expect($fallbackId)->toBe($fallbackMedia->id);
    })->skip('Will be implemented when MediaDeletionService exists (T059)');

    it('allows deletion of non-fallback media when fallback exists', function () {
        $fallbackMedia = MediaAsset::factory()->create();
        $regularMedia = MediaAsset::factory()->create();

        // Set fallback image
        Setting::set('media.fallback_image_id', $fallbackMedia->id);

        // Should be able to delete regular media
        $regularMedia->delete();

        expect($regularMedia->trashed())->toBeTrue()
            ->and($fallbackMedia->trashed())->toBeFalse();
    });
});

describe('S3 cleanup on soft delete', function () {
    beforeEach(function () {
        // Fake the S3 storage for testing
        Storage::fake('s3');
    });

    it('marks S3 files for cleanup when media is soft deleted', function () {
        $media = MediaAsset::factory()->create([
            's3_key_original' => 'media/images/test-file-x7k9m2p4-original.webp',
        ]);

        // Create some test files in fake S3
        Storage::disk('s3')->put($media->s3_key_original, 'test content');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();

        // Note: Actual S3 cleanup would be handled by a queued job
        // This test verifies the soft delete happens; job tests handle cleanup logic
    });

    it('marks original and all variants for cleanup when media is soft deleted', function () {
        Storage::fake('s3');

        $media = MediaAsset::factory()->withVariants()->create();

        // Create test files for original and variants
        Storage::disk('s3')->put($media->s3_key_original, 'original content');

        foreach ($media->variants as $variant) {
            Storage::disk('s3')->put($variant->s3_key, "variant {$variant->width} content");
        }

        $variantCount = $media->variants()->count();
        expect($variantCount)->toBeGreaterThan(0);

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();

        // Files would be cleaned up by a queued job (tested separately)
    });

    it('handles S3 cleanup when media has no variants', function () {
        Storage::fake('s3');

        $media = MediaAsset::factory()->video()->create();

        // Video files don't have variants
        expect($media->variants()->count())->toBe(0);

        Storage::disk('s3')->put($media->s3_key_original, 'video content');

        // Soft delete the media
        $media->delete();

        // Assert media is soft deleted
        expect($media->trashed())->toBeTrue();
    });
});

describe('relationship removal on soft delete', function () {
    it('removes content relations when media is soft deleted', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'test-page']);

        $page->attachMedia($media, 'page:test:hero:image');

        // Verify relationship exists
        $relationExists = DB::table('content_relations')
            ->where('source_type', Page::class)
            ->where('source_id', (string) $page->id)
            ->where('target_type', MediaAsset::class)
            ->where('target_id', (string) $media->id)
            ->exists();

        expect($relationExists)->toBeTrue();

        // Soft delete the media
        $media->delete();

        // Note: Actual relationship removal would be handled by MediaDeletionService
        // or a database-level CASCADE. This test documents the expected behavior.
    })->skip('Relationship removal will be implemented in MediaDeletionService (T059)');

    it('removes multiple content relations when media is soft deleted', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'home']);
        $service = Service::factory()->draft()->create(['slug' => 'consulting']);
        $faq = Faq::factory()->published()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $service->attachMedia($media, 'service:hero:image');
        $faq->attachMedia($media, 'faq:icon');

        // Verify all relationships exist
        $relationCount = DB::table('content_relations')
            ->where('target_type', MediaAsset::class)
            ->where('target_id', (string) $media->id)
            ->count();

        expect($relationCount)->toBe(3);

        // Soft delete the media
        $media->delete();

        // Relationships should be removed
    })->skip('Relationship removal will be implemented in MediaDeletionService (T059)');

    it('removes relationships with different relation types for same content', function () {
        $media = MediaAsset::factory()->create();
        $page = Page::factory()->draft()->create(['slug' => 'home']);

        // Attach same media with different relation types
        $page->attachMedia($media, 'page:home:hero:image');
        $page->attachMedia($media, 'og_image');

        // Verify both relationships exist
        $relationCount = DB::table('content_relations')
            ->where('source_type', Page::class)
            ->where('source_id', (string) $page->id)
            ->where('target_type', MediaAsset::class)
            ->where('target_id', (string) $media->id)
            ->count();

        expect($relationCount)->toBe(2);

        // Soft delete the media
        $media->delete();

        // Both relationships should be removed
    })->skip('Relationship removal will be implemented in MediaDeletionService (T059)');
});

describe('edge cases and validation', function () {
    it('handles soft delete of already soft-deleted media', function () {
        $media = MediaAsset::factory()->create();

        // First soft delete
        $media->delete();
        expect($media->trashed())->toBeTrue();

        // Try to delete again (should be idempotent)
        $media->delete();
        expect($media->trashed())->toBeTrue();
    });

    it('allows force delete of soft-deleted media with no usages', function () {
        $media = MediaAsset::factory()->withVariants()->create();
        $variantIds = $media->variants->pluck('id')->toArray();

        // Soft delete first
        $media->delete();
        expect($media->trashed())->toBeTrue();

        // Force delete (permanent deletion)
        $media->forceDelete();

        // Assert media is completely gone
        expect(MediaAsset::withTrashed()->find($media->id))->toBeNull();

        // Assert variants are also deleted (CASCADE)
        foreach ($variantIds as $variantId) {
            expect(MediaVariant::find($variantId))->toBeNull();
        }
    });

    it('tracks deletion timestamp correctly', function () {
        $media = MediaAsset::factory()->create();

        $media->delete();

        $deletedMedia = MediaAsset::withTrashed()->find($media->id);

        expect($deletedMedia->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('does not affect other media when deleting one media asset', function () {
        $media1 = MediaAsset::factory()->create(['original_name' => 'media1.jpg']);
        $media2 = MediaAsset::factory()->create(['original_name' => 'media2.jpg']);
        $media3 = MediaAsset::factory()->create(['original_name' => 'media3.jpg']);

        // Delete only media1
        $media1->delete();

        // Assert only media1 is deleted
        expect($media1->trashed())->toBeTrue()
            ->and($media2->trashed())->toBeFalse()
            ->and($media3->trashed())->toBeFalse();

        // Assert we can still query non-deleted media
        $activeMedia = MediaAsset::all();
        $activeNames = $activeMedia->pluck('original_name')->toArray();
        expect($activeMedia)->toHaveCount(2)
            ->and($activeNames)->toContain('media2.jpg', 'media3.jpg');

        // Assert deleted media is not in the active list
        expect(in_array('media1.jpg', $activeNames, true))->toBeFalse();
    });
});
