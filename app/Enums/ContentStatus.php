<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Published = 'published';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Published => 'success',
            self::Draft => 'gray',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Published => 'Published',
            self::Draft => 'Draft',
        };
    }
}
