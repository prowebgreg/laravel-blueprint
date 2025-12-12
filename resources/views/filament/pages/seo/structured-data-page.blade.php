<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Organization Schema Section --}}
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Organization Schema</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure your organization's structured data for search engines
                </p>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Organization Name
                    </label>
                    <input type="text" disabled placeholder="Your Company Name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Logo URL
                    </label>
                    <input type="url" disabled placeholder="https://example.com/logo.png" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Contact Email
                    </label>
                    <input type="email" disabled placeholder="contact@example.com" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Contact Phone
                    </label>
                    <input type="tel" disabled placeholder="+1 (555) 123-4567" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
            </div>
        </div>

        {{-- Website Schema Section --}}
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Website Schema</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure your website's structured data
                </p>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Site Name
                    </label>
                    <input type="text" disabled placeholder="Your Website Name" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Site URL
                    </label>
                    <input type="url" disabled placeholder="https://example.com" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Search URL Template
                    </label>
                    <input type="url" disabled placeholder="https://example.com/search?q={search_term_string}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Use {search_term_string} as the placeholder for search queries
                    </p>
                </div>
            </div>
        </div>

        {{-- Breadcrumb Schema Section --}}
        <div class="rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Breadcrumb Schema</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure breadcrumb structured data for navigation
                </p>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" disabled checked class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    </div>
                    <div class="ml-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Enable Breadcrumb Schema
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Automatically generate breadcrumb structured data for all pages
                        </p>
                    </div>
                </div>
                <div class="rounded-md bg-blue-50 dark:bg-blue-900/20 p-4">
                    <div class="flex">
                        <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-400" />
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Breadcrumb schema is automatically generated based on your page hierarchy and URL structure.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex justify-end">
            <x-filament::button
                color="primary"
                size="lg"
                disabled
            >
                Save Changes
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
