<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Models\BlogPost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('BlogPostSeeder is disabled in production environments.');

            return;
        }

        $this->createGettingStartedPost();
        $this->createBestPracticesPost();
        $this->createWhyLaravelPost();
        $this->createUpcomingFeaturesPost();
    }

    /**
     * Create the Getting Started blog post with full content blocks and SEO data.
     */
    private function createGettingStartedPost(): void
    {
        BlogPost::query()->firstOrCreate(
            ['slug' => 'getting-started-with-blueprint-cms'],
            [
                'name' => 'Getting Started with Blueprint CMS',
                'status' => ContentStatus::Published,
                'content_blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Getting Started with Blueprint CMS',
                            'lede' => 'Learn how to set up and configure Blueprint CMS for your next web development project. This comprehensive guide covers everything from installation to your first content deployment.',
                            'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1920&h=1080',
                            'cta_button_text' => 'Read the Guide',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'heading' => 'Ready to Get Started?',
                            'lede' => 'Join our community of developers building better websites with Blueprint CMS. Get the latest updates, tutorials, and best practices delivered to your inbox.',
                            'cta_button_text' => 'Subscribe to Newsletter',
                        ],
                    ],
                ],
                'meta_title' => 'Getting Started with Blueprint CMS - A Complete Guide',
                'meta_description' => 'Learn how to set up Blueprint CMS from scratch. This beginner-friendly guide covers installation, configuration, and creating your first content.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/blog/getting-started-with-blueprint-cms',
                'og_title' => 'Getting Started with Blueprint CMS - A Complete Guide',
                'og_description' => 'Learn how to set up Blueprint CMS from scratch. This beginner-friendly guide covers installation, configuration, and creating your first content.',
                'og_type' => OgType::Article,
                'og_image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&h=630',
                'twitter_title' => 'Getting Started with Blueprint CMS - A Complete Guide',
                'twitter_description' => 'Learn how to set up Blueprint CMS from scratch. This beginner-friendly guide covers installation, configuration, and creating your first content.',
                'twitter_image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Blog', 'url' => '/blog'],
                    ['label' => 'Getting Started with Blueprint CMS', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the Best Practices blog post with full content blocks and SEO data.
     */
    private function createBestPracticesPost(): void
    {
        BlogPost::query()->firstOrCreate(
            ['slug' => 'best-practices-code-defined-content-management'],
            [
                'name' => 'Best Practices for Code-Defined Content Management',
                'status' => ContentStatus::Published,
                'content_blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Best Practices for Code-Defined Content Management',
                            'lede' => 'Discover the architectural patterns and development practices that make code-defined content management systems maintainable, scalable, and developer-friendly.',
                            'image' => 'https://images.unsplash.com/photo-1516259762381-22954d7d3ad2?w=1920&h=1080',
                            'cta_button_text' => 'Learn Best Practices',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'heading' => 'Want to Level Up Your Development?',
                            'lede' => 'Get exclusive access to advanced tutorials, code samples, and architectural guides for building better content management systems.',
                            'cta_button_text' => 'Join Our Community',
                        ],
                    ],
                ],
                'meta_title' => 'Best Practices for Code-Defined Content Management',
                'meta_description' => 'Learn the architectural patterns and development practices for building maintainable, scalable code-defined content management systems.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/blog/best-practices-code-defined-content-management',
                'og_title' => 'Best Practices for Code-Defined Content Management',
                'og_description' => 'Learn the architectural patterns and development practices for building maintainable, scalable code-defined content management systems.',
                'og_type' => OgType::Article,
                'og_image' => 'https://images.unsplash.com/photo-1516259762381-22954d7d3ad2?w=1200&h=630',
                'twitter_title' => 'Best Practices for Code-Defined Content Management',
                'twitter_description' => 'Learn the architectural patterns and development practices for building maintainable, scalable code-defined content management systems.',
                'twitter_image' => 'https://images.unsplash.com/photo-1516259762381-22954d7d3ad2?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Blog', 'url' => '/blog'],
                    ['label' => 'Best Practices for Code-Defined Content Management', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the Why Laravel blog post with SEO data only, no content blocks.
     */
    private function createWhyLaravelPost(): void
    {
        BlogPost::query()->firstOrCreate(
            ['slug' => 'why-we-chose-laravel-for-blueprint-cms'],
            [
                'name' => 'Why We Chose Laravel for Blueprint CMS',
                'status' => ContentStatus::Published,
                'content_blocks' => null,
                'meta_title' => 'Why We Chose Laravel for Blueprint CMS',
                'meta_description' => 'Discover why Laravel was the perfect framework choice for building Blueprint CMS. Learn about the technical advantages and ecosystem benefits.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/blog/why-we-chose-laravel-for-blueprint-cms',
                'og_title' => 'Why We Chose Laravel for Blueprint CMS',
                'og_description' => 'Discover why Laravel was the perfect framework choice for building Blueprint CMS. Learn about the technical advantages and ecosystem benefits.',
                'og_type' => OgType::Article,
                'og_image' => 'https://images.unsplash.com/photo-1618477388954-7852f32655ec?w=1200&h=630',
                'twitter_title' => 'Why We Chose Laravel for Blueprint CMS',
                'twitter_description' => 'Discover why Laravel was the perfect framework choice for building Blueprint CMS. Learn about the technical advantages and ecosystem benefits.',
                'twitter_image' => 'https://images.unsplash.com/photo-1618477388954-7852f32655ec?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Blog', 'url' => '/blog'],
                    ['label' => 'Why We Chose Laravel for Blueprint CMS', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the Upcoming Features blog post as draft with minimal data.
     */
    private function createUpcomingFeaturesPost(): void
    {
        BlogPost::query()->firstOrCreate(
            ['slug' => 'upcoming-features-blueprint-cms'],
            [
                'name' => 'Upcoming Features in Blueprint CMS',
                'status' => ContentStatus::Draft,
                'content_blocks' => null,
                'meta_title' => null,
                'meta_description' => null,
                'meta_author' => null,
                'meta_robots' => false,
                'canonical_url' => null,
                'og_title' => null,
                'og_description' => null,
                'og_type' => OgType::Article,
                'og_image' => null,
                'twitter_title' => null,
                'twitter_description' => null,
                'twitter_image' => null,
                'breadcrumbs' => null,
            ]
        );
    }
}
