<x-filament-panels::page>
    <div class="space-y-6">
        {{-- View Toggle --}}
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing brand logos, favicons, and identity assets
            </div>
            <div class="flex gap-2">
                <button
                    wire:click="toggleViewMode('grid')"
                    class="px-4 py-2 rounded-lg transition-colors {{ $viewMode === 'grid' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
                >
                    <x-filament::icon icon="heroicon-o-squares-2x2" class="w-5 h-5 inline-block" />
                    Grid
                </button>
                <button
                    wire:click="toggleViewMode('table')"
                    class="px-4 py-2 rounded-lg transition-colors {{ $viewMode === 'table' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
                >
                    <x-filament::icon icon="heroicon-o-table-cells" class="w-5 h-5 inline-block" />
                    Table
                </button>
            </div>
        </div>

        {{-- Upload Area (Placeholder) --}}
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center bg-gray-50 dark:bg-gray-800/50">
            <x-filament::icon icon="heroicon-o-cloud-arrow-up" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" />
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                <strong>Upload brand assets</strong> (placeholder - not functional yet)
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-500">
                Drag and drop files here, or click to browse
            </p>
        </div>

        @php
            $assets = [
                ['name' => 'Logo (Light)', 'type' => 'Logo', 'dimensions' => '1200 x 400 px', 'icon' => 'heroicon-o-photo', 'bg' => 'bg-gray-100 dark:bg-gray-700'],
                ['name' => 'Logo (Dark)', 'type' => 'Logo', 'dimensions' => '1200 x 400 px', 'icon' => 'heroicon-o-photo', 'bg' => 'bg-gray-900'],
                ['name' => 'Favicon', 'type' => 'Favicon', 'dimensions' => '512 x 512 px', 'icon' => 'heroicon-o-sparkles', 'bg' => 'bg-gray-100 dark:bg-gray-700'],
                ['name' => 'Apple Touch Icon', 'type' => 'Favicon', 'dimensions' => '180 x 180 px', 'icon' => 'heroicon-o-device-phone-mobile', 'bg' => 'bg-gray-100 dark:bg-gray-700'],
                ['name' => 'OG Image', 'type' => 'Social', 'dimensions' => '1200 x 630 px', 'icon' => 'heroicon-o-globe-alt', 'bg' => 'bg-gray-100 dark:bg-gray-700'],
                ['name' => 'Brand Icon', 'type' => 'Logo', 'dimensions' => '256 x 256 px', 'icon' => 'heroicon-o-star', 'bg' => 'bg-gray-100 dark:bg-gray-700'],
            ];
        @endphp

        @if ($viewMode === 'grid')
            {{-- Grid View --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($assets as $asset)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800 hover:shadow-lg transition-shadow">
                        <div class="aspect-square {{ $asset['bg'] }} rounded-lg mb-3 flex items-center justify-center">
                            <x-filament::icon :icon="$asset['icon']" class="w-16 h-16 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100 mb-1">{{ $asset['name'] }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $asset['dimensions'] }}</p>
                        <div class="flex gap-2">
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
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Table View --}}
            <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Dimensions
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Updated
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($assets as $asset)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $asset['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $asset['dimensions'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    Dec 12, 2025
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
