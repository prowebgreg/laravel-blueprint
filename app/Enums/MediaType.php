<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaType: string
{
    case Image = 'image';
    case Video = 'video';
    case Svg = 'svg';

    /**
     * Get allowed MIME types for this media type.
     *
     * @return array<string>
     */
    public function allowedMimeTypes(): array
    {
        return match ($this) {
            self::Image => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            self::Video => ['video/mp4', 'video/webm', 'video/quicktime'],
            self::Svg => ['image/svg+xml'],
        };
    }

    /**
     * Determine media type from MIME type.
     */
    public static function fromMimeType(string $mimeType): ?self
    {
        foreach (self::cases() as $type) {
            if (in_array($mimeType, $type->allowedMimeTypes(), true)) {
                return $type;
            }
        }

        return null;
    }
}
