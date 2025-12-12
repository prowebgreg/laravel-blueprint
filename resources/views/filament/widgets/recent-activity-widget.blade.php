<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Recent Activity
        </x-slot>

        <x-slot name="description">
            Latest updates across pages, blog posts, and services
        </x-slot>

        <div class="space-y-2">
            @forelse ($this->getRecentActivity() as $item)
                <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $item['name'] }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                {{ $item['type'] }}
                            </span>
                            @if ($item['status'] === 'published')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-800 dark:text-green-100">
                                    Published
                                </span>
                            @elseif ($item['status'] === 'draft')
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                    Draft
                                </span>
                            @elseif ($item['status'] === 'scheduled')
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                    Scheduled
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            /{{ $item['slug'] }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $item['updated_at']->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No recent activity found. Start creating content to see updates here.
                    </p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
