<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects unauthenticated users to login when accessing /admin', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

it('allows authenticated users to access /admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    $response->assertSee('Dashboard');
});

it('allows unauthenticated users to access /admin/login page', function () {
    $response = $this->get('/admin/login');

    $response->assertSuccessful();
    $response->assertSee('Sign in');
});

it('displays dashboard correctly for authenticated users', function () {
    $user = User::factory()->create([
        'name' => 'Test Admin',
    ]);

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    $response->assertSee('Welcome back, Test Admin!');
    $response->assertSee('Dashboard');
});

it('prevents access to admin dashboard after logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Verify authenticated access works
    $response = $this->get('/admin');
    $response->assertSuccessful();

    // Logout
    $this->post('/admin/logout');

    // Verify access is now denied
    $response = $this->get('/admin');
    $response->assertRedirect('/admin/login');
});

it('persists authentication across multiple admin requests', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // First request
    $response = $this->get('/admin');
    $response->assertSuccessful();

    // Second request - should still be authenticated
    $response = $this->get('/admin');
    $response->assertSuccessful();

    // Verify we're still the same authenticated user
    $this->assertAuthenticatedAs($user);
});

it('displays dashboard widgets for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin');

    $response->assertSuccessful();
    $response->assertSeeLivewire(\App\Filament\Widgets\StatsOverviewWidget::class);
    $response->assertSeeLivewire(\App\Filament\Widgets\QuickActionsWidget::class);
    $response->assertSeeLivewire(\App\Filament\Widgets\RecentActivityWidget::class);
});

it('redirects authenticated users from login page to dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/admin/login');

    $response->assertRedirect('/admin');
});
