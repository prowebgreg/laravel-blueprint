<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ContentStatus;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Testimonials';

    protected static ?string $navigationGroup = 'Content Resources';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Testimonial Details')
                    ->schema([
                        Forms\Components\TextInput::make('author_name')
                            ->label('Author Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('quote')
                            ->label('Content')
                            ->required()
                            ->rows(6),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(ContentStatus::class)
                            ->default(ContentStatus::Draft)
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('author_name')
                    ->label('Name/Author')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->getStateUsing(fn (Testimonial $record): string => $record->author_name ?? $record->name),

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
            'index' => Pages\ListTestimonials::route('/'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
