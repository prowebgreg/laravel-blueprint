@props([
    'navigation',
    'livewire' => null,
])

@php
    $sunIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 20 20"><path fill="currentColor" d="M10 2a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 10 2m0 12a4 4 0 1 0 0-8a4 4 0 0 0 0 8m0-1a3 3 0 1 1 0-6a3 3 0 0 1 0 6m7.5-2.5a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1zM10 16a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1a.5.5 0 0 1 .5-.5m-6.5-5.5a.5.5 0 0 0 0-1H2.463a.5.5 0 0 0 0 1zm.646-6.354a.5.5 0 0 1 .708 0l1 1a.5.5 0 1 1-.708.708l-1-1a.5.5 0 0 1 0-.708m.708 11.708a.5.5 0 0 1-.708-.708l1-1a.5.5 0 0 1 .708.708zm11-11.708a.5.5 0 0 0-.708 0l-1 1a.5.5 0 0 0 .708.708l1-1a.5.5 0 0 0 0-.708m-.708 11.708a.5.5 0 0 0 .708-.708l-1-1a.5.5 0 0 0-.708.708z"/></svg>';
    $moonIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 20 20"><path fill="currentColor" d="M15.493 13.497a6.98 6.98 0 0 1-11.483.892c2.831-1.087 4.558-2.42 5.593-4.397c1.048-2 1.337-4.16.76-6.909a6.98 6.98 0 0 1 5.13 10.414M5.457 16.918A7.981 7.981 0 1 0 9.88 2.035a.6.6 0 0 0-.614.74c.688 2.819.434 4.876-.55 6.753c-.934 1.784-2.544 3.031-5.55 4.107a.6.6 0 0 0-.292.903a7.95 7.95 0 0 0 2.582 2.38"/></svg>';

    $heading = null;
    if ($livewire && method_exists($livewire, 'getHeading')) {
        $heading = $livewire->getHeading();
    }

    $breadcrumbs = [];
    if ($livewire && method_exists($livewire, 'getBreadcrumbs') && filament()->hasBreadcrumbs()) {
        $breadcrumbs = $livewire->getBreadcrumbs();
    }
@endphp

<div
    {{
        $attributes->class([
            'fi-topbar sticky top-0 z-20 overflow-x-clip',
            'fi-topbar-with-navigation' => filament()->hasTopNavigation(),
        ])
    }}
>
    {{-- Primary topbar - heading + theme switcher --}}
    <div
        class="fi-topbar-primary flex items-center justify-between bg-white px-4 py-4 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:px-6 lg:px-8 border-b border-gray-950/5 dark:border-white/10"
    >
        {{-- Left container - mobile toggle + page heading --}}
        <div class="fi-topbar-start flex items-center gap-x-4">
            @if (filament()->hasNavigation())
                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-o-bars-3"
                    icon-alias="panels::topbar.open-sidebar-button"
                    icon-size="lg"
                    :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                    x-cloak
                    x-data="{}"
                    x-on:click="$store.sidebar.open()"
                    x-show="! $store.sidebar.isOpen"
                    @class([
                        'fi-topbar-open-sidebar-btn',
                        'lg:hidden' => (! filament()->isSidebarFullyCollapsibleOnDesktop()) || filament()->isSidebarCollapsibleOnDesktop(),
                    ])
                />

                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-o-x-mark"
                    icon-alias="panels::topbar.close-sidebar-button"
                    icon-size="lg"
                    :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                    x-cloak
                    x-data="{}"
                    x-on:click="$store.sidebar.close()"
                    x-show="$store.sidebar.isOpen"
                    class="fi-topbar-close-sidebar-btn lg:hidden"
                />
            @endif

            @if ($heading)
                <h1 class="fi-topbar-heading text-lg font-semibold text-gray-950 dark:text-white">
                    {{ $heading }}
                </h1>
            @endif
        </div>

        {{-- Right container - theme switcher --}}
        <div class="fi-topbar-end flex items-center gap-x-4">
            <button
                type="button"
                class="fi-theme-switcher-btn"
                x-data="{ isDark: document.documentElement.classList.contains('dark') }"
                x-on:click="
                    isDark = !isDark;
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                "
                title="Toggle theme"
            >
                <span x-show="!isDark" x-cloak>{!! $sunIcon !!}</span>
                <span x-show="isDark" x-cloak>{!! $moonIcon !!}</span>
            </button>
        </div>
    </div>

    {{-- Secondary topbar - breadcrumbs --}}
    @if ($breadcrumbs)
        <div
            class="fi-topbar-breadcrumbs flex items-center bg-white px-4 py-2 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:px-6 lg:px-8 border-b border-gray-950/5 dark:border-white/10"
        >
            <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
        </div>
    @endif
</div>
