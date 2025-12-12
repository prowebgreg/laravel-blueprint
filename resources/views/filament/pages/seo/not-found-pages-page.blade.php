<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Configuration Section --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    404 Configuration
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure how your site handles 404 errors
                </p>
            </div>

            <div class="px-6 py-4 space-y-4">
                {{-- Custom 404 Page --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Custom 404 Page
                    </label>
                    <select disabled class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <option>Select a page (placeholder)</option>
                        <option>Default 404 Page</option>
                        <option>Custom Error Page</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Choose which page to display when a 404 error occurs
                    </p>
                </div>

                {{-- Enable 404 Logging --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" disabled checked class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    </div>
                    <div class="ml-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Enable 404 Logging
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Track all 404 errors for analysis and redirect creation
                        </p>
                    </div>
                </div>

                {{-- Auto-suggest Redirects --}}
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" disabled class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    </div>
                    <div class="ml-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Auto-suggest Redirects
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Automatically suggest redirect targets for frequently accessed 404 URLs
                        </p>
                    </div>
                </div>

                {{-- Log Retention Period --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Log Retention Period
                    </label>
                    <select disabled class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <option>7 days</option>
                        <option selected>30 days</option>
                        <option>90 days</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        How long to keep 404 error logs
                    </p>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                <x-filament::button disabled>
                    Save Settings (Placeholder)
                </x-filament::button>
            </div>
        </div>

        {{-- Recent 404 Hits Section --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Recent 404 Hits
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Most frequently accessed missing pages
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                URL
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Hits
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Last Seen
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $hits = [
                                ['url' => '/old-services-page', 'count' => 142, 'last' => '2 hours ago', 'badge' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'],
                                ['url' => '/blog/old-post', 'count' => 87, 'last' => '5 hours ago', 'badge' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400'],
                                ['url' => '/missing-image.jpg', 'count' => 56, 'last' => '1 day ago', 'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'],
                                ['url' => '/contact-us-old', 'count' => 34, 'last' => '2 days ago', 'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'],
                                ['url' => '/wp-admin', 'count' => 12, 'last' => '3 days ago', 'badge' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'],
                            ];
                        @endphp
                        @foreach($hits as $hit)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $hit['url'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $hit['badge'] }}">
                                        {{ $hit['count'] }} hits
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $hit['last'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <button type="button" disabled class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 cursor-not-allowed opacity-50">
                                        Create Redirect
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
