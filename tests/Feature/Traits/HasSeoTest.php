<?php

declare(strict_types=1);

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * Test model that uses the HasSeo trait.
 */
class TestSeoModel extends Model
{
    use HasSeo;

    protected $table = 'test_seo_models';

    protected $guarded = [];
}

beforeEach(function () {
    Schema::create('test_seo_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        $table->string('og_title')->nullable();
        $table->text('og_description')->nullable();
        $table->string('twitter_title')->nullable();
        $table->text('twitter_description')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_seo_models');
});

describe('og_title accessor', function () {
    test('returns og_title when explicitly set', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'og_title' => 'OG Title',
        ]);

        expect($model->og_title)->toBe('OG Title');
    });

    test('mirrors meta_title when og_title is NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'og_title' => null,
        ]);

        expect($model->og_title)->toBe('Meta Title');
    });

    test('returns NULL when both og_title and meta_title are NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => null,
            'og_title' => null,
        ]);

        expect($model->og_title)->toBeNull();
    });

    test('empty string does not mirror - returns empty string', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'og_title' => '',
        ]);

        expect($model->og_title)->toBe('');
    });

    test('setting to non-NULL breaks mirroring', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'og_title' => null,
        ]);

        expect($model->og_title)->toBe('Meta Title');

        $model->update(['og_title' => 'Independent OG Title']);
        $model->refresh();

        expect($model->og_title)->toBe('Independent OG Title');
    });
});

describe('og_description accessor', function () {
    test('returns og_description when explicitly set', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => 'Meta Description',
            'og_description' => 'OG Description',
        ]);

        expect($model->og_description)->toBe('OG Description');
    });

    test('mirrors meta_description when og_description is NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => 'Meta Description',
            'og_description' => null,
        ]);

        expect($model->og_description)->toBe('Meta Description');
    });

    test('returns NULL when both are NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => null,
            'og_description' => null,
        ]);

        expect($model->og_description)->toBeNull();
    });

    test('empty string does not mirror', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => 'Meta Description',
            'og_description' => '',
        ]);

        expect($model->og_description)->toBe('');
    });

    test('setting to non-NULL breaks mirroring', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => 'Meta Description',
            'og_description' => null,
        ]);

        expect($model->og_description)->toBe('Meta Description');

        $model->update(['og_description' => 'Independent OG Description']);
        $model->refresh();

        expect($model->og_description)->toBe('Independent OG Description');
    });
});

describe('twitter_title accessor', function () {
    test('returns twitter_title when explicitly set', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'twitter_title' => 'Twitter Title',
        ]);

        expect($model->twitter_title)->toBe('Twitter Title');
    });

    test('mirrors meta_title when twitter_title is NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'twitter_title' => null,
        ]);

        expect($model->twitter_title)->toBe('Meta Title');
    });

    test('returns NULL when both are NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => null,
            'twitter_title' => null,
        ]);

        expect($model->twitter_title)->toBeNull();
    });

    test('empty string does not mirror', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'twitter_title' => '',
        ]);

        expect($model->twitter_title)->toBe('');
    });

    test('setting to non-NULL breaks mirroring', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'twitter_title' => null,
        ]);

        expect($model->twitter_title)->toBe('Meta Title');

        $model->update(['twitter_title' => 'Independent Twitter Title']);
        $model->refresh();

        expect($model->twitter_title)->toBe('Independent Twitter Title');
    });
});

describe('twitter_description accessor', function () {
    test('returns twitter_description when explicitly set', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => 'Meta Description',
            'twitter_description' => 'Twitter Description',
        ]);

        expect($model->twitter_description)->toBe('Twitter Description');
    });

    test('mirrors meta_description when twitter_description is NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => 'Meta Description',
            'twitter_description' => null,
        ]);

        expect($model->twitter_description)->toBe('Meta Description');
    });

    test('returns NULL when both are NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => null,
            'twitter_description' => null,
        ]);

        expect($model->twitter_description)->toBeNull();
    });

    test('empty string does not mirror', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => 'Meta Description',
            'twitter_description' => '',
        ]);

        expect($model->twitter_description)->toBe('');
    });

    test('setting to non-NULL breaks mirroring', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_description' => 'Meta Description',
            'twitter_description' => null,
        ]);

        expect($model->twitter_description)->toBe('Meta Description');

        $model->update(['twitter_description' => 'Independent Twitter Description']);
        $model->refresh();

        expect($model->twitter_description)->toBe('Independent Twitter Description');
    });
});

describe('combined mirroring scenarios', function () {
    test('all social fields mirror when NULL', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => null,
            'og_description' => null,
            'twitter_title' => null,
            'twitter_description' => null,
        ]);

        expect($model->og_title)->toBe('Meta Title');
        expect($model->og_description)->toBe('Meta Description');
        expect($model->twitter_title)->toBe('Meta Title');
        expect($model->twitter_description)->toBe('Meta Description');
    });

    test('independent social fields do not affect each other', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => 'Custom OG Title',
            'og_description' => null,
            'twitter_title' => null,
            'twitter_description' => 'Custom Twitter Description',
        ]);

        expect($model->og_title)->toBe('Custom OG Title');
        expect($model->og_description)->toBe('Meta Description'); // Mirrored
        expect($model->twitter_title)->toBe('Meta Title'); // Mirrored
        expect($model->twitter_description)->toBe('Custom Twitter Description');
    });

    test('updating meta fields affects mirrored social fields', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Original Meta Title',
            'og_title' => null,
            'twitter_title' => null,
        ]);

        expect($model->og_title)->toBe('Original Meta Title');
        expect($model->twitter_title)->toBe('Original Meta Title');

        $model->update(['meta_title' => 'Updated Meta Title']);
        $model->refresh();

        expect($model->og_title)->toBe('Updated Meta Title');
        expect($model->twitter_title)->toBe('Updated Meta Title');
    });

    test('setting field back to NULL re-enables mirroring', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'og_title' => 'Custom OG Title',
        ]);

        expect($model->og_title)->toBe('Custom OG Title');

        $model->update(['og_title' => null]);
        $model->refresh();

        expect($model->og_title)->toBe('Meta Title');
    });

    test('all fields can be independently set', function () {
        $model = TestSeoModel::create([
            'name' => 'Test',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'og_title' => 'OG Title',
            'og_description' => 'OG Description',
            'twitter_title' => 'Twitter Title',
            'twitter_description' => 'Twitter Description',
        ]);

        expect($model->og_title)->toBe('OG Title');
        expect($model->og_description)->toBe('OG Description');
        expect($model->twitter_title)->toBe('Twitter Title');
        expect($model->twitter_description)->toBe('Twitter Description');
    });
});
