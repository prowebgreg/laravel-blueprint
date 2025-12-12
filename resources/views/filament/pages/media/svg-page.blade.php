<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            SVG Vector Assets
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Scalable vector graphics for icons, logos, and illustrations
                        </p>
                    </div>
                    <x-filament::button
                        icon="heroicon-o-arrow-up-tray"
                        disabled
                    >
                        Upload SVG
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Placeholder Upload Area --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12 dark:border-gray-700">
                    <x-filament::icon
                        icon="heroicon-o-cloud-arrow-up"
                        class="h-12 w-12 text-gray-400"
                    />
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                        Drag and drop SVG files here
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        or click to browse (Upload functionality coming soon)
                    </p>
                </div>
            </div>
        </div>

        {{-- Placeholder Grid --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                    @foreach(range(1, 8) as $index)
                        <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-gray-50 p-4 transition hover:border-primary-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            {{-- SVG Preview Placeholder --}}
                            <div class="flex aspect-square items-center justify-center rounded-md bg-white dark:bg-gray-900">
                                <svg class="h-16 w-16 text-gray-400 dark:text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13 7h-2v4H7v2h4v4h2v-4h4v-2h-4V7z"/>
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                </svg>
                            </div>

                            {{-- File Info --}}
                            <div class="mt-3 space-y-1">
                                <p class="truncate text-xs font-medium text-gray-900 dark:text-white">
                                    icon-{{ $index }}.svg
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ rand(2, 15) }} KB
                                </p>
                            </div>

                            {{-- Hover Actions --}}
                            <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition group-hover:opacity-100">
                                <div class="flex gap-2">
                                    <button class="rounded-md bg-white p-2 text-gray-700 hover:bg-gray-100" title="View">
                                        <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                                    </button>
                                    <button class="rounded-md bg-white p-2 text-gray-700 hover:bg-gray-100" title="Download">
                                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Empty State Indicator --}}
                <div class="mt-6 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                    <x-filament::icon
                        icon="heroicon-o-document"
                        class="mx-auto h-12 w-12 text-gray-400"
                    />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                        Placeholder Grid
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        This is a placeholder view. Actual SVG assets will be displayed here once the Media Engine integration is complete.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
