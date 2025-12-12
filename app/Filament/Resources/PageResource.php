<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Static Pages';

    protected static ?string $navigationGroup = 'Public Pages';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Page Content')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Page Name')
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
