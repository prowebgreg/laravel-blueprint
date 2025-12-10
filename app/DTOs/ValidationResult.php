<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\MediaType;

/**
 * Data Transfer Object for upload validation results.
 */
readonly class ValidationResult
{
    /**
     * Create a new validation result.
     *
     * @param  bool  $valid  Whether the upload passed validation
     * @param  MediaType|null  $mediaType  Detected media type if valid
     * @param  string|null  $error  Error message if validation failed
     * @param  array<string, int>|null  $dimensions  Image dimensions (width, height) if applicable
     */
    public function __construct(
        public bool $valid,
        public ?MediaType $mediaType = null,
        public ?string $error = null,
        public ?array $dimensions = null,
    ) {}

    /**
     * Create a successful validation result.
     *
     * @param  MediaType  $mediaType  Detected media type
     * @param  array<string, int>|null  $dimensions  Image dimensions if applicable
     */
    public static function success(MediaType $mediaType, ?array $dimensions = null): self
    {
        return new self(
            valid: true,
            mediaType: $mediaType,
            error: null,
            dimensions: $dimensions,
        );
    }

    /**
     * Create a failed validation result.
     *
     * @param  string  $error  Error message
     */
    public static function failed(string $error): self
    {
        return new self(
            valid: false,
            mediaType: null,
            error: $error,
            dimensions: null,
        );
    }
}
