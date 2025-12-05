<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TestJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public bool $shouldFail = false
    ) {}

    public function handle(): void
    {
        Log::info('TestJob started processing');

        sleep(2);

        if ($this->shouldFail) {
            throw new RuntimeException('TestJob intentionally failed for testing');
        }

        Log::info('TestJob completed successfully');
    }
}
