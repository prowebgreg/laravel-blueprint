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

    /**
     * Get placeholder image data for scaffold display.
     *
     * @return array<int, array{name: string, dimensions: string, size: string, icon: string, bg: string}>
     */
    public function getPlaceholderImages(): array
    {
        return [
            [
                'name' => 'hero-homepage.webp',
                'dimensions' => '1920 x 1080',
                'size' => '245 KB',
                'icon' => 'heroicon-o-photo',
                'bg' => 'bg-gradient-to-br from-blue-400 to-blue-600',
            ],
            [
                'name' => 'about-team-photo.jpg',
                'dimensions' => '1440 x 960',
                'size' => '512 KB',
                'icon' => 'heroicon-o-user-group',
                'bg' => 'bg-gradient-to-br from-purple-400 to-purple-600',
            ],
            [
                'name' => 'product-showcase.webp',
                'dimensions' => '1200 x 800',
                'size' => '189 KB',
                'icon' => 'heroicon-o-cube',
                'bg' => 'bg-gradient-to-br from-green-400 to-green-600',
            ],
            [
                'name' => 'office-workspace.jpg',
                'dimensions' => '1920 x 1280',
                'size' => '678 KB',
                'icon' => 'heroicon-o-building-office',
                'bg' => 'bg-gradient-to-br from-orange-400 to-orange-600',
            ],
            [
                'name' => 'testimonial-avatar-1.webp',
                'dimensions' => '480 x 480',
                'size' => '45 KB',
                'icon' => 'heroicon-o-user-circle',
                'bg' => 'bg-gradient-to-br from-pink-400 to-pink-600',
            ],
            [
                'name' => 'blog-post-header.jpg',
                'dimensions' => '1600 x 900',
                'size' => '423 KB',
                'icon' => 'heroicon-o-newspaper',
                'bg' => 'bg-gradient-to-br from-cyan-400 to-cyan-600',
            ],
            [
                'name' => 'service-illustration.webp',
                'dimensions' => '1168 x 820',
                'size' => '156 KB',
                'icon' => 'heroicon-o-sparkles',
                'bg' => 'bg-gradient-to-br from-indigo-400 to-indigo-600',
            ],
            [
                'name' => 'contact-map-background.jpg',
                'dimensions' => '1920 x 600',
                'size' => '334 KB',
                'icon' => 'heroicon-o-map',
                'bg' => 'bg-gradient-to-br from-teal-400 to-teal-600',
            ],
            [
                'name' => 'portfolio-item-1.webp',
                'dimensions' => '1200 x 900',
                'size' => '267 KB',
                'icon' => 'heroicon-o-rectangle-stack',
                'bg' => 'bg-gradient-to-br from-red-400 to-red-600',
            ],
            [
                'name' => 'cta-banner-background.jpg',
                'dimensions' => '1920 x 480',
                'size' => '198 KB',
                'icon' => 'heroicon-o-megaphone',
                'bg' => 'bg-gradient-to-br from-yellow-400 to-yellow-600',
            ],
            [
                'name' => 'gallery-thumbnail-01.webp',
                'dimensions' => '640 x 640',
                'size' => '78 KB',
                'icon' => 'heroicon-o-squares-2x2',
                'bg' => 'bg-gradient-to-br from-lime-400 to-lime-600',
            ],
            [
                'name' => 'feature-icon-set.webp',
                'dimensions' => '960 x 720',
                'size' => '123 KB',
                'icon' => 'heroicon-o-gift',
                'bg' => 'bg-gradient-to-br from-violet-400 to-violet-600',
            ],
        ];
    }
}
