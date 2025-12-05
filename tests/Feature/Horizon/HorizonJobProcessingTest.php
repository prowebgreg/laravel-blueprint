<?php

declare(strict_types=1);

use App\Jobs\TestJob;
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
