<?php

declare(strict_types=1);

namespace App\DTOs\Media;

readonly class S3UploadResult
{
    public function __construct(
        public string $s3Key,
        public string $cloudfrontUrl,
    ) {}
}
