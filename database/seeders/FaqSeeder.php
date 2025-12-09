<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('FaqSeeder is disabled in production environments.');

            return;
        }

        $this->createServicesFaq();
        $this->createGettingStartedFaq();
        $this->createPricingFaq();
        $this->createSupportFaq();
        $this->createTechnicalFaq();
        $this->createDraftFaq();
    }

    /**
     * Create the "What services do you offer?" FAQ.
     */
    private function createServicesFaq(): void
    {
        Faq::query()->firstOrCreate(
            ['name' => 'What services do you offer?'],
            [
                'status' => ContentStatus::Published,
                'question' => 'What services do you offer?',
                'answer' => 'We offer a comprehensive range of web development services including custom web application development using Laravel and PHP, mobile app development for iOS and Android platforms, UX/UI design services, and technical consulting. Our team specializes in building high-performance, scalable solutions tailored to your business needs.',
            ]
        );
    }

    /**
     * Create the "How do I get started?" FAQ.
     */
    private function createGettingStartedFaq(): void
    {
        Faq::query()->firstOrCreate(
            ['name' => 'How do I get started?'],
            [
                'status' => ContentStatus::Published,
                'question' => 'How do I get started with your services?',
                'answer' => 'Getting started is easy! Simply reach out to us through our contact form or schedule a consultation call. We\'ll discuss your project requirements, timeline, and goals. After our initial conversation, we\'ll provide you with a detailed proposal and project plan tailored to your specific needs.',
            ]
        );
    }

    /**
     * Create the "What is your pricing model?" FAQ.
     */
    private function createPricingFaq(): void
    {
        Faq::query()->firstOrCreate(
            ['name' => 'What is your pricing model?'],
            [
                'status' => ContentStatus::Published,
                'question' => 'What is your pricing model?',
                'answer' => 'Our pricing varies depending on the scope and complexity of your project. We offer both fixed-price projects and hourly rates for ongoing development work. During our initial consultation, we\'ll provide a transparent quote based on your specific requirements and budget considerations.',
            ]
        );
    }

    /**
     * Create the "What kind of support do you provide?" FAQ.
     */
    private function createSupportFaq(): void
    {
        Faq::query()->firstOrCreate(
            ['name' => 'What kind of support do you provide?'],
            [
                'status' => ContentStatus::Published,
                'question' => 'What kind of support do you provide after project completion?',
                'answer' => 'We provide comprehensive post-launch support including bug fixes, security updates, feature enhancements, and technical documentation. Our team offers flexible maintenance packages to ensure your application continues to perform optimally and stays up-to-date with the latest technologies and security standards.',
            ]
        );
    }

    /**
     * Create the "What technologies do you work with?" FAQ.
     */
    private function createTechnicalFaq(): void
    {
        Faq::query()->firstOrCreate(
            ['name' => 'What technologies do you work with?'],
            [
                'status' => ContentStatus::Published,
                'question' => 'What technologies and frameworks do you work with?',
                'answer' => 'Our primary technology stack includes PHP 8.3+, Laravel 12, PostgreSQL, Redis, and modern frontend technologies. We also work with Filament for admin panels, AWS services for cloud infrastructure, and various modern JavaScript frameworks. We stay current with the latest best practices and industry standards to deliver cutting-edge solutions.',
            ]
        );
    }

    /**
     * Create a draft FAQ with minimal data for testing purposes.
     */
    private function createDraftFaq(): void
    {
        Faq::query()->firstOrCreate(
            ['name' => 'Draft FAQ - Work in Progress'],
            [
                'status' => ContentStatus::Draft,
                'question' => null,
                'answer' => null,
            ]
        );
    }
}
