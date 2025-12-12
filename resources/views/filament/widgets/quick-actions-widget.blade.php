<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Quick Actions
        </x-slot>

        <x-slot name="description">
            Common tasks to get you started
        </x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ($this->getQuickActions() as $action)
                <a
                    href="{{ $action['url'] }}"
                    @if($action['url'] === '#')
                        aria-disabled="true"
                        role="link"
                    @endif
                    class="group flex flex-col items-center justify-center rounded-lg border border-gray-200 p-6 transition-all dark:border-gray-700 {{ $action['linkClasses'] }}"
                >
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full {{ $action['iconClasses'] }}">
                        <x-filament::icon
                            :icon="$action['icon']"
                            class="h-6 w-6"
                        />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $action['label'] }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $action['description'] }}
                    </p>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
