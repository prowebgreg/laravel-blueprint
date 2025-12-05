<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects unauthenticated users from admin panel to login page', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});
