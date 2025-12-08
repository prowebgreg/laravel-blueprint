<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('ServiceSeeder is disabled in production environments.');

            return;
        }

        $this->createWebDevelopmentService();
        $this->createMobileAppDevelopmentService();
        $this->createUxUiDesignService();
        $this->createConsultingService();
    }

    /**
     * Create the Web Development service with full content blocks and SEO data.
     */
    private function createWebDevelopmentService(): void
    {
        Service::query()->firstOrCreate(
            ['slug' => 'web-development'],
            [
                'name' => 'Web Development',
                'status' => ContentStatus::Published,
                'content_blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Enterprise Web Development Solutions',
                            'lede' => 'Build scalable, high-performance web applications with modern technologies and best practices. Our expert team delivers custom solutions tailored to your business needs.',
                            'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1920&h=1080',
                            'cta_button_text' => 'Start Your Project',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'heading' => 'Ready to Build Something Amazing?',
                            'lede' => 'Let\'s discuss your web development needs and create a solution that drives your business forward.',
                            'cta_button_text' => 'Get Started Today',
                        ],
                    ],
                ],
                'meta_title' => 'Web Development Services - Custom Web Applications',
                'meta_description' => 'Professional web development services using Laravel, PHP, and modern frameworks. Build scalable, high-performance applications for your business.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/services/web-development',
                'og_title' => 'Web Development Services - Custom Web Applications',
                'og_description' => 'Professional web development services using Laravel, PHP, and modern frameworks. Build scalable, high-performance applications for your business.',
                'og_type' => OgType::Website,
                'og_image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&h=630',
                'twitter_title' => 'Web Development Services - Custom Web Applications',
                'twitter_description' => 'Professional web development services using Laravel, PHP, and modern frameworks. Build scalable, high-performance applications for your business.',
                'twitter_image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Services', 'url' => '/services'],
                    ['label' => 'Web Development', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the Mobile App Development service with full content blocks and SEO data.
     */
    private function createMobileAppDevelopmentService(): void
    {
        Service::query()->firstOrCreate(
            ['slug' => 'mobile-app-development'],
            [
                'name' => 'Mobile App Development',
                'status' => ContentStatus::Published,
                'content_blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Native & Cross-Platform Mobile Apps',
                            'lede' => 'Create engaging mobile experiences for iOS and Android. From concept to deployment, we build apps that users love with cutting-edge technologies.',
                            'image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1920&h=1080',
                            'cta_button_text' => 'Build Your App',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'heading' => 'Turn Your App Idea Into Reality',
                            'lede' => 'Partner with our mobile development experts to create an app that stands out in the marketplace.',
                            'cta_button_text' => 'Schedule Consultation',
                        ],
                    ],
                ],
                'meta_title' => 'Mobile App Development - iOS & Android Apps',
                'meta_description' => 'Expert mobile app development for iOS and Android. Build native and cross-platform apps with modern frameworks and exceptional user experiences.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/services/mobile-app-development',
                'og_title' => 'Mobile App Development - iOS & Android Apps',
                'og_description' => 'Expert mobile app development for iOS and Android. Build native and cross-platform apps with modern frameworks and exceptional user experiences.',
                'og_type' => OgType::Website,
                'og_image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1200&h=630',
                'twitter_title' => 'Mobile App Development - iOS & Android Apps',
                'twitter_description' => 'Expert mobile app development for iOS and Android. Build native and cross-platform apps with modern frameworks and exceptional user experiences.',
                'twitter_image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Services', 'url' => '/services'],
                    ['label' => 'Mobile App Development', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the UX/UI Design service with SEO data only, no content blocks.
     */
    private function createUxUiDesignService(): void
    {
        Service::query()->firstOrCreate(
            ['slug' => 'ux-ui-design'],
            [
                'name' => 'UX/UI Design',
                'status' => ContentStatus::Published,
                'content_blocks' => null,
                'meta_title' => 'UX/UI Design Services - User Experience & Interface Design',
                'meta_description' => 'Professional UX/UI design services focused on creating intuitive, engaging digital experiences that delight users and drive conversions.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/services/ux-ui-design',
                'og_title' => 'UX/UI Design Services - User Experience & Interface Design',
                'og_description' => 'Professional UX/UI design services focused on creating intuitive, engaging digital experiences that delight users and drive conversions.',
                'og_type' => OgType::Website,
                'og_image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1200&h=630',
                'twitter_title' => 'UX/UI Design Services - User Experience & Interface Design',
                'twitter_description' => 'Professional UX/UI design services focused on creating intuitive, engaging digital experiences that delight users and drive conversions.',
                'twitter_image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Services', 'url' => '/services'],
                    ['label' => 'UX/UI Design', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the Consulting service as draft with minimal data.
     */
    private function createConsultingService(): void
    {
        Service::query()->firstOrCreate(
            ['slug' => 'consulting'],
            [
                'name' => 'Consulting',
                'status' => ContentStatus::Draft,
                'content_blocks' => null,
                'meta_title' => null,
                'meta_description' => null,
                'meta_author' => null,
                'meta_robots' => false,
                'canonical_url' => null,
                'og_title' => null,
                'og_description' => null,
                'og_type' => OgType::Website,
                'og_image' => null,
                'twitter_title' => null,
                'twitter_description' => null,
                'twitter_image' => null,
                'breadcrumbs' => null,
            ]
        );
    }
}
