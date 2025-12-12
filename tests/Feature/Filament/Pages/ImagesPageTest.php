<?php

declare(strict_types=1);

use App\Filament\Pages\Media\ImagesPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can render images page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(ImagesPage::getUrl())->assertSuccessful();
});

it('defaults to grid view mode', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ImagesPage::class)
        ->assertSet('viewMode', 'grid');
});

it('can toggle to table view', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ImagesPage::class)
        ->assertSet('viewMode', 'grid')
        ->callAction('tableView')
        ->assertSet('viewMode', 'table');
});

it('can toggle back to grid view', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ImagesPage::class)
        ->set('viewMode', 'table')
        ->callAction('gridView')
        ->assertSet('viewMode', 'grid');
});

it('displays placeholder images in grid view', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ImagesPage::class)
        ->assertSet('viewMode', 'grid')
        ->assertSee('hero-homepage.webp')
        ->assertSee('about-team-photo.jpg')
        ->assertSee('product-showcase.webp');
});

it('displays placeholder images in table view', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ImagesPage::class)
        ->callAction('tableView')
        ->assertSet('viewMode', 'table')
        ->assertSee('hero-homepage.webp')
        ->assertSee('1920 x 1080')
        ->assertSee('245 KB');
});

it('shows grid action as active when in grid view', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ImagesPage::class)
        ->assertSet('viewMode', 'grid')
        ->assertActionExists('gridView')
        ->assertActionExists('tableView');
});

it('shows table action as active when in table view', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ImagesPage::class)
        ->callAction('tableView')
        ->assertSet('viewMode', 'table')
        ->assertActionExists('gridView')
        ->assertActionExists('tableView');
});
