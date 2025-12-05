<?php

declare(strict_types=1);

/**
 * Tests for admin password reset functionality via Filament auth pages.
 *
 * Covers:
 * - Password reset notification sending
 * - Reset token generation and validation
 * - Password update flow
 * - Login with new password after reset
 * - Old password rejection after reset
 */

use App\Models\User;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Filament\Pages\Auth\Login;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Filament\Pages\Auth\PasswordReset\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('sends password reset notification when requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request');

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('sends password reset email with valid reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request');

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification, $channels) use ($user) {
        expect($notification)->toBeInstanceOf(ResetPasswordNotification::class);
        expect($channels)->toContain('mail');

        $token = $notification->token;
        expect($token)->not->toBeEmpty();

        test()->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        return true;
    });
});

it('resets password using valid reset link', function () {
    Notification::fake();

    $user = User::factory()->create();
    $oldPasswordHash = $user->password;

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request');

    $token = null;
    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    expect($token)->not->toBeNull();

    $newPassword = 'NewSecurePassword123@';
    Livewire::test(ResetPassword::class, ['token' => $token])
        ->fillForm([
            'email' => $user->email,
            'password' => $newPassword,
            'passwordConfirmation' => $newPassword,
        ])
        ->call('resetPassword');

    $user->refresh();

    expect(Hash::check($newPassword, $user->password))->toBeTrue();
    expect($user->password)->not->toBe($oldPasswordHash);
});

it('allows login with new password after reset', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request');

    $token = null;
    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $newPassword = 'NewSecurePassword123@';
    Livewire::test(ResetPassword::class, ['token' => $token])
        ->fillForm([
            'email' => $user->email,
            'password' => $newPassword,
            'passwordConfirmation' => $newPassword,
        ])
        ->call('resetPassword');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => $newPassword,
        ])
        ->call('authenticate')
        ->assertRedirect('/admin');

    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

it('does not allow login with old password after reset', function () {
    Notification::fake();

    $oldPassword = 'OldPassword123@';
    $user = User::factory()->create([
        'password' => $oldPassword,
    ]);

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => $user->email,
        ])
        ->call('request');

    $token = null;
    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $newPassword = 'NewSecurePassword123@';
    Livewire::test(ResetPassword::class, ['token' => $token])
        ->fillForm([
            'email' => $user->email,
            'password' => $newPassword,
            'passwordConfirmation' => $newPassword,
        ])
        ->call('resetPassword');

    // Logout first (previous test may have authenticated)
    Auth::logout();

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => $oldPassword,
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    expect(Auth::check())->toBeFalse();
});
