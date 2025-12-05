<?php

declare(strict_types=1);

it('returns 403 forbidden when accessing horizon without authentication', function () {
    // Attempt to access Horizon dashboard without authentication
    $response = $this->get('/horizon');

    // Assert 403 Forbidden response
    $response->assertForbidden();
});
