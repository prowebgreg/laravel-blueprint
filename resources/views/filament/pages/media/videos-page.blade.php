<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Placeholder Upload Area --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-12 text-center">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="rounded-full bg-gray-100 dark:bg-gray-700 p-4">
                    <x-filament::icon
                        icon="heroicon-o-cloud-arrow-up"
                        class="w-12 h-12 text-gray-400"
                    />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Upload video files</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">MP4, WebM, or MOV files - Upload functionality coming soon</p>
                </div>
                <button type="button" disabled class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-md text-sm font-medium cursor-not-allowed">
                    Coming Soon
                </button>
            </div>
        </div>

        {{-- Filament Table Widget --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
