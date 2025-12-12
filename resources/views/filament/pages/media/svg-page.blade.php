<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Upload Area Placeholder --}}
        <x-filament.placeholder-upload-area
            title="Upload SVG files"
            description="Drag and drop SVG files here, or click to browse"
        />

        {{-- Grid View --}}
        <div
            role="region"
            aria-label="SVG library grid view"
        >
            <div
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
                role="list"
                aria-label="SVG grid"
            >
                @foreach($this->getPlaceholderSvgs() as $index => $svg)
                    <article
                        role="listitem"
                        class="group relative rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden hover:shadow-lg hover:border-primary-500 dark:hover:border-primary-600 transition-all duration-200 cursor-pointer focus-within:ring-2 focus-within:ring-primary-500 dark:focus-within:ring-primary-600"
                        tabindex="0"
                        aria-label="{{ $svg['name'] }}, {{ $svg['category'] }}, {{ $svg['size'] }}"
                    >
                        {{-- SVG Preview with Code-Style Visual Representation --}}
                        <div class="aspect-square {{ $svg['bg'] }} flex items-center justify-center relative overflow-hidden">
                            {{-- SVG Code Pattern Background --}}
                            <div class="absolute inset-0 opacity-10" aria-hidden="true">
                                <div class="absolute top-2 left-2 right-2 text-[10px] font-mono text-white space-y-1">
                                    <div>&lt;svg&gt;</div>
                                    <div class="ml-2">&lt;path d="..."&gt;</div>
                                    <div class="ml-2">&lt;/path&gt;</div>
                                    <div>&lt;/svg&gt;</div>
                                </div>
                            </div>

                            {{-- Main SVG Icon --}}
                            <x-filament::icon
                                :icon="$svg['icon']"
                                class="relative z-10 w-16 h-16 text-white opacity-80 group-hover:scale-110 group-focus-within:scale-110 transition-transform duration-200"
                                aria-hidden="true"
                            />

                            {{-- Code Bracket Overlay in Corner --}}
                            <div class="absolute top-2 right-2 z-20" aria-hidden="true">
                                <x-filament::icon
                                    icon="heroicon-o-code-bracket"
                                    class="w-5 h-5 text-white opacity-40"
                                />
                            </div>

                            {{-- Category Badge --}}
                            <div class="absolute bottom-2 left-2 z-20">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-white/90 dark:bg-gray-900/90 text-gray-800 dark:text-gray-200 shadow-sm backdrop-blur-sm">
                                    {{ $svg['category'] }}
                                </span>
                            </div>

                            {{-- Hover Actions Overlay --}}
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 dark:group-hover:bg-black/60 group-focus-within:bg-black/40 dark:group-focus-within:bg-black/60 transition-all duration-200 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 z-30">
                                <button
                                    type="button"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-md shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-xs font-medium focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    aria-label="View {{ $svg['name'] }}"
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
                                    aria-label="Download {{ $svg['name'] }}"
                                >
                                    <x-filament::icon
                                        icon="heroicon-o-arrow-down-tray"
                                        class="w-4 h-4"
                                    />
                                    <span class="hidden sm:inline">Download</span>
                                </button>
                            </div>
                        </div>

                        {{-- SVG Details --}}
                        <div class="p-3 space-y-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400 transition-colors" title="{{ $svg['name'] }}">
                                {{ $svg['name'] }}
                            </p>
                            <div class="flex items-center gap-2">
                                <x-filament::icon
                                    icon="heroicon-o-document-text"
                                    class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500"
                                    aria-hidden="true"
                                />
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $svg['size'] }}
                                </p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        {{-- Status Footer --}}
        <div class="text-center py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Showing {{ count($this->getPlaceholderSvgs()) }} placeholder SVG files &bull; Upload and management features coming soon
            </p>
        </div>
    </div>
</x-filament-panels::page>
