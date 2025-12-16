<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Enums\ContentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Reusable table builder for Public Pages (Page, Service, BlogPost).
 *
 * Provides consistent table configuration with:
 * - Search across title, slug, meta fields
 * - Status and date range filters
 * - Sortable columns with pagination
 * - Row selection with bulk actions (publish, delete)
 * - Per-row action dropdown (edit, view, publish, delete)
 */
final class PublicPageTableBuilder
{
    /**
     * @param  string  $titleLabel  Label for the title column (e.g., "Page Name", "Service Name")
     * @param  string  $titleField  Model field for the title (default: 'name')
     * @param  string  $viewPathPrefix  URL prefix for viewing published pages (e.g., '/', '/services/', '/blog/')
     */
    public function __construct(
        private readonly string $titleLabel = 'Title',
        private readonly string $titleField = 'name',
        private readonly string $viewPathPrefix = '/',
    ) {}

    /**
     * Create a new builder instance.
     */
    public static function make(
        string $titleLabel = 'Title',
        string $titleField = 'name',
        string $viewPathPrefix = '/',
    ): self {
        return new self($titleLabel, $titleField, $viewPathPrefix);
    }

    /**
     * Configure the table with all standard public page features.
     */
    public function configure(Table $table): Table
    {
        return $table
            ->columns($this->getColumns())
            ->defaultSort('updated_at', 'desc')
            ->filters($this->getFilters(), layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->actions($this->getActions())
            ->bulkActions($this->getBulkActions())
            ->selectCurrentPageOnly()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50, 100])
            ->recordUrl(null);
    }

    /**
     * Get the table columns configuration.
     *
     * @return array<TextColumn>
     */
    private function getColumns(): array
    {
        return [
            TextColumn::make($this->titleField)
                ->label($this->titleLabel)
                ->searchable()
                ->sortable()
                ->limit(50)
                ->tooltip(fn (Model $record): ?string => strlen($record->{$this->titleField}) > 50 ? $record->{$this->titleField} : null),

            TextColumn::make('slug')
                ->label('Slug')
                ->searchable()
                ->sortable()
                ->color('gray')
                ->limit(30)
                ->tooltip(fn (Model $record): ?string => strlen($record->slug) > 30 ? $record->slug : null),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable(),

            TextColumn::make('updated_at')
                ->label('Updated')
                ->dateTime('M j, Y')
                ->sortable()
                ->toggleable(),

            TextColumn::make('meta_title')
                ->label('Meta Title')
                ->searchable()
                ->limit(30)
                ->tooltip(fn (Model $record): ?string => $record->meta_title && strlen($record->meta_title) > 30 ? $record->meta_title : null)
                ->placeholder('—')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('meta_description')
                ->label('Meta Description')
                ->searchable()
                ->limit(40)
                ->tooltip(fn (Model $record): ?string => $record->meta_description && strlen($record->meta_description) > 40 ? $record->meta_description : null)
                ->placeholder('—')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Get the table filters configuration.
     *
     * @return array<Filter|SelectFilter>
     */
    private function getFilters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status')
                ->options(ContentStatus::class)
                ->placeholder('All Statuses'),

            Filter::make('updated_at')
                ->label('Date Range')
                ->form([
                    DatePicker::make('updated_from')
                        ->label('From')
                        ->placeholder('Start date'),
                    DatePicker::make('updated_until')
                        ->label('Until')
                        ->placeholder('End date'),
                ])
                ->columns(2)
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['updated_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date),
                        )
                        ->when(
                            $data['updated_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if ($data['updated_from'] ?? null) {
                        $indicators['updated_from'] = 'From '.\Carbon\Carbon::parse($data['updated_from'])->format('M j, Y');
                    }

                    if ($data['updated_until'] ?? null) {
                        $indicators['updated_until'] = 'Until '.\Carbon\Carbon::parse($data['updated_until'])->format('M j, Y');
                    }

                    return $indicators;
                }),
        ];
    }

    /**
     * Get the row actions configuration.
     *
     * @return array<ActionGroup>
     */
    private function getActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()
                    ->label('Edit'),

                Action::make('view')
                    ->label('View Page')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Model $record): string => $this->viewPathPrefix.$record->slug)
                    ->openUrlInNewTab()
                    ->visible(fn (Model $record): bool => $record->status === ContentStatus::Published),

                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publish this item?')
                    ->modalDescription('This will make the content publicly visible.')
                    ->modalSubmitActionLabel('Yes, publish')
                    ->action(fn (Model $record) => $record->update(['status' => ContentStatus::Published]))
                    ->visible(fn (Model $record): bool => $record->status === ContentStatus::Draft),

                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Actions'),
        ];
    }

    /**
     * Get the bulk actions configuration.
     *
     * @return array<BulkActionGroup>
     */
    private function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('publish')
                    ->label('Publish Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publish selected items?')
                    ->modalDescription('This will make all selected content publicly visible.')
                    ->modalSubmitActionLabel('Yes, publish all')
                    ->action(fn (Collection $records) => $records->each->update(['status' => ContentStatus::Published]))
                    ->deselectRecordsAfterCompletion(),

                DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ]),
        ];
    }
}
