<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class WebsiteDetailsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static string $view = 'filament.pages.settings.website-details-page';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Website Details';

    protected static ?string $navigationLabel = 'Website Details';

    public ?array $data = [];

    public function mount(): void
    {
        // Placeholder data - no database persistence in scaffold phase
        $this->form->fill([
            'site_name' => 'The Blueprint CMS',
            'tagline' => 'A high-performance, developer-first CMS',
            'phone' => '+1 (555) 123-4567',
            'email' => 'hello@blueprintcms.com',
            'address' => "123 Main Street\nSuite 456\nCity, State 12345",
            'facebook_url' => 'https://facebook.com/blueprintcms',
            'twitter_url' => 'https://twitter.com/blueprintcms',
            'instagram_url' => 'https://instagram.com/blueprintcms',
            'linkedin_url' => 'https://linkedin.com/company/blueprintcms',
            'youtube_url' => 'https://youtube.com/@blueprintcms',
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Website Details';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage your website identity, contact information, and social media links.';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identity')
                    ->description('Basic information about your website.')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site Name')
                            ->placeholder('Enter your site name')
                            ->maxLength(255),
                        TextInput::make('tagline')
                            ->label('Tagline')
                            ->placeholder('A brief description of your site')
                            ->maxLength(255),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->maxSize(2048)
                            ->hint('Upload your site logo (max 2MB)')
                            ->helperText('Recommended size: 200x200px')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->description('How visitors can reach you.')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->placeholder('+1 (555) 123-4567')
                            ->maxLength(50),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->placeholder('hello@example.com')
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Address')
                            ->placeholder("123 Main Street\nSuite 456\nCity, State 12345")
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Social Media Links')
                    ->description('Connect your social media profiles.')
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook')
                            ->url()
                            ->placeholder('https://facebook.com/yourpage')
                            ->maxLength(255),
                        TextInput::make('twitter_url')
                            ->label('Twitter / X')
                            ->url()
                            ->placeholder('https://twitter.com/yourhandle')
                            ->maxLength(255),
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->placeholder('https://instagram.com/yourprofile')
                            ->maxLength(255),
                        TextInput::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url()
                            ->placeholder('https://linkedin.com/company/yourcompany')
                            ->maxLength(255),
                        TextInput::make('youtube_url')
                            ->label('YouTube')
                            ->url()
                            ->placeholder('https://youtube.com/@yourchannel')
                            ->maxLength(255),
                    ])
                    ->columns(2),
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
