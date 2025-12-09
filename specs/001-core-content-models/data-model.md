# Data Model: Core Content Models & Architecture

**Feature Branch**: `001-core-content-models`
**Date**: 2025-12-08

## Shared vs Model-Specific Fields

### Fields Shared Across All Page-Like Models (Page, Service, BlogPost)

| Field | Source |
|-------|--------|
| id, created_at, updated_at, deleted_at | Laravel conventions |
| name, slug, status | Core identity fields |
| content_blocks | HasContentBlocks trait |
| meta_title, meta_description, meta_author, meta_robots, canonical_url | HasSeo trait |
| og_title, og_description, og_type, og_image | HasSeo trait |
| twitter_title, twitter_description, twitter_image | HasSeo trait |
| breadcrumbs | HasSeo trait |

### Model-Specific Fields

| Model | Unique Fields |
|-------|---------------|
| Page | `template` (Blade template name) |
| Service | None (standard page-like) |
| BlogPost | `og_type` defaults to 'article' instead of 'website' |

### Content Resource Fields (Faq, Testimonial)

Content resources do NOT have: slug, content_blocks, SEO fields, or public URLs.

| Model | Fields |
|-------|--------|
| Faq | name, status, question, answer |
| Testimonial | name, status, author_name, author_title, location, quote, rating, avatar |

---

## Entity Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              PAGE-LIKE MODELS                                │
│                        (Publicly Routable with SEO)                          │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   ┌─────────────┐    ┌─────────────┐    ┌─────────────┐                     │
│   │    Page     │    │   Service   │    │  BlogPost   │                     │
│   │  /{slug}    │    │/services/   │    │  /blog/     │                     │
│   │             │    │  {slug}     │    │  {slug}     │                     │
│   └─────────────┘    └─────────────┘    └─────────────┘                     │
│         │                  │                  │                              │
│         │    HasSeo, HasSlug, HasContentBlocks, HasRelatedContent            │
│         │    SoftDeletes                                                     │
│         └──────────────────┼──────────────────┘                              │
│                            │                                                 │
└────────────────────────────┼────────────────────────────────────────────────┘
                             │
                    content_relations
                      (polymorphic)
                             │
┌────────────────────────────┼────────────────────────────────────────────────┐
│                            │                                                 │
│                   CONTENT RESOURCES                                          │
│               (No Public URL, No SEO)                                        │
├────────────────────────────┼────────────────────────────────────────────────┤
│                            │                                                 │
│   ┌─────────────┐    ┌─────────────┐                                        │
│   │     Faq     │    │ Testimonial │                                        │
│   │  (no URL)   │    │  (no URL)   │                                        │
│   └─────────────┘    └─────────────┘                                        │
│         │                  │                                                 │
│         │    HasRelatedContent, SoftDeletes                                  │
│         └──────────────────┘                                                 │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Entities

### Page

**Description**: Static pages with unique templates (Home, About, Contact). Each page has its own content blocks and URL.

**Table**: `pages`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| name | varchar(255) | NOT NULL | Internal page name |
| slug | varchar(255) | NOT NULL, UNIQUE | URL-safe identifier |
| template | varchar(100) | NOT NULL, DEFAULT 'default' | Blade template name (any valid Blade view path, e.g., 'default', 'about', 'contact', 'home') |
| status | varchar(20) | NOT NULL, DEFAULT 'draft' | ContentStatus enum: draft\|published |
| content_blocks | jsonb | NULL | Ordered array of typed blocks |
| meta_title | varchar(255) | NULL | SEO meta title |
| meta_description | text | NULL | SEO meta description |
| meta_author | varchar(255) | NULL | SEO meta author |
| meta_robots | boolean | NOT NULL, DEFAULT true | true=index,follow |
| canonical_url | varchar(2048) | NULL | Canonical URL override |
| og_title | varchar(255) | NULL | Open Graph title (mirrors meta_title if NULL) |
| og_description | text | NULL | Open Graph description (mirrors meta_description if NULL) |
| og_type | varchar(20) | NOT NULL, DEFAULT 'website' | OgType enum: website\|article |
| og_image | varchar(2048) | NULL | Open Graph image URL |
| twitter_title | varchar(255) | NULL | Twitter card title (mirrors meta_title if NULL) |
| twitter_description | text | NULL | Twitter card description (mirrors meta_description if NULL) |
| twitter_image | varchar(2048) | NULL | Twitter card image URL |
| breadcrumbs | jsonb | NULL | Breadcrumb trail structure |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

**Indexes**:
- `pages_slug_unique` UNIQUE on `slug`
- `pages_status_index` on `status`
- `pages_deleted_at_index` on `deleted_at`

**Traits**: HasSeo, HasSlug, HasContentBlocks, HasRelatedContent, SoftDeletes

**Route**: `/{slug}`

---

### Service

**Description**: Custom page type for service offerings. All services share the same template structure.

**Table**: `services`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| name | varchar(255) | NOT NULL | Service name |
| slug | varchar(255) | NOT NULL, UNIQUE | URL-safe identifier |
| status | varchar(20) | NOT NULL, DEFAULT 'draft' | ContentStatus enum |
| content_blocks | jsonb | NULL | Ordered array of typed blocks |
| meta_title | varchar(255) | NULL | SEO meta title |
| meta_description | text | NULL | SEO meta description |
| meta_author | varchar(255) | NULL | SEO meta author |
| meta_robots | boolean | NOT NULL, DEFAULT true | true=index,follow |
| canonical_url | varchar(2048) | NULL | Canonical URL override |
| og_title | varchar(255) | NULL | Open Graph title |
| og_description | text | NULL | Open Graph description |
| og_type | varchar(20) | NOT NULL, DEFAULT 'website' | OgType enum |
| og_image | varchar(2048) | NULL | Open Graph image URL |
| twitter_title | varchar(255) | NULL | Twitter card title |
| twitter_description | text | NULL | Twitter card description |
| twitter_image | varchar(2048) | NULL | Twitter card image URL |
| breadcrumbs | jsonb | NULL | Breadcrumb trail structure |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

**Indexes**:
- `services_slug_unique` UNIQUE on `slug`
- `services_status_index` on `status`
- `services_deleted_at_index` on `deleted_at`

**Traits**: HasSeo, HasSlug, HasContentBlocks, HasRelatedContent, SoftDeletes

**Route**: `/services/{slug}`

---

### BlogPost

**Description**: Custom page type for blog articles. All blog posts share the same template structure.

**Table**: `blog_posts`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| name | varchar(255) | NOT NULL | Blog post title |
| slug | varchar(255) | NOT NULL, UNIQUE | URL-safe identifier |
| status | varchar(20) | NOT NULL, DEFAULT 'draft' | ContentStatus enum |
| content_blocks | jsonb | NULL | Ordered array of typed blocks |
| meta_title | varchar(255) | NULL | SEO meta title |
| meta_description | text | NULL | SEO meta description |
| meta_author | varchar(255) | NULL | SEO meta author |
| meta_robots | boolean | NOT NULL, DEFAULT true | true=index,follow |
| canonical_url | varchar(2048) | NULL | Canonical URL override |
| og_title | varchar(255) | NULL | Open Graph title |
| og_description | text | NULL | Open Graph description |
| og_type | varchar(20) | NOT NULL, DEFAULT 'article' | OgType enum (default article for blog) |
| og_image | varchar(2048) | NULL | Open Graph image URL |
| twitter_title | varchar(255) | NULL | Twitter card title |
| twitter_description | text | NULL | Twitter card description |
| twitter_image | varchar(2048) | NULL | Twitter card image URL |
| breadcrumbs | jsonb | NULL | Breadcrumb trail structure |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

**Indexes**:
- `blog_posts_slug_unique` UNIQUE on `slug`
- `blog_posts_status_index` on `status`
- `blog_posts_deleted_at_index` on `deleted_at`

**Traits**: HasSeo, HasSlug, HasContentBlocks, HasRelatedContent, SoftDeletes

**Route**: `/blog/{slug}`

---

### Faq

**Description**: Content resource for frequently asked questions. No public URL. Linked to pages via relationship engine.

**Table**: `faqs`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| name | varchar(255) | NOT NULL | Internal reference name |
| status | varchar(20) | NOT NULL, DEFAULT 'draft' | ContentStatus enum |
| question | text | NULL | The FAQ question |
| answer | text | NULL | The FAQ answer |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

**Indexes**:
- `faqs_status_index` on `status`
- `faqs_deleted_at_index` on `deleted_at`

**Traits**: HasRelatedContent, SoftDeletes

**Route**: None (content resource)

---

### Testimonial

**Description**: Content resource for customer testimonials. No public URL. Linked to pages via relationship engine.

**Table**: `testimonials`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| name | varchar(255) | NOT NULL | Internal reference name |
| status | varchar(20) | NOT NULL, DEFAULT 'draft' | ContentStatus enum |
| author_name | varchar(255) | NULL | Name of testimonial author |
| author_title | varchar(255) | NULL | Author's job title |
| location | varchar(255) | NULL | Author's location |
| quote | text | NULL | The testimonial text |
| rating | smallint | NULL, CHECK (1-5) | Optional 1-5 star rating |
| avatar | varchar(2048) | NULL | Author avatar image URL |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |
| deleted_at | timestamp | NULL | Soft delete timestamp |

**Indexes**:
- `testimonials_status_index` on `status`
- `testimonials_deleted_at_index` on `deleted_at`

**Traits**: HasRelatedContent, SoftDeletes

**Route**: None (content resource)

---

### ContentRelation (Pivot)

**Description**: Generic polymorphic pivot table for many-to-many relationships between any content types.

**Table**: `content_relations`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| source_type | varchar(255) | NOT NULL | Source model fully qualified class name (e.g., `App\Models\Service`) |
| source_id | bigint | NOT NULL | Source model ID |
| target_type | varchar(255) | NOT NULL | Target model fully qualified class name (e.g., `App\Models\Faq`) |
| target_id | bigint | NOT NULL | Target model ID |
| order | integer | NOT NULL, DEFAULT 0 | Display order position (0-indexed, manually set) |
| created_at | timestamp | NOT NULL | Creation timestamp |

**Indexes**:
- `content_relations_source_index` on (`source_type`, `source_id`)
- `content_relations_target_index` on (`target_type`, `target_id`)
- `content_relations_unique` UNIQUE on (`source_type`, `source_id`, `target_type`, `target_id`)

**Note**: No foreign key constraints as this is a polymorphic table referencing multiple tables.

**Order Field Behavior**:
- Default value: 0
- Manually set when attaching relationships (not auto-incremented)
- Reordering: Update `order` values directly; no automatic resequencing
- Query results ordered by `order ASC` by default

**Duplicate Handling**: Unique constraint prevents duplicate source-target pairs. To update order of existing relationship, use `sync()` or `updateExistingPivot()`.

**Soft Delete Behavior**:
- Relationships to soft-deleted targets are excluded from normal queries
- Use `withTrashed()` on relationship to include soft-deleted targets
- Relationships preserved until source or target is permanently purged

---

## Enums

### ContentStatus

**Location**: `app/Enums/ContentStatus.php`

```php
enum ContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
```

### OgType

**Location**: `app/Enums/OgType.php`

```php
enum OgType: string
{
    case Website = 'website';
    case Article = 'article';
}
```

---

## Relationships

### Page-Like Models → Any Model (via HasRelatedContent)

```
Page ──────┬──── morphToMany ────► Faq
           ├──── morphToMany ────► Testimonial
           ├──── morphToMany ────► Service
           ├──── morphToMany ────► BlogPost
           └──── morphToMany ────► Page (self-reference)

Service ───┬──── morphToMany ────► Faq
           ├──── morphToMany ────► Testimonial
           ├──── morphToMany ────► BlogPost
           └──── morphToMany ────► Service (self-reference)

BlogPost ──┬──── morphToMany ────► Faq
           ├──── morphToMany ────► Testimonial
           └──── morphToMany ────► Service
```

### Content Resources → Any Model (via HasRelatedContent)

```
Faq ───────┬──── morphedByMany ──► Page
           ├──── morphedByMany ──► Service
           └──── morphedByMany ──► BlogPost

Testimonial ┬──── morphedByMany ──► Page
            ├──── morphedByMany ──► Service
            └──── morphedByMany ──► BlogPost
```

---

## Content Block Structure

### Block Array Format

```json
[
  {
    "type": "hero",
    "data": {
      "heading": "Welcome to Our Site",
      "lede": "We help businesses grow online.",
      "image": "https://cdn.example.com/hero.webp",
      "cta_button_text": "Get Started"
    }
  },
  {
    "type": "cta",
    "data": {
      "heading": "Ready to Begin?",
      "lede": "Contact us today for a free consultation.",
      "cta_button_text": "Contact Us"
    }
  }
]
```

### Block Type Definitions

**HeroBlock** (`app/Blocks/HeroBlock.php`)
| Field | Type | Description |
|-------|------|-------------|
| heading | string | Main headline |
| lede | string | Subheading/introduction |
| image | string | Hero image URL |
| cta_button_text | string | Call-to-action button text |

**CtaBlock** (`app/Blocks/CtaBlock.php`)
| Field | Type | Description |
|-------|------|-------------|
| heading | string | Section headline |
| lede | string | Supporting text |
| cta_button_text | string | Button text |

---

## Breadcrumb Structure

```json
{
  "current_page_name": "Web Design Services",
  "current_page_url": "https://example.com/services/web-design",
  "trail": [
    {
      "name": "Home",
      "url": "https://example.com",
      "position": 1
    },
    {
      "name": "Services",
      "url": "https://example.com/services",
      "position": 2
    },
    {
      "name": "Web Design Services",
      "url": "https://example.com/services/web-design",
      "position": 3
    }
  ]
}
```

---

## State Transitions

### Content Status

```
┌─────────┐                    ┌───────────┐
│  Draft  │ ───── publish ───► │ Published │
│         │ ◄─── unpublish ─── │           │
└─────────┘                    └───────────┘
     │                               │
     │                               │
     ▼                               ▼
┌─────────────────────────────────────────┐
│              Soft Deleted               │
│         (deleted_at IS NOT NULL)        │
└─────────────────────────────────────────┘
     │                               │
     │         restore()             │
     ▼                               │
┌─────────┐                          │
│ (back to│                          │
│ previous│                          │
│  state) │                          │
└─────────┘                          │
                                     │
     After CONTENT_RECOVERY_DAYS     │
                                     ▼
                        ┌───────────────────┐
                        │ Permanently Purged│
                        │ (forceDelete)     │
                        │ + relationships   │
                        └───────────────────┘
```

---

## Validation Rules

### Required Fields (All Models)

| Field | Rule |
|-------|------|
| name | required, string, max:255 |
| slug | required (auto-generated if empty), string, max:255, unique per table, not in reserved list |
| status | required, enum:draft,published |

### Content Block Validation

- Must be valid JSON array (or NULL)
- Each block must have `type` field (string)
- Each block must have `data` field (object, can be empty `{}`)
- Block type must have corresponding class in `app/Blocks/`
- Block `data` field values must match schema types (if provided)
- No required fields within blocks
- No maximum block count (unlimited)
- Partial updates not supported; entire array must be replaced

**Error Response Format**:
```json
{
  "message": "The content blocks field is invalid.",
  "errors": {
    "content_blocks": [
      "Block type 'invalid_type' is not defined.",
      "Block at index 2: field 'heading' must be of type string."
    ]
  }
}
```

### SEO Fields (Optional)

| Field | Rule |
|-------|------|
| meta_title | nullable, string, max:255 |
| meta_description | nullable, string, max:65535 |
| meta_author | nullable, string, max:255 |
| canonical_url | nullable, url, max:2048 |
| og_image | nullable, url, max:2048 |
| twitter_image | nullable, url, max:2048 |

**Character Limit Behavior**: Standard Laravel validation rejects values exceeding limits. If validation bypassed, database truncates at column limit.

### Testimonial-Specific

| Field | Rule |
|-------|------|
| rating | nullable, integer, min:1, max:5 |

---

## Configuration

### config/content.php

```php
return [
    'reserved_slugs' => [
        'admin',
        'horizon',
        'api',
        'login',
        'logout',
        'register',
        'password',
        'storage',
        'sanctum',
    ],

    'recovery_days' => env('CONTENT_RECOVERY_DAYS', 30),

    'max_slug_suffix_attempts' => 1000,
];
```

---

## Trait Method Reference

### HasSlug Trait

**Location**: `app/Traits/HasSlug.php`

| Method | Description |
|--------|-------------|
| `bootHasSlug()` | Registers `creating` event to auto-generate slug from name |
| `generateUniqueSlug(string $name): string` | Generates URL-safe slug, handles duplicates with -2, -3 suffix |

**Slug Generation Timing**: Executed in model `creating` event, before validation. If `slug` is already set, auto-generation is skipped.

**Duplicate Suffix Algorithm**:
1. Generate base slug from name using `Str::slug()`
2. Check against reserved slugs list; throw ValidationException if match
3. Query existing slugs (including soft-deleted) for base slug
4. If exists, increment counter (starting at 2) until unique: `{base}-2`, `{base}-3`, etc.
5. Stop at `config('content.max_slug_suffix_attempts')` (default 1000); throw exception if exhausted

### HasSeo Trait

**Location**: `app/Traits/HasSeo.php`

| Method | Description |
|--------|-------------|
| `getOgTitleAttribute(?string $value): ?string` | Returns og_title or falls back to meta_title |
| `getOgDescriptionAttribute(?string $value): ?string` | Returns og_description or falls back to meta_description |
| `getTwitterTitleAttribute(?string $value): ?string` | Returns twitter_title or falls back to meta_title |
| `getTwitterDescriptionAttribute(?string $value): ?string` | Returns twitter_description or falls back to meta_description |

**Mirroring Behavior**: NULL in database = mirror from meta field. Any non-NULL value = independent (mirroring broken).

### HasContentBlocks Trait

**Location**: `app/Traits/HasContentBlocks.php`

| Method | Description |
|--------|-------------|
| `getContentBlocksAttribute(?string $value): ?array` | Decodes JSONB to array |
| `setContentBlocksAttribute(array\|null $value): void` | Encodes array to JSONB |
| `addBlock(array $block): void` | Appends block to content_blocks array |
| `removeBlock(int $index): void` | Removes block at specified index |
| `reorderBlocks(array $order): void` | Reorders blocks based on index mapping |

### HasRelatedContent Trait

**Location**: `app/Traits/HasRelatedContent.php`

| Method | Description |
|--------|-------------|
| `relatedFaqs()` | Returns MorphToMany relationship to Faq, ordered by pivot order |
| `relatedTestimonials()` | Returns MorphToMany relationship to Testimonial, ordered by pivot order |
| `relatedServices()` | Returns MorphToMany relationship to Service, ordered by pivot order |
| `relatedBlogPosts()` | Returns MorphToMany relationship to BlogPost, ordered by pivot order |
| `relatedPages()` | Returns MorphToMany relationship to Page, ordered by pivot order |
| `relatedFromFaqs()` | Returns MorphedByMany (inverse) - which Faqs link to this model |
| `relatedFromServices()` | Returns MorphedByMany (inverse) - which Services link to this model |
| `attachRelated(Collection\|Model $targets, array $pivotData = [])` | Attaches related content with optional pivot data (order) |
| `detachRelated(Collection\|Model $targets)` | Detaches related content |
| `syncRelated(string $relatedClass, array $idsWithPivot)` | Syncs relationships with pivot data |

**Eager Loading**: Use `with(['relatedFaqs', 'relatedTestimonials'])` to prevent N+1 queries.

**Bidirectional Queries**: Query from either direction:
```php
// Forward: Get FAQs related to a Service
$service->relatedFaqs;

// Inverse: Get Services that link to an FAQ
$faq->relatedFromServices;
```
