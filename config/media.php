<?php

declare(strict_types=1);

return [
    'max_upload_size' => env('MEDIA_MAX_UPLOAD_SIZE', 20971520), // 20MB
    'max_dimensions' => env('MEDIA_MAX_DIMENSIONS', 16000),
    'min_dimensions' => env('MEDIA_MIN_DIMENSIONS', 50),
    'variant_quality' => env('MEDIA_VARIANT_QUALITY', 85),
    'variant_widths' => [480, 640, 720, 960, 1168, 1440, 1920],
    'processing_queue' => env('MEDIA_PROCESSING_QUEUE', 'media'),
    'processing_timeout' => env('MEDIA_PROCESSING_TIMEOUT', 180),
    'slow_operation_threshold' => env('MEDIA_SLOW_OPERATION_THRESHOLD', 30),
    'failed_retention_hours' => env('MEDIA_FAILED_RETENTION_HOURS', 24),
    'soft_delete_retention_days' => env('MEDIA_SOFT_DELETE_RETENTION_DAYS', 30),
    'folder_prefix' => 'media',
    'folders' => [
        'images' => 'images',
        'videos' => 'videos',
        'svg' => 'svg',
    ],
    'allowed_image_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'allowed_video_mimes' => ['video/mp4', 'video/webm', 'video/quicktime'],
    'allowed_svg_mimes' => ['image/svg+xml'],
];
