<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RedirectType;
use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Redirect>
     */
    protected $model = Redirect::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $oldPages = [
            '/old-about', '/old-contact', '/old-services',
            '/legacy/page', '/2023/post', '/archive/article',
        ];

        return [
            'source_path' => fake()->unique()->randomElement($oldPages)
                .'-'.fake()->unique()->numberBetween(1, 999),
            'target_path' => fake()->randomElement([
                '/about', '/contact', '/services', '/', '/blog',
            ]),
            'redirect_type' => fake()->randomElement(RedirectType::cases()),
            'is_active' => fake()->boolean(80),
            'hits' => fake()->numberBetween(0, 500),
            'last_hit_at' => fake()->optional(0.7)->dateTimeBetween('-30 days'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the redirect should be permanent (301).
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'redirect_type' => RedirectType::Permanent,
        ]);
    }

    /**
     * Indicate that the redirect should be temporary (302).
     */
    public function temporary(): static
    {
        return $this->state(fn (array $attributes) => [
            'redirect_type' => RedirectType::Temporary,
        ]);
    }

    /**
     * Indicate that the redirect should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the redirect should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
