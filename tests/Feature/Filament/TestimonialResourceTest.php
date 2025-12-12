<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Filament\Resources\TestimonialResource;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can render list page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(TestimonialResource::getUrl('index'))->assertSuccessful();
});

it('can render edit page', function () {
    $user = User::factory()->create();
    $testimonial = Testimonial::factory()->create();

    $this->actingAs($user);

    $this->get(TestimonialResource::getUrl('edit', ['record' => $testimonial]))->assertSuccessful();
});

it('can create testimonial via modal', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $newData = Testimonial::factory()->make();

    Livewire::actingAs($user)->test(TestimonialResource\Pages\ListTestimonials::class)
        ->callAction('create', data: [
            'name' => $newData->name,
            'author_name' => $newData->author_name,
            'quote' => $newData->quote,
            'status' => $newData->status,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Testimonial::class, [
        'name' => $newData->name,
        'author_name' => $newData->author_name,
        'quote' => $newData->quote,
        'status' => $newData->status,
    ]);
});

it('validates required fields in create modal', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::actingAs($user)->test(TestimonialResource\Pages\ListTestimonials::class)
        ->callAction('create', data: [
            'author_name' => null,
            'quote' => null,
        ])
        ->assertHasActionErrors([
            'author_name' => 'required',
            'quote' => 'required',
        ]);
});

it('can edit testimonial', function () {
    $user = User::factory()->create();
    $testimonial = Testimonial::factory()->create();

    $this->actingAs($user);

    $newData = Testimonial::factory()->make();

    Livewire::actingAs($user)->test(TestimonialResource\Pages\EditTestimonial::class, [
        'record' => $testimonial->getRouteKey(),
    ])
        ->fillForm([
            'author_name' => $newData->author_name,
            'quote' => $newData->quote,
            'status' => $newData->status,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($testimonial->refresh())
        ->author_name->toBe($newData->author_name)
        ->quote->toBe($newData->quote)
        ->status->toBe($newData->status);
});

it('can delete testimonial', function () {
    $user = User::factory()->create();
    $testimonial = Testimonial::factory()->create();

    $this->actingAs($user);

    Livewire::actingAs($user)->test(TestimonialResource\Pages\ListTestimonials::class)
        ->callTableAction('delete', $testimonial);

    $this->assertSoftDeleted($testimonial);
});

it('defaults status to draft in create modal', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $newData = Testimonial::factory()->make(['status' => ContentStatus::Draft]);

    Livewire::actingAs($user)->test(TestimonialResource\Pages\ListTestimonials::class)
        ->callAction('create', data: [
            'name' => $newData->name,
            'author_name' => $newData->author_name,
            'quote' => $newData->quote,
            // Status not provided, should default to Draft
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Testimonial::class, [
        'name' => $newData->name,
        'author_name' => $newData->author_name,
        'quote' => $newData->quote,
        'status' => ContentStatus::Draft,
    ]);
});
