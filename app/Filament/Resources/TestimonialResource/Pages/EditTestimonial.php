<?php

declare(strict_types=1);

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Filament\Resources\TestimonialResource;
use App\Models\Testimonial;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditTestimonial extends EditRecord
{
    protected static string $resource = TestimonialResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getHeading();
    }

    public function getHeading(): string|Htmlable
    {
        /** @var Testimonial $testimonial */
        $testimonial = $this->getRecord();

        return $testimonial->author_name ?? $testimonial->name ?? __('Edit Testimonial');
    }

    public function getSubheading(): ?string
    {
        /** @var Testimonial $testimonial */
        $testimonial = $this->getRecord();
        $status = $testimonial->status;

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
