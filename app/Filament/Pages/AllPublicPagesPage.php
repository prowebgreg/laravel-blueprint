<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\ContentStatus;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Service;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page as BasePage;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class AllPublicPagesPage extends BasePage implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.all-public-pages-page';

    protected static ?string $navigationGroup = 'Public Pages';

    protected static ?int $navigationSort = -1;

    protected static ?string $title = 'All Public Pages';

    protected static ?string $navigationLabel = 'All Public Pages';

    public function getHeading(): string|Htmlable
    {
        return 'All Public Pages';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Consolidated view of all static pages, services, and blog posts.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Page' => 'gray',
                        'Service' => 'info',
                        'BlogPost' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Slug copied to clipboard')
                    ->copyMessageDuration(1500),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ContentStatus $state): string => match ($state) {
                        ContentStatus::Published => 'success',
                        ContentStatus::Draft => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(function ($record): string {
                        $type = $record->type;
                        $id = $record->id;

                        // When resources are created, they will be available here
                        return match ($type) {
                            'Page' => class_exists(\App\Filament\Resources\PageResource::class)
                                ? \App\Filament\Resources\PageResource::getUrl('edit', ['record' => $id])
                                : '#',
                            'Service' => class_exists(\App\Filament\Resources\ServiceResource::class)
                                ? \App\Filament\Resources\ServiceResource::getUrl('edit', ['record' => $id])
                                : '#',
                            'BlogPost' => class_exists(\App\Filament\Resources\BlogPostResource::class)
                                ? \App\Filament\Resources\BlogPostResource::getUrl('edit', ['record' => $id])
                                : '#',
                            default => '#',
                        };
                    })
                    ->disabled(fn ($record): bool => ! $this->resourceExists($record->type)),
            ])
            ->bulkActions([
                // Bulk actions intentionally empty for scaffold phase
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('No public pages found')
            ->emptyStateDescription('Create your first page, service, or blog post to get started.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    /**
     * Build a unified query combining all three content types using UNION.
     */
    protected function getTableQuery(): Builder
    {
        // Create union query that combines all three content types
        // PostgreSQL will handle the union correctly
        $pages = Page::query()
            ->select([
                'id',
                'name',
                'slug',
                'status',
                'updated_at',
            ])
            ->selectRaw("'Page' as type");

        $services = Service::query()
            ->select([
                'id',
                'name',
                'slug',
                'status',
                'updated_at',
            ])
            ->selectRaw("'Service' as type");

        $blogPosts = BlogPost::query()
            ->select([
                'id',
                'name',
                'slug',
                'status',
                'updated_at',
            ])
            ->selectRaw("'BlogPost' as type");

        // Return the unioned query
        return $pages->union($services)->union($blogPosts);
    }

    /**
     * Check if a resource class exists for the given type.
     */
    protected function resourceExists(string $type): bool
    {
        return match ($type) {
            'Page' => class_exists(\App\Filament\Resources\PageResource::class),
            'Service' => class_exists(\App\Filament\Resources\ServiceResource::class),
            'BlogPost' => class_exists(\App\Filament\Resources\BlogPostResource::class),
            default => false,
        };
    }
}
