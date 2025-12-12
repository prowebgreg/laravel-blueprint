<?php

declare(strict_types=1);

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getHeading();
    }

    public function getHeading(): string|Htmlable
    {
        /** @var Page $page */
        $page = $this->getRecord();

        return $page->name ?? __('Edit Page');
    }

    public function getSubheading(): ?string
    {
        /** @var Page $page */
        $page = $this->getRecord();
        $status = $page->status;

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
