<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Testimonial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('TestimonialSeeder is disabled in production environments.');

            return;
        }

        $this->createCeoTestimonial();
        $this->createCtoTestimonial();
        $this->createProductManagerTestimonial();
        $this->createLeadDeveloperTestimonial();
        $this->createFreelancerTestimonial();
        $this->createEngineeringManagerTestimonial();
    }

    /**
     * Create the CEO testimonial with full profile and 5-star rating.
     */
    private function createCeoTestimonial(): void
    {
        Testimonial::query()->firstOrCreate(
            ['name' => 'ProWeb Excellence - Sarah Chen'],
            [
                'status' => ContentStatus::Published,
                'author_name' => 'Sarah Chen',
                'author_title' => 'CEO',
                'location' => 'San Francisco, CA',
                'quote' => 'The Blueprint CMS has completely transformed how we manage our web properties. The code-defined schema approach gives our development team full control while keeping content management simple for editors. We\'ve seen a 40% improvement in page load times and our Lighthouse scores are consistently above 95.',
                'rating' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&h=400',
            ]
        );
    }

    /**
     * Create the CTO testimonial with focus on development speed.
     */
    private function createCtoTestimonial(): void
    {
        Testimonial::query()->firstOrCreate(
            ['name' => 'Development Speed - Michael Rodriguez'],
            [
                'status' => ContentStatus::Published,
                'author_name' => 'Michael Rodriguez',
                'author_title' => 'CTO',
                'location' => 'Austin, TX',
                'quote' => 'After years of fighting against bloated WordPress installations and complex database configurations, Blueprint CMS is a breath of fresh air. The developer-first approach with Laravel and Filament means our team can build and iterate faster than ever. Deployment time dropped from hours to minutes.',
                'rating' => 5,
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400',
            ]
        );
    }

    /**
     * Create the Product Manager testimonial highlighting support and documentation.
     */
    private function createProductManagerTestimonial(): void
    {
        Testimonial::query()->firstOrCreate(
            ['name' => 'Great Support - Emily Thompson'],
            [
                'status' => ContentStatus::Published,
                'author_name' => 'Emily Thompson',
                'author_title' => 'Product Manager',
                'location' => 'Seattle, WA',
                'quote' => 'The Filament admin interface is intuitive and our content team picked it up quickly without extensive training. The support has been outstanding - any questions we had were answered promptly and thoroughly. The documentation is clear and comprehensive.',
                'rating' => 4,
                'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&h=400',
            ]
        );
    }

    /**
     * Create the Lead Developer testimonial about performance (no avatar).
     */
    private function createLeadDeveloperTestimonial(): void
    {
        Testimonial::query()->firstOrCreate(
            ['name' => 'Performance Champion - David Kim'],
            [
                'status' => ContentStatus::Published,
                'author_name' => 'David Kim',
                'author_title' => 'Lead Developer',
                'location' => 'New York, NY',
                'quote' => 'The performance metrics speak for themselves. With PostgreSQL JSONB for content blocks and Redis caching, we\'re handling 10x the traffic with half the infrastructure costs. The SSR-only approach means our Core Web Vitals are exceptional - no hydration overhead, no layout shifts.',
                'rating' => 5,
                'avatar' => null,
            ]
        );
    }

    /**
     * Create the Freelancer testimonial as draft (no rating).
     */
    private function createFreelancerTestimonial(): void
    {
        Testimonial::query()->firstOrCreate(
            ['name' => 'Freelance Success - Jessica Martinez'],
            [
                'status' => ContentStatus::Draft,
                'author_name' => 'Jessica Martinez',
                'author_title' => 'Freelance Web Developer',
                'location' => 'Miami, FL',
                'quote' => 'As a freelancer, I need tools that let me work efficiently across multiple client projects. Blueprint CMS\'s clean architecture and strict typing catch bugs before they reach production. The Pest testing framework integration makes TDD a joy rather than a chore.',
                'rating' => null,
                'avatar' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=400&h=400',
            ]
        );
    }

    /**
     * Create the Engineering Manager testimonial as draft (no rating, no avatar).
     */
    private function createEngineeringManagerTestimonial(): void
    {
        Testimonial::query()->firstOrCreate(
            ['name' => 'Enterprise Ready - John Anderson'],
            [
                'status' => ContentStatus::Draft,
                'author_name' => 'John Anderson',
                'author_title' => 'Engineering Manager',
                'location' => 'Boston, MA',
                'quote' => 'We evaluated several CMS platforms for our enterprise needs. Blueprint CMS stood out for its maintainability and clear separation of concerns. The Action/Service pattern keeps our codebase organized, and the polymorphic relationship engine handles complex content hierarchies elegantly.',
                'rating' => null,
                'avatar' => null,
            ]
        );
    }
}
