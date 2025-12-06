<?php

declare(strict_types=1);

/**
 * Tests for password reset email failure handling (FR-019).
 *
 * Verifies that when email service is unavailable, the system displays:
 * "Unable to send reset email. Please try again later or contact support."
 *
 * Covers:
 * - Mail service failure detection
 * - User-friendly error message display
 * - Error logging for debugging
 */

use App\Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows user-friendly error when mail service fails', function () {
    Log::spy();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Configure mail to fail by using invalid SMTP configuration
    Config::set('mail.mailers.smtp', [
        'transport' => 'smtp',
        'host' => 'invalid-smtp-host-that-does-not-exist.example.com',
        'port' => 9999,
        'encryption' => 'tls',
        'username' => null,
        'password' => null,
        'timeout' => 1,
    ]);
    Config::set('mail.default', 'smtp');

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request')
        ->assertNotified('Unable to send reset email. Please try again later or contact support.')
        ->assertNoRedirect();

    // Verify error was logged for debugging
    Log::shouldHaveReceived('error')
        ->once()
        ->with('Password reset email failed', \Mockery::on(function ($context) use ($user) {
            return $context['email'] === $user->email
                && isset($context['exception'])
                && isset($context['trace']);
        }));
});

it('shows user-friendly error when mail driver is misconfigured', function () {
    Log::spy();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Configure mail with invalid driver
    Config::set('mail.default', 'invalid_driver');

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request')
        ->assertNotified('Unable to send reset email. Please try again later or contact support.');

    // Verify error was logged
    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'Password reset email failed'
                && isset($context['exception']);
        });
});

it('still works normally when mail service is available', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Use the default mail configuration (log driver in testing)
    Config::set('mail.default', 'log');

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request')
        ->assertNotified('We have emailed your password reset link.');

    // Verify password reset token was created
    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => $user->email,
    ]);
});

it('does not expose sensitive error details to users', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Configure mail to fail
    Config::set('mail.default', 'invalid_driver');

    $response = Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request');

    // Should not see technical error messages
    $response->assertDontSee('Connection refused');
    $response->assertDontSee('SMTP');
    $response->assertDontSee('Exception');
    $response->assertDontSee('invalid_driver');

    // Should only see the user-friendly message
    $response->assertNotified('Unable to send reset email. Please try again later or contact support.');
});
