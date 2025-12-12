<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Upload Area Placeholder --}}
        <x-filament.placeholder-upload-area
            title="Upload brand assets"
            description="Drag and drop logos, favicons, or social media images here, or click to browse"
        />

        {{-- View Mode Container with Transition --}}
        <div
            wire:key="view-mode-{{ $viewMode }}"
            x-data="{ show: false }"
            x-init="$nextTick(() => show = true)"
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            role="region"
            aria-label="Brand assets {{ $viewMode }} view"
        >
            @if($viewMode === 'grid')
                {{-- Grid View --}}
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
                    role="list"
                    aria-label="Brand assets grid"
                >
                    @foreach($this->getPlaceholderBrandAssets() as $index => $asset)
                        <article
                            role="listitem"
                            class="group relative rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden hover:shadow-lg hover:border-primary-500 dark:hover:border-primary-600 transition-all duration-200 cursor-pointer focus-within:ring-2 focus-within:ring-primary-500 dark:focus-within:ring-primary-600"
                            tabindex="0"
                            aria-label="{{ $asset['name'] }}, {{ $asset['type'] }}, {{ $asset['dimensions'] }}"
                        >
                            {{-- Icon Placeholder with Background --}}
                            <div class="aspect-square {{ $asset['bg'] }} flex items-center justify-center relative overflow-hidden">
                                {{-- Main Icon --}}
                                <x-filament::icon
                                    :icon="$asset['icon']"
                                    class="relative z-10 w-16 h-16 text-white opacity-80 group-hover:scale-110 group-focus-within:scale-110 transition-transform duration-200"
                                    aria-hidden="true"
                                />

                                {{-- Type Badge --}}
                                <div class="absolute top-2 left-2 z-20">
                                    @if($asset['type'] === 'Logo')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 shadow-sm">
                                            Logo
                                        </span>
                                    @elseif($asset['type'] === 'Favicon')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 shadow-sm">
                                            Favicon
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 shadow-sm">
                                            Social
                                        </span>
                                    @endif
                                </div>

                                {{-- Hover Actions Overlay --}}
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 dark:group-hover:bg-black/60 group-focus-within:bg-black/40 dark:group-focus-within:bg-black/60 transition-all duration-200 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 z-30">
                                    <button
                                        type="button"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-md shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-xs font-medium focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        aria-label="View {{ $asset['name'] }}"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-o-eye"
                                            class="w-4 h-4"
                                        />
                                        <span class="hidden sm:inline">View</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-md shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-xs font-medium focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        aria-label="Download {{ $asset['name'] }}"
                                    >
                                        <x-filament::icon
                                            icon="heroicon-o-arrow-down-tray"
                                            class="w-4 h-4"
                                        />
                                        <span class="hidden sm:inline">Download</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Asset Details --}}
                            <div class="p-3 space-y-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400 transition-colors" title="{{ $asset['name'] }}">
                                    {{ $asset['name'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $asset['dimensions'] }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                {{-- Table View --}}
                <div class="overflow-x-auto overflow-y-visible rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20 sm:w-24">
                                    Thumbnail
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="hidden sm:table-cell px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                    Type
                                </th>
                                <th scope="col" class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32 lg:w-40">
                                    Dimensions
                                </th>
                                <th scope="col" class="hidden lg:table-cell px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                    Updated
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getPlaceholderBrandAssets() as $index => $asset)
                                <tr
                                    class="group hover:bg-gray-50 dark:hover:bg-gray-800/70 focus-within:bg-gray-50 dark:focus-within:bg-gray-800/70 transition-colors cursor-pointer"
                                    tabindex="0"
                                    role="row"
                                    aria-label="{{ $asset['name'] }}, {{ $asset['type'] }}, {{ $asset['dimensions'] }}"
                                >
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded {{ $asset['bg'] }} flex items-center justify-center flex-shrink-0">
                                            <x-filament::icon
                                                :icon="$asset['icon']"
                                                class="w-6 h-6 sm:w-8 sm:h-8 text-white opacity-80"
                                                aria-hidden="true"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400 transition-colors truncate max-w-xs">
                                                {{ $asset['name'] }}
                                            </p>
                                            {{-- Mobile-only: show type and dimensions below name --}}
                                            <p class="sm:hidden text-xs text-gray-500 dark:text-gray-400">
                                                {{ $asset['type'] }} &bull; {{ $asset['dimensions'] }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="hidden sm:table-cell px-3 py-3 whitespace-nowrap">
                                        @if($asset['type'] === 'Logo')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Logo
                                            </span>
                                        @elseif($asset['type'] === 'Favicon')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                Favicon
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Social
                                            </span>
                                        @endif
                                    </td>
                                    <td class="hidden md:table-cell px-3 py-3 whitespace-nowrap">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $asset['dimensions'] }}
                                        </p>
                                    </td>
                                    <td class="hidden lg:table-cell px-3 py-3 whitespace-nowrap">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Dec 13, 2025
                                        </p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Status Footer --}}
        <div class="text-center py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Showing {{ count($this->getPlaceholderBrandAssets()) }} placeholder brand assets &bull; Upload and management features coming soon
            </p>
        </div>
    </div>
</x-filament-panels::page>
