<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $navigationGroup = 'Public Pages';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Split::make([
                    // Main content area with tabs
                    Forms\Components\Tabs::make('Tabs')
                        ->tabs([
                            Forms\Components\Tabs\Tab::make('Page Content')
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Service Name')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\Placeholder::make('content_blocks_placeholder')
                                        ->label('Content Blocks')
                                        ->content('Content blocks editor will be implemented later'),

                                    Forms\Components\TextInput::make('template')
                                        ->label('Template')
                                        ->maxLength(255),
                                ]),

                            Forms\Components\Tabs\Tab::make('SEO Data')
                                ->schema([
                                    Forms\Components\Section::make('Meta Tags')
                                        ->schema([
                                            Forms\Components\TextInput::make('meta_title')
                                                ->label('Meta Title')
                                                ->maxLength(255),

                                            Forms\Components\Textarea::make('meta_description')
                                                ->label('Meta Description')
                                                ->maxLength(1024)
                                                ->rows(3),

                                            Forms\Components\TextInput::make('meta_author')
                                                ->label('Meta Author')
                                                ->maxLength(255),

                                            Forms\Components\Toggle::make('meta_robots')
                                                ->label('Indexable (Allow Search Engines)')
                                                ->default(true)
                                                ->inline(false),

                                            Forms\Components\TextInput::make('canonical_url')
                                                ->label('Canonical URL')
                                                ->url()
                                                ->maxLength(255),
                                        ])
                                        ->columns(2),

                                    Forms\Components\Section::make('Open Graph')
                                        ->schema([
                                            Forms\Components\TextInput::make('og_title')
                                                ->label('OG Title')
                                                ->maxLength(255),

                                            Forms\Components\Textarea::make('og_description')
                                                ->label('OG Description')
                                                ->maxLength(1024)
                                                ->rows(3),

                                            Forms\Components\Select::make('og_type')
                                                ->label('OG Type')
                                                ->options(OgType::class),

                                            Forms\Components\TextInput::make('og_image')
                                                ->label('OG Image URL')
                                                ->url()
                                                ->maxLength(255),
                                        ])
                                        ->columns(2),

                                    Forms\Components\Section::make('Twitter Card')
                                        ->schema([
                                            Forms\Components\TextInput::make('twitter_title')
                                                ->label('Twitter Title')
                                                ->maxLength(255),

                                            Forms\Components\Textarea::make('twitter_description')
                                                ->label('Twitter Description')
                                                ->maxLength(1024)
                                                ->rows(3),

                                            Forms\Components\TextInput::make('twitter_image')
                                                ->label('Twitter Image URL')
                                                ->url()
                                                ->maxLength(255),
                                        ])
                                        ->columns(2),
                                ]),
                        ])
                        ->columnSpanFull(),

                    // Right sidebar with status, slug, timestamps, and record ID
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options(ContentStatus::class)
                                ->default(ContentStatus::Draft)
                                ->required(),

                            Forms\Components\TextInput::make('slug')
                                ->label('URL Slug')
                                ->required()
                                ->maxLength(255)
                                ->disabled(fn (?Service $record): bool => $record !== null)
                                ->dehydrated(fn (?Service $record): bool => $record === null)
                                ->helperText(fn (?Service $record): ?string => $record !== null ? 'Slug cannot be changed after creation' : null),

                            Forms\Components\Placeholder::make('created_at')
                                ->label('Created')
                                ->content(fn (?Service $record): string => $record?->created_at?->diffForHumans() ?? '-')
                                ->visible(fn (?Service $record): bool => $record !== null),

                            Forms\Components\Placeholder::make('updated_at')
                                ->label('Last Modified')
                                ->content(fn (?Service $record): string => $record?->updated_at?->diffForHumans() ?? '-')
                                ->visible(fn (?Service $record): bool => $record !== null),

                            Forms\Components\Placeholder::make('id')
                                ->label('Record ID')
                                ->content(fn (?Service $record): string => (string) ($record?->id ?? '-'))
                                ->visible(fn (?Service $record): bool => $record !== null),
                        ])
                        ->grow(false),
                ])->from('md'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ContentStatus::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
