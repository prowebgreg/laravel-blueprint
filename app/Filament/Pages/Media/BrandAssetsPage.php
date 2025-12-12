<?php

declare(strict_types=1);

namespace App\Filament\Pages\Media;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;

class BrandAssetsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static string $view = 'filament.pages.media.brand-assets-page';

    protected static ?string $navigationGroup = 'Media Library';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Brand Assets';

    protected static ?string $navigationLabel = 'Brand Assets';

    #[Url]
    public string $viewMode = 'grid';

    public function getHeading(): string|Htmlable
    {
        return 'Brand Assets';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage logos, favicons, and other brand identity assets';
    }

    public function toggleViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'table'])) {
            $this->viewMode = $mode;
        }
    }
}
