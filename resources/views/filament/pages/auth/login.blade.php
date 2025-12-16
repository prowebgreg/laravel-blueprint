<x-filament-panels::page.simple>
    <style>
        .fi-simple-page {
            width: 100% !important;
        }
        .fi-simple-main.my-16 {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            --tw-ring-shadow: none !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }
    </style>

    <div class="login-card">
        {{-- Logo/Brand --}}
        <div class="login-brand">
            <span class="login-brand-name">BlueprintCMS</span>
        </div>

        {{-- Title --}}
        <div class="login-header">
            <h1 class="login-title">Sign in</h1>
            <p class="login-subtitle">Enter your credentials to access your account</p>
        </div>

        {{-- Form --}}
        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    </div>
</x-filament-panels::page.simple>
