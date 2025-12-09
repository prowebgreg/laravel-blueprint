<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('PageSeeder is disabled in production environments.');

            return;
        }

        $this->createHomePage();
        $this->createAboutPage();
        $this->createContactPage();
        $this->createServicesPage();
        $this->createPrivacyPolicyPage();
        $this->createTermsOfServicePage();
    }

    /**
     * Create the Home page with full content blocks and SEO data.
     */
    private function createHomePage(): void
    {
        Page::query()->firstOrCreate(
            ['slug' => 'home'],
            [
                'name' => 'Home',
                'template' => 'home',
                'status' => ContentStatus::Published,
                'content_blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Build Better Websites with The Blueprint CMS',
                            'lede' => 'A high-performance, developer-first Content Management System designed for professional bespoke website development.',
                            'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1920&h=1080',
                            'cta_button_text' => 'Get Started',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'heading' => 'Ready to Transform Your Web Development?',
                            'lede' => 'Join forward-thinking developers who choose code-defined structure over database configuration.',
                            'cta_button_text' => 'Start Building Today',
                        ],
                    ],
                ],
                'meta_title' => 'The Blueprint CMS - Developer-First Content Management',
                'meta_description' => 'High-performance, strictly-typed CMS built on Laravel 12 and Filament V3. Code-defined schema, optimized for professional development teams.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url'),
                'og_title' => 'The Blueprint CMS - Developer-First Content Management',
                'og_description' => 'High-performance, strictly-typed CMS built on Laravel 12 and Filament V3. Code-defined schema, optimized for professional development teams.',
                'og_type' => OgType::Website,
                'og_image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&h=630',
                'twitter_title' => 'The Blueprint CMS - Developer-First Content Management',
                'twitter_description' => 'High-performance, strictly-typed CMS built on Laravel 12 and Filament V3. Code-defined schema, optimized for professional development teams.',
                'twitter_image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                ],
            ]
        );
    }

    /**
     * Create the About page with content blocks and SEO data.
     */
    private function createAboutPage(): void
    {
        Page::query()->firstOrCreate(
            ['slug' => 'about'],
            [
                'name' => 'About',
                'template' => 'default',
                'status' => ContentStatus::Published,
                'content_blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Built by Developers, for Developers',
                            'lede' => 'The Blueprint CMS was created to solve the frustrations of working with bloated, database-driven content management systems.',
                            'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1920&h=1080',
                            'cta_button_text' => 'Learn More',
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'heading' => 'Want to Learn More?',
                            'lede' => 'Get in touch with our team to see how Blueprint CMS can work for your project.',
                            'cta_button_text' => 'Contact Us',
                        ],
                    ],
                ],
                'meta_title' => 'About The Blueprint CMS - Our Mission & Values',
                'meta_description' => 'Learn about The Blueprint CMS mission to empower developers with code-defined architecture and high-performance content management.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/about',
                'og_title' => 'About The Blueprint CMS - Our Mission & Values',
                'og_description' => 'Learn about The Blueprint CMS mission to empower developers with code-defined architecture and high-performance content management.',
                'og_type' => OgType::Website,
                'og_image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&h=630',
                'twitter_title' => 'About The Blueprint CMS - Our Mission & Values',
                'twitter_description' => 'Learn about The Blueprint CMS mission to empower developers with code-defined architecture and high-performance content management.',
                'twitter_image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'About', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the Contact page with content blocks and SEO data.
     */
    private function createContactPage(): void
    {
        Page::query()->firstOrCreate(
            ['slug' => 'contact'],
            [
                'name' => 'Contact',
                'template' => 'contact',
                'status' => ContentStatus::Published,
                'content_blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Get in Touch',
                            'lede' => 'Have questions about The Blueprint CMS? We\'re here to help you build better websites.',
                            'image' => 'https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1920&h=1080',
                            'cta_button_text' => 'Send Message',
                        ],
                    ],
                ],
                'meta_title' => 'Contact Us - The Blueprint CMS Support',
                'meta_description' => 'Get in touch with The Blueprint CMS team. We\'re here to answer questions and help you succeed with your development projects.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/contact',
                'og_title' => 'Contact Us - The Blueprint CMS Support',
                'og_description' => 'Get in touch with The Blueprint CMS team. We\'re here to answer questions and help you succeed with your development projects.',
                'og_type' => OgType::Website,
                'og_image' => 'https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1200&h=630',
                'twitter_title' => 'Contact Us - The Blueprint CMS Support',
                'twitter_description' => 'Get in touch with The Blueprint CMS team. We\'re here to answer questions and help you succeed with your development projects.',
                'twitter_image' => 'https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1200&h=630',
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Contact', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the Services page as a draft with basic content blocks.
     */
    private function createServicesPage(): void
    {
        Page::query()->firstOrCreate(
            ['slug' => 'services'],
            [
                'name' => 'Services',
                'template' => 'default',
                'status' => ContentStatus::Draft,
                'content_blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Our Services',
                            'lede' => 'Discover how The Blueprint CMS can power your next web development project.',
                            'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1920&h=1080',
                            'cta_button_text' => 'View All Services',
                        ],
                    ],
                ],
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

    /**
     * Create the Privacy Policy page with legal template and minimal content.
     */
    private function createPrivacyPolicyPage(): void
    {
        Page::query()->firstOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'name' => 'Privacy Policy',
                'template' => 'legal',
                'status' => ContentStatus::Published,
                'content_blocks' => null,
                'meta_title' => 'Privacy Policy - The Blueprint CMS',
                'meta_description' => 'Read our privacy policy to understand how we collect, use, and protect your personal information.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/privacy-policy',
                'og_title' => null, // Will mirror meta_title
                'og_description' => null, // Will mirror meta_description
                'og_type' => OgType::Website,
                'og_image' => null,
                'twitter_title' => null, // Will mirror meta_title
                'twitter_description' => null, // Will mirror meta_description
                'twitter_image' => null,
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Privacy Policy', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Create the Terms of Service page with legal template and minimal content.
     */
    private function createTermsOfServicePage(): void
    {
        Page::query()->firstOrCreate(
            ['slug' => 'terms-of-service'],
            [
                'name' => 'Terms of Service',
                'template' => 'legal',
                'status' => ContentStatus::Published,
                'content_blocks' => null,
                'meta_title' => 'Terms of Service - The Blueprint CMS',
                'meta_description' => 'Review our terms of service to understand the rules and regulations for using The Blueprint CMS.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/terms-of-service',
                'og_title' => null, // Will mirror meta_title
                'og_description' => null, // Will mirror meta_description
                'og_type' => OgType::Website,
                'og_image' => null,
                'twitter_title' => null, // Will mirror meta_title
                'twitter_description' => null, // Will mirror meta_description
                'twitter_image' => null,
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Terms of Service', 'url' => null],
                ],
            ]
        );
    }
}
