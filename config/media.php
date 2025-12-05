<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    |
    | Define the allowed file types for media uploads. Each MIME type is
    | mapped to its corresponding file extensions.
    |
    */

    'allowed_types' => [
        // Images
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'image/svg+xml' => ['svg'],

        // Documents
        'application/pdf' => ['pdf'],

        // Videos
        'video/mp4' => ['mp4'],
        'video/webm' => ['webm'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum File Size
    |--------------------------------------------------------------------------
    |
    | The maximum file size allowed for uploads in kilobytes.
    | Default: 10240 KB (10 MB)
    |
    */

    'max_file_size' => 10240, // 10 MB in KB

    /*
    |--------------------------------------------------------------------------
    | Validation Messages
    |--------------------------------------------------------------------------
    |
    | Custom validation error messages for media uploads.
    |
    */

    'validation_messages' => [
        'size' => 'File size exceeds maximum allowed size of 10MB',
        'type' => 'File type not allowed. Accepted types: jpg, jpeg, png, gif, webp, svg, pdf, mp4, webm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    |
    | The following methods can be accessed via config('media.helpers')
    |
    */

    'helpers' => [
        /**
         * Get all allowed MIME types as an array
         */
        'mime_types' => fn (): array => array_keys(config('media.allowed_types')),

        /**
         * Get all allowed file extensions as a flat array
         */
        'extensions' => fn (): array => array_merge(...array_values(config('media.allowed_types'))),

        /**
         * Get formatted extension list for validation messages
         */
        'extensions_list' => fn (): string => implode(', ', array_merge(...array_values(config('media.allowed_types')))),
    ],

];
