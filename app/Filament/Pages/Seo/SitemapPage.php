<?php

declare(strict_types=1);

namespace App\Filament\Pages\Seo;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class SitemapPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static string $view = 'filament.pages.seo.sitemap-page';

    protected static ?string $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Sitemap';

    protected static ?string $navigationLabel = 'Sitemap';

    public ?array $data = [];

    public string $generationStatus = 'ready';

    public ?string $lastGenerated = null;

    public function mount(): void
    {
        $this->form->fill([
            'include_pages' => true,
            'include_services' => true,
            'include_blog_posts' => true,
        ]);

        $this->generationStatus = 'ready';
        $this->lastGenerated = null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Sitemap Status')
                    ->description('Current sitemap generation status')
                    ->schema([
                        Placeholder::make('status')
                            ->label('Status')
                            ->content(fn (): Htmlable => $this->getStatusBadge()),
                        Placeholder::make('last_generated')
                            ->label('Last Generated')
                            ->content(fn (): string => $this->lastGenerated ?? 'Never'),
                        Placeholder::make('total_urls')
                            ->label('Total URLs')
                            ->content('0 pages'),
                        Placeholder::make('sitemap_url')
                            ->label('Sitemap URL')
                            ->content('/sitemap.xml'),
                    ])
                    ->columns(4),

                Section::make('Content Types')
                    ->description('Select which content types to include in the sitemap')
                    ->schema([
                        Checkbox::make('include_pages')
                            ->label('Static Pages')
                            ->helperText('Include all published static pages')
                            ->default(true),
                        Checkbox::make('include_services')
                            ->label('Services')
                            ->helperText('Include all published service pages')
                            ->default(true),
                        Checkbox::make('include_blog_posts')
                            ->label('Blog Posts')
                            ->helperText('Include all published blog posts')
                            ->default(true),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function getHeading(): string|Htmlable
    {
        return 'Sitemap';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Generate and manage XML sitemap for search engines.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate Sitemap')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->disabled($this->generationStatus === 'generating')
                ->action(function (): void {
                    // Update status to generating
                    $this->generationStatus = 'generating';

                    // Simulate generation delay (placeholder)
                    sleep(1);

                    // Update status to generated
                    $this->generationStatus = 'generated';
                    $this->lastGenerated = now()->format('M d, Y g:i A');

                    // Show success notification
                    Notification::make()
                        ->title('Sitemap Generated')
                        ->body('This is a placeholder. Sitemap generation will be implemented in a future phase.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Generate Sitemap')
                ->modalDescription('This will regenerate the sitemap.xml file with all selected content types.')
                ->modalSubmitActionLabel('Generate'),
        ];
    }

    protected function getStatusBadge(): Htmlable
    {
        return match ($this->generationStatus) {
            'ready' => new HtmlString(
                '<x-filament::badge color="gray">Ready to Generate</x-filament::badge>'
            ),
            'generating' => new HtmlString(
                '<x-filament::badge color="warning">Generating...</x-filament::badge>'
            ),
            'generated' => new HtmlString(
                '<x-filament::badge color="success">Generated</x-filament::badge>'
            ),
            default => new HtmlString(
                '<x-filament::badge color="gray">Unknown</x-filament::badge>'
            ),
        };
    }
}
