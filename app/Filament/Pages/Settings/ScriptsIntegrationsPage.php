<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ScriptsIntegrationsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static string $view = 'filament.pages.settings.scripts-integrations-page';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Scripts & Integrations';

    protected static ?string $navigationLabel = 'Scripts & Integrations';

    public ?array $data = [];

    public function mount(): void
    {
        // Placeholder data - no database persistence in scaffold phase
        $this->form->fill([
            'head_scripts' => '<!-- Google Analytics placeholder -->'."\n".'<script>console.log("GA tracking");</script>',
            'body_scripts' => '<!-- Body scripts placeholder -->',
            'footer_scripts' => '<!-- Footer scripts placeholder -->'."\n".'<script>console.log("Footer loaded");</script>',
            'google_analytics_id' => 'GA-XXXXXXXXX',
            'google_maps_key' => 'AIzaSy...',
            'recaptcha_site_key' => '6Le...',
            'recaptcha_secret_key' => '6Le...',
            'webhook_url' => 'https://example.com/webhook',
            'webhook_secret' => 'whsec_...',
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Scripts & Integrations';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage custom scripts, third-party API integrations, and webhook configurations.';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Scripts & Integrations')
                    ->tabs([
                        Tabs\Tab::make('Scripts')
                            ->icon('heroicon-o-code-bracket-square')
                            ->schema([
                                Section::make('Custom Scripts')
                                    ->description('Add custom HTML, CSS, or JavaScript to different sections of your pages.')
                                    ->schema([
                                        Textarea::make('head_scripts')
                                            ->label('Head Scripts')
                                            ->placeholder('<!-- Scripts to be inserted in <head> -->')
                                            ->helperText('Scripts added here will be inserted in the <head> section of all pages.')
                                            ->rows(6)
                                            ->columnSpanFull(),
                                        Textarea::make('body_scripts')
                                            ->label('Body Scripts')
                                            ->placeholder('<!-- Scripts to be inserted after opening <body> tag -->')
                                            ->helperText('Scripts added here will be inserted right after the opening <body> tag.')
                                            ->rows(6)
                                            ->columnSpanFull(),
                                        Textarea::make('footer_scripts')
                                            ->label('Footer Scripts')
                                            ->placeholder('<!-- Scripts to be inserted before closing </body> tag -->')
                                            ->helperText('Scripts added here will be inserted before the closing </body> tag.')
                                            ->rows(6)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('APIs')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Section::make('Third-Party API Keys')
                                    ->description('Configure API keys for third-party services.')
                                    ->schema([
                                        TextInput::make('google_analytics_id')
                                            ->label('Google Analytics ID')
                                            ->placeholder('GA-XXXXXXXXX or G-XXXXXXXXX')
                                            ->helperText('Your Google Analytics tracking ID.')
                                            ->maxLength(255),
                                        TextInput::make('google_maps_key')
                                            ->label('Google Maps API Key')
                                            ->placeholder('AIzaSy...')
                                            ->password()
                                            ->revealable()
                                            ->helperText('Your Google Maps JavaScript API key.')
                                            ->maxLength(255),
                                        TextInput::make('recaptcha_site_key')
                                            ->label('reCAPTCHA Site Key')
                                            ->placeholder('6Le...')
                                            ->helperText('Your Google reCAPTCHA v3 site key (public).')
                                            ->maxLength(255),
                                        TextInput::make('recaptcha_secret_key')
                                            ->label('reCAPTCHA Secret Key')
                                            ->placeholder('6Le...')
                                            ->password()
                                            ->revealable()
                                            ->helperText('Your Google reCAPTCHA v3 secret key (private).')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Webhooks')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make('Webhook Configuration')
                                    ->description('Configure webhook endpoints for external integrations.')
                                    ->schema([
                                        TextInput::make('webhook_url')
                                            ->label('Webhook URL')
                                            ->url()
                                            ->placeholder('https://example.com/webhook')
                                            ->helperText('The URL where webhook events will be sent.')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        TextInput::make('webhook_secret')
                                            ->label('Webhook Secret')
                                            ->placeholder('whsec_...')
                                            ->password()
                                            ->revealable()
                                            ->helperText('Secret key used to verify webhook signatures.')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
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
