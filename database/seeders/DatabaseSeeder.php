<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only seed in local/development environment
        if (app()->environment(['local', 'development'])) {
            $this->call([
                AdminUserSeeder::class,
                PageSeeder::class,
                ServiceSeeder::class,
                BlogPostSeeder::class,
                FaqSeeder::class,
                TestimonialSeeder::class,
                ContentRelationSeeder::class, // Must run last - depends on other models
            ]);
        }
    }
}
