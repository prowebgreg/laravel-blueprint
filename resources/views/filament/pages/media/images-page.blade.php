<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Upload Area Placeholder --}}
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800/70 transition-colors cursor-pointer">
            <x-filament::icon
                icon="heroicon-o-cloud-arrow-up"
                class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500 mb-3"
            />
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                <strong>Upload images</strong> (placeholder - not functional yet)
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-500">
                Drag and drop JPG, PNG, WebP files here, or click to browse
            </p>
        </div>

        {{-- Grid of Placeholder Images --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
            @foreach($this->getPlaceholderImages() as $image)
                <div class="group relative rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden hover:shadow-lg hover:border-primary-500 dark:hover:border-primary-600 transition-all duration-200 cursor-pointer">
                    {{-- Thumbnail Placeholder with Gradient Background --}}
                    <div class="aspect-square {{ $image['bg'] }} flex items-center justify-center relative overflow-hidden">
                        <x-filament::icon
                            :icon="$image['icon']"
                            class="w-16 h-16 text-white opacity-60 group-hover:scale-110 transition-transform duration-200"
                        />
                        {{-- Hover Overlay --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-200"></div>
                    </div>

                    {{-- Image Details --}}
                    <div class="p-3 space-y-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" title="{{ $image['name'] }}">
                            {{ $image['name'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $image['dimensions'] }} &bull; {{ $image['size'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Status Footer --}}
        <div class="text-center py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Showing {{ count($this->getPlaceholderImages()) }} placeholder images &bull; Upload and management features coming soon
            </p>
        </div>
    </div>
</x-filament-panels::page>
