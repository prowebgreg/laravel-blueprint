<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqResource\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\FaqResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Internal identifier for this FAQ'),

                    Forms\Components\TextInput::make('question')
                        ->label('Question')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('answer')
                        ->label('Answer')
                        ->required()
                        ->rows(6),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(ContentStatus::class)
                        ->default(ContentStatus::Draft)
                        ->required(),
                ])
                ->successRedirectUrl(fn (Model $record): string => FaqResource::getUrl('edit', ['record' => $record])),
        ];
    }
}
