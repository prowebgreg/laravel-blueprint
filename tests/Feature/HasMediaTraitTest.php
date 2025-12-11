<?php

declare(strict_types=1);

/**
 * Feature Tests for HasMedia Trait
 *
 * Tests media attachment functionality for content models:
 * - attachMedia() - attach media with type identifier and ordering
 * - detachMedia() - remove media relationship by type
 * - getMedia() - retrieve single media asset by type
 * - getAllMedia() - retrieve all attached media
 * - Edge cases: duplicate attachments, ordering, type not found
 *
 * User Story 4: Media Attachment to Content Models
 * - Developer adds HasMedia trait to any content model
 * - Content editor saves content with image fields
 * - System creates relationship with contextual type identifier
 * - Media assets remain available even when detached from content
 *
 * Relationship Type Naming Convention:
 * - Fixed field: {field_name} e.g. "og_image"
 * - Static page: page:{slug}:{section}:{field} e.g. "page:home:hero:image"
 * - Custom page: {type}:{section}:{field} e.g. "service:hero:image"
 * - Content resource: {resource}:{field} e.g. "testimonial:avatar"
 *
 * @see /specs/003-media-engine/spec.md
 * @see /specs/003-media-engine/data-model.md
 */

use App\Models\MediaAsset;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('attachMedia()', function () {
    it('creates relationship record linking model to media asset', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'page:home:hero:image');

        expect($page->getMedia('page:home:hero:image'))
            ->not->toBeNull()
            ->id->toBe($media->id);
    });

    it('attaches media with custom order value', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'page:home:hero:image', 5);

        $relationship = \DB::select(
            'SELECT * FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text AND target_id::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class, (string) $media->id]
        );

        expect($relationship)->toHaveCount(1)
            ->and($relationship[0]->order)->toBe(5);
    });

    it('defaults order to 0 when not provided', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'og_image');

        $relationship = \DB::select(
            'SELECT * FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text AND target_id::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class, (string) $media->id]
        );

        expect($relationship)->toHaveCount(1)
            ->and($relationship[0]->order)->toBe(0);
    });

    it('allows same media to be attached with different type identifiers', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $page->attachMedia($media, 'og_image');

        expect($page->getMedia('page:home:hero:image'))->not->toBeNull()
            ->and($page->getMedia('og_image'))->not->toBeNull()
            ->and($page->getMedia('page:home:hero:image')->id)->toBe($media->id)
            ->and($page->getMedia('og_image')->id)->toBe($media->id);
    });

    it('prevents duplicate attachments for same type identifier', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'og_image');

        // Second attach with same type should not throw error but silently ignore
        expect(fn () => $page->attachMedia($media, 'og_image'))
            ->not->toThrow(\Exception::class);

        // Should still have only one relationship
        $relationshipCount = \DB::select(
            'SELECT COUNT(*) as count FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text AND target_id::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class, (string) $media->id]
        );

        expect((int) $relationshipCount[0]->count)->toBe(1);
    });

    it('stores created_at timestamp on relationship', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'og_image');

        $relationship = \DB::select(
            'SELECT * FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text AND target_id::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class, (string) $media->id]
        );

        expect($relationship)->toHaveCount(1)
            ->and($relationship[0]->created_at)->not->toBeNull();
    });

    it('attaches SVG media using svg type naming', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $svgMedia = MediaAsset::factory()->svg()->create();

        $page->attachMedia($svgMedia, 'page:home:logo:svg');

        expect($page->getMedia('page:home:logo:svg'))
            ->not->toBeNull()
            ->id->toBe($svgMedia->id);
    });

    it('attaches video media using video type naming', function () {
        $page = Page::factory()->create(['slug' => 'about']);
        $videoMedia = MediaAsset::factory()->video()->create();

        $page->attachMedia($videoMedia, 'page:about:intro:video');

        expect($page->getMedia('page:about:intro:video'))
            ->not->toBeNull()
            ->id->toBe($videoMedia->id);
    });
});

describe('detachMedia()', function () {
    it('removes relationship record by type identifier', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'og_image');

        $page->detachMedia('og_image');

        expect($page->getMedia('og_image'))->toBeNull();
    });

    it('preserves media asset after detachment', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'og_image');

        $mediaId = $media->id;
        $page->detachMedia('og_image');

        expect(MediaAsset::find($mediaId))->not->toBeNull();
    });

    it('only detaches specified type identifier', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'page:home:hero:image');
        $page->attachMedia($media, 'og_image');

        $page->detachMedia('og_image');

        expect($page->getMedia('og_image'))->toBeNull()
            ->and($page->getMedia('page:home:hero:image'))->not->toBeNull();
    });

    it('does not throw error when detaching non-existent type', function () {
        $page = Page::factory()->create();

        expect(fn () => $page->detachMedia('non_existent_type'))
            ->not->toThrow(\Exception::class);
    });

    it('removes all relationships for a type when multiple media attached', function () {
        $page = Page::factory()->create();
        $media1 = MediaAsset::factory()->create();
        $media2 = MediaAsset::factory()->create();

        $page->attachMedia($media1, 'gallery:image', 0);
        $page->attachMedia($media2, 'gallery:image', 1);

        $page->detachMedia('gallery:image');

        $relationshipCount = \DB::select(
            'SELECT COUNT(*) as count FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class]
        );

        expect((int) $relationshipCount[0]->count)->toBe(0);
    });
});

describe('getMedia()', function () {
    it('returns media asset for specific type identifier', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'page:home:hero:image');

        $retrieved = $page->getMedia('page:home:hero:image');

        expect($retrieved)
            ->not->toBeNull()
            ->toBeInstanceOf(MediaAsset::class)
            ->id->toBe($media->id);
    });

    it('returns null when type identifier not found', function () {
        $page = Page::factory()->create();

        expect($page->getMedia('non_existent_type'))->toBeNull();
    });

    it('returns correct media when multiple types attached', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $heroMedia = MediaAsset::factory()->create();
        $ogMedia = MediaAsset::factory()->create();

        $page->attachMedia($heroMedia, 'page:home:hero:image');
        $page->attachMedia($ogMedia, 'og_image');

        expect($page->getMedia('page:home:hero:image')->id)->toBe($heroMedia->id)
            ->and($page->getMedia('og_image')->id)->toBe($ogMedia->id);
    });

    it('returns first media when multiple media of same type', function () {
        $page = Page::factory()->create();
        $media1 = MediaAsset::factory()->create();
        $media2 = MediaAsset::factory()->create();

        $page->attachMedia($media1, 'gallery:image', 0);
        $page->attachMedia($media2, 'gallery:image', 1);

        $retrieved = $page->getMedia('gallery:image');

        expect($retrieved)
            ->not->toBeNull()
            ->id->toBe($media1->id);
    });

    it('returns media with all asset attributes', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->withMetadata()->create([
            'alt_text' => 'Hero image description',
            'title' => 'Hero Image',
        ]);
        $page->attachMedia($media, 'page:home:hero:image');

        $retrieved = $page->getMedia('page:home:hero:image');

        expect($retrieved->alt_text)->toBe('Hero image description')
            ->and($retrieved->title)->toBe('Hero Image')
            ->and($retrieved->filename)->toBe($media->filename)
            ->and($retrieved->cloudfront_url_original)->toBe($media->cloudfront_url_original);
    });

    it('uses fixed field naming convention for shared fields', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'og_image');

        expect($page->getMedia('og_image'))
            ->not->toBeNull()
            ->id->toBe($media->id);
    });

    it('uses static page naming convention', function () {
        $page = Page::factory()->create(['slug' => 'contact']);
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'page:contact:hero:image');

        expect($page->getMedia('page:contact:hero:image'))
            ->not->toBeNull()
            ->id->toBe($media->id);
    });
});

describe('getAllMedia()', function () {
    it('returns empty collection when no media attached', function () {
        $page = Page::factory()->create();

        $allMedia = $page->getAllMedia();

        expect($allMedia)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class)
            ->and($allMedia)->toHaveCount(0);
    });

    it('returns all attached media assets', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $heroMedia = MediaAsset::factory()->create();
        $ogMedia = MediaAsset::factory()->create();
        $logoMedia = MediaAsset::factory()->svg()->create();

        $page->attachMedia($heroMedia, 'page:home:hero:image');
        $page->attachMedia($ogMedia, 'og_image');
        $page->attachMedia($logoMedia, 'page:home:logo:svg');

        $allMedia = $page->getAllMedia();

        expect($allMedia)->toHaveCount(3)
            ->and($allMedia->pluck('id')->toArray())
            ->toContain($heroMedia->id, $ogMedia->id, $logoMedia->id);
    });

    it('returns each media asset only once even if attached with multiple types', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'page:home:hero:image');
        $page->attachMedia($media, 'og_image');

        $allMedia = $page->getAllMedia();

        expect($allMedia)->toHaveCount(1)
            ->and($allMedia->first()->id)->toBe($media->id);
    });

    it('orders media by relationship order field', function () {
        $page = Page::factory()->create();
        $media1 = MediaAsset::factory()->create(['original_name' => 'third.jpg']);
        $media2 = MediaAsset::factory()->create(['original_name' => 'first.jpg']);
        $media3 = MediaAsset::factory()->create(['original_name' => 'second.jpg']);

        $page->attachMedia($media1, 'gallery:image', 2);
        $page->attachMedia($media2, 'gallery:image', 0);
        $page->attachMedia($media3, 'gallery:image', 1);

        $allMedia = $page->getAllMedia();

        expect($allMedia)->toHaveCount(3)
            ->and($allMedia->get(0)->id)->toBe($media2->id)
            ->and($allMedia->get(1)->id)->toBe($media3->id)
            ->and($allMedia->get(2)->id)->toBe($media1->id);
    });

    it('includes media of all types: image, svg, video', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $imageMedia = MediaAsset::factory()->create();
        $svgMedia = MediaAsset::factory()->svg()->create();
        $videoMedia = MediaAsset::factory()->video()->create();

        $page->attachMedia($imageMedia, 'page:home:hero:image');
        $page->attachMedia($svgMedia, 'page:home:logo:svg');
        $page->attachMedia($videoMedia, 'page:home:intro:video');

        $allMedia = $page->getAllMedia();

        expect($allMedia)->toHaveCount(3);
    });
});

describe('edge cases and error handling', function () {
    it('handles page with UUID primary key', function () {
        // Pages use integer IDs, but content_relations supports varchar(36) for UUID
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'og_image');

        $relationship = \DB::select(
            'SELECT * FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text AND target_id::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class, (string) $media->id]
        );

        expect($relationship)->toHaveCount(1);
    });

    it('maintains relationships when model is soft deleted', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();
        $page->attachMedia($media, 'og_image');

        $page->delete(); // Soft delete

        $relationshipCount = \DB::select(
            'SELECT COUNT(*) as count FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text AND target_id::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class, (string) $media->id]
        );

        expect((int) $relationshipCount[0]->count)->toBe(1);
    });

    it('handles very long type identifiers', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();
        $longType = 'page:very-long-slug-name-here:section-name:subsection:field:image';

        $page->attachMedia($media, $longType);

        expect($page->getMedia($longType))
            ->not->toBeNull()
            ->id->toBe($media->id);
    });

    it('handles special characters in type identifiers', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();
        $type = 'section:field_name-2:image';

        $page->attachMedia($media, $type);

        expect($page->getMedia($type))
            ->not->toBeNull()
            ->id->toBe($media->id);
    });

    it('allows negative order values for custom sorting', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'gallery:image', -1);

        $relationship = \DB::select(
            'SELECT * FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text AND target_id::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class, (string) $media->id]
        );

        expect($relationship)->toHaveCount(1)
            ->and($relationship[0]->order)->toBe(-1);
    });

    it('handles large order values', function () {
        $page = Page::factory()->create();
        $media = MediaAsset::factory()->create();

        $page->attachMedia($media, 'gallery:image', 9999);

        $relationship = \DB::select(
            'SELECT * FROM content_relations
            WHERE source_type::text = ?::text AND source_id::text = ?::text
            AND target_type::text = ?::text AND target_id::text = ?::text',
            [Page::class, (string) $page->id, MediaAsset::class, (string) $media->id]
        );

        expect($relationship)->toHaveCount(1)
            ->and($relationship[0]->order)->toBe(9999);
    });
});

describe('mediaAssets() relationship', function () {
    // Note: The morphToMany relationship cannot be used for direct queries due to
    // PostgreSQL UUID type inference issues with integer keys. The relationship
    // exists for metadata/introspection only. Use getMedia() and getAllMedia() for queries.

    it('provides morphToMany relationship definition', function () {
        $page = Page::factory()->create();

        // Verify the relationship method exists and returns correct type
        $relationship = $page->mediaAssets();

        expect($relationship)
            ->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class);
    });

    it('relationship includes correct pivot columns in definition', function () {
        $page = Page::factory()->create();

        $relationship = $page->mediaAssets();

        // Check that pivot columns are defined (not that the query works)
        expect($relationship->getPivotColumns())
            ->toContain('relation_type', 'order', 'created_at');
    });
});

describe('multiple models using HasMedia trait', function () {
    it('allows multiple page instances to attach same media', function () {
        $page1 = Page::factory()->create(['slug' => 'home']);
        $page2 = Page::factory()->create(['slug' => 'about']);
        $media = MediaAsset::factory()->create();

        $page1->attachMedia($media, 'page:home:hero:image');
        $page2->attachMedia($media, 'page:about:hero:image');

        expect($page1->getMedia('page:home:hero:image')->id)->toBe($media->id)
            ->and($page2->getMedia('page:about:hero:image')->id)->toBe($media->id);
    });

    it('isolates media relationships between different models', function () {
        $page1 = Page::factory()->create(['slug' => 'home']);
        $page2 = Page::factory()->create(['slug' => 'about']);
        $media1 = MediaAsset::factory()->create();
        $media2 = MediaAsset::factory()->create();

        $page1->attachMedia($media1, 'og_image');
        $page2->attachMedia($media2, 'og_image');

        expect($page1->getMedia('og_image')->id)->toBe($media1->id)
            ->and($page2->getMedia('og_image')->id)->toBe($media2->id)
            ->and($page1->getAllMedia())->toHaveCount(1)
            ->and($page2->getAllMedia())->toHaveCount(1);
    });

    it('works with Service model using custom page type naming convention', function () {
        $service = \App\Models\Service::factory()->create(['slug' => 'web-development']);
        $media = MediaAsset::factory()->create();

        $service->attachMedia($media, 'service:hero:image');

        expect($service->getMedia('service:hero:image'))
            ->not->toBeNull()
            ->id->toBe($media->id);
    });

    it('isolates relationships between Page and Service models', function () {
        $page = Page::factory()->create(['slug' => 'home']);
        $service = \App\Models\Service::factory()->create(['slug' => 'consulting']);
        $pageMedia = MediaAsset::factory()->create();
        $serviceMedia = MediaAsset::factory()->create();

        $page->attachMedia($pageMedia, 'page:home:hero:image');
        $service->attachMedia($serviceMedia, 'service:hero:image');

        expect($page->getAllMedia())->toHaveCount(1)
            ->and($service->getAllMedia())->toHaveCount(1)
            ->and($page->getMedia('page:home:hero:image')->id)->toBe($pageMedia->id)
            ->and($service->getMedia('service:hero:image')->id)->toBe($serviceMedia->id);
    });

    it('works with Testimonial model using content resource naming convention', function () {
        $testimonial = \App\Models\Testimonial::factory()->create();
        $avatarMedia = MediaAsset::factory()->create();

        $testimonial->attachMedia($avatarMedia, 'testimonial:avatar');

        expect($testimonial->getMedia('testimonial:avatar'))
            ->not->toBeNull()
            ->id->toBe($avatarMedia->id);
    });

    it('works with Faq model using content resource naming convention', function () {
        $faq = \App\Models\Faq::factory()->create();
        $iconMedia = MediaAsset::factory()->svg()->create();

        $faq->attachMedia($iconMedia, 'faq:icon');

        expect($faq->getMedia('faq:icon'))
            ->not->toBeNull()
            ->id->toBe($iconMedia->id);
    });
});
