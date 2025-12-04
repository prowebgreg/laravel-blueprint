# PRD: The Blueprint CMS

**Version:** 1.0.0  
**Date:** December 2025  
**Status:** Approved for Development  
**Core Stack:** Laravel 12, Filament V3, PostgreSQL 17, Redis, AWS S3.

---

## 1. Executive Summary

### 1.1 Product Vision
To create a high-performance, strictly-typed, developer-first Content Management System (CMS) that replaces WordPress for professional bespoke website development. This system acts as a "Blueprint" foundation for various projects (landing pages, corporate sites, portfolios).

### 1.2 Core Philosophy
1.  **Code-Defined Structure:** The schema (Post Types, Fields, Relations) is defined in PHP classes, ensuring version control and stability. The Admin Panel is merely a window to populate this schema.
2.  **Performance First:** The system utilizes Server-Side Rendering (Blade) to achieve 95+ Google Lighthouse scores. No client-side hydration bloat.
3.  **Asset Sovereignty:** All media is processed, optimized, and offloaded to AWS S3/CloudFront immediately, keeping the application server lightweight.
4.  **Hybrid Headless:** While the frontend is native Blade, the system exposes a full API to allow content injection via external automation tools (Notion, n8n).

---

## 2. Technical Architecture

### 2.1 Backend Stack
*   **Framework:** Laravel 12.x.
*   **Language:** PHP 8.3+.
*   **Database:** PostgreSQL 17+ (Chosen for superior `JSONB` performance for flexible content blocks).
*   **Admin Panel:** Filament PHP (V3) – leveraging Livewire and Alpine.js.
*   **Caching & Queues:** Redis (via Docker/Sail in Dev, Managed/Service in Prod).
*   **Search:** Database native (Postgres Full Text Search) or Meilisearch (optional future upgrade).

### 2.2 Infrastructure & Services
*   **Local Environment:** Laravel Sail (Docker Compose) running App, Postgres, Redis, Mailpit.
*   **File Storage:** AWS S3 (Private Bucket).
*   **CDN:** AWS CloudFront (Public access to S3 assets).
*   **Image Processing:** `spatie/laravel-medialibrary` + `spatie/image-optimizer` (requires local binaries: `jpegoptim`, `cwebp`, `optipng`).

### 2.3 Frontend Stack
*   **Templating:** Laravel Blade (Component-based architecture).
*   **Styling:** Tailwind CSS (v3/v4).
*   **Bundler:** Vite.
*   **Scripts:** Alpine.js (for minimal interactivity like Mobile Menus/Accordions).

---

## 3. Data Architecture & Models

### 3.1 Core Models (The "Page" Types)
The system distinguishes between three types of content:

1.  **Static Page (`Page`)**
    *   **Purpose:** Standard architecture pages (Home, About, Contact).
    *   **Routing:** Publicly visible (`/{slug}`).
    *   **Schema:** Fixed fields + Flexible Content Blocks (JSONB).

2.  **Custom Page (`Service`, `BlogPost`, `Portfolio`)**
    *   **Purpose:** Repeatable public content.
    *   **Routing:** Publicly visible (`/services/{slug}`, `/blog/{slug}`).
    *   **Schema:** Defined specifically for the type (e.g., Service has `icon`, `summary`).

3.  **Content Resource (`Faq`, `Testimonial`, `TeamMember`)**
    *   **Purpose:** "Hidden" content used to populate sections on other pages.
    *   **Routing:** **Not** publicly queryable (No URL).
    *   **Schema:** Small, specific data points (e.g., FAQ has `question`, `answer`).

### 3.2 Global Models
1.  **User:** Administrators/Editors.
2.  **Setting:** Key-value store for global site data (Company Info, Scripts).
3.  **Redirect:** Handles 301/302 redirects.

### 3.3 Database Design (Key Concepts)
*   **Content Storage:** We avoid the EAV (Entity-Attribute-Value) "meta" table mess of WordPress. Flexible content is stored in a `jsonb` column named `content_blocks` or similar within the model's main table.
*   **Relationships:**
    *   **Polymorphic:** Used for Media attachments.
    *   **Many-to-Many:** Used for attaching Content Resources to Pages (e.g., A `Service` page "has many" `Faqs`).

---

## 4. Functional Specifications

### Spec 1.0: System Foundation
*   **1.1:** Setup Laravel 12 Sail environment with PostgreSQL and Redis.
*   **1.2:** Configure Object Storage (S3) and CDN (CloudFront).
    *   Constraint: Assets must be served via CloudFront URL, not S3 direct URL.
*   **1.3:** Install Filament Admin Panel and configure the path to `/admin`.
*   **1.4:** Implement standard Auth (Login, Password Reset).

### Spec 2.0: The CMS Core (Content Engine)
*   **2.1 Static Pages:**
    *   Implement `Page` model.
    *   Admin Interface: Title, Slug, SEO Fields (Sidebar), and a **Builder/Repeater** field for the main content area.
*   **2.2 Custom Pages:**
    *   Implement `Service` and `BlogPost` models.
    *   Admin Interface: Specific fields for these types (e.g., Blog has `published_at`, `author`).
*   **2.3 Content Resources (Hidden):**
    *   Implement `Faq` and `Testimonial` models.
    *   Constraint: These must NOT have frontend routes generated.
*   **2.4 The "Picker" Relationship:**
    *   Feature: On a `Service` page edit screen, allow the admin to search and select multiple `Faq` items.
    *   Frontend Logic: The Service page template loops through *only* the selected FAQs.

### Spec 3.0: Advanced Media Library
*   **3.1 Upload Pipeline:**
    *   Uploads go to S3 Temporary folder -> Processed via Queue -> Moved to Permanent S3 folder.
*   **3.2 Optimization Rules:**
    *   **Format:** Convert all JPG/PNG to WebP (keep original as fallback if needed, or replace entirely).
    *   **Compression:** Run `spatie/image-optimizer` to strip metadata and compress.
    *   **Responsiveness:** Generate `thumb`, `medium`, `large` conversions.
    *   **Exclusion:** SVG and Video files must bypass resizing and WebP conversion.
*   **3.3 Admin Experience:**
    *   Grid view of images.
    *   Ability to edit `alt`, `caption`, `title`.
    *   Filtering by Collection (e.g., "Blog Images", "Banners").

### Spec 4.0: Global Settings & Webhooks
*   **4.1 Company Information:**
    *   Centralized form to manage: Logo, Company Name, Address, Phone, Email, Social Links.
    *   Data is accessible globally in Blade via helper `site('phone')`.
*   **4.2 Scripts Manager:**
    *   Fields for `head_scripts` (e.g., GTM), `body_scripts`, `footer_scripts`.
    *   Raw HTML allowed.
*   **4.3 Webhook Manager:**
    *   Fields to store external webhook URLs (e.g., `contact_form_action_url`).
    *   When a frontend form is submitted, the controller grabs this URL and POSTs the data to it (e.g., to n8n).

### Spec 5.0: SEO & Routing Engine
*   **5.1 SEO Trait (`HasSeo`):**
    *   A reusable PHP Trait attached to `Page`, `Service`, `BlogPost`.
    *   Adds fields: Meta Title, Meta Description, Canonical URL, NoIndex Toggle.
    *   Admin: These fields appear in a dedicated "SEO" tab or sidebar.
    *   Logic: If `NoIndex` is true, render `<meta name="robots" content="noindex">`.
*   **5.2 Sitemap:**
    *   Automated `sitemap.xml` generation via `spatie/laravel-sitemap`.
    *   Logic: Only include public models where `is_visible` is true.
*   **5.3 Redirects:**
    *   Middleware that intercepts 404s.
    *   Checks a `redirects` table.
    *   If match found: Perform 301 Redirect.
    *   If no match: Render custom 404 Blade template.

### Spec 6.0: API Layer (Headless)
*   **6.1 Authentication:**
    *   Sanctum Token for external tools (Notion/n8n).
*   **6.2 Endpoints:**
    *   `POST /api/v1/services`: Create/Update a service page.
    *   `POST /api/v1/content/{type}`: Generic handler for adding Content Resources (e.g., adding a new Testimonial from a Notion database).
    *   Validation: Must strictly validate payload against the Model's requirements.

---

## 5. Development Roadmap (Spec-Driven Checklist)

This roadmap acts as the prompt context for your AI Coding Agent.

### Phase 1: Architecture & Environment
- [ ] **Spec 1.1:** Init Laravel 12 Sail (Postgres + Redis). Commit initial structure.
- [ ] **Spec 1.2:** Configure `.env` with AWS S3/CloudFront credentials.
- [ ] **Spec 1.3:** Install Filament V3 + Livewire + Tailwind. Create Admin User.
- [ ] **Spec 1.4:** Verify Redis queue connection (required for Media).

### Phase 2: Core Data Structure
- [ ] **Spec 2.1:** Create `Page` Model and Resource. Define `jsonb` column for content.
- [ ] **Spec 2.2:** Create `Service` and `BlogPost` Models/Resources (Public).
- [ ] **Spec 2.3:** Create `Faq` and `Testimonial` Models/Resources (Hidden).
- [ ] **Spec 2.4:** Implement `HasSeo` trait and attach to Public models.

### Phase 3: The Media Engine
- [ ] **Spec 3.1:** Install `spatie/laravel-medialibrary`.
- [ ] **Spec 3.2:** Configure S3 Disk.
- [ ] **Spec 3.3:** Create the Media Conversion Job (Optimize + WebP + Resize).
- [ ] **Spec 3.4:** Implement `SpatieMediaLibraryFileUpload` in Filament forms.

### Phase 4: Settings & Utilities
- [ ] **Spec 4.1:** Create `GlobalSettings` using `spatie/laravel-valuestore` or DB table.
- [ ] **Spec 4.2:** Build the Admin Settings Page (Company Info, Scripts, Webhooks).
- [ ] **Spec 4.3:** Implement `Redirect` model and 404 Middleware.
- [ ] **Spec 4.4:** Configure Sitemap Scheduler.

### Phase 5: API Integration
- [ ] **Spec 5.1:** Enable Sanctum. Create "Automation" User/Token.
- [ ] **Spec 5.2:** Build API Controller for external content creation.
- [ ] **Spec 5.3:** Test POST requests via Postman/Curl.

### Phase 6: Frontend Templating (The "Vibe" Phase)
- [ ] **Spec 6.1:** Create `layouts/app.blade.php`. Inject SEO and Scripts.
- [ ] **Spec 6.2:** Create atomic Blade Components (`<x-hero>`, `<x-button>`, `<x-container>`).
- [ ] **Spec 6.3:** Connect `PageController` to render views.
- [ ] **Spec 6.4:** Implement the "Flexible Content" loop (Render blocks based on JSON data).

### Phase 7: Optimization
- [ ] **Spec 7.1:** Install `spatie/laravel-responsecache`.
- [ ] **Spec 7.2:** Run Audit (Lighthouse). Optimize Cumulative Layout Shift (CLS) and LCP.

---

## 6. Developer Guidelines (Context for AI)

1.  **Strict Typing:** All PHP classes must declare `strict_types=1`.
2.  **Filament Best Practice:** Use `schema()` within Resource files. Do not rely on auto-discovery.
3.  **Media Output:** In Blade, always use the `<picture>` tag or the Spatie helper to ensure the WebP version and responsive `srcset` are used.
4.  **No Logic in Views:** Blade files should only display data. Logic belongs in ViewModels or Components.
5.  **Security:** All API endpoints must be guarded by Sanctum. All Admin routes guarded by Filament Auth.