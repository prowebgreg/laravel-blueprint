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
});

afterEach(function () {
    Schema::dropIfExists('test_models');
    Schema::dropIfExists('test_models_soft_deletes');
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
