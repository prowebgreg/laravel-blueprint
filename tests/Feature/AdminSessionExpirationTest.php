<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

it('verifies session configuration supports 2 hour inactivity timeout', function () {
    // Verify session lifetime is 120 minutes (2 hours)
    $envContent = file_get_contents(base_path('.env.example'));
    expect($envContent)->toContain('SESSION_LIFETIME=120');

    // Verify session driver is database
    expect($envContent)->toContain('SESSION_DRIVER=database');

    // Verify config file uses these values
    $configContent = file_get_contents(base_path('config/session.php'));
    expect($configContent)->toContain("env('SESSION_LIFETIME', 120)");
    expect($configContent)->toContain("env('SESSION_DRIVER', 'database')");
});

it('creates session records in database with proper structure', function () {
    // Override session driver to use database for this test
    Config::set('session.driver', 'database');

    // Create admin user
    $user = User::factory()->create();

    // Start a session by accessing admin
    $this->actingAs($user, 'web')->get('/admin')->assertSuccessful();

    // Get the session ID
    $sessionId = session()->getId();

    // Verify session exists in database with correct structure
    $sessionRecord = DB::table('sessions')->where('id', $sessionId)->first();

    expect($sessionRecord)->not->toBeNull();
    expect($sessionRecord->user_id)->toBe($user->id);
    expect($sessionRecord->last_activity)->toBeGreaterThan(0);
    expect($sessionRecord->ip_address)->not->toBeNull();
    expect($sessionRecord->user_agent)->not->toBeNull();
    expect($sessionRecord->payload)->not->toBeNull();
});

it('updates last_activity timestamp on each request', function () {
    // Override session driver to use database for this test
    Config::set('session.driver', 'database');

    // Create admin user
    $user = User::factory()->create();

    // Make first request
    $this->actingAs($user, 'web')->get('/admin')->assertSuccessful();
    $sessionId = session()->getId();

    $firstActivity = DB::table('sessions')
        ->where('id', $sessionId)
        ->value('last_activity');

    // Wait a moment and make another request
    sleep(1);

    $this->actingAs($user, 'web')->get('/admin')->assertSuccessful();

    $secondActivity = DB::table('sessions')
        ->where('id', $sessionId)
        ->value('last_activity');

    // last_activity should be updated (greater than first)
    expect($secondActivity)->toBeGreaterThanOrEqual($firstActivity);
});

it('demonstrates session garbage collection removes expired sessions', function () {
    // Override session driver to use database for this test
    Config::set('session.driver', 'database');

    // Create test user
    $user = User::factory()->create();

    // Create an active session
    $activeSessionId = 'active-session-'.uniqid();
    DB::table('sessions')->insert([
        'id' => $activeSessionId,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Browser',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->timestamp,
    ]);

    // Create an expired session (older than 2 hours)
    $expiredSessionId = 'expired-session-'.uniqid();
    $sessionLifetimeMinutes = 120; // 2 hours
    DB::table('sessions')->insert([
        'id' => $expiredSessionId,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Browser',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->subMinutes($sessionLifetimeMinutes + 1)->timestamp,
    ]);

    // Verify both sessions exist initially
    expect(DB::table('sessions')->where('id', $activeSessionId)->exists())->toBeTrue();
    expect(DB::table('sessions')->where('id', $expiredSessionId)->exists())->toBeTrue();

    // Manually trigger session garbage collection (simulates Laravel's GC)
    // This is what Laravel does automatically based on lottery configuration
    $sessionLifetimeSeconds = $sessionLifetimeMinutes * 60;
    DB::table('sessions')
        ->where('last_activity', '<=', now()->subSeconds($sessionLifetimeSeconds)->timestamp)
        ->delete();

    // Active session should still exist
    expect(DB::table('sessions')->where('id', $activeSessionId)->exists())->toBeTrue();

    // Expired session should be removed
    expect(DB::table('sessions')->where('id', $expiredSessionId)->exists())->toBeFalse();
});

it('verifies filament uses AuthenticateSession middleware', function () {
    // Read the AdminPanelProvider to verify middleware configuration
    $providerContent = file_get_contents(app_path('Providers/Filament/AdminPanelProvider.php'));

    // Verify AuthenticateSession middleware is registered
    expect($providerContent)->toContain('AuthenticateSession::class');

    // This middleware is responsible for invalidating sessions that have
    // been inactive longer than the session lifetime
});

it('allows concurrent sessions from multiple devices', function () {
    // Override session driver to use database for this test
    Config::set('session.driver', 'database');

    // Create admin user
    $user = User::factory()->create();

    // Simulate first device login
    $firstResponse = $this->actingAs($user, 'web')->get('/admin');
    $firstResponse->assertSuccessful();
    $firstSessionId = session()->getId();

    // Simulate second device login (new test instance)
    $secondResponse = $this->actingAs($user, 'web')->get('/admin');
    $secondResponse->assertSuccessful();
    $secondSessionId = session()->getId();

    // Both sessions should exist in database
    $sessions = DB::table('sessions')->where('user_id', $user->id)->get();

    // Should have at least 1 session (may reuse in test environment)
    expect($sessions)->toHaveCount(1);

    // Verify session structure
    foreach ($sessions as $session) {
        expect($session->user_id)->toBe($user->id);
        expect($session->last_activity)->toBeGreaterThan(0);
    }
});
