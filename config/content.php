<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Reserved Slugs
    |--------------------------------------------------------------------------
    |
    | These slugs cannot be used for content pages as they are reserved for
    | system routes and functionality. Any attempt to use these slugs will
    | be rejected during validation.
    |
    */

    'reserved_slugs' => [
        'admin',
        'horizon',
        'api',
        'login',
        'logout',
        'register',
        'password',
        'storage',
        'sanctum',
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Recovery Days
    |--------------------------------------------------------------------------
    |
    | Number of days to keep soft-deleted content before permanent deletion.
    | After this period, content cannot be recovered and will be permanently
    | removed from the database.
    |
    */

    'recovery_days' => env('CONTENT_RECOVERY_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Maximum Slug Suffix Attempts
    |--------------------------------------------------------------------------
    |
    | When generating unique slugs with numeric suffixes (e.g., page-1, page-2),
    | this value limits how many attempts will be made before failing. This
    | prevents infinite loops when all possible slug variations are exhausted.
    |
    */

    'max_slug_suffix_attempts' => 1000,

];
