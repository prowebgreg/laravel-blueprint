<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('displays welcome message with user name', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    $response->assertSee('Welcome back, John Doe!');
});

it('displays welcome message for guest when no user', function () {
    $response = $this->get('/admin/login');

    $response->assertSuccessful();
});

it('displays subheading on dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    $response->assertSee('Here\'s an overview of your content management system.');
});

it('displays stats overview widget', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    $response->assertSeeLivewire(\App\Filament\Widgets\StatsOverviewWidget::class);
});

it('displays quick actions widget', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    $response->assertSeeLivewire(\App\Filament\Widgets\QuickActionsWidget::class);
});

it('displays recent activity widget', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    $response->assertSeeLivewire(\App\Filament\Widgets\RecentActivityWidget::class);
});

it('displays quick action links with correct labels', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(\App\Filament\Widgets\QuickActionsWidget::class)
        ->assertSee('Quick Actions')
        ->assertSee('Create Page')
        ->assertSee('Create Post')
        ->assertSee('Upload Media')
        ->assertSee('Add a new static page')
        ->assertSee('Write a new blog post')
        ->assertSee('Upload images or files');
});
