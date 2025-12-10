<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaFolder: string
{
    case Images = 'images';
    case Videos = 'videos';
    case Svg = 'svg';

    /**
     * Get folder for media type.
     */
    public static function forMediaType(MediaType $type): self
    {
        return match ($type) {
            MediaType::Image => self::Images,
            MediaType::Video => self::Videos,
            MediaType::Svg => self::Svg,
        };
    }

    /**
     * Get full S3 prefix path.
     */
    public function s3Prefix(): string
    {
        return 'media/'.$this->value;
    }
}
