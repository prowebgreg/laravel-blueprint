<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

it('verifies jobs table structure exists', function () {
    // Verify the jobs table has the expected columns for queue processing
    $columns = DB::getSchemaBuilder()->getColumnListing('jobs');

    expect($columns)->toContain('id');
    expect($columns)->toContain('queue');
    expect($columns)->toContain('payload');
    expect($columns)->toContain('attempts');
    expect($columns)->toContain('reserved_at');
    expect($columns)->toContain('available_at');
    expect($columns)->toContain('created_at');
});

it('verifies jobs table is empty initially', function () {
    // Clear the jobs table
    DB::table('jobs')->truncate();

    // Verify the table is empty
    expect(DB::table('jobs')->count())->toBe(0);
});

it('can manually insert a job payload into jobs table', function () {
    // Clear the jobs table
    DB::table('jobs')->truncate();

    // Manually insert a job record to verify table structure works
    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => json_encode([
            'displayName' => 'Example\\Job',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => [
                'commandName' => 'Example\\Job',
            ],
        ]),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);

    // Verify the job was inserted
    $job = DB::table('jobs')->first();
    expect($job)->not->toBeNull();
    expect($job->queue)->toBe('default');
    expect($job->attempts)->toBe(0);
});
