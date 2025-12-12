<?php

declare(strict_types=1);

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use App\Models\Faq;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditFaq extends EditRecord
{
    protected static string $resource = FaqResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getHeading();
    }

    public function getHeading(): string|Htmlable
    {
        /** @var Faq $faq */
        $faq = $this->getRecord();

        return $faq->question ?? __('Edit FAQ');
    }

    public function getSubheading(): ?string
    {
        /** @var Faq $faq */
        $faq = $this->getRecord();
        $status = $faq->status;

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
