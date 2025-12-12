<?php

declare(strict_types=1);

namespace App\Filament\Pages\Media;

use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class BrandAssetsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static string $view = 'filament.pages.media.brand-assets-page';

    protected static ?string $navigationGroup = 'Media Library';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Brand Assets';

    protected static ?string $navigationLabel = 'Brand Assets';

    public string $viewMode = 'grid';

    public function getHeading(): string|Htmlable
    {
        return 'Brand Assets';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage logos, favicons, and other brand identity assets';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gridView')
                ->label('Grid')
                ->icon('heroicon-o-squares-2x2')
                ->color($this->viewMode === 'grid' ? 'primary' : 'gray')
                ->disabled($this->viewMode === 'grid')
                ->extraAttributes([
                    'aria-label' => 'Switch to grid view',
                    'aria-pressed' => $this->viewMode === 'grid' ? 'true' : 'false',
                ])
                ->action(fn () => $this->viewMode = 'grid'),
            Action::make('tableView')
                ->label('Table')
                ->icon('heroicon-o-table-cells')
                ->color($this->viewMode === 'table' ? 'primary' : 'gray')
                ->disabled($this->viewMode === 'table')
                ->extraAttributes([
                    'aria-label' => 'Switch to table view',
                    'aria-pressed' => $this->viewMode === 'table' ? 'true' : 'false',
                ])
                ->action(fn () => $this->viewMode = 'table'),
        ];
    }

    /**
     * Get placeholder brand asset data for scaffold display.
     *
     * @return array<int, array{name: string, type: string, dimensions: string, size: string, icon: string, bg: string}>
     */
    public function getPlaceholderBrandAssets(): array
    {
        return [
            [
                'name' => 'logo-light.svg',
                'type' => 'Logo',
                'dimensions' => '800 x 200',
                'size' => '12 KB',
                'icon' => 'heroicon-o-sparkles',
                'bg' => 'bg-gradient-to-br from-blue-400 to-blue-600',
            ],
            [
                'name' => 'logo-dark.svg',
                'type' => 'Logo',
                'dimensions' => '800 x 200',
                'size' => '12 KB',
                'icon' => 'heroicon-o-sparkles',
                'bg' => 'bg-gradient-to-br from-slate-700 to-slate-900',
            ],
            [
                'name' => 'favicon.ico',
                'type' => 'Favicon',
                'dimensions' => '32 x 32',
                'size' => '4 KB',
                'icon' => 'heroicon-o-document',
                'bg' => 'bg-gradient-to-br from-purple-400 to-purple-600',
            ],
            [
                'name' => 'apple-touch-icon.png',
                'type' => 'Favicon',
                'dimensions' => '180 x 180',
                'size' => '8 KB',
                'icon' => 'heroicon-o-device-phone-mobile',
                'bg' => 'bg-gradient-to-br from-green-400 to-green-600',
            ],
            [
                'name' => 'og-image.png',
                'type' => 'Social',
                'dimensions' => '1200 x 630',
                'size' => '145 KB',
                'icon' => 'heroicon-o-share',
                'bg' => 'bg-gradient-to-br from-orange-400 to-orange-600',
            ],
            [
                'name' => 'brand-icon.svg',
                'type' => 'Logo',
                'dimensions' => '512 x 512',
                'size' => '8 KB',
                'icon' => 'heroicon-o-cube',
                'bg' => 'bg-gradient-to-br from-pink-400 to-pink-600',
            ],
        ];
    }
}
