<?php

declare(strict_types=1);

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

// Test model without soft deletes
class TestModel extends Model
{
    use HasSlug;

    protected $table = 'test_models';

    protected $guarded = [];
}

// Test model with soft deletes
class TestModelWithSoftDeletes extends Model
{
    use HasSlug, SoftDeletes;

    protected $table = 'test_models_soft_deletes';

    protected $guarded = [];
}

beforeEach(function () {
    // Create test table without soft deletes
    Schema::create('test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
    });

    // Create test table with soft deletes
    Schema::create('test_models_soft_deletes', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
        $table->softDeletes();
    });

    // Create table for cross-model testing (AnotherTestModel)
    Schema::create('another_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_models');
    Schema::dropIfExists('test_models_soft_deletes');
    Schema::dropIfExists('another_test_models');
});

test('generates slug from name on creation', function () {
    $model = TestModel::create(['name' => 'Test Page']);

    expect($model->slug)->toBe('test-page');
});

test('generates unique slug with numeric suffix when duplicate exists', function () {
    TestModel::create(['name' => 'Test Page']);
    $model2 = TestModel::create(['name' => 'Test Page']);
    $model3 = TestModel::create(['name' => 'Test Page']);

    expect($model2->slug)->toBe('test-page-2');
    expect($model3->slug)->toBe('test-page-3');
});

test('does not override manually set slug', function () {
    $model = TestModel::create([
        'name' => 'Test Page',
        'slug' => 'custom-slug',
    ]);

    expect($model->slug)->toBe('custom-slug');
});

test('throws validation exception for reserved slugs', function () {
    TestModel::create(['name' => 'admin']);
})->throws(ValidationException::class, "The slug 'admin' is reserved and cannot be used.");

test('handles multiple reserved slugs correctly', function () {
    $reservedSlugs = config('content.reserved_slugs');

    foreach (['admin', 'api', 'horizon', 'login'] as $reserved) {
        if (in_array($reserved, $reservedSlugs)) {
            expect(fn () => TestModel::create(['name' => $reserved]))
                ->toThrow(ValidationException::class);
        }
    }
});

test('handles special characters in name', function () {
    $model = TestModel::create(['name' => 'Test & Page @ 2024!']);

    // Laravel's Str::slug() converts @ to 'at'
    expect($model->slug)->toBe('test-page-at-2024');
});

test('handles unicode characters', function () {
    $model = TestModel::create(['name' => 'Tëst Pâgé']);

    expect($model->slug)->toBeString();
    expect($model->slug)->not->toBeEmpty();
});

test('includes soft deleted records when checking uniqueness', function () {
    $model1 = TestModelWithSoftDeletes::create(['name' => 'Test Page']);
    expect($model1->slug)->toBe('test-page');

    $model1->delete(); // Soft delete

    $model2 = TestModelWithSoftDeletes::create(['name' => 'Test Page']);
    expect($model2->slug)->toBe('test-page-2');
});

test('handles very long names by limiting length', function () {
    // Use a shorter name that won't exceed database limits
    $longName = str_repeat('Very-Long-Name-', 10);
    $model = TestModel::create(['name' => $longName]);

    expect($model->slug)->toBeString();
    expect($model->slug)->not->toBeEmpty();
    expect(strlen($model->slug))->toBeLessThanOrEqual(255);
});

test('throws runtime exception when max attempts exceeded', function () {
    // Mock the config to a smaller number for testing
    config(['content.max_slug_suffix_attempts' => 3]);

    TestModel::create(['name' => 'Test']);
    TestModel::create(['name' => 'Test']); // test-2
    TestModel::create(['name' => 'Test']); // test-3

    TestModel::create(['name' => 'Test']); // Should throw
})->throws(\RuntimeException::class);

test('generates url safe slugs', function () {
    $model = TestModel::create(['name' => 'Test/Page\\With:Invalid*Characters']);

    expect($model->slug)->toMatch('/^[a-z0-9-]+$/');
    expect(str_contains($model->slug, '/'))->toBeFalse();
    expect(str_contains($model->slug, '\\'))->toBeFalse();
    expect(str_contains($model->slug, ':'))->toBeFalse();
    expect(str_contains($model->slug, '*'))->toBeFalse();
});

test('trims whitespace from name', function () {
    $model = TestModel::create(['name' => '  Test Page  ']);

    expect($model->slug)->toBe('test-page');
});

test('throws exception for empty string name', function () {
    TestModel::create(['name' => '']);
})->throws(\RuntimeException::class, 'Name contains no valid characters for slug generation');

test('handles numeric names', function () {
    $model = TestModel::create(['name' => '12345']);

    expect($model->slug)->toBe('12345');
});

test('generates unique slugs for similar names', function () {
    $model1 = TestModel::create(['name' => 'Test Page']);
    $model2 = TestModel::create(['name' => 'test-page']); // Same slug base
    $model3 = TestModel::create(['name' => 'TEST PAGE']); // Same slug base

    expect($model1->slug)->toBe('test-page');
    expect($model2->slug)->toBe('test-page-2');
    expect($model3->slug)->toBe('test-page-3');
});

test('throws exception for reserved slug with suffix', function () {
    // Add 'admin' to reserved slugs if not already present
    config(['content.reserved_slugs' => array_merge(
        config('content.reserved_slugs', []),
        ['admin']
    )]);

    // Create a model that takes the 'admin' slug
    TestModel::create(['name' => 'Administrator', 'slug' => 'admin-test']);

    // Now try to create 'admin' which should fail on base slug
    expect(fn () => TestModel::create(['name' => 'admin']))
        ->toThrow(ValidationException::class);
});

test('throws runtime exception for empty slug generation', function () {
    // Test with a name that only contains characters that Str::slug removes
    expect(fn () => TestModel::create(['name' => '!!!']))
        ->toThrow(\RuntimeException::class, 'Name contains no valid characters for slug generation');
});

test('handles names with only special characters by throwing exception', function () {
    expect(fn () => TestModel::create(['name' => '😀😁😂🤣']))
        ->toThrow(\RuntimeException::class);
});

test('validates reserved slugs case sensitively', function () {
    // Ensure 'admin' is in reserved slugs
    config(['content.reserved_slugs' => ['admin', 'api']]);

    // 'admin' should throw exception
    expect(fn () => TestModel::create(['name' => 'admin']))
        ->toThrow(ValidationException::class);

    // 'ADMIN' becomes 'admin' via Str::slug(), should also throw
    expect(fn () => TestModel::create(['name' => 'ADMIN']))
        ->toThrow(ValidationException::class);
});

test('slug remains stable when updating model name', function () {
    $model = TestModel::create(['name' => 'Original Name']);
    expect($model->slug)->toBe('original-name');

    // Update the name - slug should NOT change (SEO best practice)
    $model->update(['name' => 'Updated Name']);
    $model->refresh();

    expect($model->slug)->toBe('original-name');
    expect($model->name)->toBe('Updated Name');
});

test('can manually update slug on existing model', function () {
    $model = TestModel::create(['name' => 'Test Page']);
    expect($model->slug)->toBe('test-page');

    // Manually update slug
    $model->update(['slug' => 'new-custom-slug']);
    $model->refresh();

    expect($model->slug)->toBe('new-custom-slug');
});

test('updating model does not create duplicate slug conflict with itself', function () {
    $model = TestModel::create(['name' => 'Test Page']);
    expect($model->slug)->toBe('test-page');

    // Update the model multiple times - should not cause slug uniqueness issues
    $model->update(['name' => 'Different Name']);
    $model->update(['name' => 'Another Name']);

    // Slug should remain the same (not get suffixed)
    expect($model->slug)->toBe('test-page');
});

// ============================================================================
// EDGE CASE TESTS - T051
// ============================================================================

// ---------------------------------------------------------------------------
// 1. Duplicate Suffix Edge Cases
// ---------------------------------------------------------------------------

test('suffix increments sequentially without skipping gaps', function () {
    // Create page, page-3 (manually skipping page-2)
    TestModel::create(['name' => 'Test Page']);
    TestModel::create(['name' => 'Another Page', 'slug' => 'test-page-3']);

    // Now create another "Test Page" - should get page-2, NOT page-4
    $model = TestModel::create(['name' => 'Test Page']);

    expect($model->slug)->toBe('test-page-2');
});

test('handles high suffix numbers correctly', function () {
    // Create test-page and test-page-99
    TestModel::create(['name' => 'Test Page']);
    TestModel::create(['name' => 'Another', 'slug' => 'test-page-99']);

    // Next one should be test-page-2 (algorithm starts at 2 and increments)
    $model = TestModel::create(['name' => 'Test Page']);
    expect($model->slug)->toBe('test-page-2');
});

test('suffix works when manually set slug uses suffix pattern', function () {
    // Manually set 'test-page-5'
    TestModel::create(['name' => 'Custom', 'slug' => 'test-page-5']);

    // Create 'Test Page' - should get test-page
    $model1 = TestModel::create(['name' => 'Test Page']);
    expect($model1->slug)->toBe('test-page');

    // Create another 'Test Page' - should get test-page-2
    $model2 = TestModel::create(['name' => 'Test Page']);
    expect($model2->slug)->toBe('test-page-2');
});

test('handles base slug ending with number', function () {
    // Create 'Page 2024'
    $model1 = TestModel::create(['name' => 'Page 2024']);
    expect($model1->slug)->toBe('page-2024');

    // Create duplicate - should become page-2024-2
    $model2 = TestModel::create(['name' => 'Page 2024']);
    expect($model2->slug)->toBe('page-2024-2');

    // Third one - should become page-2024-3
    $model3 = TestModel::create(['name' => 'Page 2024']);
    expect($model3->slug)->toBe('page-2024-3');
});

test('suffix generation handles gaps in sequence', function () {
    // Create test-page, test-page-2, test-page-5
    TestModel::create(['name' => 'Test Page']); // test-page
    TestModel::create(['name' => 'Test Page']); // test-page-2
    TestModel::create(['name' => 'Custom', 'slug' => 'test-page-5']);

    // Next creation should get test-page-3 (fills the gap)
    $model = TestModel::create(['name' => 'Test Page']);
    expect($model->slug)->toBe('test-page-3');
});

test('suffix algorithm continues past manually created suffixes', function () {
    TestModel::create(['name' => 'Test']); // test
    TestModel::create(['name' => 'Custom', 'slug' => 'test-100']); // test-100

    // Should still get test-2, not test-101
    $model = TestModel::create(['name' => 'Test']);
    expect($model->slug)->toBe('test-2');
});

// ---------------------------------------------------------------------------
// 2. Reserved Slug Edge Cases
// ---------------------------------------------------------------------------

test('suffixed reserved slugs ARE allowed when only base is reserved', function () {
    // Configure reserved slugs
    config(['content.reserved_slugs' => ['admin', 'api']]);

    // 'admin' should fail
    expect(fn () => TestModel::create(['name' => 'admin']))
        ->toThrow(ValidationException::class);

    // But 'admin-panel' should succeed (only 'admin' is reserved, not 'admin-*')
    $model1 = TestModel::create(['name' => 'Admin Panel']); // Generates 'admin-panel'
    expect($model1->slug)->toBe('admin-panel');

    // And manually setting 'admin-2' should also succeed
    $model2 = TestModel::create(['name' => 'Custom', 'slug' => 'admin-2']);
    expect($model2->slug)->toBe('admin-2');
});

test('reserved slug check validates all config values', function () {
    $reservedSlugs = config('content.reserved_slugs');

    // Verify all reserved slugs are blocked
    foreach ($reservedSlugs as $reserved) {
        expect(fn () => TestModel::create(['name' => $reserved]))
            ->toThrow(ValidationException::class, "The slug '{$reserved}' is reserved and cannot be used.");
    }
});

test('reserved slug exception includes exact slug that failed', function () {
    config(['content.reserved_slugs' => ['admin', 'api', 'horizon']]);

    try {
        TestModel::create(['name' => 'horizon']);
        throw new \Exception('Should have thrown ValidationException');
    } catch (ValidationException $e) {
        expect($e->getMessage())->toContain("The slug 'horizon' is reserved and cannot be used.");
    }
});

test('reserved slugs are checked case sensitively', function () {
    config(['content.reserved_slugs' => ['admin']]);

    // 'Admin' becomes 'admin' via Str::slug(), should throw
    expect(fn () => TestModel::create(['name' => 'Admin']))
        ->toThrow(ValidationException::class);

    // 'ADMIN' becomes 'admin' via Str::slug(), should throw
    expect(fn () => TestModel::create(['name' => 'ADMIN']))
        ->toThrow(ValidationException::class);

    // 'AdMiN' becomes 'admin' via Str::slug(), should throw
    expect(fn () => TestModel::create(['name' => 'AdMiN']))
        ->toThrow(ValidationException::class);
});

test('attempting to create content that collides with reserved slug fails immediately', function () {
    config(['content.reserved_slugs' => ['admin', 'api']]);

    // Create 'Administrator' with manual slug 'admin-system'
    TestModel::create(['name' => 'Administrator', 'slug' => 'admin-system']);

    // Try to create 'admin' - should fail immediately
    expect(fn () => TestModel::create(['name' => 'admin']))
        ->toThrow(ValidationException::class, "The slug 'admin' is reserved and cannot be used.");
});

// ---------------------------------------------------------------------------
// 3. Cross-Model Same Slug Tests (CRITICAL)
// ---------------------------------------------------------------------------

// Second test model for cross-model testing
class AnotherTestModel extends Model
{
    use HasSlug;

    protected $table = 'another_test_models';

    protected $guarded = [];
}

test('different models can have the same slug', function () {
    // Create 'test-page' in TestModel
    $model1 = TestModel::create(['name' => 'Test Page']);
    expect($model1->slug)->toBe('test-page');

    // Create 'test-page' in AnotherTestModel - should succeed with same slug
    $model2 = AnotherTestModel::create(['name' => 'Test Page']);
    expect($model2->slug)->toBe('test-page');

    // Both should exist with the same slug (use whereRaw for PostgreSQL 17 compatibility)
    expect(TestModel::whereRaw('slug::text = ?::text', ['test-page'])->exists())->toBeTrue();
    expect(AnotherTestModel::whereRaw('slug::text = ?::text', ['test-page'])->exists())->toBeTrue();
});

test('slug uniqueness is per-table not global', function () {
    // Create multiple records with same name in different models
    $testModel1 = TestModel::create(['name' => 'About Us']);
    $testModel2 = TestModel::create(['name' => 'About Us']); // Should get suffix
    $anotherModel = AnotherTestModel::create(['name' => 'About Us']); // Should NOT get suffix

    expect($testModel1->slug)->toBe('about-us');
    expect($testModel2->slug)->toBe('about-us-2'); // Suffix within same model
    expect($anotherModel->slug)->toBe('about-us'); // No suffix in different model
});

test('cross-model duplicate slug generation works independently', function () {
    // Create in TestModel: contact, contact-2
    TestModel::create(['name' => 'Contact']);
    TestModel::create(['name' => 'Contact']);

    // Create in AnotherTestModel: contact, contact-2
    AnotherTestModel::create(['name' => 'Contact']);
    AnotherTestModel::create(['name' => 'Contact']);

    // Verify both tables have their own sequence (use whereRaw for PostgreSQL 17 compatibility)
    expect(TestModel::whereRaw('slug::text = ?::text', ['contact'])->count())->toBe(1);
    expect(TestModel::whereRaw('slug::text = ?::text', ['contact-2'])->count())->toBe(1);
    expect(AnotherTestModel::whereRaw('slug::text = ?::text', ['contact'])->count())->toBe(1);
    expect(AnotherTestModel::whereRaw('slug::text = ?::text', ['contact-2'])->count())->toBe(1);
});

test('reserved slugs apply across all models using the trait', function () {
    config(['content.reserved_slugs' => ['admin']]);

    // 'admin' should be blocked in TestModel
    expect(fn () => TestModel::create(['name' => 'admin']))
        ->toThrow(ValidationException::class);

    // 'admin' should also be blocked in AnotherTestModel
    expect(fn () => AnotherTestModel::create(['name' => 'admin']))
        ->toThrow(ValidationException::class);
});
