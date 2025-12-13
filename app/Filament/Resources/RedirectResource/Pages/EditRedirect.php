<?php

declare(strict_types=1);

namespace App\Filament\Resources\RedirectResource\Pages;

use App\Filament\Resources\RedirectResource;
use App\Models\Redirect;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditRedirect extends EditRecord
{
    protected static string $resource = RedirectResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getHeading();
    }

    public function getHeading(): string|Htmlable
    {
        /** @var Redirect $redirect */
        $redirect = $this->getRecord();

        return $redirect->source_path ?? __('Edit Redirect');
    }

    public function getSubheading(): ?string
    {
        /** @var Redirect $redirect */
        $redirect = $this->getRecord();
        $redirectType = $redirect->redirect_type;

        return $redirectType !== null ? __('Type: :type', ['type' => $redirectType->getLabel()]) : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to List')
                ->url(static::getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
            Actions\DeleteAction::make(),
        ];
    }
}
