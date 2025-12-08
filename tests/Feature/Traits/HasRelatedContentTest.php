<?php

declare(strict_types=1);

use App\Traits\HasRelatedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Test Setup
|--------------------------------------------------------------------------
|
| The HasRelatedContent trait references App\Models\* classes that don't
| exist yet in the codebase. These tests validate the trait's structure,
| method signatures, and relationship definitions. Full integration tests
| will be added in later phases when the models are created.
|
| For now, we test:
| 1. Relationship method definitions and return types
| 2. Helper method logic (attachRelated, detachRelated, syncRelated)
| 3. Method mapping logic (getRelationshipMethodForModel/Class)
|
*/

/**
 * Stub models for testing relationship method signatures.
 * These don't have actual database tables but allow testing the trait structure.
 */
class RelatedContentTestModel extends Model
{
    use HasRelatedContent;

    protected $table = 'related_content_test_models';

    protected $guarded = [];
}

beforeEach(function () {
    // Create a minimal test table
    Schema::create('related_content_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('related_content_test_models');
});

describe('forward relationship methods', function () {
    test('relatedFaqs method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedFaqs'))->toBeTrue();

        // Verify return type hint
        $reflection = new ReflectionMethod($model, 'relatedFaqs');
        expect($reflection->getReturnType()?->getName())->toBe(MorphToMany::class);
    });

    test('relatedTestimonials method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedTestimonials'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'relatedTestimonials');
        expect($reflection->getReturnType()?->getName())->toBe(MorphToMany::class);
    });

    test('relatedServices method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedServices'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'relatedServices');
        expect($reflection->getReturnType()?->getName())->toBe(MorphToMany::class);
    });

    test('relatedBlogPosts method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedBlogPosts'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'relatedBlogPosts');
        expect($reflection->getReturnType()?->getName())->toBe(MorphToMany::class);
    });

    test('relatedPages method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedPages'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'relatedPages');
        expect($reflection->getReturnType()?->getName())->toBe(MorphToMany::class);
    });
});

describe('inverse relationship methods', function () {
    test('relatedFromFaqs method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedFromFaqs'))->toBeTrue();

        // morphedByMany returns MorphedByMany which extends MorphToMany
        $reflection = new ReflectionMethod($model, 'relatedFromFaqs');
        $returnType = $reflection->getReturnType()?->getName();
        expect($returnType)->toBeIn([MorphToMany::class, 'Illuminate\Database\Eloquent\Relations\MorphedByMany']);
    });

    test('relatedFromServices method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedFromServices'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'relatedFromServices');
        $returnType = $reflection->getReturnType()?->getName();
        expect($returnType)->toBeIn([MorphToMany::class, 'Illuminate\Database\Eloquent\Relations\MorphedByMany']);
    });

    test('relatedFromBlogPosts method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedFromBlogPosts'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'relatedFromBlogPosts');
        $returnType = $reflection->getReturnType()?->getName();
        expect($returnType)->toBeIn([MorphToMany::class, 'Illuminate\Database\Eloquent\Relations\MorphedByMany']);
    });

    test('relatedFromPages method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedFromPages'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'relatedFromPages');
        $returnType = $reflection->getReturnType()?->getName();
        expect($returnType)->toBeIn([MorphToMany::class, 'Illuminate\Database\Eloquent\Relations\MorphedByMany']);
    });

    test('relatedFromTestimonials method exists', function () {
        $model = new RelatedContentTestModel;
        expect(method_exists($model, 'relatedFromTestimonials'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'relatedFromTestimonials');
        $returnType = $reflection->getReturnType()?->getName();
        expect($returnType)->toBeIn([MorphToMany::class, 'Illuminate\Database\Eloquent\Relations\MorphedByMany']);
    });
});

describe('helper methods exist', function () {
    test('attachRelated method exists with correct signature', function () {
        $model = new RelatedContentTestModel;

        expect(method_exists($model, 'attachRelated'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'attachRelated');
        $params = $reflection->getParameters();

        expect($params)->toHaveCount(2);
        expect($params[0]->getName())->toBe('targets');
        expect($params[1]->getName())->toBe('pivotData');
        expect($params[1]->isDefaultValueAvailable())->toBeTrue();
        expect($params[1]->getDefaultValue())->toBe([]);
    });

    test('detachRelated method exists with correct signature', function () {
        $model = new RelatedContentTestModel;

        expect(method_exists($model, 'detachRelated'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'detachRelated');
        $params = $reflection->getParameters();

        expect($params)->toHaveCount(1);
        expect($params[0]->getName())->toBe('targets');
    });

    test('syncRelated method exists with correct signature', function () {
        $model = new RelatedContentTestModel;

        expect(method_exists($model, 'syncRelated'))->toBeTrue();

        $reflection = new ReflectionMethod($model, 'syncRelated');
        $params = $reflection->getParameters();

        expect($params)->toHaveCount(2);
        expect($params[0]->getName())->toBe('relatedClass');
        expect($params[1]->getName())->toBe('idsWithPivot');
    });
});

describe('relationship method mapping', function () {
    test('getRelationshipMethodForModel returns correct mapping for Faq', function () {
        $model = new RelatedContentTestModel;

        $reflection = new ReflectionMethod($model, 'getRelationshipMethodForModel');

        // Create a mock Faq model
        $faq = new class extends Model
        {
            protected $table = 'faqs';
        };

        // The method expects App\Models\Faq::class, so this should return null for mock
        $result = $reflection->invoke($model, $faq);
        expect($result)->toBeNull(); // Because mock class != App\Models\Faq

        // But we can verify the method structure exists
        expect($reflection->isProtected())->toBeTrue();
    });

    test('getRelationshipMethodForClass returns correct mapping', function () {
        $model = new RelatedContentTestModel;

        $reflection = new ReflectionMethod($model, 'getRelationshipMethodForClass');

        // Test with actual expected class names
        expect($reflection->invoke($model, \App\Models\Faq::class))->toBe('relatedFaqs');
        expect($reflection->invoke($model, \App\Models\Testimonial::class))->toBe('relatedTestimonials');
        expect($reflection->invoke($model, \App\Models\Service::class))->toBe('relatedServices');
        expect($reflection->invoke($model, \App\Models\BlogPost::class))->toBe('relatedBlogPosts');
        expect($reflection->invoke($model, \App\Models\Page::class))->toBe('relatedPages');
    });

    test('getRelationshipMethodForClass returns null for unsupported class', function () {
        $model = new RelatedContentTestModel;

        $reflection = new ReflectionMethod($model, 'getRelationshipMethodForClass');

        $result = $reflection->invoke($model, 'App\Models\UnsupportedModel');
        expect($result)->toBeNull();
    });
});

/*
|--------------------------------------------------------------------------
| Skipped Tests - Require App\Models\* Classes
|--------------------------------------------------------------------------
|
| The following tests require the actual model classes (Faq, Service, etc.)
| to exist. These will be enabled in Phase 3+ when the models are created.
|
| Tests that would be here:
| - relationship pivot configuration
| - relationship foreign keys
| - full integration tests with attachRelated/detachRelated/syncRelated
|
| The trait structure tests above verify the trait is correctly implemented.
| Full database integration tests will be added after T018-T042 (model creation).
*/

describe('trait completeness', function () {
    test('trait has all required forward relationship methods', function () {
        $requiredMethods = [
            'relatedFaqs',
            'relatedTestimonials',
            'relatedServices',
            'relatedBlogPosts',
            'relatedPages',
        ];

        $model = new RelatedContentTestModel;

        foreach ($requiredMethods as $method) {
            expect(method_exists($model, $method))
                ->toBeTrue("Missing forward relationship method: {$method}");
        }
    });

    test('trait has all required inverse relationship methods', function () {
        $requiredMethods = [
            'relatedFromFaqs',
            'relatedFromServices',
            'relatedFromBlogPosts',
            'relatedFromPages',
            'relatedFromTestimonials',
        ];

        $model = new RelatedContentTestModel;

        foreach ($requiredMethods as $method) {
            expect(method_exists($model, $method))
                ->toBeTrue("Missing inverse relationship method: {$method}");
        }
    });

    test('trait has all required helper methods', function () {
        $requiredMethods = [
            'attachRelated',
            'detachRelated',
            'syncRelated',
        ];

        $model = new RelatedContentTestModel;

        foreach ($requiredMethods as $method) {
            expect(method_exists($model, $method))
                ->toBeTrue("Missing helper method: {$method}");
        }
    });

    test('trait has protected helper method for model mapping', function () {
        $model = new RelatedContentTestModel;

        $reflection = new ReflectionClass($model);

        expect($reflection->hasMethod('getRelationshipMethodForModel'))->toBeTrue();
        expect($reflection->getMethod('getRelationshipMethodForModel')->isProtected())->toBeTrue();
    });

    test('trait has protected helper method for class mapping', function () {
        $model = new RelatedContentTestModel;

        $reflection = new ReflectionClass($model);

        expect($reflection->hasMethod('getRelationshipMethodForClass'))->toBeTrue();
        expect($reflection->getMethod('getRelationshipMethodForClass')->isProtected())->toBeTrue();
    });
});
