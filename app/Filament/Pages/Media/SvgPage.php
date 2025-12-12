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

    /**
     * Get placeholder SVG data for scaffold display.
     *
     * @return array<int, array{name: string, size: string, category: string, icon: string, bg: string}>
     */
    public function getPlaceholderSvgs(): array
    {
        return [
            [
                'name' => 'logo-main.svg',
                'size' => '4.2 KB',
                'category' => 'Brand',
                'icon' => 'heroicon-o-building-storefront',
                'bg' => 'bg-gradient-to-br from-blue-500 to-blue-700',
            ],
            [
                'name' => 'logo-dark.svg',
                'size' => '4.1 KB',
                'category' => 'Brand',
                'icon' => 'heroicon-o-building-storefront',
                'bg' => 'bg-gradient-to-br from-gray-700 to-gray-900',
            ],
            [
                'name' => 'icon-arrow-right.svg',
                'size' => '0.8 KB',
                'category' => 'Icon',
                'icon' => 'heroicon-o-arrow-right',
                'bg' => 'bg-gradient-to-br from-green-400 to-green-600',
            ],
            [
                'name' => 'icon-check-circle.svg',
                'size' => '1.2 KB',
                'category' => 'Icon',
                'icon' => 'heroicon-o-check-circle',
                'bg' => 'bg-gradient-to-br from-emerald-400 to-emerald-600',
            ],
            [
                'name' => 'icon-close.svg',
                'size' => '0.6 KB',
                'category' => 'Icon',
                'icon' => 'heroicon-o-x-mark',
                'bg' => 'bg-gradient-to-br from-red-400 to-red-600',
            ],
            [
                'name' => 'icon-menu.svg',
                'size' => '0.9 KB',
                'category' => 'Icon',
                'icon' => 'heroicon-o-bars-3',
                'bg' => 'bg-gradient-to-br from-slate-400 to-slate-600',
            ],
            [
                'name' => 'pattern-dots.svg',
                'size' => '5.6 KB',
                'category' => 'Pattern',
                'icon' => 'heroicon-o-squares-2x2',
                'bg' => 'bg-gradient-to-br from-purple-400 to-purple-600',
            ],
            [
                'name' => 'pattern-waves.svg',
                'size' => '8.3 KB',
                'category' => 'Pattern',
                'icon' => 'heroicon-o-paint-brush',
                'bg' => 'bg-gradient-to-br from-cyan-400 to-cyan-600',
            ],
            [
                'name' => 'illustration-empty.svg',
                'size' => '12.4 KB',
                'category' => 'Illustration',
                'icon' => 'heroicon-o-document',
                'bg' => 'bg-gradient-to-br from-amber-400 to-amber-600',
            ],
            [
                'name' => 'illustration-success.svg',
                'size' => '15.2 KB',
                'category' => 'Illustration',
                'icon' => 'heroicon-o-sparkles',
                'bg' => 'bg-gradient-to-br from-lime-400 to-lime-600',
            ],
            [
                'name' => 'social-twitter.svg',
                'size' => '1.1 KB',
                'category' => 'Social',
                'icon' => 'heroicon-o-at-symbol',
                'bg' => 'bg-gradient-to-br from-sky-400 to-sky-600',
            ],
            [
                'name' => 'social-linkedin.svg',
                'size' => '1.3 KB',
                'category' => 'Social',
                'icon' => 'heroicon-o-user-group',
                'bg' => 'bg-gradient-to-br from-indigo-400 to-indigo-600',
            ],
        ];
    }
}
