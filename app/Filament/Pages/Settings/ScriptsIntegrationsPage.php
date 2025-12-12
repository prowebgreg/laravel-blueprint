<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ScriptsIntegrationsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static string $view = 'filament.pages.settings.scripts-integrations-page';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Scripts & Integrations';

    protected static ?string $navigationLabel = 'Scripts & Integrations';

    public string $activeTab = 'scripts';

    public function getHeading(): string|Htmlable
    {
        return 'Scripts & Integrations';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage custom scripts and third-party integrations';
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
}
