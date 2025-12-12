<?php

declare(strict_types=1);

namespace App\Filament\Pages\Seo;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class StructuredDataPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static string $view = 'filament.pages.seo.structured-data-page';

    protected static ?string $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Structured Data';

    protected static ?string $navigationLabel = 'Structured Data';

    public function getHeading(): string|Htmlable
    {
        return 'Structured Data';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Configure JSON-LD schema markup for search engines';
    }
}
