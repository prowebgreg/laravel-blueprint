# Quickstart: Core Content Models & Architecture

**Feature Branch**: `001-core-content-models`
**Date**: 2025-12-08

## Prerequisites

- Phase 1 complete (Laravel Sail environment running)
- PostgreSQL 17 with JSONB support
- PHP 8.3.x

## Quick Verification

After implementation, verify the feature works:

```bash
# Start the environment
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed example data
./vendor/bin/sail artisan db:seed --class=PageSeeder
./vendor/bin/sail artisan db:seed --class=ServiceSeeder
./vendor/bin/sail artisan db:seed --class=BlogPostSeeder
./vendor/bin/sail artisan db:seed --class=FaqSeeder
./vendor/bin/sail artisan db:seed --class=TestimonialSeeder
./vendor/bin/sail artisan db:seed --class=ContentRelationSeeder

# Run tests
./vendor/bin/sail artisan test --filter=Page
./vendor/bin/sail artisan test --filter=Service
./vendor/bin/sail artisan test --filter=BlogPost
./vendor/bin/sail artisan test --filter=Faq
./vendor/bin/sail artisan test --filter=Testimonial
./vendor/bin/sail artisan test --filter=HasSeo
./vendor/bin/sail artisan test --filter=HasSlug
./vendor/bin/sail artisan test --filter=HasContentBlocks
./vendor/bin/sail artisan test --filter=HasRelatedContent
./vendor/bin/sail artisan test --filter=Block
```

## Usage Examples

### Creating a Page

```php
use App\Models\Page;
use App\Enums\ContentStatus;

$page = Page::create([
    'name' => 'About Us',
    // slug auto-generated: 'about-us'
    'template' => 'about',
    'status' => ContentStatus::Draft,
    'content_blocks' => [
        [
            'type' => 'hero',
            'data' => [
                'heading' => 'About Our Company',
                'lede' => 'We have been serving clients since 2010.',
            ],
        ],
    ],
    'meta_title' => 'About Us | Company Name',
    'meta_description' => 'Learn about our company history and values.',
]);

// Publish the page
$page->update(['status' => ContentStatus::Published]);
```

### Creating a Service with Content Blocks

```php
use App\Models\Service;
use App\Enums\ContentStatus;

$service = Service::create([
    'name' => 'Web Design',
    'status' => ContentStatus::Published,
    'content_blocks' => [
        [
            'type' => 'hero',
            'data' => [
                'heading' => 'Professional Web Design',
                'lede' => 'Custom websites that convert visitors into customers.',
                'cta_button_text' => 'Get a Quote',
            ],
        ],
        [
            'type' => 'cta',
            'data' => [
                'heading' => 'Ready to Start?',
                'lede' => 'Contact us for a free consultation.',
                'cta_button_text' => 'Contact Us',
            ],
        ],
    ],
    'meta_title' => 'Web Design Services',
]);
```

### Creating Content Resources

```php
use App\Models\Faq;
use App\Models\Testimonial;
use App\Enums\ContentStatus;

$faq = Faq::create([
    'name' => 'Pricing FAQ',
    'status' => ContentStatus::Published,
    'question' => 'How much does web design cost?',
    'answer' => 'Our web design packages start at $2,500.',
]);

$testimonial = Testimonial::create([
    'name' => 'John Smith Testimonial',
    'status' => ContentStatus::Published,
    'author_name' => 'John Smith',
    'author_title' => 'CEO',
    'location' => 'New York, NY',
    'quote' => 'Working with this team transformed our online presence.',
    'rating' => 5,
]);
```

### Creating Relationships

```php
use App\Models\Service;
use App\Models\Faq;
use App\Models\Testimonial;

$service = Service::find(1);
$faqs = Faq::whereIn('id', [1, 2, 3])->get();
$testimonials = Testimonial::whereIn('id', [1, 2])->get();

// Attach FAQs with specific order
$service->attachRelated($faqs, [
    1 => ['order' => 1],
    2 => ['order' => 2],
    3 => ['order' => 3],
]);

// Attach testimonials
$service->attachRelated($testimonials, [
    1 => ['order' => 1],
    2 => ['order' => 2],
]);
```

### Querying Related Content

```php
// Get all FAQs related to a service (ordered)
$service = Service::find(1);
$relatedFaqs = $service->relatedFaqs; // Returns ordered collection

// Get all services that link to an FAQ (bidirectional)
$faq = Faq::find(1);
$relatedServices = $faq->relatedFromServices; // Services that link to this FAQ

// Eager load relationships
$services = Service::with(['relatedFaqs', 'relatedTestimonials'])
    ->where('status', ContentStatus::Published)
    ->get();
```

### SEO Field Mirroring

```php
$page = Page::create([
    'name' => 'Test Page',
    'meta_title' => 'Test Page Title',
    'meta_description' => 'Test page description.',
    // og_title and twitter_title are NULL
]);

// OG/Twitter fields mirror meta fields when NULL
echo $page->og_title;       // "Test Page Title" (from meta_title)
echo $page->twitter_title;  // "Test Page Title" (from meta_title)

// Set OG title independently
$page->update(['og_title' => 'Custom OG Title']);
echo $page->og_title;       // "Custom OG Title" (independent)
echo $page->twitter_title;  // "Test Page Title" (still mirroring)
```

### Slug Management

```php
// Auto-generation from name
$page1 = Page::create(['name' => 'Contact Us', 'status' => 'draft']);
echo $page1->slug; // "contact-us"

// Duplicate handling
$page2 = Page::create(['name' => 'Contact Us', 'status' => 'draft']);
echo $page2->slug; // "contact-us-2"

// Manual override
$page2->update(['slug' => 'contact-page']);

// Reserved slug rejection (throws ValidationException)
$page3 = Page::create(['name' => 'Admin', 'status' => 'draft']); // Error!
```

### Soft Delete and Recovery

```php
// Soft delete
$page = Page::find(1);
$page->delete();

// Page is hidden from normal queries
Page::all(); // Does not include deleted page

// Query including deleted
Page::withTrashed()->find(1);

// Restore within recovery period
$page = Page::withTrashed()->find(1);
$page->restore();

// Relationships are preserved during soft delete
$page = Page::withTrashed()->with('relatedFaqs')->find(1);
// relatedFaqs still accessible after restore
```

### Running the Purge Command

```bash
# Manual execution
./vendor/bin/sail artisan content:purge-deleted

# Schedule configuration (in routes/console.php or app/Console/Kernel.php)
Schedule::command('content:purge-deleted')->daily();
```

## Testing Tinker Commands

```bash
./vendor/bin/sail artisan tinker
```

```php
// Check seeded data
App\Models\Page::count();
App\Models\Service::count();
App\Models\Faq::count();

// Test slug generation
$page = new App\Models\Page(['name' => 'Test Page']);
$page->save();
$page->slug; // Should be "test-page"

// Test block validation
$page = App\Models\Page::first();
$page->content_blocks = [['type' => 'invalid_type', 'data' => []]];
$page->save(); // Should throw validation error

// Test relationships
$service = App\Models\Service::first();
$service->relatedFaqs()->attach(App\Models\Faq::first()->id, ['order' => 1]);
$service->relatedFaqs;
```

## Common Issues

### Block Type Not Found

**Error**: `Block type 'xyz' is not defined`

**Solution**: Create a block class at `app/Blocks/XyzBlock.php` implementing `BlockInterface`.

### Reserved Slug Error

**Error**: `This slug is reserved for system use. Please choose another.`

**Solution**: Choose a different name or manually set a non-reserved slug.

### Duplicate Slug Suffix

**Issue**: Slugs getting unexpected suffixes like `-2`, `-3`

**Explanation**: This is expected behavior when the base slug already exists. Use manual slug to override.

## File Locations

| Component | Location |
|-----------|----------|
| Models | `app/Models/{Page,Service,BlogPost,Faq,Testimonial}.php` |
| Traits | `app/Traits/{HasSeo,HasSlug,HasContentBlocks,HasRelatedContent}.php` |
| Blocks | `app/Blocks/{HeroBlock,CtaBlock}.php` |
| Enums | `app/Enums/{ContentStatus,OgType}.php` |
| Config | `config/content.php` |
| Migrations | `database/migrations/2025_12_08_*.php` |
| Factories | `database/factories/{Page,Service,BlogPost,Faq,Testimonial}Factory.php` |
| Seeders | `database/seeders/*Seeder.php` |
| Tests | `tests/Feature/{Models,Traits,Commands}/*.php`, `tests/Unit/Blocks/*.php` |
