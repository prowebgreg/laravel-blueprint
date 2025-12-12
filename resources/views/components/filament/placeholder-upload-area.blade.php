{{--
    Placeholder Upload Area Component

    A reusable upload area placeholder for the Media Library pages in the Filament admin panel.
    This component provides a consistent, accessible drop zone UI that is non-functional until
    the full media upload functionality is implemented.

    Usage:
        <x-filament.placeholder-upload-area
            title="Upload images"
            description="Drag and drop JPG, PNG, WebP files here, or click to browse"
        />

    Props:
        - title: The main upload action text (default: "Upload files")
        - description: File type hints or instructions (default: "Drag and drop files here, or click to browse")
        - icon: Heroicon name for the upload icon (default: "heroicon-o-cloud-arrow-up")
--}}

@props([
    'title' => 'Upload files',
    'description' => 'Drag and drop files here, or click to browse',
    'icon' => 'heroicon-o-cloud-arrow-up',
])

<div
    {{ $attributes->merge(['class' => 'border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800/70 focus-within:ring-2 focus-within:ring-primary-500 dark:focus-within:ring-primary-600 focus-within:border-primary-500 dark:focus-within:border-primary-600 transition-colors cursor-pointer']) }}
    role="button"
    tabindex="0"
    aria-label="{{ $title }} (placeholder - not functional yet)"
    aria-disabled="true"
>
    <x-filament::icon
        :icon="$icon"
        class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500 mb-3"
        aria-hidden="true"
    />
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
        <strong>{{ $title }}</strong> (placeholder - not functional yet)
    </p>
    <p class="text-xs text-gray-500 dark:text-gray-400">
        {{ $description }}
    </p>
</div>
