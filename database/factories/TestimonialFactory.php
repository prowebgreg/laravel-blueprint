<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Testimonial::class;

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
            'author_name' => fake()->name(),
            'author_title' => fake()->jobTitle(),
            'location' => fake()->city().', '.fake()->stateAbbr(),
            'quote' => fake()->paragraph(3),
            'rating' => null,
            'avatar' => null,
        ];
    }

    /**
     * Indicate that the testimonial should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Published,
        ]);
    }

    /**
     * Indicate that the testimonial should be a draft (explicit state).
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Draft,
        ]);
    }

    /**
     * Add a rating to the testimonial (1-5 stars).
     */
    public function withRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Add an avatar image URL to the testimonial.
     */
    public function withAvatar(): static
    {
        return $this->state(fn (array $attributes) => [
            'avatar' => fake()->imageUrl(400, 400, 'people', true),
        ]);
    }

    /**
     * Create a complete testimonial profile with all optional fields.
     */
    public function withFullProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'author_name' => fake()->name(),
            'author_title' => fake()->jobTitle(),
            'location' => fake()->city().', '.fake()->stateAbbr(),
            'rating' => fake()->numberBetween(4, 5),
            'avatar' => fake()->imageUrl(400, 400, 'people', true),
            'quote' => fake()->randomElement([
                'Working with this team was an absolute pleasure. They exceeded our expectations at every turn and delivered results beyond what we thought possible.',
                'The professionalism and expertise demonstrated throughout our project was exceptional. I would recommend their services without hesitation.',
                'From start to finish, the entire process was smooth and efficient. Their attention to detail and commitment to quality truly sets them apart.',
                'Outstanding service and incredible results. They took the time to understand our needs and delivered a solution that perfectly fit our requirements.',
                'The level of support and communication was fantastic. They were always available to answer questions and went above and beyond to ensure our success.',
            ]),
        ]);
    }
}
