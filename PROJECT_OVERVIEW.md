# Project Overview: Blueprint CMS

## 1. Introduction
Blueprint CMS is a modern, block-based Content Management System built on Laravel 12 and Filament 3. It is designed to provide a flexible, scalable, and developer-friendly platform for managing complex website content. The system emphasizes a structured content approach using typed content blocks, a powerful media engine, and a robust relationship system.

## 2. Tech Stack & Infrastructure
*   **Framework**: Laravel 12.0
*   **Admin Interface**: Filament 3.0
*   **Database**: PostgreSQL 17
*   **Cache & Queue**: Redis 7
*   **Search/Jobs**: Laravel Horizon
*   **Asset Storage**: AWS S3 + CloudFront CDN
*   **Image Processing**: Spatie Image / Image Optimizer
*   **Development Environment**: Laravel Sail (Docker)
*   **Testing**: Pest PHP

## 3. Core Architecture

### 3.1 Content Modeling
The CMS distinguishes between three primary types of content:
1.  **Static Pages**: Unique, single-instance pages (e.g., Home, About, Contact) with dedicated templates.
2.  **Custom Page Types**: Collection-based content where multiple records share the same structure (e.g., Services, Blog Posts).
3.  **Content Resources**: Internal data structures not directly accessible via public URLs but used to populate other pages (e.g., FAQs, Testimonials).

**Key Models:**
*   `Page`: Standard static pages.
*   `Service`: Business service offerings.
*   `BlogPost`: Blog articles.
*   `Faq`: Questions and answers.
*   `Testimonial`: Customer reviews.

### 3.2 Block-Based Content Editor
Content is not stored as a single HTML blob but as a structured list of **Content Blocks**.
*   **Storage**: JSONB column `content_blocks`.
*   **Validation**: Each block type is defined as a PHP class in `app/Blocks/` with strict schema and validation rules.
*   **Flexibility**: Editors can add, remove, and reorder blocks (e.g., Hero, CTA, Text, Gallery) to compose pages.
*   **Traits**: The `HasContentBlocks` trait enables this functionality on any model.

### 3.3 Media Engine
A comprehensive media management system handles file uploads, processing, and delivery.
*   **Uploads**: Supports Images (JPEG, PNG, GIF, WebP), SVGs, and Videos (MP4, WebM).
*   **Processing**:
    *   Automatic conversion to WebP format.
    *   Generation of responsive size variants (480w to 1920w).
    *   SVG sanitization.
    *   Asynchronous processing via background jobs.
*   **Storage**: Files are stored in S3 (partitioned by `temp/` and `permanent/`) and served via CloudFront.
*   **Integration**: The `HasMedia` trait allows attaching media to any content model with context-specific keys (e.g., `page:home:hero:image`).

### 3.4 Relationship System
A polymorphic relationship engine connects different content types.
*   **Flexibility**: Any model can relate to any other model (e.g., a Service page related to specific FAQs and Testimonials).
*   **Ordering**: Relationships preserve manual sort order.
*   **Bidirectional**: Queries can be performed from either side of the relationship.

## 4. Key Features

### 4.1 Admin Panel
Built with Filament 3, providing a consistent and responsive UI.
*   **Dashboards**: Activity monitoring and quick actions.
*   **Resource Management**: CRUD interfaces for all content types.
*   **Media Library**: Grid and list views for managing assets.
*   **Theming**: Support for Light/Dark modes and custom branding.

### 4.2 SEO Management
Integrated SEO tools ensure content is optimized for search engines.
*   **Metadata**: Title, Description, Author, Robots, Canonical URLs.
*   **Social**: Open Graph and Twitter Card settings (with auto-mirroring from metadata).
*   **Sitemaps & Redirects**: Built-in management for site structure and URL redirection.

### 4.3 Background Processing
Long-running tasks are handled asynchronously to ensure UI responsiveness.
*   **Queue**: Powered by Redis and Laravel Horizon.
*   **Jobs**: Media processing, cleanup tasks, and maintenance operations.
*   **Monitoring**: Horizon dashboard for tracking job status and throughput.

## 5. Development Workflow

### 5.1 Setup
The project uses Laravel Sail for a zero-configuration Docker environment.
```bash
./vendor/bin/sail up -d
```
This commands starts the Application, PostgreSQL, Redis, and Mailpit containers.

### 5.2 Folder Structure
*   `app/Blocks/`: Definitions for content blocks.
*   `app/Models/`: Eloquent models for content and system entities.
*   `app/Filament/`: Admin panel resources, pages, and widgets.
*   `app/Actions/Media/`: Logic for media handling.
*   `specs/`: Detailed architectural specifications.

## 6. Specifications & Documentation
Refer to the `specs/` directory for detailed technical requirements:
*   `001-core-content-models`: Content architecture and data design.
*   `002-system-foundation`: Environment, storage, and infrastructure.
*   `003-media-engine`: detailed media processing logic.
*   `004-admin-panel-scaffold`: Admin UI guidelines and structure.

