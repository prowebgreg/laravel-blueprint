# Data Model: Admin Panel Scaffold

**Branch**: `004-admin-panel-scaffold` | **Date**: 2025-12-12

This document defines the data models required for the Admin Panel Scaffold feature.

---

## Overview

The Admin Panel Scaffold requires **one new model** (Redirect) and **one new enum** (RedirectType). All other content models already exist from Phase 2.

---

## New Entity: Redirect

### Purpose
Stores URL redirect rules for SEO management. Allows administrators to configure 301 (permanent) and 302 (temporary) redirects.

### Schema

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | bigint | PK, auto-increment | Primary key |
| `source_path` | varchar(255) | NOT NULL, unique | The path to redirect from (e.g., `/old-page`) |
| `target_path` | varchar(255) | NOT NULL | The path to redirect to (e.g., `/new-page`) |
| `redirect_type` | varchar(10) | NOT NULL, default '301' | Redirect type (301 or 302) |
| `is_active` | boolean | NOT NULL, default true | Whether redirect is active |
| `hits` | integer | NOT NULL, default 0 | Number of times redirect was triggered |
| `last_hit_at` | timestamp | nullable | Last time redirect was triggered |
| `notes` | text | nullable | Admin notes about the redirect |
| `created_at` | timestamp | auto | Creation timestamp |
| `updated_at` | timestamp | auto | Last update timestamp |

### Indexes
- `redirects_source_path_unique` - UNIQUE on `source_path`
- `redirects_is_active_index` - INDEX on `is_active` for filtering

### Migration
```php
Schema::create('redirects', function (Blueprint $table) {
    $table->id();
    $table->string('source_path')->unique();
    $table->string('target_path');
    $table->string('redirect_type', 10)->default('301');
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('hits')->default(0);
    $table->timestamp('last_hit_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index('is_active');
});
```

### Model Definition
```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RedirectType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_path',
        'target_path',
        'redirect_type',
        'is_active',
        'hits',
        'last_hit_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'redirect_type' => RedirectType::class,
            'is_active' => 'boolean',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }
}
```

### Validation Rules
| Field | Rules |
|-------|-------|
| `source_path` | required, string, max:255, starts_with:/, unique:redirects |
| `target_path` | required, string, max:255 |
| `redirect_type` | required, in:301,302 |
| `is_active` | boolean |
| `notes` | nullable, string |

---

## New Enum: RedirectType

### Purpose
Type-safe representation of HTTP redirect codes.

### Definition
```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum RedirectType: string
{
    case Permanent = '301';
    case Temporary = '302';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => '301 (Permanent)',
            self::Temporary => '302 (Temporary)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Permanent => 'Permanently moved - search engines will update their index',
            self::Temporary => 'Temporarily moved - search engines will keep checking the original URL',
        };
    }
}
```

---

## Existing Entities Reference

These models already exist and are used by the Admin Panel Scaffold:

### Page
```
pages
├── id (bigint)
├── name (varchar)
├── slug (varchar) - unique
├── template (varchar)
├── status (varchar) - ContentStatus enum
├── content_blocks (jsonb)
├── meta_title (varchar, nullable)
├── meta_description (text, nullable)
├── meta_author (varchar, nullable)
├── meta_robots (boolean)
├── canonical_url (varchar, nullable)
├── og_title (varchar, nullable)
├── og_description (text, nullable)
├── og_type (varchar) - OgType enum
├── og_image (varchar, nullable)
├── twitter_title (varchar, nullable)
├── twitter_description (text, nullable)
├── twitter_image (varchar, nullable)
├── breadcrumbs (jsonb, nullable)
├── created_at (timestamp)
├── updated_at (timestamp)
└── deleted_at (timestamp, nullable)
```

### Service
Same structure as Page (without `template` field).

### BlogPost
Same structure as Service.

### Faq
```
faqs
├── id (bigint)
├── name (varchar)
├── status (varchar) - ContentStatus enum
├── question (text, nullable)
├── answer (text, nullable)
├── created_at (timestamp)
├── updated_at (timestamp)
└── deleted_at (timestamp, nullable)
```

### Testimonial
```
testimonials
├── id (bigint)
├── name (varchar)
├── status (varchar) - ContentStatus enum
├── author_name (varchar, nullable)
├── author_title (varchar, nullable)
├── location (varchar, nullable)
├── quote (text, nullable)
├── rating (integer, nullable) - 1-5
├── avatar (varchar, nullable)
├── created_at (timestamp)
├── updated_at (timestamp)
└── deleted_at (timestamp, nullable)
```

### User
Standard Laravel User model with Filament-compatible authentication.

---

## Factory: RedirectFactory

```php
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
    protected $model = Redirect::class;

    public function definition(): array
    {
        $oldPages = [
            '/old-about', '/old-contact', '/old-services',
            '/legacy/page', '/2023/post', '/archive/article',
        ];

        return [
            'source_path' => $this->faker->unique()->randomElement($oldPages)
                . '-' . $this->faker->unique()->numberBetween(1, 999),
            'target_path' => $this->faker->randomElement([
                '/about', '/contact', '/services', '/', '/blog',
            ]),
            'redirect_type' => $this->faker->randomElement(RedirectType::cases()),
            'is_active' => $this->faker->boolean(80), // 80% active
            'hits' => $this->faker->numberBetween(0, 500),
            'last_hit_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days'),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function permanent(): static
    {
        return $this->state(fn () => [
            'redirect_type' => RedirectType::Permanent,
        ]);
    }

    public function temporary(): static
    {
        return $this->state(fn () => [
            'redirect_type' => RedirectType::Temporary,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
```

---

## Seeder: AdminScaffoldSeeder

This seeder creates placeholder data for demonstrating the admin panel.

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class AdminScaffoldSeeder extends Seeder
{
    public function run(): void
    {
        $this->createPages();
        $this->createServices();
        $this->createBlogPosts();
        $this->createFaqs();
        $this->createTestimonials();
        $this->createRedirects();
    }

    private function createPages(): void
    {
        $pages = [
            ['name' => 'Home', 'slug' => 'home', 'status' => ContentStatus::Published],
            ['name' => 'About Us', 'slug' => 'about', 'status' => ContentStatus::Published],
            ['name' => 'Contact', 'slug' => 'contact', 'status' => ContentStatus::Published],
            ['name' => 'Privacy Policy', 'slug' => 'privacy-policy', 'status' => ContentStatus::Draft],
            ['name' => 'Terms of Service', 'slug' => 'terms', 'status' => ContentStatus::Draft],
        ];

        foreach ($pages as $page) {
            Page::factory()->create($page);
        }
    }

    private function createServices(): void
    {
        $services = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'status' => ContentStatus::Published],
            ['name' => 'Mobile Apps', 'slug' => 'mobile-apps', 'status' => ContentStatus::Published],
            ['name' => 'UI/UX Design', 'slug' => 'ui-ux-design', 'status' => ContentStatus::Published],
            ['name' => 'Cloud Solutions', 'slug' => 'cloud-solutions', 'status' => ContentStatus::Draft],
        ];

        foreach ($services as $service) {
            Service::factory()->create($service);
        }
    }

    private function createBlogPosts(): void
    {
        $posts = [
            ['name' => 'Getting Started with Laravel', 'slug' => 'getting-started-laravel', 'status' => ContentStatus::Published],
            ['name' => 'Modern PHP Best Practices', 'slug' => 'modern-php-best-practices', 'status' => ContentStatus::Published],
            ['name' => 'Building APIs with Filament', 'slug' => 'building-apis-filament', 'status' => ContentStatus::Published],
            ['name' => 'Draft Post Example', 'slug' => 'draft-post-example', 'status' => ContentStatus::Draft],
        ];

        foreach ($posts as $post) {
            BlogPost::factory()->create($post);
        }
    }

    private function createFaqs(): void
    {
        $faqs = [
            [
                'name' => 'Payment Methods',
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept all major credit cards, PayPal, and bank transfers.',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Turnaround Time',
                'question' => 'How long does a typical project take?',
                'answer' => 'Project timelines vary based on complexity. Most projects complete within 4-8 weeks.',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Support Policy',
                'question' => 'Do you offer ongoing support?',
                'answer' => 'Yes, we offer various support packages including maintenance and updates.',
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Refund Policy',
                'question' => 'What is your refund policy?',
                'answer' => 'We offer a satisfaction guarantee with partial refunds based on project stage.',
                'status' => ContentStatus::Draft,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::factory()->create($faq);
        }
    }

    private function createTestimonials(): void
    {
        $testimonials = [
            [
                'name' => 'Sarah Johnson Review',
                'author_name' => 'Sarah Johnson',
                'author_title' => 'CEO, TechStart Inc.',
                'quote' => 'Exceptional work! They delivered our project on time and exceeded expectations.',
                'rating' => 5,
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Michael Chen Review',
                'author_name' => 'Michael Chen',
                'author_title' => 'Founder, GrowthLabs',
                'quote' => 'Professional team with great communication throughout the project.',
                'rating' => 5,
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Emily Rodriguez Review',
                'author_name' => 'Emily Rodriguez',
                'author_title' => 'Marketing Director',
                'quote' => 'The website transformed our online presence. Highly recommended!',
                'rating' => 4,
                'status' => ContentStatus::Published,
            ],
            [
                'name' => 'Draft Testimonial',
                'author_name' => 'John Doe',
                'author_title' => 'Manager',
                'quote' => 'Pending review content.',
                'rating' => 4,
                'status' => ContentStatus::Draft,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::factory()->create($testimonial);
        }
    }

    private function createRedirects(): void
    {
        $redirects = [
            [
                'source_path' => '/old-about',
                'target_path' => '/about',
                'redirect_type' => '301',
                'is_active' => true,
                'hits' => 127,
            ],
            [
                'source_path' => '/services/web-design',
                'target_path' => '/services/web-development',
                'redirect_type' => '301',
                'is_active' => true,
                'hits' => 45,
            ],
            [
                'source_path' => '/blog/2023/old-post',
                'target_path' => '/blog/getting-started-laravel',
                'redirect_type' => '301',
                'is_active' => true,
                'hits' => 23,
            ],
            [
                'source_path' => '/temp-promo',
                'target_path' => '/services',
                'redirect_type' => '302',
                'is_active' => false,
                'hits' => 89,
                'notes' => 'Holiday promotion - ended',
            ],
        ];

        foreach ($redirects as $redirect) {
            Redirect::create($redirect);
        }
    }
}
```

---

## Entity Relationships Summary

```
┌─────────────────────────────────────────────────────────────┐
│                    Admin Panel Models                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌────────────┐    ┌────────────┐    ┌────────────┐        │
│  │    Page    │    │  Service   │    │  BlogPost  │        │
│  │  (Static)  │    │  (Custom)  │    │  (Custom)  │        │
│  └─────┬──────┘    └─────┬──────┘    └─────┬──────┘        │
│        │                 │                 │                │
│        └─────────────────┼─────────────────┘                │
│                          │                                   │
│            ┌─────────────┴─────────────┐                    │
│            ▼                           ▼                    │
│     ┌────────────┐              ┌────────────┐              │
│     │    Faq     │              │Testimonial │              │
│     │ (Resource) │              │ (Resource) │              │
│     └────────────┘              └────────────┘              │
│                                                              │
│  ┌────────────┐    ┌────────────┐                           │
│  │  Redirect  │    │    User    │                           │
│  │   (SEO)    │    │  (Auth)    │                           │
│  └────────────┘    └────────────┘                           │
│       NEW              EXISTING                              │
│                                                              │
└─────────────────────────────────────────────────────────────┘

Relationships (via content_relations pivot):
- Page ↔ Faq, Testimonial
- Service ↔ Faq, Testimonial
- BlogPost ↔ Faq, Testimonial

Redirect: Standalone (no relationships)
```

---

## Database Changes Summary

| Action | Table | Description |
|--------|-------|-------------|
| CREATE | `redirects` | New table for URL redirects |
| SEED | Multiple | Placeholder data for all content types |
