<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth\PasswordReset;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function request(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $data = $this->form->getState();

        try {
            $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
                $this->getCredentialsFromFormData($data),
                function (CanResetPassword $user, string $token): void {
                    if (
                        ($user instanceof \Filament\Models\Contracts\FilamentUser) &&
                        (! $user->canAccessPanel(Filament::getCurrentPanel()))
                    ) {
                        return;
                    }

                    if (! method_exists($user, 'notify')) {
                        $userClass = $user::class;

                        throw new \Exception("Model [{$userClass}] does not have a [notify()] method.");
                    }

                    $notification = app(\Filament\Notifications\Auth\ResetPassword::class, ['token' => $token]);
                    $notification->url = Filament::getResetPasswordUrl($token, $user);

                    $user->notify($notification);

                    if (class_exists(PasswordResetLinkSent::class)) {
                        event(new PasswordResetLinkSent($user));
                    }
                },
            );

            if ($status !== Password::RESET_LINK_SENT) {
                $this->getFailureNotification($status)?->send();

                return;
            }

            $this->getSentNotification($status)?->send();

            $this->form->fill();
        } catch (Throwable $exception) {
            // Log the actual error for debugging
            Log::error('Password reset email failed', [
                'email' => $data['email'] ?? 'unknown',
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            // Display user-friendly error message as per FR-019
            Notification::make()
                ->title('Unable to send reset email. Please try again later or contact support.')
                ->danger()
                ->send();
        }
    }
}
