<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Purge Deleted Content Command
 *
 * Permanently deletes soft-deleted content that has exceeded the configured
 * recovery period. Cleans up related content_relations table entries.
 */
class PurgeDeletedContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:purge-deleted
                            {--dry-run : Preview what would be purged without actually deleting}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete soft-deleted content older than the configured recovery period';

    /**
     * Content models that use soft deletes.
     *
     * @var array<class-string<Model>>
     */
    protected array $contentModels = [
        Page::class,
        Service::class,
        BlogPost::class,
        Faq::class,
        Testimonial::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recoveryDays = config('content.recovery_days', 30);
        $cutoffDate = now()->subDays($recoveryDays);

        $this->info("Purging content soft-deleted before: {$cutoffDate->toDateTimeString()}");
        $this->info("Recovery period: {$recoveryDays} days");
        $this->newLine();

        // Collect all soft-deleted content to purge
        $toPurge = $this->collectContentToPurge($cutoffDate);

        if ($toPurge->isEmpty()) {
            $this->info('No content found to purge.');

            return self::SUCCESS;
        }

        // Display summary
        $this->displaySummary($toPurge);

        // Dry run mode - preview only
        if ($this->option('dry-run')) {
            $this->newLine();
            $this->warn('DRY RUN MODE: No content was deleted.');

            return self::SUCCESS;
        }

        // Confirm before purging (unless --force)
        if (! $this->option('force') && ! $this->confirm('Permanently delete this content? This action cannot be undone.')) {
            $this->info('Purge cancelled.');

            return self::SUCCESS;
        }

        // Execute purge
        $purged = $this->executePurge($toPurge);

        $this->newLine();
        $this->info("Purge complete. Deleted {$purged['total']} content items and {$purged['relationships']} relationships.");

        return self::SUCCESS;
    }

    /**
     * Collect all soft-deleted content older than cutoff date.
     *
     * @param  \Illuminate\Support\Carbon  $cutoffDate
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Model>>
     */
    protected function collectContentToPurge($cutoffDate): \Illuminate\Support\Collection
    {
        $collection = collect();

        foreach ($this->contentModels as $modelClass) {
            $deleted = $modelClass::onlyTrashed()
                ->where('deleted_at', '<=', $cutoffDate)
                ->get();

            if ($deleted->isNotEmpty()) {
                $collection->put($modelClass, $deleted);
            }
        }

        return $collection;
    }

    /**
     * Display summary of content to be purged.
     *
     * @param  \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Model>>  $toPurge
     */
    protected function displaySummary(\Illuminate\Support\Collection $toPurge): void
    {
        $total = 0;

        $this->table(
            ['Model', 'Count', 'IDs'],
            $toPurge->map(function ($items, $modelClass) use (&$total) {
                $count = $items->count();
                $total += $count;

                $ids = $items->pluck('id')->take(10)->join(', ');
                if ($items->count() > 10) {
                    $ids .= '...';
                }

                return [
                    class_basename($modelClass),
                    $count,
                    $ids,
                ];
            })->values()->toArray()
        );

        $this->info("Total items to purge: {$total}");
    }

    /**
     * Execute the purge operation.
     *
     * @param  \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Model>>  $toPurge
     * @return array{total: int, relationships: int}
     */
    protected function executePurge(\Illuminate\Support\Collection $toPurge): array
    {
        $totalPurged = 0;
        $relationshipsPurged = 0;

        DB::transaction(function () use ($toPurge, &$totalPurged, &$relationshipsPurged) {
            foreach ($toPurge as $modelClass => $items) {
                $modelName = class_basename($modelClass);

                foreach ($items as $item) {
                    $itemId = (int) $item->getKey();

                    // Count relationships before purging
                    $relationshipsDeleted = $this->purgeRelationships($modelClass, $itemId);
                    $relationshipsPurged += $relationshipsDeleted;

                    // Force delete the model
                    $item->forceDelete();

                    if ($this->option('verbose')) {
                        $this->line("Deleted {$modelName} ID: {$itemId} ({$relationshipsDeleted} relationships)");
                    }

                    $totalPurged++;
                }

                $count = $items->count();
                $this->info("Purged {$count} {$modelName} record(s)");
            }
        });

        return [
            'total' => $totalPurged,
            'relationships' => $relationshipsPurged,
        ];
    }

    /**
     * Purge relationships for a specific model instance.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function purgeRelationships(string $modelClass, int $modelId): int
    {
        // Delete relationships where this model is the source
        $deletedAsSource = DB::table('content_relations')
            ->where('source_type', $modelClass)
            ->where('source_id', $modelId)
            ->delete();

        // Delete relationships where this model is the target
        $deletedAsTarget = DB::table('content_relations')
            ->where('target_type', $modelClass)
            ->where('target_id', $modelId)
            ->delete();

        return $deletedAsSource + $deletedAsTarget;
    }
}
