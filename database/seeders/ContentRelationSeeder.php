<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentRelationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('ContentRelationSeeder is disabled in production environments.');

            return;
        }

        $this->linkWebDevelopmentService();
        $this->linkMobileAppDevelopmentService();
        $this->linkUxUiDesignService();
    }

    /**
     * Link Web Development service to FAQs and Testimonials.
     */
    private function linkWebDevelopmentService(): void
    {
        $service = Service::query()->where('slug', 'web-development')->first();

        if ($service === null) {
            $this->command->warn('Web Development service not found - skipping relationship seeding.');

            return;
        }

        // Link FAQs in specific order
        $faqs = [
            'What services do you offer?' => ['order' => 0],
            'What technologies do you work with?' => ['order' => 1],
            'How do I get started?' => ['order' => 2],
        ];

        $faqIds = [];
        foreach ($faqs as $faqName => $pivotData) {
            $faq = Faq::query()->where('name', $faqName)->first();
            if ($faq !== null) {
                $faqIds[$faq->id] = $pivotData;
            }
        }

        if (\count($faqIds) > 0) {
            $service->syncRelated(Faq::class, $faqIds);
        }

        // Link Testimonials in specific order
        $testimonials = [
            'Development Speed - Michael Rodriguez' => ['order' => 0],
            'Performance Champion - David Kim' => ['order' => 1],
            'ProWeb Excellence - Sarah Chen' => ['order' => 2],
        ];

        $testimonialIds = [];
        foreach ($testimonials as $testimonialName => $pivotData) {
            $testimonial = Testimonial::query()->where('name', $testimonialName)->first();
            if ($testimonial !== null) {
                $testimonialIds[$testimonial->id] = $pivotData;
            }
        }

        if (\count($testimonialIds) > 0) {
            $service->syncRelated(Testimonial::class, $testimonialIds);
        }
    }

    /**
     * Link Mobile App Development service to FAQs and Testimonials.
     */
    private function linkMobileAppDevelopmentService(): void
    {
        $service = Service::query()->where('slug', 'mobile-app-development')->first();

        if ($service === null) {
            $this->command->warn('Mobile App Development service not found - skipping relationship seeding.');

            return;
        }

        // Link FAQs in specific order
        $faqs = [
            'How do I get started?' => ['order' => 0],
            'What is your pricing model?' => ['order' => 1],
        ];

        $faqIds = [];
        foreach ($faqs as $faqName => $pivotData) {
            $faq = Faq::query()->where('name', $faqName)->first();
            if ($faq !== null) {
                $faqIds[$faq->id] = $pivotData;
            }
        }

        if (\count($faqIds) > 0) {
            $service->syncRelated(Faq::class, $faqIds);
        }

        // Link Testimonials in specific order
        $testimonials = [
            'ProWeb Excellence - Sarah Chen' => ['order' => 0],
            'Great Support - Emily Thompson' => ['order' => 1],
        ];

        $testimonialIds = [];
        foreach ($testimonials as $testimonialName => $pivotData) {
            $testimonial = Testimonial::query()->where('name', $testimonialName)->first();
            if ($testimonial !== null) {
                $testimonialIds[$testimonial->id] = $pivotData;
            }
        }

        if (\count($testimonialIds) > 0) {
            $service->syncRelated(Testimonial::class, $testimonialIds);
        }
    }

    /**
     * Link UX/UI Design service to FAQs and Testimonials.
     */
    private function linkUxUiDesignService(): void
    {
        $service = Service::query()->where('slug', 'ux-ui-design')->first();

        if ($service === null) {
            $this->command->warn('UX/UI Design service not found - skipping relationship seeding.');

            return;
        }

        // Link FAQs in specific order
        $faqs = [
            'What services do you offer?' => ['order' => 0],
            'What kind of support do you provide?' => ['order' => 1],
        ];

        $faqIds = [];
        foreach ($faqs as $faqName => $pivotData) {
            $faq = Faq::query()->where('name', $faqName)->first();
            if ($faq !== null) {
                $faqIds[$faq->id] = $pivotData;
            }
        }

        if (\count($faqIds) > 0) {
            $service->syncRelated(Faq::class, $faqIds);
        }

        // Link Testimonials in specific order
        $testimonials = [
            'Great Support - Emily Thompson' => ['order' => 0],
        ];

        $testimonialIds = [];
        foreach ($testimonials as $testimonialName => $pivotData) {
            $testimonial = Testimonial::query()->where('name', $testimonialName)->first();
            if ($testimonial !== null) {
                $testimonialIds[$testimonial->id] = $pivotData;
            }
        }

        if (\count($testimonialIds) > 0) {
            $service->syncRelated(Testimonial::class, $testimonialIds);
        }
    }
}
