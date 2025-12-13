<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RedirectType;
use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Redirects';

    protected static ?string $navigationGroup = 'SEO';

    protected static ?string $recordTitleAttribute = 'source_path';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Redirect Details')
                    ->schema([
                        Forms\Components\TextInput::make('source_path')
                            ->label('Source URL')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('/old-page')
                            ->helperText('The URL path to redirect from (e.g., /old-page)'),

                        Forms\Components\TextInput::make('target_path')
                            ->label('Target URL')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('/new-page')
                            ->helperText('The URL path to redirect to (e.g., /new-page)'),

                        Forms\Components\Select::make('redirect_type')
                            ->label('Redirect Type')
                            ->options(RedirectType::class)
                            ->default(RedirectType::Permanent)
                            ->required()
                            ->helperText('301 for permanent, 302 for temporary redirects'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Only active redirects will be processed'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Optional notes about this redirect')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source_path')
                    ->label('Source URL')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Source URL copied'),

                Tables\Columns\TextColumn::make('target_path')
                    ->label('Target URL')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Target URL copied'),

                Tables\Columns\TextColumn::make('redirect_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('redirect_type')
                    ->label('Type')
                    ->options(RedirectType::class),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All redirects')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
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
            'index' => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit' => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
