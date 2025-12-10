<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MediaAsset;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('MediaSeeder is disabled in production environments.');

            return;
        }

        $this->createHeroImage();
        $this->createLogoSvg();
        $this->createTestimonialAvatar();
        $this->createFallbackImage();
        $this->createProcessingExample();
        $this->createFailedExample();
    }

    /**
     * Create a hero background image with metadata and variants.
     */
    private function createHeroImage(): void
    {
        MediaAsset::factory()
            ->withMetadata()
            ->withVariants()
            ->create([
                'original_name' => 'hero-background.jpg',
                'alt_text' => 'Hero background image',
                'title' => 'Hero Background',
                'caption' => 'Beautiful hero section background image for homepage',
            ]);
    }

    /**
     * Create an SVG logo.
     */
    private function createLogoSvg(): void
    {
        MediaAsset::factory()
            ->svg()
            ->create([
                'original_name' => 'company-logo.svg',
                'alt_text' => 'Company Logo',
                'title' => 'Company Logo',
            ]);
    }

    /**
     * Create a testimonial avatar with variants.
     */
    private function createTestimonialAvatar(): void
    {
        MediaAsset::factory()
            ->withVariants()
            ->create([
                'original_name' => 'testimonial-avatar.jpg',
                'alt_text' => 'Customer photo',
                'title' => 'Customer Avatar',
                'caption' => 'Testimonial customer profile photo',
            ]);
    }

    /**
     * Create a fallback/placeholder image with variants and set it in settings.
     */
    private function createFallbackImage(): void
    {
        $fallbackImage = MediaAsset::factory()
            ->withVariants()
            ->create([
                'original_name' => 'placeholder.jpg',
                'alt_text' => 'Placeholder image',
                'title' => 'Fallback Placeholder',
                'caption' => 'Default placeholder image used when media is unavailable',
            ]);

        Setting::set('media.fallback_image_id', $fallbackImage->id);
    }

    /**
     * Create an example asset in processing state.
     */
    private function createProcessingExample(): void
    {
        MediaAsset::factory()
            ->processing()
            ->create([
                'original_name' => 'processing-example.jpg',
                'alt_text' => 'Processing example',
            ]);
    }

    /**
     * Create an example asset in failed state.
     */
    private function createFailedExample(): void
    {
        MediaAsset::factory()
            ->failed()
            ->create([
                'original_name' => 'failed-example.jpg',
                'alt_text' => 'Failed example',
            ]);
    }
}
