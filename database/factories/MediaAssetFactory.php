<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MediaFolder;
use App\Enums\MediaState;
use App\Enums\MediaType;
use App\Models\MediaAsset;
use App\Models\MediaVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<MediaAsset>
     */
    protected $model = MediaAsset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nanoid = Str::random(8);
        $name = fake()->slug(2);
        $filename = "{$name}-{$nanoid}";

        return [
            'filename' => $filename,
            'original_name' => "{$name}.jpg",
            'media_type' => MediaType::Image,
            'folder' => MediaFolder::Images,
            'file_size' => fake()->numberBetween(50000, 5000000),
            'dimensions' => ['width' => 1920, 'height' => 1080],
            'mime_type' => 'image/webp',
            'state' => MediaState::Ready,
            'error_message' => null,
            's3_key_original' => "media/images/{$filename}-original.webp",
            'cloudfront_url_original' => "https://cdn.example.com/media/images/{$filename}-original.webp",
            'alt_text' => null,
            'title' => null,
            'caption' => null,
            'focal_point' => null,
            'tags' => [],
        ];
    }

    /**
     * Set state to uploading.
     */
    public function uploading(): self
    {
        return $this->state(fn (): array => ['state' => MediaState::Uploading]);
    }

    /**
     * Set state to processing.
     */
    public function processing(): self
    {
        return $this->state(fn (): array => ['state' => MediaState::Processing]);
    }

    /**
     * Set state to failed with error message.
     */
    public function failed(): self
    {
        return $this->state(fn (): array => [
            'state' => MediaState::Failed,
            'error_message' => 'Processing failed: timeout',
        ]);
    }

    /**
     * Create a video type asset.
     */
    public function video(): self
    {
        return $this->state(function (): array {
            $nanoid = Str::random(8);
            $name = fake()->slug(2);
            $filename = "{$name}-{$nanoid}";

            return [
                'filename' => $filename,
                'original_name' => "{$name}.mp4",
                'media_type' => MediaType::Video,
                'folder' => MediaFolder::Videos,
                'dimensions' => null,
                'mime_type' => 'video/mp4',
                's3_key_original' => "media/videos/{$filename}.mp4",
                'cloudfront_url_original' => "https://cdn.example.com/media/videos/{$filename}.mp4",
            ];
        });
    }

    /**
     * Create an SVG type asset.
     */
    public function svg(): self
    {
        return $this->state(function (): array {
            $nanoid = Str::random(8);
            $name = fake()->slug(2);
            $filename = "{$name}-{$nanoid}";

            return [
                'filename' => $filename,
                'original_name' => "{$name}.svg",
                'media_type' => MediaType::Svg,
                'folder' => MediaFolder::Svg,
                'dimensions' => null,
                'mime_type' => 'image/svg+xml',
                's3_key_original' => "media/svg/{$filename}.svg",
                'cloudfront_url_original' => "https://cdn.example.com/media/svg/{$filename}.svg",
            ];
        });
    }

    /**
     * Add metadata (alt_text, title, caption, focal_point, tags).
     */
    public function withMetadata(): self
    {
        return $this->state(fn (): array => [
            'alt_text' => fake()->sentence(3),
            'title' => fake()->words(3, true),
            'caption' => fake()->paragraph(),
            'focal_point' => ['x' => 0.5, 'y' => 0.3],
            'tags' => fake()->words(3),
        ]);
    }

    /**
     * Create variants after creating the asset.
     */
    public function withVariants(): self
    {
        return $this->afterCreating(function (MediaAsset $asset): void {
            if ($asset->media_type !== MediaType::Image) {
                return;
            }

            $widths = [480, 640, 720, 960, 1168, 1440, 1920];
            $originalWidth = $asset->dimensions['width'] ?? 1920;
            $originalHeight = $asset->dimensions['height'] ?? 1080;
            $aspectRatio = $originalHeight / $originalWidth;

            foreach ($widths as $width) {
                if ($width > $originalWidth) {
                    continue;
                }

                $height = (int) round($width * $aspectRatio);

                MediaVariant::factory()->create([
                    'media_asset_id' => $asset->id,
                    'width' => $width,
                    'height' => $height,
                ]);
            }
        });
    }
}
