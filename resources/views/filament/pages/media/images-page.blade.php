<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Upload Area Placeholder --}}
        <x-filament.placeholder-upload-area
            title="Upload images"
            description="Drag and drop JPG, PNG, WebP files here, or click to browse"
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
            aria-label="Image library {{ $viewMode }} view"
        >
            @if($viewMode === 'grid')
                {{-- Grid View --}}
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
                    role="list"
                    aria-label="Image grid"
                >
                    @foreach($this->getPlaceholderImages() as $index => $image)
                        <article
                            role="listitem"
                            class="group relative rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden hover:shadow-lg hover:border-primary-500 dark:hover:border-primary-600 transition-all duration-200 cursor-pointer focus-within:ring-2 focus-within:ring-primary-500 dark:focus-within:ring-primary-600"
                            tabindex="0"
                            aria-label="{{ $image['name'] }}, {{ $image['dimensions'] }}, {{ $image['size'] }}"
                        >
                            {{-- Thumbnail Placeholder with Gradient Background --}}
                            <div class="aspect-square {{ $image['bg'] }} flex items-center justify-center relative overflow-hidden">
                                <x-filament::icon
                                    :icon="$image['icon']"
                                    class="w-16 h-16 text-white opacity-60 group-hover:scale-110 group-focus-within:scale-110 transition-transform duration-200"
                                    aria-hidden="true"
                                />
                                {{-- Hover Overlay --}}
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 group-focus-within:bg-black/10 transition-colors duration-200"></div>
                            </div>

                            {{-- Image Details --}}
                            <div class="p-3 space-y-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400 transition-colors" title="{{ $image['name'] }}">
                                    {{ $image['name'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $image['dimensions'] }} &bull; {{ $image['size'] }}
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
                                <th scope="col" class="hidden sm:table-cell px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32 lg:w-40">
                                    Dimensions
                                </th>
                                <th scope="col" class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24 lg:w-32">
                                    Size
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getPlaceholderImages() as $index => $image)
                                <tr
                                    class="group hover:bg-gray-50 dark:hover:bg-gray-800/70 focus-within:bg-gray-50 dark:focus-within:bg-gray-800/70 transition-colors cursor-pointer"
                                    tabindex="0"
                                    role="row"
                                    aria-label="{{ $image['name'] }}, {{ $image['dimensions'] }}, {{ $image['size'] }}"
                                >
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded {{ $image['bg'] }} flex items-center justify-center flex-shrink-0">
                                            <x-filament::icon
                                                :icon="$image['icon']"
                                                class="w-6 h-6 sm:w-8 sm:h-8 text-white opacity-60"
                                                aria-hidden="true"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400 transition-colors truncate max-w-xs">
                                                {{ $image['name'] }}
                                            </p>
                                            {{-- Mobile-only: show dimensions and size below name --}}
                                            <p class="sm:hidden text-xs text-gray-500 dark:text-gray-400">
                                                {{ $image['dimensions'] }} &bull; {{ $image['size'] }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="hidden sm:table-cell px-3 py-3 whitespace-nowrap">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $image['dimensions'] }}
                                        </p>
                                    </td>
                                    <td class="hidden md:table-cell px-3 py-3 whitespace-nowrap">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $image['size'] }}
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
                Showing {{ count($this->getPlaceholderImages()) }} placeholder images &bull; Upload and management features coming soon
            </p>
        </div>
    </div>
</x-filament-panels::page>
