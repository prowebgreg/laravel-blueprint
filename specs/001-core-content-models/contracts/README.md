# API Contracts

**Feature Branch**: `001-core-content-models`
**Date**: 2025-12-08

## Overview

Phase 2 (Core Content Models) does not include API endpoints. The API layer is scheduled for Phase 6.

This directory is a placeholder for future API contract definitions when the following endpoints are implemented:

### Future Endpoints (Phase 6)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/pages` | Create a new static page |
| GET | `/api/v1/pages` | List all pages |
| GET | `/api/v1/pages/{slug}` | Get page by slug |
| PUT | `/api/v1/pages/{slug}` | Update a page |
| DELETE | `/api/v1/pages/{slug}` | Soft delete a page |
| POST | `/api/v1/services` | Create a new service |
| GET | `/api/v1/services` | List all services |
| GET | `/api/v1/services/{slug}` | Get service by slug |
| PUT | `/api/v1/services/{slug}` | Update a service |
| DELETE | `/api/v1/services/{slug}` | Soft delete a service |
| POST | `/api/v1/blog-posts` | Create a new blog post |
| GET | `/api/v1/blog-posts` | List all blog posts |
| GET | `/api/v1/blog-posts/{slug}` | Get blog post by slug |
| PUT | `/api/v1/blog-posts/{slug}` | Update a blog post |
| DELETE | `/api/v1/blog-posts/{slug}` | Soft delete a blog post |
| POST | `/api/v1/faqs` | Create a new FAQ |
| GET | `/api/v1/faqs` | List all FAQs |
| PUT | `/api/v1/faqs/{id}` | Update an FAQ |
| DELETE | `/api/v1/faqs/{id}` | Soft delete an FAQ |
| POST | `/api/v1/testimonials` | Create a new testimonial |
| GET | `/api/v1/testimonials` | List all testimonials |
| PUT | `/api/v1/testimonials/{id}` | Update a testimonial |
| DELETE | `/api/v1/testimonials/{id}` | Soft delete a testimonial |
| POST | `/api/v1/relations` | Create a content relation |
| DELETE | `/api/v1/relations/{id}` | Remove a content relation |
| PUT | `/api/v1/relations/reorder` | Reorder content relations |

## Internal Contracts (Phase 2)

While no HTTP API is exposed in Phase 2, the following internal contracts are defined:

### Model Trait Contracts

See `data-model.md` for entity definitions and relationships.

### Block Interface Contract

```php
interface BlockInterface
{
    /**
     * Get the block type identifier.
     */
    public static function type(): string;

    /**
     * Get the schema definition for this block type.
     * Returns array of field_name => type mappings.
     */
    public static function schema(): array;

    /**
     * Validate block data against schema.
     */
    public static function validateData(array $data, Closure $fail, int $index): void;
}
```

### Content Block Structure

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "array",
  "items": {
    "type": "object",
    "required": ["type"],
    "properties": {
      "type": {
        "type": "string",
        "description": "Block type identifier matching class in app/Blocks/"
      },
      "data": {
        "type": "object",
        "description": "Block-specific data fields"
      }
    }
  }
}
```

### Breadcrumb Structure

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "current_page_name": { "type": "string" },
    "current_page_url": { "type": "string", "format": "uri" },
    "trail": {
      "type": "array",
      "items": {
        "type": "object",
        "required": ["name", "url", "position"],
        "properties": {
          "name": { "type": "string" },
          "url": { "type": "string", "format": "uri" },
          "position": { "type": "integer", "minimum": 1 }
        }
      }
    }
  }
}
```
