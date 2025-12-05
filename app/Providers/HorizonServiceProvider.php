<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Filament\Exceptions\NoDefaultPanelSetException;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     * Access is granted only to users who can access the Filament admin panel.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (User $user): bool {
            try {
                $adminPanel = Filament::getPanel('admin');

                return $user->canAccessPanel($adminPanel);
            } catch (NoDefaultPanelSetException) {
                return false;
            }
        });
    }
}
