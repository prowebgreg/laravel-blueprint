<?php

declare(strict_types=1);

namespace App\Filament\Pages\Seo;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class NotFoundPagesPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string $view = 'filament.pages.seo.not-found-pages-page';

    protected static ?string $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = '404 Pages';

    protected static ?string $navigationLabel = '404 Pages';

    public function getHeading(): string|Htmlable
    {
        return '404 Pages';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Monitor and configure 404 error handling';
    }
}
