<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Placeholder Upload Area --}}
        <x-filament.placeholder-upload-area
            title="Upload video files"
            description="Drag and drop MP4, WebM, or MOV files here, or click to browse"
        />

        {{-- Filament Table Widget --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
