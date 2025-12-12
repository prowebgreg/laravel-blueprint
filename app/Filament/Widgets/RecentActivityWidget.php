<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Service;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '30s';

    /**
     * Get recent activity across all content types.
     *
     * @return Collection<int, array{id: int, type: string, name: string, slug: string, status: string, updated_at: \Illuminate\Support\Carbon}>
     */
    public function getRecentActivity(): Collection
    {
        $pages = Page::query()
            ->select('id', 'name', 'slug', 'status', 'updated_at')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($page) => [
                'id' => $page->id,
                'type' => 'Page',
                'name' => $page->name,
                'slug' => $page->slug,
                'status' => $page->status->value,
                'updated_at' => $page->updated_at,
            ]);

        $blogPosts = BlogPost::query()
            ->select('id', 'name', 'slug', 'status', 'updated_at')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'type' => 'Blog Post',
                'name' => $post->name,
                'slug' => $post->slug,
                'status' => $post->status->value,
                'updated_at' => $post->updated_at,
            ]);

        $services = Service::query()
            ->select('id', 'name', 'slug', 'status', 'updated_at')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn ($service) => [
                'id' => $service->id,
                'type' => 'Service',
                'name' => $service->name,
                'slug' => $service->slug,
                'status' => $service->status->value,
                'updated_at' => $service->updated_at,
            ]);

        return $pages
            ->concat($blogPosts)
            ->concat($services)
            ->sortByDesc('updated_at')
            ->take(10);
    }
}
