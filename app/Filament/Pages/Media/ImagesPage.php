<?php

declare(strict_types=1);

namespace App\Filament\Pages\Media;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ImagesPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static string $view = 'filament.pages.media.images-page';

    protected static ?string $navigationGroup = 'Media Library';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Images';

    protected static ?string $navigationLabel = 'Images';

    public function getHeading(): string|Htmlable
    {
        return 'Images';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Browse and manage image assets';
    }
}
