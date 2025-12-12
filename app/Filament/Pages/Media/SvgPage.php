<?php

declare(strict_types=1);

namespace App\Filament\Pages\Media;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class SvgPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static string $view = 'filament.pages.media.svg-page';

    protected static ?string $navigationGroup = 'Media Library';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'SVG';

    protected static ?string $navigationLabel = 'SVG';

    public function getHeading(): string|Htmlable
    {
        return 'SVG';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Browse and manage SVG vector assets';
    }
}
