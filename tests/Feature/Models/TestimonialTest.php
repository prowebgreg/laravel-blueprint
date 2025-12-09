<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CRUD operations', function () {
    test('creates testimonial with factory defaults', function () {
        $testimonial = Testimonial::factory()->create(['name' => 'Test Testimonial']);

        expect($testimonial)->toBeInstanceOf(Testimonial::class);
        expect($testimonial->name)->toBe('Test Testimonial');
        expect($testimonial->status)->toBe(ContentStatus::Draft);
        // Factory provides default values for author fields
        expect($testimonial->author_name)->not->toBeNull();
        expect($testimonial->author_title)->not->toBeNull();
        expect($testimonial->location)->not->toBeNull();
        expect($testimonial->quote)->not->toBeNull();
        // Factory leaves rating and avatar as null by default
        expect($testimonial->rating)->toBeNull();
        expect($testimonial->avatar)->toBeNull();
    });

    test('creates testimonial with only required name field', function () {
        $testimonial = Testimonial::factory()->create([
            'name' => 'Minimal Testimonial',
            'author_name' => null,
            'author_title' => null,
            'location' => null,
            'quote' => null,
        ]);

        expect($testimonial)->toBeInstanceOf(Testimonial::class);
        expect($testimonial->name)->toBe('Minimal Testimonial');
        expect($testimonial->status)->toBe(ContentStatus::Draft);
        expect($testimonial->author_name)->toBeNull();
        expect($testimonial->author_title)->toBeNull();
        expect($testimonial->location)->toBeNull();
        expect($testimonial->quote)->toBeNull();
        expect($testimonial->rating)->toBeNull();
        expect($testimonial->avatar)->toBeNull();
    });

    test('creates testimonial with all fields populated', function () {
        $testimonial = Testimonial::factory()
            ->published()
            ->withFullProfile()
            ->create(['name' => 'Full Testimonial']);

        expect($testimonial->name)->toBe('Full Testimonial');
        expect($testimonial->status)->toBe(ContentStatus::Published);
        expect($testimonial->author_name)->not->toBeNull();
        expect($testimonial->author_title)->not->toBeNull();
        expect($testimonial->location)->not->toBeNull();
        expect($testimonial->quote)->not->toBeNull();
        expect($testimonial->rating)->not->toBeNull();
        expect($testimonial->rating)->toBeGreaterThanOrEqual(4);
        expect($testimonial->rating)->toBeLessThanOrEqual(5);
        expect($testimonial->avatar)->not->toBeNull();
    });

    test('reads testimonial and verifies all fields', function () {
        $data = [
            'name' => 'Great Experience',
            'status' => ContentStatus::Published,
            'author_name' => 'John Doe',
            'author_title' => 'CEO',
            'location' => 'San Francisco, CA',
            'quote' => 'This is an excellent service!',
            'rating' => 5,
            'avatar' => 'https://example.com/avatar.jpg',
        ];

        $testimonial = Testimonial::factory()->create($data);
        $retrieved = Testimonial::find($testimonial->id);

        expect($retrieved->name)->toBe($data['name']);
        expect($retrieved->status)->toBe($data['status']);
        expect($retrieved->author_name)->toBe($data['author_name']);
        expect($retrieved->author_title)->toBe($data['author_title']);
        expect($retrieved->location)->toBe($data['location']);
        expect($retrieved->quote)->toBe($data['quote']);
        expect($retrieved->rating)->toBe($data['rating']);
        expect($retrieved->avatar)->toBe($data['avatar']);
    });

    test('updates testimonial fields', function () {
        $testimonial = Testimonial::factory()->create([
            'name' => 'Original Name',
            'author_name' => 'Original Author',
        ]);

        $testimonial->update([
            'name' => 'Updated Name',
            'author_name' => 'Updated Author',
            'rating' => 5,
        ]);

        $testimonial->refresh();

        expect($testimonial->name)->toBe('Updated Name');
        expect($testimonial->author_name)->toBe('Updated Author');
        expect($testimonial->rating)->toBe(5);
    });

    test('soft deletes testimonial', function () {
        $testimonial = Testimonial::factory()->create(['name' => 'To Delete']);

        $testimonial->delete();

        expect(Testimonial::find($testimonial->id))->toBeNull();
        expect(Testimonial::withTrashed()->find($testimonial->id))->not->toBeNull();
        expect($testimonial->deleted_at)->not->toBeNull();
    });

    test('restores soft-deleted testimonial', function () {
        $testimonial = Testimonial::factory()->create(['name' => 'To Restore']);
        $testimonial->delete();

        expect(Testimonial::find($testimonial->id))->toBeNull();

        $testimonial->restore();

        expect(Testimonial::find($testimonial->id))->not->toBeNull();
        expect($testimonial->deleted_at)->toBeNull();
    });

    test('queries all testimonials', function () {
        Testimonial::factory()->count(3)->create();

        $testimonials = Testimonial::all();

        expect($testimonials)->toHaveCount(3);
    });

    test('queries testimonials by status', function () {
        Testimonial::factory()->draft()->count(2)->create();
        Testimonial::factory()->published()->count(3)->create();

        $drafts = Testimonial::where('status', ContentStatus::Draft)->get();
        $published = Testimonial::where('status', ContentStatus::Published)->get();

        expect($drafts)->toHaveCount(2);
        expect($published)->toHaveCount(3);
    });

    test('soft-deleted testimonials excluded from normal queries', function () {
        Testimonial::factory()->count(3)->create();
        $toDelete = Testimonial::factory()->create();
        $toDelete->delete();

        $testimonials = Testimonial::all();

        expect($testimonials)->toHaveCount(3);
        expect(Testimonial::withTrashed()->get())->toHaveCount(4);
    });
});

describe('status transitions', function () {
    test('default status is draft', function () {
        $testimonial = Testimonial::factory()->create();

        expect($testimonial->status)->toBe(ContentStatus::Draft);
    });

    test('changes status from draft to published', function () {
        $testimonial = Testimonial::factory()->draft()->create();

        expect($testimonial->status)->toBe(ContentStatus::Draft);

        $testimonial->update(['status' => ContentStatus::Published]);

        expect($testimonial->fresh()->status)->toBe(ContentStatus::Published);
    });

    test('changes status from published to draft', function () {
        $testimonial = Testimonial::factory()->published()->create();

        expect($testimonial->status)->toBe(ContentStatus::Published);

        $testimonial->update(['status' => ContentStatus::Draft]);

        expect($testimonial->fresh()->status)->toBe(ContentStatus::Draft);
    });

    test('queries only published testimonials', function () {
        Testimonial::factory()->draft()->count(3)->create();
        Testimonial::factory()->published()->count(2)->create();

        $published = Testimonial::where('status', ContentStatus::Published)->get();

        expect($published)->toHaveCount(2);
        foreach ($published as $testimonial) {
            expect($testimonial->status)->toBe(ContentStatus::Published);
        }
    });

    test('queries only draft testimonials', function () {
        Testimonial::factory()->draft()->count(3)->create();
        Testimonial::factory()->published()->count(2)->create();

        $drafts = Testimonial::where('status', ContentStatus::Draft)->get();

        expect($drafts)->toHaveCount(3);
        foreach ($drafts as $testimonial) {
            expect($testimonial->status)->toBe(ContentStatus::Draft);
        }
    });
});

describe('rating validation', function () {
    test('null rating is valid', function () {
        $testimonial = Testimonial::factory()->create(['rating' => null]);

        expect($testimonial->rating)->toBeNull();
        expect($testimonial->exists)->toBeTrue();
    });

    test('rating 1 is valid (minimum)', function () {
        $testimonial = Testimonial::factory()->create(['rating' => 1]);

        expect($testimonial->rating)->toBe(1);
        expect($testimonial->exists)->toBeTrue();
    });

    test('rating 5 is valid (maximum)', function () {
        $testimonial = Testimonial::factory()->create(['rating' => 5]);

        expect($testimonial->rating)->toBe(5);
        expect($testimonial->exists)->toBeTrue();
    });

    test('rating 3 is valid (middle)', function () {
        $testimonial = Testimonial::factory()->create(['rating' => 3]);

        expect($testimonial->rating)->toBe(3);
        expect($testimonial->exists)->toBeTrue();
    });

    test('rating 0 is rejected by database constraint', function () {
        Testimonial::factory()->create(['rating' => 0]);
    })->throws(\Illuminate\Database\QueryException::class);

    test('rating 6 is rejected by database constraint', function () {
        Testimonial::factory()->create(['rating' => 6]);
    })->throws(\Illuminate\Database\QueryException::class);

    test('rating -1 is rejected by database constraint', function () {
        Testimonial::factory()->create(['rating' => -1]);
    })->throws(\Illuminate\Database\QueryException::class);

    test('all valid ratings 1-5 are accepted', function () {
        $ratings = [1, 2, 3, 4, 5];

        foreach ($ratings as $rating) {
            $testimonial = Testimonial::factory()->create(['rating' => $rating]);
            expect($testimonial->rating)->toBe($rating);
            expect($testimonial->exists)->toBeTrue();
        }

        expect(Testimonial::count())->toBe(5);
    });

    test('withRating factory state creates valid rating', function () {
        $testimonial = Testimonial::factory()->withRating()->create();

        expect($testimonial->rating)->not->toBeNull();
        expect($testimonial->rating)->toBeGreaterThanOrEqual(1);
        expect($testimonial->rating)->toBeLessThanOrEqual(5);
    });
});

describe('author fields', function () {
    test('creates testimonial with author fields', function () {
        $testimonial = Testimonial::factory()->create([
            'author_name' => 'Jane Smith',
            'author_title' => 'Marketing Director',
            'location' => 'New York, NY',
        ]);

        expect($testimonial->author_name)->toBe('Jane Smith');
        expect($testimonial->author_title)->toBe('Marketing Director');
        expect($testimonial->location)->toBe('New York, NY');
    });

    test('author fields are nullable', function () {
        $testimonial = Testimonial::factory()->create([
            'author_name' => null,
            'author_title' => null,
            'location' => null,
        ]);

        expect($testimonial->author_name)->toBeNull();
        expect($testimonial->author_title)->toBeNull();
        expect($testimonial->location)->toBeNull();
        expect($testimonial->exists)->toBeTrue();
    });

    test('quote field is nullable', function () {
        $testimonial = Testimonial::factory()->create(['quote' => null]);

        expect($testimonial->quote)->toBeNull();
        expect($testimonial->exists)->toBeTrue();
    });

    test('avatar field is nullable', function () {
        $testimonial = Testimonial::factory()->create(['avatar' => null]);

        expect($testimonial->avatar)->toBeNull();
        expect($testimonial->exists)->toBeTrue();
    });

    test('withAvatar factory state adds avatar', function () {
        $testimonial = Testimonial::factory()->withAvatar()->create();

        expect($testimonial->avatar)->not->toBeNull();
        expect($testimonial->avatar)->toBeString();
    });

    test('withFullProfile factory state fills all optional fields', function () {
        $testimonial = Testimonial::factory()->withFullProfile()->create();

        expect($testimonial->author_name)->not->toBeNull();
        expect($testimonial->author_title)->not->toBeNull();
        expect($testimonial->location)->not->toBeNull();
        expect($testimonial->quote)->not->toBeNull();
        expect($testimonial->rating)->not->toBeNull();
        expect($testimonial->rating)->toBeGreaterThanOrEqual(4);
        expect($testimonial->rating)->toBeLessThanOrEqual(5);
        expect($testimonial->avatar)->not->toBeNull();
    });
});

describe('testimonial-specific tests', function () {
    test('testimonial table is correct', function () {
        $testimonial = Testimonial::factory()->create();

        expect($testimonial->getTable())->toBe('testimonials');
    });

    test('testimonial uses HasRelatedContent trait', function () {
        $traits = class_uses_recursive(Testimonial::class);

        expect($traits)->toHaveKey(\App\Traits\HasRelatedContent::class);
    });

    test('testimonial uses SoftDeletes trait', function () {
        $traits = class_uses_recursive(Testimonial::class);

        expect($traits)->toHaveKey(\Illuminate\Database\Eloquent\SoftDeletes::class);
    });

    test('testimonial does not use HasSeo trait', function () {
        $traits = class_uses_recursive(Testimonial::class);

        expect($traits)->not->toHaveKey(\App\Traits\HasSeo::class);
    });

    test('testimonial does not use HasSlug trait', function () {
        $traits = class_uses_recursive(Testimonial::class);

        expect($traits)->not->toHaveKey(\App\Traits\HasSlug::class);
    });

    test('testimonial does not use HasContentBlocks trait', function () {
        $traits = class_uses_recursive(Testimonial::class);

        expect($traits)->not->toHaveKey(\App\Traits\HasContentBlocks::class);
    });

    test('testimonial casts are correct', function () {
        $testimonial = Testimonial::factory()->create();

        $casts = $testimonial->getCasts();

        expect($casts['status'])->toBe(ContentStatus::class);
        expect($casts['rating'])->toBe('integer');
        expect($casts['created_at'])->toBe('datetime');
        expect($casts['updated_at'])->toBe('datetime');
        expect($casts['deleted_at'])->toBe('datetime');
    });

    test('fillable fields include all required attributes', function () {
        $testimonial = new Testimonial;

        $fillable = $testimonial->getFillable();

        $requiredFields = [
            'name',
            'status',
            'author_name',
            'author_title',
            'location',
            'quote',
            'rating',
            'avatar',
        ];

        foreach ($requiredFields as $field) {
            expect($fillable)->toContain($field);
        }
    });

    test('timestamps are present', function () {
        $testimonial = Testimonial::factory()->create();

        expect($testimonial->created_at)->not->toBeNull();
        expect($testimonial->updated_at)->not->toBeNull();
        expect($testimonial->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($testimonial->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    test('soft delete timestamp is nullable', function () {
        $testimonial = Testimonial::factory()->create();

        expect($testimonial->deleted_at)->toBeNull();

        $testimonial->delete();

        expect($testimonial->deleted_at)->not->toBeNull();
        expect($testimonial->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });
});

describe('edge cases', function () {
    test('handles very long testimonial name', function () {
        $longName = str_repeat('Very-Long-Name-', 10); // ~150 chars
        $testimonial = Testimonial::factory()->create(['name' => $longName]);

        expect($testimonial->name)->toBe($longName);
        expect($testimonial->exists)->toBeTrue();
    });

    test('handles very long quote', function () {
        $longQuote = str_repeat('This is a very detailed and comprehensive testimonial quote. ', 20);
        $testimonial = Testimonial::factory()->create(['quote' => $longQuote]);

        expect($testimonial->quote)->toBe($longQuote);
        expect($testimonial->exists)->toBeTrue();
    });

    test('handles unicode in author fields', function () {
        $testimonial = Testimonial::factory()->create([
            'author_name' => '山田太郎',
            'location' => 'Tōkyō, Japan',
            'quote' => 'これは素晴らしいサービスです！',
        ]);

        expect($testimonial->author_name)->toBe('山田太郎');
        expect($testimonial->location)->toBe('Tōkyō, Japan');
        expect($testimonial->quote)->toBe('これは素晴らしいサービスです！');
    });

    test('handles emoji in quote', function () {
        $testimonial = Testimonial::factory()->create([
            'quote' => 'Amazing service! 😀🎉🚀 Highly recommended! 👍',
        ]);

        expect($testimonial->quote)->toBe('Amazing service! 😀🎉🚀 Highly recommended! 👍');
    });

    test('handles all null optional fields', function () {
        $testimonial = Testimonial::factory()->create([
            'author_name' => null,
            'author_title' => null,
            'location' => null,
            'quote' => null,
            'rating' => null,
            'avatar' => null,
        ]);

        expect($testimonial->exists)->toBeTrue();
        expect($testimonial->author_name)->toBeNull();
        expect($testimonial->author_title)->toBeNull();
        expect($testimonial->location)->toBeNull();
        expect($testimonial->quote)->toBeNull();
        expect($testimonial->rating)->toBeNull();
        expect($testimonial->avatar)->toBeNull();
    });

    test('multiple operations in sequence', function () {
        $testimonial = Testimonial::factory()->create(['name' => 'Test']);

        // Update author info
        $testimonial->update([
            'author_name' => 'John Doe',
            'author_title' => 'CEO',
        ]);

        // Update status
        $testimonial->update(['status' => ContentStatus::Published]);

        // Add rating
        $testimonial->update(['rating' => 5]);

        // Update quote
        $testimonial->update(['quote' => 'Excellent service!']);

        $fresh = $testimonial->fresh();

        expect($fresh->author_name)->toBe('John Doe');
        expect($fresh->author_title)->toBe('CEO');
        expect($fresh->status)->toBe(ContentStatus::Published);
        expect($fresh->rating)->toBe(5);
        expect($fresh->quote)->toBe('Excellent service!');
    });

    test('handles numeric testimonial name', function () {
        $testimonial = Testimonial::factory()->create(['name' => '12345']);

        expect($testimonial->name)->toBe('12345');
    });

    test('rating field is cast to integer', function () {
        $testimonial = Testimonial::factory()->create(['rating' => '4']);

        expect($testimonial->rating)->toBe(4);
        expect($testimonial->rating)->toBeInt();
    });
});
