<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faq>
 */
class FaqFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Faq>
     */
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'status' => ContentStatus::Draft,
            'question' => null,
            'answer' => null,
        ];
    }

    /**
     * Indicate that the FAQ should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Published,
        ]);
    }

    /**
     * Indicate that the FAQ should be a draft (explicit state).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Draft,
        ]);
    }

    /**
     * Fill question and answer fields with sample FAQ content.
     */
    public function withQuestionAndAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'question' => fake()->randomElement([
                'What services do you offer?',
                'How long does a typical project take?',
                'What is your pricing structure?',
                'Do you offer support after project completion?',
                'What industries do you specialize in?',
                'How do I get started with a project?',
                'What is your refund policy?',
                'Do you work with international clients?',
                'What technologies do you use?',
                'Can you help with existing projects?',
            ]),
            'answer' => fake()->randomElement([
                'We offer a comprehensive range of services including web development, digital marketing, business consulting, and technical SEO. Our team of experts works closely with clients to deliver customized solutions that meet their specific business needs and objectives.',
                'Project timelines vary depending on scope and complexity. A typical website project takes 4-8 weeks from initial consultation to launch. We provide detailed project timelines during the planning phase and maintain clear communication throughout the development process.',
                'Our pricing is tailored to each project based on specific requirements, scope, and timeline. We offer transparent pricing with no hidden fees. Contact us for a free consultation and detailed quote that matches your budget and objectives.',
                'Absolutely! We provide ongoing support and maintenance packages to ensure your project continues to perform optimally. Our support includes updates, security patches, technical assistance, and feature enhancements as your business evolves.',
                'While we work across various industries, we have particular expertise in technology, healthcare, finance, e-commerce, and professional services. Our team adapts to your industry\'s unique requirements and regulatory considerations.',
                'Getting started is easy! Simply reach out through our contact form or schedule a free consultation call. We\'ll discuss your project goals, requirements, and timeline, then provide a detailed proposal outlining our approach and investment.',
            ]),
        ]);
    }
}
