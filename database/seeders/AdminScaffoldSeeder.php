<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\OgType;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Seeds the database with placeholder data for the Filament admin panel scaffold.
 *
 * Creates:
 * - 5 Pages (Home, About, Contact - published; Privacy Policy, Terms of Service - draft)
 * - 4 Services (2 published, 2 draft)
 * - 4 Blog Posts (2 published, 2 draft)
 * - 4 FAQs (3 published, 1 draft) - with Blueprint CMS-specific content
 * - 4 Testimonials (3 published, 1 draft) - with realistic tech professional profiles
 * - 4 Redirects (3 active with 2 permanent + 1 temporary, 1 inactive permanent)
 *
 * Note: This seeder uses specific content for admin panel demo purposes rather than
 * factory-generated random data to ensure recognizable, professional demo content.
 */
class AdminScaffoldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('AdminScaffoldSeeder is disabled in production environments.');

            return;
        }

        $this->seedPages();
        $this->seedServices();
        $this->seedBlogPosts();
        $this->seedFaqs();
        $this->seedTestimonials();
        $this->seedRedirects();
    }

    /**
     * Seed 5 pages with recognizable names for admin panel demo.
     */
    private function seedPages(): void
    {
        // Home Page (published)
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

        // About Page (published)
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

        // Contact Page (published)
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

        // Privacy Policy Page (draft)
        Page::query()->firstOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'name' => 'Privacy Policy',
                'template' => 'legal',
                'status' => ContentStatus::Draft,
                'content_blocks' => null,
                'meta_title' => 'Privacy Policy - The Blueprint CMS',
                'meta_description' => 'Read our privacy policy to understand how we collect, use, and protect your personal information.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/privacy-policy',
                'og_title' => null,
                'og_description' => null,
                'og_type' => OgType::Website,
                'og_image' => null,
                'twitter_title' => null,
                'twitter_description' => null,
                'twitter_image' => null,
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Privacy Policy', 'url' => null],
                ],
            ]
        );

        // Terms of Service Page (draft)
        Page::query()->firstOrCreate(
            ['slug' => 'terms-of-service'],
            [
                'name' => 'Terms of Service',
                'template' => 'legal',
                'status' => ContentStatus::Draft,
                'content_blocks' => null,
                'meta_title' => 'Terms of Service - The Blueprint CMS',
                'meta_description' => 'Review our terms of service to understand the rules and regulations for using The Blueprint CMS.',
                'meta_author' => 'ProWeb.ai',
                'meta_robots' => true,
                'canonical_url' => config('app.url').'/terms-of-service',
                'og_title' => null,
                'og_description' => null,
                'og_type' => OgType::Website,
                'og_image' => null,
                'twitter_title' => null,
                'twitter_description' => null,
                'twitter_image' => null,
                'breadcrumbs' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'Terms of Service', 'url' => null],
                ],
            ]
        );
    }

    /**
     * Seed 4 services (2 published, 2 draft).
     */
    private function seedServices(): void
    {
        $services = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'status' => ContentStatus::Published, 'full' => true],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'status' => ContentStatus::Published, 'full' => true],
            ['name' => 'SEO Consulting', 'slug' => 'seo-consulting', 'status' => ContentStatus::Draft, 'full' => false],
            ['name' => 'Brand Strategy', 'slug' => 'brand-strategy', 'status' => ContentStatus::Draft, 'full' => false],
        ];

        foreach ($services as $service) {
            if (Service::query()->where('slug', $service['slug'])->exists()) {
                continue;
            }

            $factory = Service::factory()->state(['status' => $service['status']]);
            if ($service['full']) {
                $factory = $factory->withContentBlocks()->withSeoData();
            }
            $factory->create(['name' => $service['name']]);
        }
    }

    /**
     * Seed 4 blog posts (2 published, 2 draft).
     */
    private function seedBlogPosts(): void
    {
        $posts = [
            ['name' => 'Getting Started with The Blueprint CMS', 'slug' => 'getting-started-with-the-blueprint-cms', 'status' => ContentStatus::Published, 'full' => true],
            ['name' => 'Why Code-Defined Schema Matters for Modern Web Development', 'slug' => 'why-code-defined-schema-matters-for-modern-web-development', 'status' => ContentStatus::Published, 'full' => true],
            ['name' => 'Optimizing Laravel Performance with Redis', 'slug' => 'optimizing-laravel-performance-with-redis', 'status' => ContentStatus::Draft, 'full' => false],
            ['name' => 'Building Headless CMS APIs with Laravel', 'slug' => 'building-headless-cms-apis-with-laravel', 'status' => ContentStatus::Draft, 'full' => false],
        ];

        foreach ($posts as $post) {
            if (BlogPost::query()->where('slug', $post['slug'])->exists()) {
                continue;
            }

            $factory = BlogPost::factory()->state(['status' => $post['status']]);
            if ($post['full']) {
                $factory = $factory->withContentBlocks()->withSeoData();
            }
            $factory->create(['name' => $post['name']]);
        }
    }

    /**
     * Seed 4 FAQs (3 published, 1 draft).
     *
     * Uses specific Q&A content rather than factory-generated data for admin panel demo purposes.
     */
    private function seedFaqs(): void
    {
        $faqs = [
            [
                'name' => 'What is The Blueprint CMS?',
                'question' => 'What is The Blueprint CMS?',
                'answer' => 'The Blueprint CMS is a high-performance, developer-first Content Management System built on Laravel 12 and Filament V3. It uses code-defined schema instead of database configuration, giving developers full control over content structure.',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'How is Blueprint different from WordPress?',
                'question' => 'How is Blueprint different from WordPress?',
                'answer' => 'Unlike WordPress which stores structure in the database, Blueprint CMS defines all schema in code. This provides type safety, version control, and eliminates the need for database migrations when changing content structure. It\'s built for professional development teams who value code quality and performance.',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'What tech stack does Blueprint use?',
                'question' => 'What tech stack does Blueprint use?',
                'answer' => 'Blueprint CMS is built on Laravel 12, PostgreSQL 17, Redis, Filament V3 for the admin panel, and uses AWS S3 with CloudFront for media storage and delivery. All PHP code uses strict types and follows PSR-12 standards.',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Can I migrate from WordPress?',
                'question' => 'Can I migrate my existing WordPress site to Blueprint CMS?',
                'answer' => 'Yes, we provide migration tools and services to help you transition from WordPress to Blueprint CMS. The process involves mapping your WordPress content structure to Blueprint models and migrating content via our API.',
                'status' => ContentStatus::Draft,
            ],
        ];

        foreach ($faqs as $faq) {
            if (Faq::query()->where('name', $faq['name'])->exists()) {
                continue;
            }

            Faq::factory()
                ->state(['status' => $faq['status']])
                ->create([
                    'name' => $faq['name'],
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                ]);
        }
    }

    /**
     * Seed 4 testimonials (3 published, 1 draft).
     *
     * Uses specific profile data rather than factory-generated data for admin panel demo purposes.
     */
    private function seedTestimonials(): void
    {
        $testimonials = [
            [
                'name' => 'Sarah Johnson - Tech Lead',
                'author_name' => 'Sarah Johnson',
                'author_title' => 'Tech Lead',
                'location' => 'San Francisco, CA',
                'rating' => 5,
                'quote' => 'Blueprint CMS completely changed how we build websites. The code-defined schema gives us the type safety and developer experience we always wanted. Our team is more productive and our sites are faster.',
                'avatar' => 'https://i.pravatar.cc/400?img=5',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Michael Chen - CTO',
                'author_name' => 'Michael Chen',
                'author_title' => 'CTO',
                'location' => 'Austin, TX',
                'rating' => 5,
                'quote' => 'After years of fighting with WordPress, Blueprint CMS is a breath of fresh air. The performance improvements alone justified the switch, but the developer experience is what keeps our team happy.',
                'avatar' => 'https://i.pravatar.cc/400?img=12',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Emily Rodriguez - Senior Developer',
                'author_name' => 'Emily Rodriguez',
                'author_title' => 'Senior Developer',
                'location' => 'New York, NY',
                'rating' => 5,
                'quote' => 'The Filament admin panel is incredibly intuitive and the code-first approach means we can version control everything. No more database surprises in production deployments.',
                'avatar' => 'https://i.pravatar.cc/400?img=9',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'David Park - Lead Engineer',
                'author_name' => 'David Park',
                'author_title' => 'Lead Engineer',
                'location' => 'Seattle, WA',
                'rating' => 4,
                'quote' => 'We migrated three sites to Blueprint CMS and saw immediate performance gains. The learning curve was minimal thanks to excellent documentation.',
                'avatar' => 'https://i.pravatar.cc/400?img=15',
                'status' => ContentStatus::Draft,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            if (Testimonial::query()->where('name', $testimonial['name'])->exists()) {
                continue;
            }

            $status = $testimonial['status'];
            unset($testimonial['status']);

            Testimonial::factory()
                ->state(['status' => $status])
                ->create($testimonial);
        }
    }

    /**
     * Seed 4 redirects (mix of permanent/temporary, active/inactive).
     */
    private function seedRedirects(): void
    {
        $redirects = [
            [
                'source_path' => '/old-home',
                'target_path' => '/',
                'type' => 'permanent',
                'active' => true,
                'notes' => 'Redirect from old homepage URL',
                'hits' => 247,
                'last_hit_days_ago' => 2,
            ],
            [
                'source_path' => '/blog/2023/introducing-blueprint',
                'target_path' => '/blog/getting-started-with-the-blueprint-cms',
                'type' => 'permanent',
                'active' => true,
                'notes' => 'Redirect old blog post URL to new slug format',
                'hits' => 89,
                'last_hit_days_ago' => 7,
            ],
            [
                'source_path' => '/promo',
                'target_path' => '/services/web-development',
                'type' => 'temporary',
                'active' => true,
                'notes' => 'Temporary promotional campaign redirect',
                'hits' => 156,
                'last_hit_days_ago' => 5,
            ],
            [
                'source_path' => '/legacy/old-contact',
                'target_path' => '/contact',
                'type' => 'permanent',
                'active' => false,
                'notes' => 'Disabled - testing new contact form',
                'hits' => 12,
                'last_hit_days_ago' => 30,
            ],
        ];

        foreach ($redirects as $redirect) {
            if (Redirect::query()->where('source_path', $redirect['source_path'])->exists()) {
                continue;
            }

            $factory = Redirect::factory();
            $factory = $redirect['type'] === 'permanent' ? $factory->permanent() : $factory->temporary();
            $factory = $redirect['active'] ? $factory->active() : $factory->inactive();

            $factory->create([
                'source_path' => $redirect['source_path'],
                'target_path' => $redirect['target_path'],
                'notes' => $redirect['notes'],
                'hits' => $redirect['hits'],
                'last_hit_at' => now()->subDays($redirect['last_hit_days_ago']),
            ]);
        }
    }
}
