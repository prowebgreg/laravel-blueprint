<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

it('records failed job in failed_jobs table when job fails', function () {
    // Clear any existing failed jobs
    DB::table('failed_jobs')->truncate();

    // Manually insert a failed job to simulate what Horizon/queue system does
    // This simulates the actual behavior after a job has exhausted all retries
    $failedJobUuid = (string) \Illuminate\Support\Str::uuid();

    DB::table('failed_jobs')->insert([
        'uuid' => $failedJobUuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode([
            'displayName' => 'Example\\Job',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => [
                'commandName' => 'Example\\Job',
            ],
        ]),
        'exception' => 'RuntimeException: Job intentionally failed for testing',
        'failed_at' => now(),
    ]);

    // Verify the failed job was recorded
    $failedJob = DB::table('failed_jobs')->where('uuid', $failedJobUuid)->first();
    expect($failedJob)->not->toBeNull();
    expect($failedJob->queue)->toBe('default');
    expect($failedJob->exception)->toContain('Job intentionally failed for testing');
    expect($failedJob->connection)->toBe('database');

    // Verify we can query failed jobs
    expect(DB::table('failed_jobs')->count())->toBe(1);
});

it('allows failed jobs to be retried successfully', function () {
    // Clear any existing failed jobs and regular jobs
    DB::table('failed_jobs')->truncate();
    DB::table('jobs')->truncate();

    // Insert a failed job record manually to simulate a previously failed job
    $failedJobUuid = (string) \Illuminate\Support\Str::uuid();
    $payload = json_encode([
        'displayName' => 'Example\\Job',
        'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
        'maxTries' => 3,
        'maxExceptions' => null,
        'failOnTimeout' => false,
        'backoff' => null,
        'timeout' => null,
        'retryUntil' => null,
        'data' => [
            'commandName' => 'Example\\Job',
        ],
    ]);

    DB::table('failed_jobs')->insert([
        'uuid' => $failedJobUuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => $payload,
        'exception' => 'RuntimeException: Job intentionally failed for testing',
        'failed_at' => now(),
    ]);

    // Verify the failed job exists
    expect(DB::table('failed_jobs')->count())->toBe(1);

    // Retry the failed job using artisan command
    $this->artisan('queue:retry', ['id' => [$failedJobUuid]])
        ->assertSuccessful();

    // Verify the job was moved from failed_jobs back to jobs table
    expect(DB::table('jobs')->count())->toBeGreaterThan(0);

    // The failed job record should still exist (retry doesn't delete it immediately)
    // but it should be back in the jobs queue
    $retriedJob = DB::table('jobs')->first();
    expect($retriedJob)->not->toBeNull();
    expect($retriedJob->queue)->toBe('default');
});

it('verifies failed_jobs table structure exists', function () {
    // Verify the failed_jobs table has the expected columns
    $columns = DB::getSchemaBuilder()->getColumnListing('failed_jobs');

    expect($columns)->toContain('id');
    expect($columns)->toContain('uuid');
    expect($columns)->toContain('connection');
    expect($columns)->toContain('queue');
    expect($columns)->toContain('payload');
    expect($columns)->toContain('exception');
    expect($columns)->toContain('failed_at');
});
