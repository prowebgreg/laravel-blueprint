<?php

declare(strict_types=1);

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\TestimonialResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListTestimonials extends ListRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Internal identifier for this testimonial'),

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
                ->successRedirectUrl(fn (Model $record): string => TestimonialResource::getUrl('edit', ['record' => $record])),
        ];
    }
}
