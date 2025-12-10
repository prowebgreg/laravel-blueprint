<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaVariant>
 */
class MediaVariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<MediaVariant>
     */
    protected $model = MediaVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $width = fake()->randomElement([480, 640, 720, 960, 1168, 1440, 1920]);
        $height = (int) round($width * 9 / 16); // 16:9 aspect ratio
        $nanoid = Str::random(8);

        return [
            'media_asset_id' => MediaAsset::factory(),
            'width' => $width,
            'height' => $height,
            'format' => 'webp',
            'file_size' => fake()->numberBetween(10000, 500000),
            's3_key' => "media/images/sample-{$nanoid}-{$width}.webp",
            'cloudfront_url' => "https://cdn.example.com/media/images/sample-{$nanoid}-{$width}.webp",
        ];
    }
}
