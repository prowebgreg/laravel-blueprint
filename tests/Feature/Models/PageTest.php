<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

describe('CRUD operations', function () {
    test('creates page with minimal required fields', function () {
        $page = Page::factory()->create(['name' => 'Test Page']);

        expect($page)->toBeInstanceOf(Page::class);
        expect($page->name)->toBe('Test Page');
        expect($page->slug)->toBe('test-page');
        expect($page->status)->toBe(ContentStatus::Draft);
        expect($page->template)->toBe('default');
    });

    test('creates page with all fields populated', function () {
        $page = Page::factory()
            ->published()
            ->withContentBlocks()
            ->withSeoData()
            ->withTemplate('custom')
            ->create(['name' => 'Full Page']);

        expect($page->name)->toBe('Full Page');
        expect($page->slug)->toBe('full-page');
        expect($page->template)->toBe('custom');
        expect($page->status)->toBe(ContentStatus::Published);
        expect($page->content_blocks)->toBeArray();
        expect($page->content_blocks)->toHaveCount(2);
        expect($page->meta_title)->not->toBeNull();
        expect($page->meta_description)->not->toBeNull();
        expect($page->og_title)->not->toBeNull();
        expect($page->twitter_title)->not->toBeNull();
    });

    test('reads page and verifies all fields', function () {
        $data = [
            'name' => 'About Us',
            'template' => 'about',
            'status' => ContentStatus::Published,
            'meta_title' => 'About Our Company',
            'meta_description' => 'Learn about our mission and values',
            'canonical_url' => 'https://example.com/about',
        ];

        $page = Page::factory()->create($data);
        $retrieved = Page::find($page->id);

        expect($retrieved->name)->toBe($data['name']);
        expect($retrieved->template)->toBe($data['template']);
        expect($retrieved->status)->toBe($data['status']);
        expect($retrieved->meta_title)->toBe($data['meta_title']);
        expect($retrieved->meta_description)->toBe($data['meta_description']);
        expect($retrieved->canonical_url)->toBe($data['canonical_url']);
    });

    test('updates page fields', function () {
        $page = Page::factory()->create([
            'name' => 'Original Name',
            'template' => 'default',
        ]);

        $page->update([
            'name' => 'Updated Name',
            'template' => 'custom',
            'meta_title' => 'New Title',
        ]);

        $page->refresh();

        expect($page->name)->toBe('Updated Name');
        expect($page->template)->toBe('custom');
        expect($page->meta_title)->toBe('New Title');
    });

    test('soft deletes page', function () {
        $page = Page::factory()->create(['name' => 'To Delete']);

        $page->delete();

        expect(Page::find($page->id))->toBeNull();
        expect(Page::withTrashed()->find($page->id))->not->toBeNull();
        expect($page->deleted_at)->not->toBeNull();
    });

    test('restores soft-deleted page', function () {
        $page = Page::factory()->create(['name' => 'To Restore']);
        $page->delete();

        expect(Page::find($page->id))->toBeNull();

        $page->restore();

        expect(Page::find($page->id))->not->toBeNull();
        expect($page->deleted_at)->toBeNull();
    });

    test('queries all pages', function () {
        Page::factory()->count(3)->create();

        $pages = Page::all();

        expect($pages)->toHaveCount(3);
    });

    test('queries pages by status', function () {
        Page::factory()->draft()->count(2)->create();
        Page::factory()->published()->count(3)->create();

        $drafts = Page::where('status', ContentStatus::Draft)->get();
        $published = Page::where('status', ContentStatus::Published)->get();

        expect($drafts)->toHaveCount(2);
        expect($published)->toHaveCount(3);
    });

    test('soft-deleted pages excluded from normal queries', function () {
        Page::factory()->count(3)->create();
        $toDelete = Page::factory()->create();
        $toDelete->delete();

        $pages = Page::all();

        expect($pages)->toHaveCount(3);
        expect(Page::withTrashed()->get())->toHaveCount(4);
    });
});

describe('status transitions', function () {
    test('default status is draft', function () {
        $page = Page::factory()->create();

        expect($page->status)->toBe(ContentStatus::Draft);
    });

    test('changes status from draft to published', function () {
        $page = Page::factory()->draft()->create();

        expect($page->status)->toBe(ContentStatus::Draft);

        $page->update(['status' => ContentStatus::Published]);

        expect($page->fresh()->status)->toBe(ContentStatus::Published);
    });

    test('changes status from published to draft', function () {
        $page = Page::factory()->published()->create();

        expect($page->status)->toBe(ContentStatus::Published);

        $page->update(['status' => ContentStatus::Draft]);

        expect($page->fresh()->status)->toBe(ContentStatus::Draft);
    });

    test('queries only published pages', function () {
        Page::factory()->draft()->count(3)->create();
        Page::factory()->published()->count(2)->create();

        $published = Page::where('status', ContentStatus::Published)->get();

        expect($published)->toHaveCount(2);
        foreach ($published as $page) {
            expect($page->status)->toBe(ContentStatus::Published);
        }
    });

    test('queries only draft pages', function () {
        Page::factory()->draft()->count(3)->create();
        Page::factory()->published()->count(2)->create();

        $drafts = Page::where('status', ContentStatus::Draft)->get();

        expect($drafts)->toHaveCount(3);
        foreach ($drafts as $page) {
            expect($page->status)->toBe(ContentStatus::Draft);
        }
    });
});

describe('content blocks', function () {
    test('creates page with content blocks', function () {
        $page = Page::factory()->withContentBlocks()->create();

        expect($page->content_blocks)->toBeArray();
        expect($page->content_blocks)->toHaveCount(2);
        expect($page->content_blocks[0]['type'])->toBe('hero');
        expect($page->content_blocks[1]['type'])->toBe('cta');
    });

    test('stores and retrieves content blocks correctly', function () {
        $blocks = [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => 'Welcome to Our Site',
                    'lede' => 'This is a test hero section',
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Get Started',
                    'cta_button_text' => 'Sign Up',
                ],
            ],
        ];

        $page = Page::factory()->create(['content_blocks' => $blocks]);
        $fresh = $page->fresh();

        expect($fresh->content_blocks)->toEqual($blocks);
    });

    test('adds block to existing page', function () {
        $page = Page::factory()->create(['content_blocks' => null]);

        $page->addBlock(['type' => 'hero', 'data' => ['heading' => 'New Hero']]);
        $page->save();

        $fresh = $page->fresh();

        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('hero');
    });

    test('removes block from page', function () {
        $page = Page::factory()->withContentBlocks()->create();

        expect($page->content_blocks)->toHaveCount(2);

        $page->removeBlock(0);
        $page->save();

        $fresh = $page->fresh();

        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('cta');
    });

    test('reorders blocks on page', function () {
        $blocks = [
            ['type' => 'hero', 'data' => []],
            ['type' => 'cta', 'data' => []],
            ['type' => 'hero', 'data' => []],
        ];

        $page = Page::factory()->create(['content_blocks' => $blocks]);

        $page->reorderBlocks([2, 0, 1]);
        $page->save();

        $fresh = $page->fresh();

        expect($fresh->content_blocks[0]['type'])->toBe('hero');
        expect($fresh->content_blocks[1]['type'])->toBe('hero');
        expect($fresh->content_blocks[2]['type'])->toBe('cta');
    });

    test('page with null content_blocks is valid', function () {
        $page = Page::factory()->create(['content_blocks' => null]);

        expect($page->content_blocks)->toBeNull();
        expect($page->exists)->toBeTrue();
    });

    test('page with empty array content_blocks is valid', function () {
        $page = Page::factory()->create(['content_blocks' => []]);

        expect($page->fresh()->content_blocks)->toBe([]);
        expect($page->exists)->toBeTrue();
    });

    test('adds multiple blocks sequentially', function () {
        $page = Page::factory()->create(['content_blocks' => null]);

        $page->addBlock(['type' => 'hero', 'data' => ['heading' => 'First']]);
        $page->addBlock(['type' => 'cta', 'data' => ['heading' => 'Second']]);
        $page->addBlock(['type' => 'hero', 'data' => ['heading' => 'Third']]);
        $page->save();

        $fresh = $page->fresh();

        expect($fresh->content_blocks)->toHaveCount(3);
        expect($fresh->content_blocks[0]['data']['heading'])->toBe('First');
        expect($fresh->content_blocks[1]['data']['heading'])->toBe('Second');
        expect($fresh->content_blocks[2]['data']['heading'])->toBe('Third');
    });
});

describe('SEO fields', function () {
    test('creates page with SEO data', function () {
        $page = Page::factory()->withSeoData()->create();

        expect($page->meta_title)->not->toBeNull();
        expect($page->meta_description)->not->toBeNull();
        expect($page->meta_author)->not->toBeNull();
        expect($page->canonical_url)->not->toBeNull();
    });

    test('SEO fields are stored and retrieved correctly', function () {
        $seoData = [
            'meta_title' => 'Test Page Title',
            'meta_description' => 'This is a test description',
            'meta_author' => 'John Doe',
            'meta_robots' => true,
            'canonical_url' => 'https://example.com/test',
        ];

        $page = Page::factory()->create($seoData);
        $fresh = $page->fresh();

        expect($fresh->meta_title)->toBe($seoData['meta_title']);
        expect($fresh->meta_description)->toBe($seoData['meta_description']);
        expect($fresh->meta_author)->toBe($seoData['meta_author']);
        expect($fresh->meta_robots)->toBe($seoData['meta_robots']);
        expect($fresh->canonical_url)->toBe($seoData['canonical_url']);
    });

    test('OG fields mirror meta fields when not explicitly set', function () {
        $page = Page::factory()->create([
            'meta_title' => 'My Page Title',
            'meta_description' => 'My page description',
            'og_title' => null,
            'og_description' => null,
        ]);

        expect($page->og_title)->toBe('My Page Title');
        expect($page->og_description)->toBe('My page description');
    });

    test('Twitter fields mirror meta fields when not explicitly set', function () {
        $page = Page::factory()->create([
            'meta_title' => 'My Page Title',
            'meta_description' => 'My page description',
            'twitter_title' => null,
            'twitter_description' => null,
        ]);

        expect($page->twitter_title)->toBe('My Page Title');
        expect($page->twitter_description)->toBe('My page description');
    });

    test('manually set OG fields are independent from meta', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => 'Custom OG Title',
            'og_description' => 'Custom OG Description',
        ]);

        expect($page->og_title)->toBe('Custom OG Title');
        expect($page->og_description)->toBe('Custom OG Description');

        // Update meta fields - OG should remain independent
        $page->update([
            'meta_title' => 'Updated Meta Title',
            'meta_description' => 'Updated Meta Description',
        ]);

        $page->refresh();

        expect($page->og_title)->toBe('Custom OG Title');
        expect($page->og_description)->toBe('Custom OG Description');
    });

    test('manually set Twitter fields are independent from meta', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'twitter_title' => 'Custom Twitter Title',
            'twitter_description' => 'Custom Twitter Description',
        ]);

        expect($page->twitter_title)->toBe('Custom Twitter Title');
        expect($page->twitter_description)->toBe('Custom Twitter Description');

        // Update meta fields - Twitter should remain independent
        $page->update([
            'meta_title' => 'Updated Meta Title',
            'meta_description' => 'Updated Meta Description',
        ]);

        $page->refresh();

        expect($page->twitter_title)->toBe('Custom Twitter Title');
        expect($page->twitter_description)->toBe('Custom Twitter Description');
    });

    test('breadcrumbs are stored correctly', function () {
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'About', 'url' => '/about'],
            ['label' => 'Team', 'url' => null],
        ];

        $page = Page::factory()->create(['breadcrumbs' => $breadcrumbs]);

        expect($page->fresh()->breadcrumbs)->toEqual($breadcrumbs);
    });

    test('OG type is stored correctly', function () {
        $page = Page::factory()->create(['og_type' => OgType::Article]);

        expect($page->og_type)->toBe(OgType::Article);
    });

    test('meta robots defaults to false', function () {
        $page = Page::factory()->create();

        expect($page->meta_robots)->toBe(false);
    });

    test('updating meta fields affects mirrored social fields when null', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Original Title',
            'meta_description' => 'Original Description',
            'og_title' => null,
            'og_description' => null,
            'twitter_title' => null,
            'twitter_description' => null,
        ]);

        expect($page->og_title)->toBe('Original Title');
        expect($page->twitter_title)->toBe('Original Title');

        $page->update([
            'meta_title' => 'Updated Title',
            'meta_description' => 'Updated Description',
        ]);
        $page->refresh();

        expect($page->og_title)->toBe('Updated Title');
        expect($page->og_description)->toBe('Updated Description');
        expect($page->twitter_title)->toBe('Updated Title');
        expect($page->twitter_description)->toBe('Updated Description');
    });
});

describe('SEO mirroring integration', function () {
    test('empty string in og_title does not mirror - returns empty string', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Meta Title',
            'og_title' => '',
        ]);

        expect($page->og_title)->toBe('');
    });

    test('empty string in og_description does not mirror - returns empty string', function () {
        $page = Page::factory()->create([
            'meta_description' => 'Meta Description',
            'og_description' => '',
        ]);

        expect($page->og_description)->toBe('');
    });

    test('empty string in twitter_title does not mirror - returns empty string', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Meta Title',
            'twitter_title' => '',
        ]);

        expect($page->twitter_title)->toBe('');
    });

    test('empty string in twitter_description does not mirror - returns empty string', function () {
        $page = Page::factory()->create([
            'meta_description' => 'Meta Description',
            'twitter_description' => '',
        ]);

        expect($page->twitter_description)->toBe('');
    });

    test('setting og_title back to NULL re-enables mirroring from meta_title', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Meta Title',
            'og_title' => 'Custom OG Title',
        ]);

        expect($page->og_title)->toBe('Custom OG Title');

        $page->update(['og_title' => null]);
        $page->refresh();

        expect($page->og_title)->toBe('Meta Title');
    });

    test('setting og_description back to NULL re-enables mirroring from meta_description', function () {
        $page = Page::factory()->create([
            'meta_description' => 'Meta Description',
            'og_description' => 'Custom OG Description',
        ]);

        expect($page->og_description)->toBe('Custom OG Description');

        $page->update(['og_description' => null]);
        $page->refresh();

        expect($page->og_description)->toBe('Meta Description');
    });

    test('setting twitter_title back to NULL re-enables mirroring from meta_title', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Meta Title',
            'twitter_title' => 'Custom Twitter Title',
        ]);

        expect($page->twitter_title)->toBe('Custom Twitter Title');

        $page->update(['twitter_title' => null]);
        $page->refresh();

        expect($page->twitter_title)->toBe('Meta Title');
    });

    test('setting twitter_description back to NULL re-enables mirroring from meta_description', function () {
        $page = Page::factory()->create([
            'meta_description' => 'Meta Description',
            'twitter_description' => 'Custom Twitter Description',
        ]);

        expect($page->twitter_description)->toBe('Custom Twitter Description');

        $page->update(['twitter_description' => null]);
        $page->refresh();

        expect($page->twitter_description)->toBe('Meta Description');
    });

    test('mixed mirroring - some fields mirrored and some independent', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => 'Custom OG Title',
            'og_description' => null, // Mirrored
            'twitter_title' => null, // Mirrored
            'twitter_description' => 'Custom Twitter Description',
        ]);

        expect($page->og_title)->toBe('Custom OG Title');
        expect($page->og_description)->toBe('Meta Description');
        expect($page->twitter_title)->toBe('Meta Title');
        expect($page->twitter_description)->toBe('Custom Twitter Description');
    });

    test('updating meta_title affects only NULL social fields - custom ones remain independent', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Original Meta Title',
            'og_title' => 'Custom OG Title',
            'twitter_title' => null,
        ]);

        expect($page->og_title)->toBe('Custom OG Title');
        expect($page->twitter_title)->toBe('Original Meta Title');

        $page->update(['meta_title' => 'Updated Meta Title']);
        $page->refresh();

        expect($page->og_title)->toBe('Custom OG Title'); // Remains independent
        expect($page->twitter_title)->toBe('Updated Meta Title'); // Mirrors updated value
    });

    test('updating meta_description affects only NULL social fields - custom ones remain independent', function () {
        $page = Page::factory()->create([
            'meta_description' => 'Original Meta Description',
            'og_description' => null,
            'twitter_description' => 'Custom Twitter Description',
        ]);

        expect($page->og_description)->toBe('Original Meta Description');
        expect($page->twitter_description)->toBe('Custom Twitter Description');

        $page->update(['meta_description' => 'Updated Meta Description']);
        $page->refresh();

        expect($page->og_description)->toBe('Updated Meta Description'); // Mirrors updated value
        expect($page->twitter_description)->toBe('Custom Twitter Description'); // Remains independent
    });

    test('all social fields can have completely different values', function () {
        $page = Page::factory()->create([
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => 'Unique OG Title',
            'og_description' => 'Unique OG Description',
            'twitter_title' => 'Unique Twitter Title',
            'twitter_description' => 'Unique Twitter Description',
        ]);

        expect($page->og_title)->toBe('Unique OG Title');
        expect($page->og_description)->toBe('Unique OG Description');
        expect($page->twitter_title)->toBe('Unique Twitter Title');
        expect($page->twitter_description)->toBe('Unique Twitter Description');

        // None should match meta fields
        expect($page->og_title)->not->toBe($page->meta_title);
        expect($page->og_description)->not->toBe($page->meta_description);
        expect($page->twitter_title)->not->toBe($page->meta_title);
        expect($page->twitter_description)->not->toBe($page->meta_description);
    });
});

describe('slug behavior', function () {
    test('slug is auto-generated from name', function () {
        $page = Page::factory()->create(['name' => 'About Our Company']);

        expect($page->slug)->toBe('about-our-company');
    });

    test('duplicate names get unique slugs with suffix', function () {
        Page::factory()->create(['name' => 'Test Page']);
        $page2 = Page::factory()->create(['name' => 'Test Page']);
        $page3 = Page::factory()->create(['name' => 'Test Page']);

        expect($page2->slug)->toBe('test-page-2');
        expect($page3->slug)->toBe('test-page-3');
    });

    test('includes soft deleted pages when checking slug uniqueness', function () {
        $page1 = Page::factory()->create(['name' => 'Test Page']);
        expect($page1->slug)->toBe('test-page');

        $page1->delete(); // Soft delete

        $page2 = Page::factory()->create(['name' => 'Test Page']);
        expect($page2->slug)->toBe('test-page-2');
    });

    test('reserved slugs are rejected', function () {
        Page::factory()->create(['name' => 'admin']);
    })->throws(ValidationException::class);

    test('slug remains stable when updating name', function () {
        $page = Page::factory()->create(['name' => 'Original Name']);

        expect($page->slug)->toBe('original-name');

        $page->update(['name' => 'Updated Name']);
        $page->refresh();

        expect($page->slug)->toBe('original-name');
        expect($page->name)->toBe('Updated Name');
    });

    test('manually set slug is preserved', function () {
        $page = Page::factory()->create([
            'name' => 'Test Page',
            'slug' => 'custom-slug',
        ]);

        expect($page->slug)->toBe('custom-slug');
    });

    test('slug can be manually updated', function () {
        $page = Page::factory()->create(['name' => 'Test Page']);

        expect($page->slug)->toBe('test-page');

        $page->update(['slug' => 'new-custom-slug']);
        $page->refresh();

        expect($page->slug)->toBe('new-custom-slug');
    });

    test('special characters in name are handled', function () {
        $page = Page::factory()->create(['name' => 'Test & Page @ 2024!']);

        expect($page->slug)->toBe('test-page-at-2024');
    });

    test('unicode characters are handled', function () {
        $page = Page::factory()->create(['name' => 'Tëst Pâgé']);

        expect($page->slug)->toBeString();
        expect($page->slug)->not->toBeEmpty();
    });

    test('empty string name throws exception', function () {
        Page::factory()->create(['name' => '']);
    })->throws(\RuntimeException::class);

    test('slug is url safe', function () {
        $page = Page::factory()->create(['name' => 'Test/Page\\With:Invalid*Characters']);

        expect($page->slug)->toMatch('/^[a-z0-9-]+$/');
    });
});

describe('page-specific tests', function () {
    test('template field is stored correctly', function () {
        $page = Page::factory()->withTemplate('custom-template')->create();

        expect($page->template)->toBe('custom-template');
    });

    test('page table is correct', function () {
        $page = Page::factory()->create();

        expect($page->getTable())->toBe('pages');
    });

    test('page uses HasSeo trait', function () {
        $traits = class_uses_recursive(Page::class);

        expect($traits)->toHaveKey(\App\Traits\HasSeo::class);
    });

    test('page uses HasSlug trait', function () {
        $traits = class_uses_recursive(Page::class);

        expect($traits)->toHaveKey(\App\Traits\HasSlug::class);
    });

    test('page uses HasContentBlocks trait', function () {
        $traits = class_uses_recursive(Page::class);

        expect($traits)->toHaveKey(\App\Traits\HasContentBlocks::class);
    });

    test('page uses HasRelatedContent trait', function () {
        $traits = class_uses_recursive(Page::class);

        expect($traits)->toHaveKey(\App\Traits\HasRelatedContent::class);
    });

    test('page uses SoftDeletes trait', function () {
        $traits = class_uses_recursive(Page::class);

        expect($traits)->toHaveKey(\Illuminate\Database\Eloquent\SoftDeletes::class);
    });

    test('page casts are correct', function () {
        $page = Page::factory()->create();

        $casts = $page->getCasts();

        expect($casts['status'])->toBe(ContentStatus::class);
        expect($casts['og_type'])->toBe(OgType::class);
        expect($casts['meta_robots'])->toBe('boolean');
        expect($casts['breadcrumbs'])->toBe('array');
    });

    test('fillable fields include all required attributes', function () {
        $page = new Page;

        $fillable = $page->getFillable();

        $requiredFields = [
            'name',
            'slug',
            'template',
            'status',
            'content_blocks',
            'meta_title',
            'meta_description',
            'meta_author',
            'meta_robots',
            'canonical_url',
            'og_title',
            'og_description',
            'og_type',
            'og_image',
            'twitter_title',
            'twitter_description',
            'twitter_image',
            'breadcrumbs',
        ];

        foreach ($requiredFields as $field) {
            expect($fillable)->toContain($field);
        }
    });

    test('timestamps are present', function () {
        $page = Page::factory()->create();

        expect($page->created_at)->not->toBeNull();
        expect($page->updated_at)->not->toBeNull();
        expect($page->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($page->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    test('soft delete timestamp is nullable', function () {
        $page = Page::factory()->create();

        expect($page->deleted_at)->toBeNull();

        $page->delete();

        expect($page->deleted_at)->not->toBeNull();
        expect($page->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });
});

describe('edge cases', function () {
    test('handles very long page name', function () {
        // Use a name that's long but within database limits for 'name' field
        $longName = str_repeat('Very-Long-Name-', 10); // ~150 chars
        $page = Page::factory()->create(['name' => $longName]);

        expect($page->slug)->toBeString();
        expect($page->slug)->not->toBeEmpty();
        expect(strlen($page->slug))->toBeLessThanOrEqual(255);
    });

    test('handles numeric page name', function () {
        $page = Page::factory()->create(['name' => '12345']);

        expect($page->slug)->toBe('12345');
    });

    test('handles page with only special characters', function () {
        Page::factory()->create(['name' => '!!!']);
    })->throws(\RuntimeException::class);

    test('handles complex nested content blocks', function () {
        $blocks = [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => 'Welcome',
                    'nested' => [
                        'level1' => [
                            'level2' => ['value' => 'deep'],
                        ],
                    ],
                ],
            ],
        ];

        $page = Page::factory()->create(['content_blocks' => $blocks]);

        expect($page->fresh()->content_blocks)->toEqual($blocks);
    });

    test('handles large number of content blocks', function () {
        $blocks = [];
        for ($i = 0; $i < 50; $i++) {
            $blocks[] = ['type' => 'hero', 'data' => ['index' => $i]];
        }

        $page = Page::factory()->create(['content_blocks' => $blocks]);

        expect($page->fresh()->content_blocks)->toHaveCount(50);
    });

    test('handles unicode in content blocks', function () {
        $blocks = [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => 'こんにちは',
                    'emoji' => '😀🎉🚀',
                ],
            ],
        ];

        $page = Page::factory()->create(['content_blocks' => $blocks]);

        expect($page->fresh()->content_blocks[0]['data']['heading'])->toBe('こんにちは');
        expect($page->fresh()->content_blocks[0]['data']['emoji'])->toBe('😀🎉🚀');
    });

    test('handles null values in all nullable SEO fields', function () {
        $page = Page::factory()->create([
            'meta_title' => null,
            'meta_description' => null,
            'meta_author' => null,
            'canonical_url' => null,
            'og_title' => null,
            'og_description' => null,
            'og_image' => null,
            'twitter_title' => null,
            'twitter_description' => null,
            'twitter_image' => null,
            'breadcrumbs' => null,
        ]);

        expect($page->exists)->toBeTrue();
        expect($page->meta_title)->toBeNull();
        expect($page->og_title)->toBeNull();
        expect($page->twitter_title)->toBeNull();
    });

    test('multiple operations in sequence', function () {
        $page = Page::factory()->create(['name' => 'Test']);

        // Add blocks
        $page->addBlock(['type' => 'hero', 'data' => []]);
        $page->addBlock(['type' => 'cta', 'data' => []]);
        $page->save();

        // Update status
        $page->update(['status' => ContentStatus::Published]);

        // Remove a block
        $page->removeBlock(0);
        $page->save();

        // Update SEO
        $page->update(['meta_title' => 'Updated Title']);

        $fresh = $page->fresh();

        expect($fresh->status)->toBe(ContentStatus::Published);
        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('cta');
        expect($fresh->meta_title)->toBe('Updated Title');
    });
});
