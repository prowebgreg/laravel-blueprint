<?php

declare(strict_types=1);

use App\Models\User;

it('allows authenticated admin user to access horizon dashboard', function () {
    // Create and authenticate an admin user
    $user = User::factory()->create();

    // Authenticate as the user
    $this->actingAs($user);

    // Access Horizon dashboard
    $response = $this->get('/horizon');

    // Assert successful access
    $response->assertSuccessful();
});
