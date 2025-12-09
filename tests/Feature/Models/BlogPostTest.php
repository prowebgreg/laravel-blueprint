<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

describe('CRUD operations', function () {
    test('creates blog post with minimal required fields', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'Test Blog Post']);

        expect($blogPost)->toBeInstanceOf(BlogPost::class);
        expect($blogPost->name)->toBe('Test Blog Post');
        expect($blogPost->slug)->toBe('test-blog-post');
        expect($blogPost->status)->toBe(ContentStatus::Draft);
    });

    test('creates blog post with all fields populated', function () {
        $blogPost = BlogPost::factory()
            ->published()
            ->withContentBlocks()
            ->withSeoData()
            ->create(['name' => 'Full Blog Post']);

        expect($blogPost->name)->toBe('Full Blog Post');
        expect($blogPost->slug)->toBe('full-blog-post');
        expect($blogPost->status)->toBe(ContentStatus::Published);
        expect($blogPost->content_blocks)->toBeArray();
        expect($blogPost->content_blocks)->toHaveCount(2);
        expect($blogPost->meta_title)->not->toBeNull();
        expect($blogPost->meta_description)->not->toBeNull();
        expect($blogPost->og_title)->not->toBeNull();
        expect($blogPost->twitter_title)->not->toBeNull();
    });

    test('reads blog post and verifies all fields', function () {
        $data = [
            'name' => 'My Blog Article',
            'status' => ContentStatus::Published,
            'meta_title' => 'Blog Article Title',
            'meta_description' => 'Learn about this amazing topic',
            'canonical_url' => 'https://example.com/blog/article',
        ];

        $blogPost = BlogPost::factory()->create($data);
        $retrieved = BlogPost::find($blogPost->id);

        expect($retrieved->name)->toBe($data['name']);
        expect($retrieved->status)->toBe($data['status']);
        expect($retrieved->meta_title)->toBe($data['meta_title']);
        expect($retrieved->meta_description)->toBe($data['meta_description']);
        expect($retrieved->canonical_url)->toBe($data['canonical_url']);
    });

    test('updates blog post fields', function () {
        $blogPost = BlogPost::factory()->create([
            'name' => 'Original Name',
        ]);

        $blogPost->update([
            'name' => 'Updated Name',
            'meta_title' => 'New Title',
        ]);

        $blogPost->refresh();

        expect($blogPost->name)->toBe('Updated Name');
        expect($blogPost->meta_title)->toBe('New Title');
    });

    test('soft deletes blog post', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'To Delete']);

        $blogPost->delete();

        expect(BlogPost::find($blogPost->id))->toBeNull();
        expect(BlogPost::withTrashed()->find($blogPost->id))->not->toBeNull();
        expect($blogPost->deleted_at)->not->toBeNull();
    });

    test('restores soft-deleted blog post', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'To Restore']);
        $blogPost->delete();

        expect(BlogPost::find($blogPost->id))->toBeNull();

        $blogPost->restore();

        expect(BlogPost::find($blogPost->id))->not->toBeNull();
        expect($blogPost->deleted_at)->toBeNull();
    });

    test('queries all blog posts', function () {
        BlogPost::factory()->count(3)->create();

        $blogPosts = BlogPost::all();

        expect($blogPosts)->toHaveCount(3);
    });

    test('queries blog posts by status', function () {
        BlogPost::factory()->draft()->count(2)->create();
        BlogPost::factory()->published()->count(3)->create();

        $drafts = BlogPost::where('status', ContentStatus::Draft)->get();
        $published = BlogPost::where('status', ContentStatus::Published)->get();

        expect($drafts)->toHaveCount(2);
        expect($published)->toHaveCount(3);
    });

    test('soft-deleted blog posts excluded from normal queries', function () {
        BlogPost::factory()->count(3)->create();
        $toDelete = BlogPost::factory()->create();
        $toDelete->delete();

        $blogPosts = BlogPost::all();

        expect($blogPosts)->toHaveCount(3);
        expect(BlogPost::withTrashed()->get())->toHaveCount(4);
    });
});

describe('status transitions', function () {
    test('default status is draft', function () {
        $blogPost = BlogPost::factory()->create();

        expect($blogPost->status)->toBe(ContentStatus::Draft);
    });

    test('changes status from draft to published', function () {
        $blogPost = BlogPost::factory()->draft()->create();

        expect($blogPost->status)->toBe(ContentStatus::Draft);

        $blogPost->update(['status' => ContentStatus::Published]);

        expect($blogPost->fresh()->status)->toBe(ContentStatus::Published);
    });

    test('changes status from published to draft', function () {
        $blogPost = BlogPost::factory()->published()->create();

        expect($blogPost->status)->toBe(ContentStatus::Published);

        $blogPost->update(['status' => ContentStatus::Draft]);

        expect($blogPost->fresh()->status)->toBe(ContentStatus::Draft);
    });

    test('queries only published blog posts', function () {
        BlogPost::factory()->draft()->count(3)->create();
        BlogPost::factory()->published()->count(2)->create();

        $published = BlogPost::where('status', ContentStatus::Published)->get();

        expect($published)->toHaveCount(2);
        foreach ($published as $blogPost) {
            expect($blogPost->status)->toBe(ContentStatus::Published);
        }
    });

    test('queries only draft blog posts', function () {
        BlogPost::factory()->draft()->count(3)->create();
        BlogPost::factory()->published()->count(2)->create();

        $drafts = BlogPost::where('status', ContentStatus::Draft)->get();

        expect($drafts)->toHaveCount(3);
        foreach ($drafts as $blogPost) {
            expect($blogPost->status)->toBe(ContentStatus::Draft);
        }
    });
});

describe('content blocks', function () {
    test('creates blog post with content blocks', function () {
        $blogPost = BlogPost::factory()->withContentBlocks()->create();

        expect($blogPost->content_blocks)->toBeArray();
        expect($blogPost->content_blocks)->toHaveCount(2);
        expect($blogPost->content_blocks[0]['type'])->toBe('hero');
        expect($blogPost->content_blocks[1]['type'])->toBe('cta');
    });

    test('stores and retrieves content blocks correctly', function () {
        $blocks = [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => 'Welcome to Our Blog',
                    'lede' => 'This is a test article section',
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Subscribe Now',
                    'cta_button_text' => 'Join Our Newsletter',
                ],
            ],
        ];

        $blogPost = BlogPost::factory()->create(['content_blocks' => $blocks]);
        $fresh = $blogPost->fresh();

        expect($fresh->content_blocks)->toEqual($blocks);
    });

    test('adds block to existing blog post', function () {
        $blogPost = BlogPost::factory()->create(['content_blocks' => null]);

        $blogPost->addBlock(['type' => 'hero', 'data' => ['heading' => 'New Hero']]);
        $blogPost->save();

        $fresh = $blogPost->fresh();

        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('hero');
    });

    test('removes block from blog post', function () {
        $blogPost = BlogPost::factory()->withContentBlocks()->create();

        expect($blogPost->content_blocks)->toHaveCount(2);

        $blogPost->removeBlock(0);
        $blogPost->save();

        $fresh = $blogPost->fresh();

        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('cta');
    });

    test('reorders blocks on blog post', function () {
        $blocks = [
            ['type' => 'hero', 'data' => []],
            ['type' => 'cta', 'data' => []],
            ['type' => 'hero', 'data' => []],
        ];

        $blogPost = BlogPost::factory()->create(['content_blocks' => $blocks]);

        $blogPost->reorderBlocks([2, 0, 1]);
        $blogPost->save();

        $fresh = $blogPost->fresh();

        expect($fresh->content_blocks[0]['type'])->toBe('hero');
        expect($fresh->content_blocks[1]['type'])->toBe('hero');
        expect($fresh->content_blocks[2]['type'])->toBe('cta');
    });

    test('blog post with null content_blocks is valid', function () {
        $blogPost = BlogPost::factory()->create(['content_blocks' => null]);

        expect($blogPost->content_blocks)->toBeNull();
        expect($blogPost->exists)->toBeTrue();
    });

    test('blog post with empty array content_blocks is valid', function () {
        $blogPost = BlogPost::factory()->create(['content_blocks' => []]);

        expect($blogPost->fresh()->content_blocks)->toBe([]);
        expect($blogPost->exists)->toBeTrue();
    });

    test('adds multiple blocks sequentially', function () {
        $blogPost = BlogPost::factory()->create(['content_blocks' => null]);

        $blogPost->addBlock(['type' => 'hero', 'data' => ['heading' => 'First']]);
        $blogPost->addBlock(['type' => 'cta', 'data' => ['heading' => 'Second']]);
        $blogPost->addBlock(['type' => 'hero', 'data' => ['heading' => 'Third']]);
        $blogPost->save();

        $fresh = $blogPost->fresh();

        expect($fresh->content_blocks)->toHaveCount(3);
        expect($fresh->content_blocks[0]['data']['heading'])->toBe('First');
        expect($fresh->content_blocks[1]['data']['heading'])->toBe('Second');
        expect($fresh->content_blocks[2]['data']['heading'])->toBe('Third');
    });
});

describe('SEO fields', function () {
    test('creates blog post with SEO data', function () {
        $blogPost = BlogPost::factory()->withSeoData()->create();

        expect($blogPost->meta_title)->not->toBeNull();
        expect($blogPost->meta_description)->not->toBeNull();
        expect($blogPost->meta_author)->not->toBeNull();
        expect($blogPost->canonical_url)->not->toBeNull();
    });

    test('SEO fields are stored and retrieved correctly', function () {
        $seoData = [
            'meta_title' => 'Test Blog Post Title',
            'meta_description' => 'This is a test article description',
            'meta_author' => 'Jane Smith',
            'meta_robots' => true,
            'canonical_url' => 'https://example.com/blog/test',
        ];

        $blogPost = BlogPost::factory()->create($seoData);
        $fresh = $blogPost->fresh();

        expect($fresh->meta_title)->toBe($seoData['meta_title']);
        expect($fresh->meta_description)->toBe($seoData['meta_description']);
        expect($fresh->meta_author)->toBe($seoData['meta_author']);
        expect($fresh->meta_robots)->toBe($seoData['meta_robots']);
        expect($fresh->canonical_url)->toBe($seoData['canonical_url']);
    });

    test('OG fields mirror meta fields when not explicitly set', function () {
        $blogPost = BlogPost::factory()->create([
            'meta_title' => 'My Blog Post Title',
            'meta_description' => 'My blog post description',
            'og_title' => null,
            'og_description' => null,
        ]);

        expect($blogPost->og_title)->toBe('My Blog Post Title');
        expect($blogPost->og_description)->toBe('My blog post description');
    });

    test('Twitter fields mirror meta fields when not explicitly set', function () {
        $blogPost = BlogPost::factory()->create([
            'meta_title' => 'My Blog Post Title',
            'meta_description' => 'My blog post description',
            'twitter_title' => null,
            'twitter_description' => null,
        ]);

        expect($blogPost->twitter_title)->toBe('My Blog Post Title');
        expect($blogPost->twitter_description)->toBe('My blog post description');
    });

    test('manually set OG fields are independent from meta', function () {
        $blogPost = BlogPost::factory()->create([
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => 'Custom OG Title',
            'og_description' => 'Custom OG Description',
        ]);

        expect($blogPost->og_title)->toBe('Custom OG Title');
        expect($blogPost->og_description)->toBe('Custom OG Description');

        // Update meta fields - OG should remain independent
        $blogPost->update([
            'meta_title' => 'Updated Meta Title',
            'meta_description' => 'Updated Meta Description',
        ]);

        $blogPost->refresh();

        expect($blogPost->og_title)->toBe('Custom OG Title');
        expect($blogPost->og_description)->toBe('Custom OG Description');
    });

    test('manually set Twitter fields are independent from meta', function () {
        $blogPost = BlogPost::factory()->create([
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'twitter_title' => 'Custom Twitter Title',
            'twitter_description' => 'Custom Twitter Description',
        ]);

        expect($blogPost->twitter_title)->toBe('Custom Twitter Title');
        expect($blogPost->twitter_description)->toBe('Custom Twitter Description');

        // Update meta fields - Twitter should remain independent
        $blogPost->update([
            'meta_title' => 'Updated Meta Title',
            'meta_description' => 'Updated Meta Description',
        ]);

        $blogPost->refresh();

        expect($blogPost->twitter_title)->toBe('Custom Twitter Title');
        expect($blogPost->twitter_description)->toBe('Custom Twitter Description');
    });

    test('breadcrumbs are stored correctly', function () {
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Blog', 'url' => '/blog'],
            ['label' => 'Article', 'url' => null],
        ];

        $blogPost = BlogPost::factory()->create(['breadcrumbs' => $breadcrumbs]);

        expect($blogPost->fresh()->breadcrumbs)->toEqual($breadcrumbs);
    });

    test('OG type defaults to article', function () {
        $blogPost = BlogPost::factory()->create();

        expect($blogPost->og_type)->toBe(OgType::Article);
    });

    test('meta robots defaults to false', function () {
        $blogPost = BlogPost::factory()->create();

        expect($blogPost->meta_robots)->toBe(false);
    });

    test('updating meta fields affects mirrored social fields when null', function () {
        $blogPost = BlogPost::factory()->create([
            'meta_title' => 'Original Title',
            'meta_description' => 'Original Description',
            'og_title' => null,
            'og_description' => null,
            'twitter_title' => null,
            'twitter_description' => null,
        ]);

        expect($blogPost->og_title)->toBe('Original Title');
        expect($blogPost->twitter_title)->toBe('Original Title');

        $blogPost->update([
            'meta_title' => 'Updated Title',
            'meta_description' => 'Updated Description',
        ]);
        $blogPost->refresh();

        expect($blogPost->og_title)->toBe('Updated Title');
        expect($blogPost->og_description)->toBe('Updated Description');
        expect($blogPost->twitter_title)->toBe('Updated Title');
        expect($blogPost->twitter_description)->toBe('Updated Description');
    });
});

describe('slug behavior', function () {
    test('slug is auto-generated from name', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'About Our Company']);

        expect($blogPost->slug)->toBe('about-our-company');
    });

    test('duplicate names get unique slugs with suffix', function () {
        BlogPost::factory()->create(['name' => 'Test Blog Post']);
        $blogPost2 = BlogPost::factory()->create(['name' => 'Test Blog Post']);
        $blogPost3 = BlogPost::factory()->create(['name' => 'Test Blog Post']);

        expect($blogPost2->slug)->toBe('test-blog-post-2');
        expect($blogPost3->slug)->toBe('test-blog-post-3');
    });

    test('includes soft deleted blog posts when checking slug uniqueness', function () {
        $blogPost1 = BlogPost::factory()->create(['name' => 'Test Blog Post']);
        expect($blogPost1->slug)->toBe('test-blog-post');

        $blogPost1->delete(); // Soft delete

        $blogPost2 = BlogPost::factory()->create(['name' => 'Test Blog Post']);
        expect($blogPost2->slug)->toBe('test-blog-post-2');
    });

    test('reserved slugs are rejected', function () {
        BlogPost::factory()->create(['name' => 'admin']);
    })->throws(ValidationException::class);

    test('slug remains stable when updating name', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'Original Name']);

        expect($blogPost->slug)->toBe('original-name');

        $blogPost->update(['name' => 'Updated Name']);
        $blogPost->refresh();

        expect($blogPost->slug)->toBe('original-name');
        expect($blogPost->name)->toBe('Updated Name');
    });

    test('manually set slug is preserved', function () {
        $blogPost = BlogPost::factory()->create([
            'name' => 'Test Blog Post',
            'slug' => 'custom-slug',
        ]);

        expect($blogPost->slug)->toBe('custom-slug');
    });

    test('slug can be manually updated', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'Test Blog Post']);

        expect($blogPost->slug)->toBe('test-blog-post');

        $blogPost->update(['slug' => 'new-custom-slug']);
        $blogPost->refresh();

        expect($blogPost->slug)->toBe('new-custom-slug');
    });

    test('special characters in name are handled', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'Test & Blog Post @ 2024!']);

        expect($blogPost->slug)->toBe('test-blog-post-at-2024');
    });

    test('unicode characters are handled', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'Tëst Blög Pöst']);

        expect($blogPost->slug)->toBeString();
        expect($blogPost->slug)->not->toBeEmpty();
    });

    test('empty string name throws exception', function () {
        BlogPost::factory()->create(['name' => '']);
    })->throws(\RuntimeException::class);

    test('slug is url safe', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'Test/Blog\\Post:With*Invalid*Characters']);

        expect($blogPost->slug)->toMatch('/^[a-z0-9-]+$/');
    });
});

describe('blog-post-specific tests', function () {
    test('blog post table is correct', function () {
        $blogPost = BlogPost::factory()->create();

        expect($blogPost->getTable())->toBe('blog_posts');
    });

    test('blog post uses HasSeo trait', function () {
        $traits = class_uses_recursive(BlogPost::class);

        expect($traits)->toHaveKey(\App\Traits\HasSeo::class);
    });

    test('blog post uses HasSlug trait', function () {
        $traits = class_uses_recursive(BlogPost::class);

        expect($traits)->toHaveKey(\App\Traits\HasSlug::class);
    });

    test('blog post uses HasContentBlocks trait', function () {
        $traits = class_uses_recursive(BlogPost::class);

        expect($traits)->toHaveKey(\App\Traits\HasContentBlocks::class);
    });

    test('blog post uses HasRelatedContent trait', function () {
        $traits = class_uses_recursive(BlogPost::class);

        expect($traits)->toHaveKey(\App\Traits\HasRelatedContent::class);
    });

    test('blog post uses SoftDeletes trait', function () {
        $traits = class_uses_recursive(BlogPost::class);

        expect($traits)->toHaveKey(\Illuminate\Database\Eloquent\SoftDeletes::class);
    });

    test('blog post casts are correct', function () {
        $blogPost = BlogPost::factory()->create();

        $casts = $blogPost->getCasts();

        expect($casts['status'])->toBe(ContentStatus::class);
        expect($casts['og_type'])->toBe(OgType::class);
        expect($casts['meta_robots'])->toBe('boolean');
        expect($casts['breadcrumbs'])->toBe('array');
    });

    test('fillable fields include all required attributes', function () {
        $blogPost = new BlogPost;

        $fillable = $blogPost->getFillable();

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
        $blogPost = BlogPost::factory()->create();

        expect($blogPost->created_at)->not->toBeNull();
        expect($blogPost->updated_at)->not->toBeNull();
        expect($blogPost->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($blogPost->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    test('soft delete timestamp is nullable', function () {
        $blogPost = BlogPost::factory()->create();

        expect($blogPost->deleted_at)->toBeNull();

        $blogPost->delete();

        expect($blogPost->deleted_at)->not->toBeNull();
        expect($blogPost->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });
});

describe('edge cases', function () {
    test('handles very long blog post name', function () {
        // Use a name that's long but within database limits for 'name' field
        $longName = str_repeat('Very-Long-Name-', 10); // ~150 chars
        $blogPost = BlogPost::factory()->create(['name' => $longName]);

        expect($blogPost->slug)->toBeString();
        expect($blogPost->slug)->not->toBeEmpty();
        expect(strlen($blogPost->slug))->toBeLessThanOrEqual(255);
    });

    test('handles numeric blog post name', function () {
        $blogPost = BlogPost::factory()->create(['name' => '12345']);

        expect($blogPost->slug)->toBe('12345');
    });

    test('handles blog post with only special characters', function () {
        BlogPost::factory()->create(['name' => '!!!']);
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

        $blogPost = BlogPost::factory()->create(['content_blocks' => $blocks]);

        expect($blogPost->fresh()->content_blocks)->toEqual($blocks);
    });

    test('handles large number of content blocks', function () {
        $blocks = [];
        for ($i = 0; $i < 50; $i++) {
            $blocks[] = ['type' => 'hero', 'data' => ['index' => $i]];
        }

        $blogPost = BlogPost::factory()->create(['content_blocks' => $blocks]);

        expect($blogPost->fresh()->content_blocks)->toHaveCount(50);
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

        $blogPost = BlogPost::factory()->create(['content_blocks' => $blocks]);

        expect($blogPost->fresh()->content_blocks[0]['data']['heading'])->toBe('こんにちは');
        expect($blogPost->fresh()->content_blocks[0]['data']['emoji'])->toBe('😀🎉🚀');
    });

    test('handles null values in all nullable SEO fields', function () {
        $blogPost = BlogPost::factory()->create([
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

        expect($blogPost->exists)->toBeTrue();
        expect($blogPost->meta_title)->toBeNull();
        expect($blogPost->og_title)->toBeNull();
        expect($blogPost->twitter_title)->toBeNull();
    });

    test('multiple operations in sequence', function () {
        $blogPost = BlogPost::factory()->create(['name' => 'Test']);

        // Add blocks
        $blogPost->addBlock(['type' => 'hero', 'data' => []]);
        $blogPost->addBlock(['type' => 'cta', 'data' => []]);
        $blogPost->save();

        // Update status
        $blogPost->update(['status' => ContentStatus::Published]);

        // Remove a block
        $blogPost->removeBlock(0);
        $blogPost->save();

        // Update SEO
        $blogPost->update(['meta_title' => 'Updated Title']);

        $fresh = $blogPost->fresh();

        expect($fresh->status)->toBe(ContentStatus::Published);
        expect($fresh->content_blocks)->toHaveCount(1);
        expect($fresh->content_blocks[0]['type'])->toBe('cta');
        expect($fresh->meta_title)->toBe('Updated Title');
    });
});
