<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getHeading(): string
    {
        $user = auth()->user();
        $name = $user ? $user->name : 'Guest';

        return "Welcome back, {$name}!";
    }

    public function getSubheading(): ?string
    {
        return 'Here\'s an overview of your content management system.';
    }
}
