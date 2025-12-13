<?php

declare(strict_types=1);

namespace App\Filament\Pages\Seo;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class StructuredDataPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static string $view = 'filament.pages.seo.structured-data-page';

    protected static ?string $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Structured Data';

    protected static ?string $navigationLabel = 'Structured Data';

    public ?array $data = [];

    public function mount(): void
    {
        // Placeholder data - no database persistence in scaffold phase
        $this->form->fill([
            'organization_name' => 'The Blueprint CMS',
            'organization_logo_url' => 'https://example.com/logo.png',
            'organization_email' => 'contact@blueprintcms.com',
            'organization_phone' => '+1 (555) 123-4567',
            'organization_description' => 'A high-performance, developer-first Content Management System built to replace WordPress for professional bespoke website development.',
            'website_name' => 'The Blueprint CMS',
            'website_alternate_name' => 'Blueprint',
            'website_url' => 'https://blueprintcms.com',
            'website_search_url_template' => 'https://blueprintcms.com/search?q={search_term_string}',
            'breadcrumb_enabled' => true,
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Structured Data';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Configure JSON-LD schema markup for search engines.';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Organization Schema')
                    ->description('Define your organization\'s structured data for search engines. This will be rendered as JSON-LD on all pages.')
                    ->schema([
                        TextInput::make('organization_name')
                            ->label('Organization Name')
                            ->placeholder('Your Company Name')
                            ->maxLength(255)
                            ->helperText('The legal name of your organization.'),
                        TextInput::make('organization_logo_url')
                            ->label('Logo URL')
                            ->url()
                            ->placeholder('https://example.com/logo.png')
                            ->maxLength(255)
                            ->helperText('A fully qualified URL to your organization\'s logo image.'),
                        TextInput::make('organization_email')
                            ->label('Contact Email')
                            ->email()
                            ->placeholder('contact@example.com')
                            ->maxLength(255),
                        TextInput::make('organization_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->placeholder('+1 (555) 123-4567')
                            ->maxLength(50),
                        Textarea::make('organization_description')
                            ->label('Description')
                            ->placeholder('A brief description of your organization')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->helperText('A short description that will appear in search results.'),
                    ])
                    ->columns(2),

                Section::make('Website Schema')
                    ->description('Configure your website\'s structured data including site search functionality.')
                    ->schema([
                        TextInput::make('website_name')
                            ->label('Site Name')
                            ->placeholder('Your Website Name')
                            ->maxLength(255)
                            ->helperText('The primary name of your website.'),
                        TextInput::make('website_alternate_name')
                            ->label('Alternate Name (Optional)')
                            ->placeholder('Short name or acronym')
                            ->maxLength(255)
                            ->helperText('A commonly used alternative name for your website.'),
                        TextInput::make('website_url')
                            ->label('Site URL')
                            ->url()
                            ->placeholder('https://example.com')
                            ->maxLength(255)
                            ->helperText('Your website\'s canonical URL.'),
                        TextInput::make('website_search_url_template')
                            ->label('Search URL Template')
                            ->url()
                            ->placeholder('https://example.com/search?q={search_term_string}')
                            ->maxLength(255)
                            ->helperText('Use {search_term_string} as the placeholder for search queries.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Breadcrumb Schema')
                    ->description('Configure automatic breadcrumb structured data generation.')
                    ->schema([
                        Toggle::make('breadcrumb_enabled')
                            ->label('Enable Breadcrumb Schema')
                            ->helperText('Automatically generate BreadcrumbList JSON-LD based on page hierarchy and URL structure.')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        // Placeholder - no backend functionality in scaffold phase
        // In a future implementation, this would save to database or config

        Notification::make()
            ->title('Saved (Placeholder)')
            ->body('This is a placeholder page. Changes are not persisted.')
            ->warning()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Changes')
                ->action('save')
                ->color('primary'),
        ];
    }
}
