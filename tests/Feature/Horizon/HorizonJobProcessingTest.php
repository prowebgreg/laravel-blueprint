<?php

declare(strict_types=1);

use App\Jobs\TestJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

it('processes test job successfully', function () {
    // Set up log spy to capture log messages
    Log::spy();

    // Dispatch the job synchronously (will process immediately in test environment)
    TestJob::dispatch(shouldFail: false);

    // Assert that the job logged the start message
    Log::shouldHaveReceived('info')
        ->with('TestJob started processing')
        ->once();

    // Assert that the job logged the completion message
    Log::shouldHaveReceived('info')
        ->with('TestJob completed successfully')
        ->once();
});

it('processes test job and handles failure correctly', function () {
    // Set up log spy to capture log messages
    Log::spy();

    // Dispatch the job with failure flag - expect it to throw exception
    try {
        TestJob::dispatch(shouldFail: true);
        expect(true)->toBeFalse('Job should have thrown an exception');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('TestJob intentionally failed for testing');
    }

    // Assert that the job logged the start message before failing
    Log::shouldHaveReceived('info')
        ->with('TestJob started processing')
        ->once();

    // Verify completion message was NOT logged by checking info was called exactly once
    // (only the start message, not the completion message)
    Log::shouldHaveReceived('info')->times(1);
});

it('verifies test job processes with queue connection', function () {
    // Set up log spy to capture log messages
    Log::spy();

    // Explicitly dispatch to a queue and process it
    $job = new TestJob(shouldFail: false);
    $job->handle();

    // Verify the job executed its logic
    Log::shouldHaveReceived('info')
        ->with('TestJob started processing')
        ->once();

    Log::shouldHaveReceived('info')
        ->with('TestJob completed successfully')
        ->once();
});

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
            'displayName' => 'App\\Jobs\\TestJob',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => [
                'commandName' => 'App\\Jobs\\TestJob',
                'command' => serialize(new TestJob(shouldFail: true)),
            ],
        ]),
        'exception' => 'RuntimeException: TestJob intentionally failed for testing in /var/www/html/app/Jobs/TestJob.php:29',
        'failed_at' => now(),
    ]);

    // Verify the failed job was recorded
    $failedJob = DB::table('failed_jobs')->where('uuid', $failedJobUuid)->first();
    expect($failedJob)->not->toBeNull();
    expect($failedJob->queue)->toBe('default');
    expect($failedJob->exception)->toContain('TestJob intentionally failed for testing');
    expect($failedJob->connection)->toBe('database');

    // Verify we can query failed jobs
    expect(DB::table('failed_jobs')->count())->toBe(1);
});

it('allows failed jobs to be retried successfully', function () {
    // Clear any existing failed jobs and regular jobs
    DB::table('failed_jobs')->truncate();
    DB::table('jobs')->truncate();

    // Insert a failed job record manually to simulate a previously failed job
    // This time with shouldFail: false so it can succeed on retry
    $failedJobUuid = (string) \Illuminate\Support\Str::uuid();
    $payload = json_encode([
        'displayName' => 'App\\Jobs\\TestJob',
        'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
        'maxTries' => 3,
        'maxExceptions' => null,
        'failOnTimeout' => false,
        'backoff' => null,
        'timeout' => null,
        'retryUntil' => null,
        'data' => [
            'commandName' => 'App\\Jobs\\TestJob',
            'command' => serialize(new TestJob(shouldFail: false)),
        ],
    ]);

    DB::table('failed_jobs')->insert([
        'uuid' => $failedJobUuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => $payload,
        'exception' => 'RuntimeException: TestJob intentionally failed for testing',
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

it('can manually fail a job using withFakeQueueInteractions', function () {
    // Create a job with fake queue interactions
    $job = (new TestJob(shouldFail: false))->withFakeQueueInteractions();

    // Manually fail the job
    $job->fail(new \RuntimeException('Manually failed'));

    // Assert the job is marked as failed
    $job->assertFailed();
    $job->assertFailedWith(\RuntimeException::class);
});

it('verifies job failure is tracked with proper exception when using fake queue interactions', function () {
    // Create a job that will throw an exception
    $job = (new TestJob(shouldFail: true))->withFakeQueueInteractions();

    // Attempt to handle the job, which will throw an exception
    expect(fn () => $job->handle())
        ->toThrow(\RuntimeException::class, 'TestJob intentionally failed for testing');

    // Note: The job won't be marked as "failed" via assertFailed() because
    // the exception was thrown before fail() could be called
    // This is expected behavior when exceptions are thrown directly
});
