<?php

declare(strict_types=1);

use App\Enums\RedirectType;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can instantiate a redirect model', function () {
    $redirect = new Redirect;

    expect($redirect)->toBeInstanceOf(Redirect::class);
    expect($redirect->getTable())->toBe('redirects');
});

it('can create a redirect using factory', function () {
    $redirect = Redirect::factory()->create();

    expect($redirect)->toBeInstanceOf(Redirect::class);
    expect($redirect->id)->toBeInt();
    expect($redirect->source_path)->toBeString();
    expect($redirect->target_path)->toBeString();
    expect($redirect->redirect_type)->toBeInstanceOf(RedirectType::class);
    expect($redirect->is_active)->toBeBool();
    expect($redirect->hits)->toBeInt();
    expect($redirect->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($redirect->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('casts redirect_type to RedirectType enum correctly', function () {
    $redirect = Redirect::factory()->create([
        'redirect_type' => RedirectType::Permanent,
    ]);

    expect($redirect->redirect_type)->toBeInstanceOf(RedirectType::class);
    expect($redirect->redirect_type)->toBe(RedirectType::Permanent);
    expect($redirect->redirect_type->value)->toBe('301');

    $redirect->redirect_type = RedirectType::Temporary;
    $redirect->save();
    $redirect->refresh();

    expect($redirect->redirect_type)->toBe(RedirectType::Temporary);
    expect($redirect->redirect_type->value)->toBe('302');
});

it('casts is_active to boolean correctly', function () {
    $redirect = Redirect::factory()->create([
        'is_active' => true,
    ]);

    expect($redirect->is_active)->toBeBool();
    expect($redirect->is_active)->toBeTrue();

    $redirect->is_active = false;
    $redirect->save();
    $redirect->refresh();

    expect($redirect->is_active)->toBeBool();
    expect($redirect->is_active)->toBeFalse();
});

it('casts last_hit_at to datetime correctly', function () {
    $now = now();

    $redirect = Redirect::factory()->create([
        'last_hit_at' => $now,
    ]);

    expect($redirect->last_hit_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($redirect->last_hit_at->format('Y-m-d H:i:s'))->toBe($now->format('Y-m-d H:i:s'));
});

it('allows last_hit_at to be nullable', function () {
    $redirect = Redirect::factory()->create([
        'last_hit_at' => null,
    ]);

    expect($redirect->last_hit_at)->toBeNull();
});

it('casts hits to integer correctly', function () {
    $redirect = Redirect::factory()->create([
        'hits' => 127,
    ]);

    expect($redirect->hits)->toBeInt();
    expect($redirect->hits)->toBe(127);
});

it('can create a permanent redirect using factory state', function () {
    $redirect = Redirect::factory()->permanent()->create();

    expect($redirect->redirect_type)->toBe(RedirectType::Permanent);
    expect($redirect->redirect_type->value)->toBe('301');
});

it('can create a temporary redirect using factory state', function () {
    $redirect = Redirect::factory()->temporary()->create();

    expect($redirect->redirect_type)->toBe(RedirectType::Temporary);
    expect($redirect->redirect_type->value)->toBe('302');
});

it('can create an active redirect using factory state', function () {
    $redirect = Redirect::factory()->active()->create();

    expect($redirect->is_active)->toBeTrue();
});

it('can create an inactive redirect using factory state', function () {
    $redirect = Redirect::factory()->inactive()->create();

    expect($redirect->is_active)->toBeFalse();
});

it('can combine multiple factory states', function () {
    $redirect = Redirect::factory()
        ->permanent()
        ->active()
        ->create();

    expect($redirect->redirect_type)->toBe(RedirectType::Permanent);
    expect($redirect->is_active)->toBeTrue();
});

it('has correct fillable properties', function () {
    $fillable = [
        'source_path',
        'target_path',
        'redirect_type',
        'is_active',
        'hits',
        'last_hit_at',
        'notes',
    ];

    $redirect = new Redirect;

    expect($redirect->getFillable())->toBe($fillable);
});

it('can mass assign fillable attributes', function () {
    $data = [
        'source_path' => '/old-page',
        'target_path' => '/new-page',
        'redirect_type' => RedirectType::Permanent,
        'is_active' => true,
        'hits' => 50,
        'last_hit_at' => now(),
        'notes' => 'Test redirect',
    ];

    $redirect = Redirect::factory()->create($data);

    expect($redirect->source_path)->toBe('/old-page');
    expect($redirect->target_path)->toBe('/new-page');
    expect($redirect->redirect_type)->toBe(RedirectType::Permanent);
    expect($redirect->is_active)->toBeTrue();
    expect($redirect->hits)->toBe(50);
    expect($redirect->last_hit_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($redirect->notes)->toBe('Test redirect');
});

it('enforces unique constraint on source_path', function () {
    Redirect::factory()->create([
        'source_path' => '/unique-path',
    ]);

    expect(fn () => Redirect::factory()->create([
        'source_path' => '/unique-path',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('allows notes to be nullable', function () {
    $redirect = Redirect::factory()->create([
        'notes' => null,
    ]);

    expect($redirect->notes)->toBeNull();
});

it('stores and retrieves notes correctly', function () {
    $notes = 'This is a redirect for the old about page that was renamed during the 2024 redesign';

    $redirect = Redirect::factory()->create([
        'notes' => $notes,
    ]);

    expect($redirect->notes)->toBe($notes);

    $redirect->refresh();

    expect($redirect->notes)->toBe($notes);
});

it('has timestamps enabled', function () {
    $redirect = Redirect::factory()->create();

    expect($redirect->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($redirect->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('updates updated_at timestamp on modification', function () {
    $redirect = Redirect::factory()->create();

    $originalUpdatedAt = $redirect->updated_at;

    // Use travel to move time forward instead of sleep
    $this->travel(2)->seconds();

    $redirect->hits = 100;
    $redirect->save();

    expect($redirect->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
});
