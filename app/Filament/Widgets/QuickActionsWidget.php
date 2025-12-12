<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Quick actions widget for the Dashboard.
 *
 * Displays shortcut links to common content creation tasks.
 * URLs are placeholders until respective resources are implemented.
 */
class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    /**
     * Get quick action links for the dashboard.
     *
     * Note: Using complete Tailwind class strings instead of dynamic interpolation
     * because Tailwind's JIT compiler cannot detect dynamically constructed classes.
     *
     * @return array<int, array{label: string, url: string, icon: string, linkClasses: string, iconClasses: string, description: string}>
     */
    public function getQuickActions(): array
    {
        return [
            [
                'label' => 'Create Page',
                'url' => '#', // Placeholder until PageResource is created
                'icon' => 'heroicon-o-document-plus',
                'linkClasses' => 'hover:border-primary-500 hover:bg-primary-50 dark:hover:border-primary-500 dark:hover:bg-primary-900/10',
                'iconClasses' => 'bg-primary-100 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400',
                'description' => 'Add a new static page',
            ],
            [
                'label' => 'Create Post',
                'url' => '#', // Placeholder until BlogPostResource is created
                'icon' => 'heroicon-o-newspaper',
                'linkClasses' => 'hover:border-success-500 hover:bg-success-50 dark:hover:border-success-500 dark:hover:bg-success-900/10',
                'iconClasses' => 'bg-success-100 text-success-600 dark:bg-success-900/20 dark:text-success-400',
                'description' => 'Write a new blog post',
            ],
            [
                'label' => 'Upload Media',
                'url' => '#', // Placeholder until Media Library is created
                'icon' => 'heroicon-o-photo',
                'linkClasses' => 'hover:border-warning-500 hover:bg-warning-50 dark:hover:border-warning-500 dark:hover:bg-warning-900/10',
                'iconClasses' => 'bg-warning-100 text-warning-600 dark:bg-warning-900/20 dark:text-warning-400',
                'description' => 'Upload images or files',
            ],
        ];
    }
}
