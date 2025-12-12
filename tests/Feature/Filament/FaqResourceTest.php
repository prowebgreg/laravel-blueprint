<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Filament\Resources\FaqResource;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can render list page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(FaqResource::getUrl('index'))->assertSuccessful();
});

it('can render edit page', function () {
    $user = User::factory()->create();
    $faq = Faq::factory()->withQuestionAndAnswer()->create();

    $this->actingAs($user);

    $this->get(FaqResource::getUrl('edit', ['record' => $faq]))->assertSuccessful();
});

it('can create faq via modal', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $newData = Faq::factory()->withQuestionAndAnswer()->make();

    Livewire::actingAs($user)->test(FaqResource\Pages\ListFaqs::class)
        ->callAction('create', data: [
            'name' => $newData->name,
            'question' => $newData->question,
            'answer' => $newData->answer,
            'status' => $newData->status,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Faq::class, [
        'name' => $newData->name,
        'question' => $newData->question,
        'answer' => $newData->answer,
        'status' => $newData->status,
    ]);
});

it('validates required fields in create modal', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::actingAs($user)->test(FaqResource\Pages\ListFaqs::class)
        ->callAction('create', data: [
            'question' => null,
            'answer' => null,
        ])
        ->assertHasActionErrors([
            'question' => 'required',
            'answer' => 'required',
        ]);
});

it('can edit faq', function () {
    $user = User::factory()->create();
    $faq = Faq::factory()->withQuestionAndAnswer()->create();

    $this->actingAs($user);

    $newData = Faq::factory()->withQuestionAndAnswer()->make();

    Livewire::actingAs($user)->test(FaqResource\Pages\EditFaq::class, [
        'record' => $faq->getRouteKey(),
    ])
        ->fillForm([
            'question' => $newData->question,
            'answer' => $newData->answer,
            'status' => $newData->status,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($faq->refresh())
        ->question->toBe($newData->question)
        ->answer->toBe($newData->answer)
        ->status->toBe($newData->status);
});

it('can delete faq', function () {
    $user = User::factory()->create();
    $faq = Faq::factory()->withQuestionAndAnswer()->create();

    $this->actingAs($user);

    Livewire::actingAs($user)->test(FaqResource\Pages\ListFaqs::class)
        ->callTableAction('delete', $faq);

    $this->assertSoftDeleted($faq);
});

it('defaults status to draft in create modal', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $newData = Faq::factory()->withQuestionAndAnswer()->make(['status' => ContentStatus::Draft]);

    Livewire::actingAs($user)->test(FaqResource\Pages\ListFaqs::class)
        ->callAction('create', data: [
            'name' => $newData->name,
            'question' => $newData->question,
            'answer' => $newData->answer,
            // Status not provided, should default to Draft
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Faq::class, [
        'name' => $newData->name,
        'question' => $newData->question,
        'answer' => $newData->answer,
        'status' => ContentStatus::Draft,
    ]);
});
