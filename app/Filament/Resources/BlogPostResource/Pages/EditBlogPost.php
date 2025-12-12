<?php

declare(strict_types=1);

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPost;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getHeading();
    }

    public function getHeading(): string|Htmlable
    {
        /** @var BlogPost $blogPost */
        $blogPost = $this->getRecord();

        return $blogPost->name ?? __('Edit Blog Post');
    }

    public function getSubheading(): ?string
    {
        /** @var BlogPost $blogPost */
        $blogPost = $this->getRecord();
        $status = $blogPost->status;

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
