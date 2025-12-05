<?php

declare(strict_types=1);

use App\Jobs\TestJob;
use Illuminate\Support\Facades\Queue;

it('dispatches test job to queue and verifies it appears as pending', function () {
    // Fake the queue to prevent actual processing
    Queue::fake();

    // Dispatch the TestJob
    TestJob::dispatch(shouldFail: false);

    // Assert the job was pushed to the queue
    Queue::assertPushed(TestJob::class, function (TestJob $job) {
        return $job->shouldFail === false;
    });
});

it('dispatches test job with failure flag', function () {
    // Fake the queue to prevent actual processing
    Queue::fake();

    // Dispatch the TestJob with failure flag
    TestJob::dispatch(shouldFail: true);

    // Assert the job was pushed to the queue with correct parameters
    Queue::assertPushed(TestJob::class, function (TestJob $job) {
        return $job->shouldFail === true;
    });
});

it('verifies test job has correct retry configuration', function () {
    // Fake the queue to prevent actual processing
    Queue::fake();

    // Dispatch the TestJob
    TestJob::dispatch();

    // Assert the job was pushed and verify its configuration
    Queue::assertPushed(TestJob::class, function (TestJob $job) {
        return $job->tries === 3;
    });
});
