<?php

declare(strict_types=1);

namespace App\Filament\Pages\Media;

use App\Enums\MediaType;
use App\Models\MediaAsset;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page as BasePage;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class VideosPage extends BasePage implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static string $view = 'filament.pages.media.videos-page';

    protected static ?string $navigationGroup = 'Media Library';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Videos';

    protected static ?string $navigationLabel = 'Videos';

    public function getHeading(): string|Htmlable
    {
        return 'Videos';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage video media assets.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MediaAsset::query()
                    ->where('media_type', MediaType::Video)
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->default(fn (MediaAsset $record): string => $record->original_name ?? 'Untitled')
                    ->description(fn (MediaAsset $record): ?string => $record->caption),
                TextColumn::make('duration')
                    ->label('Duration')
                    ->placeholder('--:--')
                    ->description('Placeholder: Duration extraction coming soon'),
                TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn (int $state): string => $this->formatBytes($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (MediaAsset $record): string => $record->cloudfront_url_original)
                    ->openUrlInNewTab(),
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url('#')
                    ->disabled(),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->disabled(),
            ])
            ->bulkActions([
                // Bulk actions intentionally empty for scaffold phase
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No videos found')
            ->emptyStateDescription('Upload your first video to get started.')
            ->emptyStateIcon('heroicon-o-video-camera');
    }

    /**
     * Format file size in bytes to human-readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor(log($bytes, 1024));

        return round($bytes / (1024 ** $factor), 2).' '.$units[$factor];
    }
}
