<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getHeading();
    }

    public function getHeading(): string|Htmlable
    {
        /** @var Service $service */
        $service = $this->getRecord();

        return $service->name ?? __('Edit Service');
    }

    public function getSubheading(): ?string
    {
        /** @var Service $service */
        $service = $this->getRecord();
        $status = $service->status;

        return $status !== null ? __('Status: :status', ['status' => $status->getLabel()]) : null;
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
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
