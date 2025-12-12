<?php

declare(strict_types=1);

namespace App\Enums;

enum RedirectType: string
{
    case Permanent = '301';
    case Temporary = '302';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => '301 (Permanent)',
            self::Temporary => '302 (Temporary)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Permanent => 'Permanently moved - search engines will update their index',
            self::Temporary => 'Temporarily moved - search engines will keep checking the original URL',
        };
    }
}
