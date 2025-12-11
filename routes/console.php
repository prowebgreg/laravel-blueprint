<?php

declare(strict_types=1);

use App\Jobs\Media\CleanupFailedMediaJob;
use App\Jobs\Media\SyncOrphanedFilesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cleanup failed media assets that have been stuck for 24+ hours
Schedule::job(new CleanupFailedMediaJob)->dailyAt('03:00');

// Sync orphaned files between S3 and database to detect inconsistencies
Schedule::job(new SyncOrphanedFilesJob)->weekly()->sundays()->at('04:00');
