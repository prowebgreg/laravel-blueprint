<?php

declare(strict_types=1);

namespace App\DTOs\Media;

readonly class S3DeleteResult
{
    /**
     * @param  array<string>  $failedKeys  S3 keys that failed to delete
     */
    public function __construct(
        public bool $success,
        public int $deletedCount,
        public array $failedKeys,
        public ?string $errorMessage = null,
    ) {}
}
