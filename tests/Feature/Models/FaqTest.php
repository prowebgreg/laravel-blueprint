<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CRUD operations', function () {
    test('creates faq with minimal required fields', function () {
        $faq = Faq::factory()->create(['name' => 'Test FAQ']);

        expect($faq)->toBeInstanceOf(Faq::class);
        expect($faq->name)->toBe('Test FAQ');
        expect($faq->status)->toBe(ContentStatus::Draft);
        expect($faq->question)->toBeNull();
        expect($faq->answer)->toBeNull();
    });

    test('creates faq with all fields populated', function () {
        $faq = Faq::factory()
            ->published()
            ->withQuestionAndAnswer()
            ->create(['name' => 'Full FAQ']);

        expect($faq->name)->toBe('Full FAQ');
        expect($faq->status)->toBe(ContentStatus::Published);
        expect($faq->question)->not->toBeNull();
        expect($faq->answer)->not->toBeNull();
    });

    test('reads faq and verifies all fields', function () {
        $data = [
            'name' => 'Pricing FAQ',
            'status' => ContentStatus::Published,
            'question' => 'How much does it cost?',
            'answer' => 'Our pricing varies based on project scope and requirements.',
        ];

        $faq = Faq::factory()->create($data);
        $retrieved = Faq::find($faq->id);

        expect($retrieved->name)->toBe($data['name']);
        expect($retrieved->status)->toBe($data['status']);
        expect($retrieved->question)->toBe($data['question']);
        expect($retrieved->answer)->toBe($data['answer']);
    });

    test('updates faq fields', function () {
        $faq = Faq::factory()->create([
            'name' => 'Original FAQ',
            'question' => 'Original question?',
        ]);

        $faq->update([
            'name' => 'Updated FAQ',
            'question' => 'Updated question?',
            'answer' => 'New answer text',
        ]);

        $faq->refresh();

        expect($faq->name)->toBe('Updated FAQ');
        expect($faq->question)->toBe('Updated question?');
        expect($faq->answer)->toBe('New answer text');
    });

    test('soft deletes faq', function () {
        $faq = Faq::factory()->create(['name' => 'To Delete']);

        $faq->delete();

        expect(Faq::find($faq->id))->toBeNull();
        expect(Faq::withTrashed()->find($faq->id))->not->toBeNull();
        expect($faq->deleted_at)->not->toBeNull();
    });

    test('restores soft-deleted faq', function () {
        $faq = Faq::factory()->create(['name' => 'To Restore']);
        $faq->delete();

        expect(Faq::find($faq->id))->toBeNull();

        $faq->restore();

        expect(Faq::find($faq->id))->not->toBeNull();
        expect($faq->deleted_at)->toBeNull();
    });

    test('queries all faqs', function () {
        Faq::factory()->count(3)->create();

        $faqs = Faq::all();

        expect($faqs)->toHaveCount(3);
    });

    test('queries faqs by status', function () {
        Faq::factory()->draft()->count(2)->create();
        Faq::factory()->published()->count(3)->create();

        $drafts = Faq::where('status', ContentStatus::Draft)->get();
        $published = Faq::where('status', ContentStatus::Published)->get();

        expect($drafts)->toHaveCount(2);
        expect($published)->toHaveCount(3);
    });

    test('soft-deleted faqs excluded from normal queries', function () {
        Faq::factory()->count(3)->create();
        $toDelete = Faq::factory()->create();
        $toDelete->delete();

        $faqs = Faq::all();

        expect($faqs)->toHaveCount(3);
        expect(Faq::withTrashed()->get())->toHaveCount(4);
    });
});

describe('status transitions', function () {
    test('default status is draft', function () {
        $faq = Faq::factory()->create();

        expect($faq->status)->toBe(ContentStatus::Draft);
    });

    test('changes status from draft to published', function () {
        $faq = Faq::factory()->draft()->create();

        expect($faq->status)->toBe(ContentStatus::Draft);

        $faq->update(['status' => ContentStatus::Published]);

        expect($faq->fresh()->status)->toBe(ContentStatus::Published);
    });

    test('changes status from published to draft', function () {
        $faq = Faq::factory()->published()->create();

        expect($faq->status)->toBe(ContentStatus::Published);

        $faq->update(['status' => ContentStatus::Draft]);

        expect($faq->fresh()->status)->toBe(ContentStatus::Draft);
    });

    test('queries only published faqs', function () {
        Faq::factory()->draft()->count(3)->create();
        Faq::factory()->published()->count(2)->create();

        $published = Faq::where('status', ContentStatus::Published)->get();

        expect($published)->toHaveCount(2);
        foreach ($published as $faq) {
            expect($faq->status)->toBe(ContentStatus::Published);
        }
    });

    test('queries only draft faqs', function () {
        Faq::factory()->draft()->count(3)->create();
        Faq::factory()->published()->count(2)->create();

        $drafts = Faq::where('status', ContentStatus::Draft)->get();

        expect($drafts)->toHaveCount(3);
        foreach ($drafts as $faq) {
            expect($faq->status)->toBe(ContentStatus::Draft);
        }
    });
});

describe('question and answer fields', function () {
    test('stores and retrieves question and answer correctly', function () {
        $question = 'What is your refund policy?';
        $answer = 'We offer a 30-day money-back guarantee on all services.';

        $faq = Faq::factory()->create([
            'question' => $question,
            'answer' => $answer,
        ]);

        $fresh = $faq->fresh();

        expect($fresh->question)->toBe($question);
        expect($fresh->answer)->toBe($answer);
    });

    test('creates faq with question and answer using factory state', function () {
        $faq = Faq::factory()->withQuestionAndAnswer()->create();

        expect($faq->question)->not->toBeNull();
        expect($faq->answer)->not->toBeNull();
        expect($faq->question)->toBeString();
        expect($faq->answer)->toBeString();
    });

    test('updates question and answer independently', function () {
        $faq = Faq::factory()->create([
            'question' => 'Original question?',
            'answer' => 'Original answer.',
        ]);

        $faq->update(['question' => 'Updated question?']);
        $faq->refresh();

        expect($faq->question)->toBe('Updated question?');
        expect($faq->answer)->toBe('Original answer.');

        $faq->update(['answer' => 'Updated answer.']);
        $faq->refresh();

        expect($faq->question)->toBe('Updated question?');
        expect($faq->answer)->toBe('Updated answer.');
    });

    test('handles null values for question and answer', function () {
        $faq = Faq::factory()->create([
            'question' => null,
            'answer' => null,
        ]);

        expect($faq->question)->toBeNull();
        expect($faq->answer)->toBeNull();
        expect($faq->exists)->toBeTrue();
    });

    test('handles long question and answer text', function () {
        $longQuestion = str_repeat('What is this? ', 50); // ~650 chars
        $longAnswer = str_repeat('This is a detailed answer. ', 100); // ~2700 chars

        $faq = Faq::factory()->create([
            'question' => $longQuestion,
            'answer' => $longAnswer,
        ]);

        expect($faq->fresh()->question)->toBe($longQuestion);
        expect($faq->fresh()->answer)->toBe($longAnswer);
    });

    test('handles unicode in question and answer', function () {
        $faq = Faq::factory()->create([
            'question' => 'Quelles sont vos heures d\'ouverture? 🕐',
            'answer' => 'Nous sommes ouverts de 9h à 17h du lundi au vendredi. 📅',
        ]);

        expect($faq->fresh()->question)->toBe('Quelles sont vos heures d\'ouverture? 🕐');
        expect($faq->fresh()->answer)->toBe('Nous sommes ouverts de 9h à 17h du lundi au vendredi. 📅');
    });
});

describe('faq-specific tests', function () {
    test('faq table is correct', function () {
        $faq = Faq::factory()->create();

        expect($faq->getTable())->toBe('faqs');
    });

    test('faq uses HasRelatedContent trait', function () {
        $traits = class_uses_recursive(Faq::class);

        expect($traits)->toHaveKey(\App\Traits\HasRelatedContent::class);
    });

    test('faq uses SoftDeletes trait', function () {
        $traits = class_uses_recursive(Faq::class);

        expect($traits)->toHaveKey(\Illuminate\Database\Eloquent\SoftDeletes::class);
    });

    test('faq does not use HasSeo trait', function () {
        $traits = class_uses_recursive(Faq::class);

        expect($traits)->not->toHaveKey(\App\Traits\HasSeo::class);
    });

    test('faq does not use HasSlug trait', function () {
        $traits = class_uses_recursive(Faq::class);

        expect($traits)->not->toHaveKey(\App\Traits\HasSlug::class);
    });

    test('faq does not use HasContentBlocks trait', function () {
        $traits = class_uses_recursive(Faq::class);

        expect($traits)->not->toHaveKey(\App\Traits\HasContentBlocks::class);
    });

    test('faq casts are correct', function () {
        $faq = Faq::factory()->create();

        $casts = $faq->getCasts();

        expect($casts['status'])->toBe(ContentStatus::class);
        expect($casts['created_at'])->toBe('datetime');
        expect($casts['updated_at'])->toBe('datetime');
        expect($casts['deleted_at'])->toBe('datetime');
    });

    test('fillable fields include all required attributes', function () {
        $faq = new Faq;

        $fillable = $faq->getFillable();

        $requiredFields = [
            'name',
            'status',
            'question',
            'answer',
        ];

        foreach ($requiredFields as $field) {
            expect($fillable)->toContain($field);
        }
    });

    test('fillable fields do not include SEO or slug fields', function () {
        $faq = new Faq;

        $fillable = $faq->getFillable();

        $excludedFields = [
            'slug',
            'meta_title',
            'meta_description',
            'og_title',
            'content_blocks',
        ];

        foreach ($excludedFields as $field) {
            expect($fillable)->not->toContain($field);
        }
    });

    test('timestamps are present', function () {
        $faq = Faq::factory()->create();

        expect($faq->created_at)->not->toBeNull();
        expect($faq->updated_at)->not->toBeNull();
        expect($faq->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($faq->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    test('soft delete timestamp is nullable', function () {
        $faq = Faq::factory()->create();

        expect($faq->deleted_at)->toBeNull();

        $faq->delete();

        expect($faq->deleted_at)->not->toBeNull();
        expect($faq->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });
});

describe('edge cases', function () {
    test('handles very long faq name', function () {
        $longName = str_repeat('Very-Long-Name-', 10); // ~150 chars
        $faq = Faq::factory()->create(['name' => $longName]);

        expect($faq->name)->toBe($longName);
        expect($faq->exists)->toBeTrue();
    });

    test('handles numeric faq name', function () {
        $faq = Faq::factory()->create(['name' => '12345']);

        expect($faq->name)->toBe('12345');
    });

    test('handles faq with special characters in name', function () {
        $faq = Faq::factory()->create(['name' => 'FAQ: Support & Help @ 2024!']);

        expect($faq->name)->toBe('FAQ: Support & Help @ 2024!');
    });

    test('handles multiple operations in sequence', function () {
        $faq = Faq::factory()->create(['name' => 'Test FAQ']);

        // Add question and answer
        $faq->update([
            'question' => 'Initial question?',
            'answer' => 'Initial answer.',
        ]);

        // Update status
        $faq->update(['status' => ContentStatus::Published]);

        // Update question
        $faq->update(['question' => 'Updated question?']);

        $fresh = $faq->fresh();

        expect($fresh->status)->toBe(ContentStatus::Published);
        expect($fresh->question)->toBe('Updated question?');
        expect($fresh->answer)->toBe('Initial answer.');
    });

    test('handles empty string question and answer', function () {
        $faq = Faq::factory()->create([
            'question' => '',
            'answer' => '',
        ]);

        expect($faq->question)->toBe('');
        expect($faq->answer)->toBe('');
        expect($faq->exists)->toBeTrue();
    });

    test('handles HTML in question and answer', function () {
        $question = 'How do I use <strong>bold</strong> text?';
        $answer = 'You can use <code>HTML tags</code> in your content.';

        $faq = Faq::factory()->create([
            'question' => $question,
            'answer' => $answer,
        ]);

        expect($faq->fresh()->question)->toBe($question);
        expect($faq->fresh()->answer)->toBe($answer);
    });

    test('multiple faqs with same name are allowed', function () {
        Faq::factory()->create(['name' => 'Common FAQ']);
        $faq2 = Faq::factory()->create(['name' => 'Common FAQ']);
        $faq3 = Faq::factory()->create(['name' => 'Common FAQ']);

        expect($faq2->name)->toBe('Common FAQ');
        expect($faq3->name)->toBe('Common FAQ');
        expect(Faq::where('name', 'Common FAQ')->count())->toBe(3);
    });
});
