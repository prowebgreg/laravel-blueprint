<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Pages\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can login with seeded admin credentials and access dashboard', function () {
    // Seed the admin user using the AdminUserSeeder logic
    $user = User::factory()->create([
        'email' => 'info@proweb.ai',
        'password' => 'Levonik2007@',
        'name' => 'Admin',
        'email_verified_at' => now(),
    ]);

    // Test the login page with Livewire
    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'info@proweb.ai',
            'password' => 'Levonik2007@',
        ])
        ->call('authenticate')
        ->assertRedirect('/admin');

    // Assert user is authenticated
    $this->assertAuthenticatedAs($user);

    // Access the admin dashboard
    $dashboardResponse = $this->get('/admin');

    // Assert dashboard loads successfully
    $dashboardResponse->assertSuccessful();

    // Assert we can see the dashboard page (Filament renders the dashboard)
    $dashboardResponse->assertSee('Dashboard');
});

it('shows validation errors for invalid credentials', function () {
    // Create a user with different password
    User::factory()->create([
        'email' => 'info@proweb.ai',
        'password' => 'different-password',
    ]);

    // Attempt to login with wrong password using Livewire
    Livewire::test(Login::class)
        ->fillForm([
            'email' => 'info@proweb.ai',
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    // Assert user is not authenticated
    $this->assertGuest();
});

it('requires authentication to access admin dashboard', function () {
    // Attempt to access dashboard without authentication
    $response = $this->get('/admin');

    // Assert redirected to login page
    $response->assertRedirect('/admin/login');
});

it('can logout and be redirected to login page', function () {
    // Create and authenticate as admin user
    $user = User::factory()->create([
        'email' => 'info@proweb.ai',
        'password' => 'Levonik2007@',
    ]);

    $this->actingAs($user);

    // Assert user is authenticated
    $this->assertAuthenticated();

    // Logout
    $response = $this->post('/admin/logout');

    // Assert user is logged out
    $this->assertGuest();

    // Assert redirected to login page
    $response->assertRedirect('/admin/login');

    // Verify that after logout, protected admin pages require re-authentication
    $protectedPageResponse = $this->get('/admin');
    $protectedPageResponse->assertRedirect('/admin/login');
});
