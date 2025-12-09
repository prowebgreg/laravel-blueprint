<?php

declare(strict_types=1);

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('relationship creation and basic operations', function () {
    test('can attach single related model with order', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        $service->attachRelated($faq, ['order' => 1]);

        $relatedFaqs = $service->relatedFaqs()->get();

        expect($relatedFaqs)->toHaveCount(1);
        expect($relatedFaqs->first()->id)->toBe($faq->id);
        expect($relatedFaqs->first()->pivot->order)->toBe(1);
    });

    test('can attach multiple related models with different orders', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();
        $faq3 = Faq::factory()->create();

        $service->attachRelated($faq1, ['order' => 2]);
        $service->attachRelated($faq2, ['order' => 0]);
        $service->attachRelated($faq3, ['order' => 1]);

        $relatedFaqs = $service->relatedFaqs()->get();

        expect($relatedFaqs)->toHaveCount(3);
        // Should be ordered by pivot 'order' column
        expect($relatedFaqs[0]->id)->toBe($faq2->id); // order 0
        expect($relatedFaqs[1]->id)->toBe($faq3->id); // order 1
        expect($relatedFaqs[2]->id)->toBe($faq1->id); // order 2
    });

    test('can attach collection of related models', function () {
        $service = Service::factory()->create();
        $faqs = Faq::factory()->count(3)->create();

        $service->attachRelated($faqs, ['order' => 0]);

        $relatedFaqs = $service->relatedFaqs()->get();

        expect($relatedFaqs)->toHaveCount(3);
    });

    test('can detach related model', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();

        $service->attachRelated($faq1);
        $service->attachRelated($faq2);

        expect($service->relatedFaqs()->count())->toBe(2);

        $service->detachRelated($faq1);

        $relatedFaqs = $service->relatedFaqs()->get();
        expect($relatedFaqs)->toHaveCount(1);
        expect($relatedFaqs->first()->id)->toBe($faq2->id);
    });

    test('detach non-existent relationship is silently ignored', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        // No relationship exists yet
        $service->detachRelated($faq);

        expect($service->relatedFaqs()->count())->toBe(0);
    });

    test('duplicate relationship attempt throws unique constraint violation', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        $service->attachRelated($faq, ['order' => 0]);

        // Attempting to attach the same relationship again should fail
        $service->attachRelated($faq, ['order' => 1]);
    })->throws(\Illuminate\Database\QueryException::class);

    test('relationship is stored in content_relations table with correct columns', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        $service->attachRelated($faq, ['order' => 5]);

        $relation = DB::table('content_relations')
            ->where('source_type', Service::class)
            ->where('source_id', $service->id)
            ->where('target_type', Faq::class)
            ->where('target_id', $faq->id)
            ->first();

        expect($relation)->not->toBeNull();
        expect($relation->order)->toBe(5);
        expect($relation->created_at)->not->toBeNull();
    });
});

describe('ordering behavior', function () {
    test('query related models returns correct order ascending', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create(['name' => 'FAQ 1']);
        $faq2 = Faq::factory()->create(['name' => 'FAQ 2']);
        $faq3 = Faq::factory()->create(['name' => 'FAQ 3']);
        $faq4 = Faq::factory()->create(['name' => 'FAQ 4']);

        $service->attachRelated($faq1, ['order' => 3]);
        $service->attachRelated($faq2, ['order' => 1]);
        $service->attachRelated($faq3, ['order' => 2]);
        $service->attachRelated($faq4, ['order' => 0]);

        $relatedFaqs = $service->relatedFaqs()->get();

        expect($relatedFaqs[0]->name)->toBe('FAQ 4'); // order 0
        expect($relatedFaqs[1]->name)->toBe('FAQ 2'); // order 1
        expect($relatedFaqs[2]->name)->toBe('FAQ 3'); // order 2
        expect($relatedFaqs[3]->name)->toBe('FAQ 1'); // order 3
    });

    test('sync relationships adds removes and updates order', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();
        $faq3 = Faq::factory()->create();

        // Initial attach
        $service->attachRelated($faq1, ['order' => 0]);
        $service->attachRelated($faq2, ['order' => 1]);

        expect($service->relatedFaqs()->count())->toBe(2);

        // Sync: keep faq2, remove faq1, add faq3, update order
        $service->syncRelated(Faq::class, [
            $faq2->id => ['order' => 10],
            $faq3->id => ['order' => 5],
        ]);

        $relatedFaqs = $service->relatedFaqs()->get();

        expect($relatedFaqs)->toHaveCount(2);
        expect($relatedFaqs[0]->id)->toBe($faq3->id); // order 5
        expect($relatedFaqs[0]->pivot->order)->toBe(5);
        expect($relatedFaqs[1]->id)->toBe($faq2->id); // order 10
        expect($relatedFaqs[1]->pivot->order)->toBe(10);
    });

    test('reorder by updating pivot order values directly', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create(['name' => 'FAQ 1']);
        $faq2 = Faq::factory()->create(['name' => 'FAQ 2']);

        $service->attachRelated($faq1, ['order' => 0]);
        $service->attachRelated($faq2, ['order' => 1]);

        $originalOrder = $service->relatedFaqs()->pluck('name')->toArray();
        expect($originalOrder)->toBe(['FAQ 1', 'FAQ 2']);

        // Update pivot order directly
        $service->relatedFaqs()->updateExistingPivot($faq1->id, ['order' => 10]);
        $service->relatedFaqs()->updateExistingPivot($faq2->id, ['order' => 0]);

        $newOrder = $service->relatedFaqs()->pluck('name')->toArray();
        expect($newOrder)->toBe(['FAQ 2', 'FAQ 1']);
    });

    test('default order is 0 when not specified', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        $service->attachRelated($faq);

        $relatedFaq = $service->relatedFaqs()->first();
        expect($relatedFaq->pivot->order)->toBe(0);
    });

    test('multiple models with same order value maintain stable sort', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();
        $faq3 = Faq::factory()->create();

        $service->attachRelated($faq1, ['order' => 0]);
        $service->attachRelated($faq2, ['order' => 0]);
        $service->attachRelated($faq3, ['order' => 0]);

        $relatedFaqs = $service->relatedFaqs()->get();

        expect($relatedFaqs)->toHaveCount(3);
        expect($relatedFaqs->pluck('pivot.order')->unique())->toHaveCount(1);
        expect($relatedFaqs->pluck('pivot.order')->unique()->first())->toBe(0);
    });
});

describe('bidirectional queries', function () {
    test('forward query service to related faqs returns FAQs', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();

        $service->attachRelated($faq1);
        $service->attachRelated($faq2);

        $relatedFaqs = $service->relatedFaqs()->get();

        expect($relatedFaqs)->toHaveCount(2);
        expect($relatedFaqs->pluck('id')->toArray())->toContain($faq1->id, $faq2->id);
    });

    test('inverse query faq to related from services returns Services', function () {
        $service1 = Service::factory()->create(['name' => 'Service 1']);
        $service2 = Service::factory()->create(['name' => 'Service 2']);
        $faq = Faq::factory()->create();

        $service1->attachRelated($faq);
        $service2->attachRelated($faq);

        $relatedFromServices = $faq->relatedFromServices()->get();

        expect($relatedFromServices)->toHaveCount(2);
        expect($relatedFromServices->pluck('id')->toArray())->toContain($service1->id, $service2->id);
    });

    test('cross type relationships service to faq and service to blog post', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();
        $blogPost1 = BlogPost::factory()->create();
        $blogPost2 = BlogPost::factory()->create();

        $service->attachRelated($faq1);
        $service->attachRelated($faq2);
        $service->attachRelated($blogPost1);
        $service->attachRelated($blogPost2);

        $relatedFaqs = $service->relatedFaqs()->get();
        $relatedBlogPosts = $service->relatedBlogPosts()->get();

        expect($relatedFaqs)->toHaveCount(2);
        expect($relatedBlogPosts)->toHaveCount(2);
        expect($relatedFaqs->pluck('id')->toArray())->toContain($faq1->id, $faq2->id);
        expect($relatedBlogPosts->pluck('id')->toArray())->toContain($blogPost1->id, $blogPost2->id);
    });

    test('self reference relationship service to service', function () {
        $service1 = Service::factory()->create(['name' => 'Service 1']);
        $service2 = Service::factory()->create(['name' => 'Service 2']);
        $service3 = Service::factory()->create(['name' => 'Service 3']);

        $service1->attachRelated($service2);
        $service1->attachRelated($service3);

        $relatedServices = $service1->relatedServices()->get();

        expect($relatedServices)->toHaveCount(2);
        expect($relatedServices->pluck('id')->toArray())->toContain($service2->id, $service3->id);
    });

    test('bidirectional relationship maintains separate direction queries', function () {
        $service = Service::factory()->create(['name' => 'Web Development']);
        $faq = Faq::factory()->create(['name' => 'Pricing FAQ']);

        $service->attachRelated($faq, ['order' => 0]);

        // Forward: Service -> FAQ
        $serviceFaqs = $service->relatedFaqs()->get();
        expect($serviceFaqs)->toHaveCount(1);
        expect($serviceFaqs->first()->name)->toBe('Pricing FAQ');

        // Inverse: FAQ -> Service
        $faqServices = $faq->relatedFromServices()->get();
        expect($faqServices)->toHaveCount(1);
        expect($faqServices->first()->name)->toBe('Web Development');
    });

    test('multiple source types can relate to same target', function () {
        $service = Service::factory()->create(['name' => 'Service']);
        $blogPost = BlogPost::factory()->create(['name' => 'Blog']);
        $page = Page::factory()->create(['name' => 'Page']);
        $faq = Faq::factory()->create(['name' => 'Common FAQ']);

        $service->attachRelated($faq);
        $blogPost->attachRelated($faq);
        $page->attachRelated($faq);

        // Check inverse relationships from FAQ
        expect($faq->relatedFromServices()->count())->toBe(1);
        expect($faq->relatedFromBlogPosts()->count())->toBe(1);
        expect($faq->relatedFromPages()->count())->toBe(1);
    });

    test('testimonial can be related to service and vice versa', function () {
        $service = Service::factory()->create();
        $testimonial = Testimonial::factory()->create();

        $service->attachRelated($testimonial, ['order' => 0]);

        // Forward: Service -> Testimonial
        $relatedTestimonials = $service->relatedTestimonials()->get();
        expect($relatedTestimonials)->toHaveCount(1);
        expect($relatedTestimonials->first()->id)->toBe($testimonial->id);

        // Inverse: Testimonial -> Service
        $relatedFromServices = $testimonial->relatedFromServices()->get();
        expect($relatedFromServices)->toHaveCount(1);
        expect($relatedFromServices->first()->id)->toBe($service->id);
    });
});

describe('soft delete behavior', function () {
    test('query with soft deleted target excludes it from results', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create(['name' => 'Active FAQ']);
        $faq2 = Faq::factory()->create(['name' => 'To Delete FAQ']);

        $service->attachRelated($faq1);
        $service->attachRelated($faq2);

        expect($service->relatedFaqs()->count())->toBe(2);

        // Soft delete faq2
        $faq2->delete();

        // Normal query should exclude soft-deleted
        $relatedFaqs = $service->relatedFaqs()->get();
        expect($relatedFaqs)->toHaveCount(1);
        expect($relatedFaqs->first()->name)->toBe('Active FAQ');
    });

    test('query with withTrashed includes soft deleted targets', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create(['name' => 'Active FAQ']);
        $faq2 = Faq::factory()->create(['name' => 'Deleted FAQ']);

        $service->attachRelated($faq1);
        $service->attachRelated($faq2);

        $faq2->delete();

        // Query with withTrashed should include soft-deleted
        $relatedFaqsWithTrashed = $service->relatedFaqs()->withTrashed()->get();
        expect($relatedFaqsWithTrashed)->toHaveCount(2);
        expect($relatedFaqsWithTrashed->pluck('name')->toArray())->toContain('Active FAQ', 'Deleted FAQ');
    });

    test('relationships preserved when target is soft deleted', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        $service->attachRelated($faq, ['order' => 5]);

        // Soft delete the FAQ
        $faq->delete();

        // Relationship still exists in pivot table
        $relationExists = DB::table('content_relations')
            ->where('source_type', Service::class)
            ->where('source_id', $service->id)
            ->where('target_type', Faq::class)
            ->where('target_id', $faq->id)
            ->exists();

        expect($relationExists)->toBeTrue();
    });

    test('relationships preserved when source is soft deleted', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        $service->attachRelated($faq, ['order' => 5]);

        // Soft delete the source
        $service->delete();

        // Relationship still exists in pivot table
        $relationExists = DB::table('content_relations')
            ->where('source_type', Service::class)
            ->where('source_id', $service->id)
            ->where('target_type', Faq::class)
            ->where('target_id', $faq->id)
            ->exists();

        expect($relationExists)->toBeTrue();
    });

    test('restored target appears in normal queries again', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create(['name' => 'Restorable FAQ']);

        $service->attachRelated($faq);

        // Delete and verify exclusion
        $faq->delete();
        expect($service->relatedFaqs()->count())->toBe(0);

        // Restore and verify inclusion
        $faq->restore();
        $relatedFaqs = $service->relatedFaqs()->get();
        expect($relatedFaqs)->toHaveCount(1);
        expect($relatedFaqs->first()->name)->toBe('Restorable FAQ');
    });

    test('inverse relationship excludes soft deleted sources', function () {
        $service1 = Service::factory()->create(['name' => 'Active Service']);
        $service2 = Service::factory()->create(['name' => 'Deleted Service']);
        $faq = Faq::factory()->create();

        $service1->attachRelated($faq);
        $service2->attachRelated($faq);

        expect($faq->relatedFromServices()->count())->toBe(2);

        // Soft delete service2
        $service2->delete();

        // Inverse query should exclude soft-deleted source
        $relatedServices = $faq->relatedFromServices()->get();
        expect($relatedServices)->toHaveCount(1);
        expect($relatedServices->first()->name)->toBe('Active Service');
    });
});

describe('eager loading', function () {
    test('eager loading relationships works correctly', function () {
        $service1 = Service::factory()->create(['name' => 'Service 1']);
        $service2 = Service::factory()->create(['name' => 'Service 2']);
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();

        $service1->attachRelated($faq1);
        $service1->attachRelated($faq2);
        $service2->attachRelated($faq1);

        // Eager load
        $services = Service::with('relatedFaqs')->get();

        expect($services)->toHaveCount(2);
        expect($services[0]->relatedFaqs)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
        expect($services[1]->relatedFaqs)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);

        // Verify data loaded correctly
        $service1Loaded = $services->firstWhere('name', 'Service 1');
        $service2Loaded = $services->firstWhere('name', 'Service 2');

        expect($service1Loaded->relatedFaqs)->toHaveCount(2);
        expect($service2Loaded->relatedFaqs)->toHaveCount(1);
    });

    test('eager loading multiple relationship types', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();
        $testimonial = Testimonial::factory()->create();
        $blogPost = BlogPost::factory()->create();

        $service->attachRelated($faq);
        $service->attachRelated($testimonial);
        $service->attachRelated($blogPost);

        // Eager load multiple relationships
        $serviceLoaded = Service::with(['relatedFaqs', 'relatedTestimonials', 'relatedBlogPosts'])
            ->find($service->id);

        expect($serviceLoaded->relatedFaqs)->toHaveCount(1);
        expect($serviceLoaded->relatedTestimonials)->toHaveCount(1);
        expect($serviceLoaded->relatedBlogPosts)->toHaveCount(1);
    });

    test('eager loading inverse relationships', function () {
        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();
        $faq1 = Faq::factory()->create(['name' => 'FAQ 1']);
        $faq2 = Faq::factory()->create(['name' => 'FAQ 2']);

        $service1->attachRelated($faq1);
        $service1->attachRelated($faq2);
        $service2->attachRelated($faq1);

        // Eager load inverse relationship
        $faqs = Faq::with('relatedFromServices')->get();

        $faq1Loaded = $faqs->firstWhere('name', 'FAQ 1');
        $faq2Loaded = $faqs->firstWhere('name', 'FAQ 2');

        expect($faq1Loaded->relatedFromServices)->toHaveCount(2);
        expect($faq2Loaded->relatedFromServices)->toHaveCount(1);
    });

    test('eager loading preserves pivot order', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create(['name' => 'FAQ 1']);
        $faq2 = Faq::factory()->create(['name' => 'FAQ 2']);
        $faq3 = Faq::factory()->create(['name' => 'FAQ 3']);

        $service->attachRelated($faq1, ['order' => 2]);
        $service->attachRelated($faq2, ['order' => 0]);
        $service->attachRelated($faq3, ['order' => 1]);

        // Eager load
        $serviceLoaded = Service::with('relatedFaqs')->find($service->id);

        expect($serviceLoaded->relatedFaqs[0]->name)->toBe('FAQ 2'); // order 0
        expect($serviceLoaded->relatedFaqs[1]->name)->toBe('FAQ 3'); // order 1
        expect($serviceLoaded->relatedFaqs[2]->name)->toBe('FAQ 1'); // order 2
    });
});

describe('pivot data access', function () {
    test('pivot data includes order and created_at', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        $service->attachRelated($faq, ['order' => 7]);

        $relatedFaq = $service->relatedFaqs()->first();

        expect($relatedFaq->pivot)->not->toBeNull();
        expect($relatedFaq->pivot->order)->toBe(7);
        expect($relatedFaq->pivot->created_at)->not->toBeNull();
    });

    test('pivot includes source and target polymorphic data', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        $service->attachRelated($faq);

        $relatedFaq = $service->relatedFaqs()->first();

        expect($relatedFaq->pivot->source_type)->toBe(Service::class);
        expect($relatedFaq->pivot->source_id)->toBe($service->id);
        expect($relatedFaq->pivot->target_type)->toBe(Faq::class);
        expect($relatedFaq->pivot->target_id)->toBe($faq->id);
    });
});

describe('edge cases and validation', function () {
    test('attaching same model to itself is allowed', function () {
        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();

        $service1->attachRelated($service2);
        $service2->attachRelated($service1);

        expect($service1->relatedServices()->count())->toBe(1);
        expect($service2->relatedServices()->count())->toBe(1);
    });

    test('sync with empty array removes all relationships of that type', function () {
        $service = Service::factory()->create();
        $faq1 = Faq::factory()->create();
        $faq2 = Faq::factory()->create();
        $testimonial = Testimonial::factory()->create();

        $service->attachRelated($faq1);
        $service->attachRelated($faq2);
        $service->attachRelated($testimonial);

        expect($service->relatedFaqs()->count())->toBe(2);
        expect($service->relatedTestimonials()->count())->toBe(1);

        // Sync empty array for FAQs
        $service->syncRelated(Faq::class, []);

        expect($service->relatedFaqs()->count())->toBe(0);
        // Testimonial relationship unaffected
        expect($service->relatedTestimonials()->count())->toBe(1);
    });

    test('large number of relationships handled correctly', function () {
        $service = Service::factory()->create();
        $faqs = Faq::factory()->count(100)->create();

        foreach ($faqs as $index => $faq) {
            $service->attachRelated($faq, ['order' => $index]);
        }

        expect($service->relatedFaqs()->count())->toBe(100);
    });

    test('relationships maintain integrity across transaction rollback', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        DB::beginTransaction();
        $service->attachRelated($faq, ['order' => 5]);
        expect($service->relatedFaqs()->count())->toBe(1);
        DB::rollBack();

        // Relationship should not exist after rollback
        expect($service->fresh()->relatedFaqs()->count())->toBe(0);
    });

    test('relationships persist across transaction commit', function () {
        $service = Service::factory()->create();
        $faq = Faq::factory()->create();

        DB::beginTransaction();
        $service->attachRelated($faq, ['order' => 5]);
        DB::commit();

        // Relationship should exist after commit
        expect($service->fresh()->relatedFaqs()->count())->toBe(1);
    });
});
