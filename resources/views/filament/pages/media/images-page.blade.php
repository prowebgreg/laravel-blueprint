<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Upload Area Placeholder --}}
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
            <x-filament::icon
                icon="heroicon-o-arrow-up-tray"
                class="mx-auto h-12 w-12 text-gray-400"
            />
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Upload Area (Placeholder)
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-500">
                Drag and drop images here or click to browse
            </p>
        </div>

        {{-- Grid of Placeholder Images --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach(range(1, 8) as $index)
                <div class="group relative rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden hover:shadow-lg transition-shadow">
                    {{-- Thumbnail Placeholder --}}
                    <div class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <x-filament::icon
                            icon="heroicon-o-photo"
                            class="h-16 w-16 text-gray-400"
                        />
                    </div>

                    {{-- Image Details --}}
                    <div class="p-3 space-y-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            placeholder-image-{{ $index }}.jpg
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            1920 x 1080 &bull; 245 KB
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
