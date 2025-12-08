<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

describe('CRUD operations', function () {
    test('creates service with minimal required fields', function () {
        $service = Service::factory()->create(['name' => 'Test Service']);

        expect($service)->toBeInstanceOf(Service::class);
        expect($service->name)->toBe('Test Service');
        expect($service->slug)->toBe('test-service');
        expect($service->status)->toBe(ContentStatus::Draft);
    });

    test('creates service with all fields populated', function () {
        $service = Service::factory()
            ->published()
            ->withContentBlocks()
            ->withSeoData()
            ->create(['name' => 'Full Service']);

        expect($service->name)->toBe('Full Service');
        expect($service->slug)->toBe('full-service');
        expect($service->status)->toBe(ContentStatus::Published);
        expect($service->content_blocks)->toBeArray();
        expect($service->content_blocks)->toHaveCount(2);
        expect($service->meta_title)->not->toBeNull();
        expect($service->meta_description)->not->toBeNull();
        expect($service->og_title)->not->toBeNull();
        expect($service->twitter_title)->not->toBeNull();
    });

    test('reads service and verifies all fields', function () {
        $data = [
            'name' => 'Web Development',
            'status' => ContentStatus::Published,
            'meta_title' => 'Web Development Services',
            'meta_description' => 'Professional web development for your business',
            'canonical_url' => 'https://example.com/services/web-development',
        ];

        $service = Service::factory()->create($data);
        $retrieved = Service::find($service->id);

        expect($retrieved->name)->toBe($data['name']);
        expect($retrieved->status)->toBe($data['status']);
        expect($retrieved->meta_title)->toBe($data['meta_title']);
        expect($retrieved->meta_description)->toBe($data['meta_description']);
        expect($retrieved->canonical_url)->toBe($data['canonical_url']);
    });

    test('updates service fields', function () {
        $service = Service::factory()->create([
            'name' => 'Original Name',
        ]);

        $service->update([
            'name' => 'Updated Name',
            'meta_title' => 'New Title',
        ]);

        $service->refresh();

        expect($service->name)->toBe('Updated Name');
        expect($service->meta_title)->toBe('New Title');
    });

    test('soft deletes service', function () {
        $service = Service::factory()->create(['name' => 'To Delete']);

        $service->delete();

        expect(Service::find($service->id))->toBeNull();
        expect(Service::withTrashed()->find($service->id))->not->toBeNull();
        expect($service->deleted_at)->not->toBeNull();
    });

    test('restores soft-deleted service', function () {
        $service = Service::factory()->create(['name' => 'To Restore']);
        $service->delete();

        expect(Service::find($service->id))->toBeNull();

        $service->restore();

        expect(Service::find($service->id))->not->toBeNull();
        expect($service->deleted_at)->toBeNull();
    });

    test('queries all services', function () {
        Service::factory()->count(3)->create();

        $services = Service::all();

        expect($services)->toHaveCount(3);
    });

    test('queries services by status', function () {
        Service::factory()->draft()->count(2)->create();
        Service::factory()->published()->count(3)->create();

        $drafts = Service::where('status', ContentStatus::Draft)->get();
        $published = Service::where('status', ContentStatus::Published)->get();

        expect($drafts)->toHaveCount(2);
        expect($published)->toHaveCount(3);
    });

    test('soft-deleted services excluded from normal queries', function () {
        Service::factory()->count(3)->create();
        $toDelete = Service::factory()->create();
        $toDelete->delete();

        $services = Service::all();

        expect($services)->toHaveCount(3);
        expect(Service::withTrashed()->get())->toHaveCount(4);
    });
});

describe('status transitions', function () {
    test('default status is draft', function () {
        $service = Service::factory()->create();

        expect($service->status)->toBe(ContentStatus::Draft);
    });

    test('changes status from draft to published', function () {
        $service = Service::factory()->draft()->create();

        expect($service->status)->toBe(ContentStatus::Draft);

        $service->update(['status' => ContentStatus::Published]);

        expect($service->fresh()->status)->toBe(ContentStatus::Published);
    });

    test('changes status from published to draft', function () {
        $service = Service::factory()->published()->create();

        expect($service->status)->toBe(ContentStatus::Published);

        $service->update(['status' => ContentStatus::Draft]);

        expect($service->fresh()->status)->toBe(ContentStatus::Draft);
    });

    test('queries only published services', function () {
        Service::factory()->draft()->count(3)->create();
        Service::factory()->published()->count(2)->create();

        $published = Service::where('status', ContentStatus::Published)->get();

        expect($published)->toHaveCount(2);
        foreach ($published as $service) {
            expect($service->status)->toBe(ContentStatus::Published);
        }
    });

    test('queries only draft services', function () {
        Service::factory()->draft()->count(3)->create();
        Service::factory()->published()->count(2)->create();

        $drafts = Service::where('status', ContentStatus::Draft)->get();

        expect($drafts)->toHaveCount(3);
        foreach ($drafts as $service) {
            expect($service->status)->toBe(ContentStatus::Draft);
        }
    });
});

describe('content blocks', function () {
    test('creates service with content blocks', function () {
        $service = Service::factory()->withContentBlocks()->create();

        expect($service->content_blocks)->toBeArray();
        expect($service->content_blocks)->toHaveCount(2);
        expect($service->content_blocks[0]['type'])->toBe('hero');
        expect($service->content_blocks[1]['type'])->toBe('cta');
    });

    test('stores and retrieves content blocks correctly', function () {
        $blocks = [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => 'Welcome to Our Services',
                    'lede' => 'This is a test hero section',
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Get Started',
                    'cta_button_text' => 'Contact Us',
                ],
            ],
        ];

        $service = Service::factory()->create(['content_blocks' => $blocks]);
        $fresh = $service->fresh();

        expect($fresh->content_blocks)->toEqual($blocks);
    });

    test('adds block to existing service', function () {
        $service = Service::factory()->create(['content_blocks' => null]);

        $service->addBlock(['type' => 'hero', 'data' => ['heading' => 'New Hero']]);
        $service->save();

        $fresh = $service->fresh();

        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('hero');
    });

    test('removes block from service', function () {
        $service = Service::factory()->withContentBlocks()->create();

        expect($service->content_blocks)->toHaveCount(2);

        $service->removeBlock(0);
        $service->save();

        $fresh = $service->fresh();

        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('cta');
    });

    test('reorders blocks on service', function () {
        $blocks = [
            ['type' => 'hero', 'data' => []],
            ['type' => 'cta', 'data' => []],
            ['type' => 'hero', 'data' => []],
        ];

        $service = Service::factory()->create(['content_blocks' => $blocks]);

        $service->reorderBlocks([2, 0, 1]);
        $service->save();

        $fresh = $service->fresh();

        expect($fresh->content_blocks[0]['type'])->toBe('hero');
        expect($fresh->content_blocks[1]['type'])->toBe('hero');
        expect($fresh->content_blocks[2]['type'])->toBe('cta');
    });

    test('service with null content_blocks is valid', function () {
        $service = Service::factory()->create(['content_blocks' => null]);

        expect($service->content_blocks)->toBeNull();
        expect($service->exists)->toBeTrue();
    });

    test('service with empty array content_blocks is valid', function () {
        $service = Service::factory()->create(['content_blocks' => []]);

        expect($service->fresh()->content_blocks)->toBe([]);
        expect($service->exists)->toBeTrue();
    });

    test('adds multiple blocks sequentially', function () {
        $service = Service::factory()->create(['content_blocks' => null]);

        $service->addBlock(['type' => 'hero', 'data' => ['heading' => 'First']]);
        $service->addBlock(['type' => 'cta', 'data' => ['heading' => 'Second']]);
        $service->addBlock(['type' => 'hero', 'data' => ['heading' => 'Third']]);
        $service->save();

        $fresh = $service->fresh();

        expect($fresh->content_blocks)->toHaveCount(3);
        expect($fresh->content_blocks[0]['data']['heading'])->toBe('First');
        expect($fresh->content_blocks[1]['data']['heading'])->toBe('Second');
        expect($fresh->content_blocks[2]['data']['heading'])->toBe('Third');
    });
});

describe('SEO fields', function () {
    test('creates service with SEO data', function () {
        $service = Service::factory()->withSeoData()->create();

        expect($service->meta_title)->not->toBeNull();
        expect($service->meta_description)->not->toBeNull();
        expect($service->meta_author)->not->toBeNull();
        expect($service->canonical_url)->not->toBeNull();
    });

    test('SEO fields are stored and retrieved correctly', function () {
        $seoData = [
            'meta_title' => 'Test Service Title',
            'meta_description' => 'This is a test description',
            'meta_author' => 'John Doe',
            'meta_robots' => true,
            'canonical_url' => 'https://example.com/services/test',
        ];

        $service = Service::factory()->create($seoData);
        $fresh = $service->fresh();

        expect($fresh->meta_title)->toBe($seoData['meta_title']);
        expect($fresh->meta_description)->toBe($seoData['meta_description']);
        expect($fresh->meta_author)->toBe($seoData['meta_author']);
        expect($fresh->meta_robots)->toBe($seoData['meta_robots']);
        expect($fresh->canonical_url)->toBe($seoData['canonical_url']);
    });

    test('OG fields mirror meta fields when not explicitly set', function () {
        $service = Service::factory()->create([
            'meta_title' => 'My Service Title',
            'meta_description' => 'My service description',
            'og_title' => null,
            'og_description' => null,
        ]);

        expect($service->og_title)->toBe('My Service Title');
        expect($service->og_description)->toBe('My service description');
    });

    test('Twitter fields mirror meta fields when not explicitly set', function () {
        $service = Service::factory()->create([
            'meta_title' => 'My Service Title',
            'meta_description' => 'My service description',
            'twitter_title' => null,
            'twitter_description' => null,
        ]);

        expect($service->twitter_title)->toBe('My Service Title');
        expect($service->twitter_description)->toBe('My service description');
    });

    test('manually set OG fields are independent from meta', function () {
        $service = Service::factory()->create([
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => 'Custom OG Title',
            'og_description' => 'Custom OG Description',
        ]);

        expect($service->og_title)->toBe('Custom OG Title');
        expect($service->og_description)->toBe('Custom OG Description');

        // Update meta fields - OG should remain independent
        $service->update([
            'meta_title' => 'Updated Meta Title',
            'meta_description' => 'Updated Meta Description',
        ]);

        $service->refresh();

        expect($service->og_title)->toBe('Custom OG Title');
        expect($service->og_description)->toBe('Custom OG Description');
    });

    test('manually set Twitter fields are independent from meta', function () {
        $service = Service::factory()->create([
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'twitter_title' => 'Custom Twitter Title',
            'twitter_description' => 'Custom Twitter Description',
        ]);

        expect($service->twitter_title)->toBe('Custom Twitter Title');
        expect($service->twitter_description)->toBe('Custom Twitter Description');

        // Update meta fields - Twitter should remain independent
        $service->update([
            'meta_title' => 'Updated Meta Title',
            'meta_description' => 'Updated Meta Description',
        ]);

        $service->refresh();

        expect($service->twitter_title)->toBe('Custom Twitter Title');
        expect($service->twitter_description)->toBe('Custom Twitter Description');
    });

    test('breadcrumbs are stored correctly', function () {
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Services', 'url' => '/services'],
            ['label' => 'Web Development', 'url' => null],
        ];

        $service = Service::factory()->create(['breadcrumbs' => $breadcrumbs]);

        expect($service->fresh()->breadcrumbs)->toEqual($breadcrumbs);
    });

    test('OG type is stored correctly', function () {
        $service = Service::factory()->create(['og_type' => OgType::Article]);

        expect($service->og_type)->toBe(OgType::Article);
    });

    test('meta robots defaults to false', function () {
        $service = Service::factory()->create();

        expect($service->meta_robots)->toBe(false);
    });

    test('updating meta fields affects mirrored social fields when null', function () {
        $service = Service::factory()->create([
            'meta_title' => 'Original Title',
            'meta_description' => 'Original Description',
            'og_title' => null,
            'og_description' => null,
            'twitter_title' => null,
            'twitter_description' => null,
        ]);

        expect($service->og_title)->toBe('Original Title');
        expect($service->twitter_title)->toBe('Original Title');

        $service->update([
            'meta_title' => 'Updated Title',
            'meta_description' => 'Updated Description',
        ]);
        $service->refresh();

        expect($service->og_title)->toBe('Updated Title');
        expect($service->og_description)->toBe('Updated Description');
        expect($service->twitter_title)->toBe('Updated Title');
        expect($service->twitter_description)->toBe('Updated Description');
    });
});

describe('slug behavior', function () {
    test('slug is auto-generated from name', function () {
        $service = Service::factory()->create(['name' => 'Web Development Services']);

        expect($service->slug)->toBe('web-development-services');
    });

    test('duplicate names get unique slugs with suffix', function () {
        Service::factory()->create(['name' => 'Test Service']);
        $service2 = Service::factory()->create(['name' => 'Test Service']);
        $service3 = Service::factory()->create(['name' => 'Test Service']);

        expect($service2->slug)->toBe('test-service-2');
        expect($service3->slug)->toBe('test-service-3');
    });

    test('includes soft deleted services when checking slug uniqueness', function () {
        $service1 = Service::factory()->create(['name' => 'Test Service']);
        expect($service1->slug)->toBe('test-service');

        $service1->delete(); // Soft delete

        $service2 = Service::factory()->create(['name' => 'Test Service']);
        expect($service2->slug)->toBe('test-service-2');
    });

    test('reserved slugs are rejected', function () {
        Service::factory()->create(['name' => 'admin']);
    })->throws(ValidationException::class);

    test('slug remains stable when updating name', function () {
        $service = Service::factory()->create(['name' => 'Original Name']);

        expect($service->slug)->toBe('original-name');

        $service->update(['name' => 'Updated Name']);
        $service->refresh();

        expect($service->slug)->toBe('original-name');
        expect($service->name)->toBe('Updated Name');
    });

    test('manually set slug is preserved', function () {
        $service = Service::factory()->create([
            'name' => 'Test Service',
            'slug' => 'custom-slug',
        ]);

        expect($service->slug)->toBe('custom-slug');
    });

    test('slug can be manually updated', function () {
        $service = Service::factory()->create(['name' => 'Test Service']);

        expect($service->slug)->toBe('test-service');

        $service->update(['slug' => 'new-custom-slug']);
        $service->refresh();

        expect($service->slug)->toBe('new-custom-slug');
    });

    test('special characters in name are handled', function () {
        $service = Service::factory()->create(['name' => 'Test & Service @ 2024!']);

        expect($service->slug)->toBe('test-service-at-2024');
    });

    test('unicode characters are handled', function () {
        $service = Service::factory()->create(['name' => 'Tëst Sërvicé']);

        expect($service->slug)->toBeString();
        expect($service->slug)->not->toBeEmpty();
    });

    test('empty string name throws exception', function () {
        Service::factory()->create(['name' => '']);
    })->throws(\RuntimeException::class);

    test('slug is url safe', function () {
        $service = Service::factory()->create(['name' => 'Test/Service\\With:Invalid*Characters']);

        expect($service->slug)->toMatch('/^[a-z0-9-]+$/');
    });
});

describe('service-specific tests', function () {
    test('service table is correct', function () {
        $service = Service::factory()->create();

        expect($service->getTable())->toBe('services');
    });

    test('service uses HasSeo trait', function () {
        $traits = class_uses_recursive(Service::class);

        expect($traits)->toHaveKey(\App\Traits\HasSeo::class);
    });

    test('service uses HasSlug trait', function () {
        $traits = class_uses_recursive(Service::class);

        expect($traits)->toHaveKey(\App\Traits\HasSlug::class);
    });

    test('service uses HasContentBlocks trait', function () {
        $traits = class_uses_recursive(Service::class);

        expect($traits)->toHaveKey(\App\Traits\HasContentBlocks::class);
    });

    test('service uses HasRelatedContent trait', function () {
        $traits = class_uses_recursive(Service::class);

        expect($traits)->toHaveKey(\App\Traits\HasRelatedContent::class);
    });

    test('service uses SoftDeletes trait', function () {
        $traits = class_uses_recursive(Service::class);

        expect($traits)->toHaveKey(\Illuminate\Database\Eloquent\SoftDeletes::class);
    });

    test('service casts are correct', function () {
        $service = Service::factory()->create();

        $casts = $service->getCasts();

        expect($casts['status'])->toBe(ContentStatus::class);
        expect($casts['og_type'])->toBe(OgType::class);
        expect($casts['meta_robots'])->toBe('boolean');
        expect($casts['breadcrumbs'])->toBe('array');
    });

    test('fillable fields include all required attributes', function () {
        $service = new Service;

        $fillable = $service->getFillable();

        $requiredFields = [
            'name',
            'slug',
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
        $service = Service::factory()->create();

        expect($service->created_at)->not->toBeNull();
        expect($service->updated_at)->not->toBeNull();
        expect($service->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($service->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    test('soft delete timestamp is nullable', function () {
        $service = Service::factory()->create();

        expect($service->deleted_at)->toBeNull();

        $service->delete();

        expect($service->deleted_at)->not->toBeNull();
        expect($service->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });
});

describe('edge cases', function () {
    test('handles very long service name', function () {
        // Use a name that's long but within database limits for 'name' field
        $longName = str_repeat('Very-Long-Name-', 10); // ~150 chars
        $service = Service::factory()->create(['name' => $longName]);

        expect($service->slug)->toBeString();
        expect($service->slug)->not->toBeEmpty();
        expect(strlen($service->slug))->toBeLessThanOrEqual(255);
    });

    test('handles numeric service name', function () {
        $service = Service::factory()->create(['name' => '12345']);

        expect($service->slug)->toBe('12345');
    });

    test('handles service with only special characters', function () {
        Service::factory()->create(['name' => '!!!']);
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

        $service = Service::factory()->create(['content_blocks' => $blocks]);

        expect($service->fresh()->content_blocks)->toEqual($blocks);
    });

    test('handles large number of content blocks', function () {
        $blocks = [];
        for ($i = 0; $i < 50; $i++) {
            $blocks[] = ['type' => 'hero', 'data' => ['index' => $i]];
        }

        $service = Service::factory()->create(['content_blocks' => $blocks]);

        expect($service->fresh()->content_blocks)->toHaveCount(50);
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

        $service = Service::factory()->create(['content_blocks' => $blocks]);

        expect($service->fresh()->content_blocks[0]['data']['heading'])->toBe('こんにちは');
        expect($service->fresh()->content_blocks[0]['data']['emoji'])->toBe('😀🎉🚀');
    });

    test('handles null values in all nullable SEO fields', function () {
        $service = Service::factory()->create([
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

        expect($service->exists)->toBeTrue();
        expect($service->meta_title)->toBeNull();
        expect($service->og_title)->toBeNull();
        expect($service->twitter_title)->toBeNull();
    });

    test('multiple operations in sequence', function () {
        $service = Service::factory()->create(['name' => 'Test']);

        // Add blocks
        $service->addBlock(['type' => 'hero', 'data' => []]);
        $service->addBlock(['type' => 'cta', 'data' => []]);
        $service->save();

        // Update status
        $service->update(['status' => ContentStatus::Published]);

        // Remove a block
        $service->removeBlock(0);
        $service->save();

        // Update SEO
        $service->update(['meta_title' => 'Updated Title']);

        $fresh = $service->fresh();

        expect($fresh->status)->toBe(ContentStatus::Published);
        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('cta');
        expect($fresh->meta_title)->toBe('Updated Title');
    });
});
