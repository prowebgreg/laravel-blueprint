<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\BlogPost;
use App\Models\MediaAsset;
use App\Models\Page;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pages', Page::count())
                ->description('Published and draft pages')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Total Posts', BlogPost::count())
                ->description('Blog posts in the system')
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('success'),

            Stat::make('Media Items', MediaAsset::count())
                ->description('Images, videos, and files')
                ->descriptionIcon('heroicon-m-photo')
                ->color('warning'),
        ];
    }
}
