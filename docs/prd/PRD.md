# PRD: The Blueprint CMS

**Version:** 1.1.0  
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
*   **Styling (Admin):** Tailwind CSS (v3/v4) used exclusively for the Filament/Admin UI.
*   **Styling (Public Frontend):** Custom SCSS + CSS variables; no Tailwind utility classes in public Blade views by default.
*   **Bundler:** Vite (compiles SCSS and JavaScript assets).
*   **Scripts:** Alpine.js (for minimal interactivity like Mobile Menus/Accordions where needed).

---

## 3. Data Architecture & Models

### 3.1 Core Models (The "Page" Types)
The system distinguishes between three types of content:

1.  **Static Page (`Page`)**
    *   **Purpose:** Standard architecture pages (Home, About, Contact).
    *   **Routing:** Publicly visible (`/{slug}`).
    *   **Schema:** **Fixed Fields** (shared across all pages: identity + SEO-related fields) plus page-specific **Custom Fields** and optional **Reusable Fields** (shared section/component types with page-level content). Flexible section data is stored in a `jsonb` column on the `pages` table.

2.  **Custom Page (`Service`, `BlogPost`, `Portfolio`)**
    *   **Purpose:** Repeatable public content.
    *   **Routing:** Publicly visible (`/services/{slug}`, `/blog/{slug}`).
    *   **Schema:** **Fixed Fields** (shared across all pages: identity + SEO-related fields) plus type-specific **Custom Fields** that define a common structure for all records of that type (e.g., `Service` has `icon`, `summary`). May also use **Reusable Fields** for shared sections/components as needed.
    *   **Blueprint Scope:** In the base Blueprint project, `Service` and `BlogPost` are example custom page types to demonstrate the pattern. Real client projects may define any number of additional custom page types following this approach.

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
    *   **Many-to-Many:** Used for attaching related records (Pages ↔ Content Resources, Page ↔ Page, and Content Resource ↔ Content Resource), with support for configurable relationship pairs and ordered display.

### 3.4 Field Types & Content Schema
*   **Fixed Fields:** Fields that exist on every page and page type (and on Content Resources where applicable), e.g., human-readable page name, slug, `status` (`draft` or `published`), and SEO-related fields such as meta title, meta description, canonical URL, meta author, and breadcrumbs-related data. These form the common baseline for all page-like models.
*   **Custom Fields:** Fields that are unique to a specific static page or page type, defining that page's or type's particular content structure (e.g., Home page hero fields, or `Service`-specific attributes).
*   **Reusable Fields:** Fields grouped into reusable section/component types (e.g., a CTA section, FAQs section) that can be used across multiple pages while sharing the same design/layout/structure. Each page instance provides its own content values for these sections.
*   **Structured Data Fields:** Fields used to populate JSON-LD structured data templates. These reuse existing Fixed, Custom, and Reusable Fields wherever possible, and introduce additional schema-only fields per page type or schema type when SEO requirements need data that is not otherwise stored.

### 3.5 Relationship Engine
*   **Generic Many-to-Many Relationships:** The system must support a generic, configurable many-to-many relationship engine between any page-like model and any content resource, including Page ↔ Content Resource, Page ↔ Page, and Content Resource ↔ Content Resource.
*   **Configurable Pairs:** Blueprint ships with example relationship pairs (e.g., `Service` ↔ `Faq`, `Service` ↔ `ServiceArea`), and real projects can define additional pairs as needed.
*   **Ordered Display:** Relationships must preserve a manually defined order for related items so that frontend templates can render them in a specific sequence.

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
    *   Blueprint Role: These models act as reference implementations for future custom page types in real projects.
*   **2.3 Content Resources (Hidden):**
    *   Implement `Faq` and `Testimonial` models.
    *   Constraint: These must NOT have frontend routes generated.
*   **2.4 The "Picker" Relationship:**
    *   Feature: Provide a generic relationship picker that allows editors to search and select related records (pages or content resources) according to configured relationship pairs (e.g., a `Service` page can link to multiple `Faq` or `ServiceArea` items).
    *   Ordering: Preserve the manually defined order of selected related items for display.
    *   Frontend Logic: For example, a `Service` page template loops through only the selected related FAQs or Service Areas, in the configured order.

### Spec 3.0: Advanced Media Library
*   **3.1 Upload Pipeline:**
    *   Uploads go to an S3 `/temp` folder -> Processed via Queue -> Moved to a `/permanent` folder.
    *   Each real project using this Blueprint must provision these two top-level folders in its S3 bucket; any further organization (if needed) is handled via metadata and naming conventions, not additional physical folders.
*   **3.2 Optimization Rules:**
    *   **Format:** Convert all JPG/PNG to WebP (keep original as fallback if needed, or replace entirely).
    *   **Compression:** Run `spatie/image-optimizer` to strip metadata and compress.
    *   **Responsiveness:** Generate multiple responsive width variants for images (e.g., 480px, 640px, 720px, 960px, 1168px, 1440px, 1920px).
    *   **Exclusion:** SVG and Video files must bypass resizing and WebP conversion.
*   **3.3 Media Metadata & Organization:**
    *   Each media item must store descriptive metadata such as `alt`, `caption`, and `title`, which can be managed and reused wherever the asset is used.
    *   Each media item must also store a media type classification (e.g., image, video, SVG, branding/other) as metadata, so that admin views can filter or provide separate screens per media type without relying on S3 folder structure.
    *   Media items can be grouped into logical Collections (e.g., "Blog Images", "Banners") to support organization and filtering; the exact admin UI for this is defined separately.

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
*   **4.4 Structured Data Globals:**
    *   Global fields used by the Structured Data Engine to populate shared JSON-LD properties across pages (e.g., website/organization objects, publisher, or other values that are common to all page-level WebPage schemas).

### Spec 5.0: SEO & Routing Engine
*   **5.1 SEO Trait (`HasSeo`):**
    *   A reusable PHP Trait attached to `Page`, `Service`, `BlogPost`.
    *   Implements the SEO-related subset of **Fixed Fields**: Meta Title, Meta Description, Canonical URL, NoIndex Toggle, and related SEO/head configuration.
    *   Admin: These fields appear in a dedicated "SEO" tab or sidebar.
    *   Logic: If `NoIndex` is true, render `<meta name="robots" content="noindex">`.
*   **5.2 Sitemap:**
    *   Automated `sitemap.xml` generation via `spatie/laravel-sitemap`.
    *   Logic: Only include public models where `status = 'published'` (or equivalent `is_visible` flag) is true.
*   **5.3 Redirects:**
    *   Middleware that intercepts 404s.
    *   Checks a `redirects` table.
    *   If match found: Perform 301 Redirect.
    *   If no match: Render custom 404 Blade template.
    *   Draft Handling: Requests to routes for draft pages or resources should behave as 404 (no public preview is required in the base Blueprint).
*   **5.4 Structured Data Engine:**
    *   Structured data is generated as JSON-LD templates defined in code (PHP/Blade/classes) with placeholders.
    *   Placeholders are populated from a combination of global Structured Data fields in Settings and page-level fields (Fixed, Custom, Reusable, and Structured Data Fields) to avoid duplicating data.
    *   Field reuse is preferred wherever possible; additional schema-only fields are introduced only when SEO requirements demand data not present elsewhere.
    *   Blueprint may include example templates (e.g., WebPage with `mainEntity`), but concrete schema types and templates are defined per real project.

### Spec 6.0: API Layer (Headless)
*   **6.1 Authentication:**
    *   Sanctum Token for external tools (Notion/n8n).
*   **6.2 Endpoints:**
    *   `POST /api/v1/pages/{type}`: Generic handler for creating/updating any page-like model (static pages and custom pages such as `service`, `blog_post`, `service_area`).
    *   `POST /api/v1/content/{type}`: Generic handler for creating/updating Content Resources (e.g., adding a new Testimonial or FAQ from a Notion database).
    *   Payloads are JSON objects whose structure mirrors the declared schema for the target type (Fixed Fields, Custom Fields, Reusable Fields, and Structured Data Fields), including nested objects/arrays for repeater-style data.
    *   A dedicated `relationships` section in the payload must allow attaching/detaching related records (Pages and Content Resources) using identifiers (e.g., IDs or slugs) and specifying display order, leveraging the generic Relationship Engine.
    *   Validation: Must strictly validate payloads against the model's schema, rejecting unknown fields and type mismatches.

### Admin Panel & Editor Experience (High Level)
*   **Global Layout:** All admin screens share a common layout with a persistent left sidebar (logo from Website Settings, grouped navigation, user/account block) and a main content area. Visual theming is implementation-specific, but structural consistency is required.
*   **Navigation Groups:** Sidebar navigation is grouped into: Public Pages (Static Pages plus each custom page type), Content Resources (one entry per resource type), Media Library (one entry per configured media view based on media-type metadata), SEO (Sitemap, Redirects, Structured Data, 404 Pages), Settings (Website Details, Scripts & Integrations, Users, and related settings pages), and Account.
*   **Listing Screens for Pages & Resources:** Each page type and each Content Resource type has a listing screen under its group (e.g., `/admin/public-pages/static-pages`), showing a filterable/sortable table and an "Add New" action. A consolidated "All Public Pages" view provides a cross-type overview.
*   **Editing Screens – Pages:** All static and custom pages use a shared edit layout: a header (editable page name, back link to the type's list view, and a `status` selector for Draft/Published), a main canvas with tabbed sections (e.g., "Page Content" for Custom/Reusable Fields, "SEO Data" for Fixed + SEO-related + breadcrumbs/Structured Data Fields), and a right sidebar with Save controls, "View page" shortcut, editable slug, key SEO summary, created/updated timestamps, and the page's unique ID.
*   **Editing Screens – Content Resources:** Content Resources use a simplified edit layout: header (resource name and Draft/Published status), a single main section containing the resource's content fields, and a right sidebar with Save controls and the resource's unique ID.
*   **Create Flows:** Creating a new page or content resource uses a lightweight modal (for example, entering the new page name and slug) after which the editor is taken directly to the full edit screen; no separate dedicated "create" pages are required.
*   **Media Library Views:** The Media Library group exposes multiple views that filter media by type/metadata (e.g., Service Images, Headshots, Brand Assets), each offering both grid and table/list views and integrating the shared Media Library behavior defined elsewhere in this PRD.

---

## 5. Development Roadmap (Spec-Driven Checklist)

This roadmap is the execution plan for AI-assisted development. For each task, the goal is to ship working code **plus minimal example data** (example page types, sample records, example templates) so that the feature can be verified end-to-end. Exact example content is chosen per real project, but every feature must have at least one concrete example.

### Phase 1: Architecture & Environment (Already Implemented)
- [x] **Spec 1.1:** Init Laravel 12 Sail (Postgres + Redis). Commit initial structure.
- [x] **Spec 1.2:** Configure `.env` with AWS S3/CloudFront credentials.
- [x] **Spec 1.3:** Install Filament V3 + Livewire + Tailwind. Create Admin User.
- [x] **Spec 1.4:** Verify Redis queue connection (required for Media).

### Phase 2: Core Data Models & Schema
- [ ] **Page Models:** Implement `Page` (static pages) and at least two example custom page types (e.g., `Service`, `BlogPost`), including migrations with `jsonb` content column, `status`, slug, and all Fixed Fields.
- [ ] **Content Resources:** Implement `Faq` and `Testimonial` models/tables as example Content Resources with their own Fixed/Custom Fields.
- [ ] **Relationship Engine:** Implement the generic many-to-many Relationship Engine (tables, Eloquent relations, ordering) and wire example relationships (e.g., `Service` ↔ `Faq`, `Service` ↔ `ServiceArea`).
- [ ] **HasSeo Trait:** Implement `HasSeo` and attach it to all public page models, ensuring SEO-related Fixed Fields are available in code and the database.
- [ ] **Example Data:** Seed a small set of example records for each model (e.g., Home page, at least two Services, a few FAQs/Testimonials) to validate queries and relationships.

### Phase 3: Media Engine & Asset Model
- [ ] **Spatie Medialibrary:** Install and configure `spatie/laravel-medialibrary` with S3 disk using `/temp` and `/permanent` folders.
- [ ] **Media Model & Metadata:** Implement a Media model (or configure Medialibrary) to store media metadata (type, alt, title, caption, collections) and link to CloudFront URLs.
- [ ] **Conversions & Optimization:** Create media conversions for the required responsive widths (480, 640, 720, 960, 1168, 1440, 1920) plus WebP/optimization rules, excluding SVG and video.
- [ ] **Usage Tracking:** Integrate media usage into the Relationship Engine so pages and content resources can declare which media they use.
- [ ] **Example Assets:** Upload a small set of example images (hero, logo, content image) and verify conversions, URLs, and usage links.

### Phase 4: Settings, SEO Infrastructure & Routing
- [ ] **Global Settings Storage:** Implement a `GlobalSettings` mechanism (e.g., `spatie/valuestore` or a dedicated table) to store Website Details and Scripts & Integrations.
- [ ] **Settings Admin Screens:** Build Filament/admin forms for Website Details and Scripts & Integrations, using example fields (logo, favicon, phone, GA script, webhook URL).
- [ ] **Redirect Model & 404 Middleware:** Implement `Redirect` model, middleware to intercept 404s and perform 301/302 based on rules, and a custom 404 Blade template.
- [ ] **Sitemap Generation:** Configure `spatie/laravel-sitemap` (or equivalent) to generate `sitemap.xml` including only `published` public pages.
- [ ] **Structured Data Engine Skeleton:** Implement the basic JSON-LD template system in code (placeholders + mapping from Fixed/Custom/Structured Data Fields and Structured Data Globals) with at least one example template wired to a sample page.

### Phase 5: Admin Panel – Pages, Resources & Media Library
- [ ] **Global Admin Layout:** Implement the shared admin layout (left sidebar navigation + main content area) respecting the nav groups defined in this PRD.
- [ ] **Page & Resource Listings:** Create listing screens for all example page types and content resource types, including filters, status indicators, and "Add New" modals.
- [ ] **Edit Screens – Pages:** Implement the common page edit layout with header, tabbed Page Content / SEO Data sections, and right sidebar (Save, slug, status, timestamps, ID).
- [ ] **Edit Screens – Content Resources:** Implement the simplified edit layout for Content Resources, reusing patterns from pages where appropriate.
- [ ] **Media Library Views:** Implement the Media Library admin views (grid + table) filtered by media type/metadata, integrating upload, metadata editing, and usage display with the Media Engine.

### Phase 6: API Layer & Automation
- [ ] **Sanctum:** Enable Sanctum authentication and create an "Automation" user/token for external tools (Notion, n8n).
- [ ] **Page API:** Implement `POST /api/v1/pages/{type}` to create/update static and custom pages, including Fixed/Custom/Reusable/Structured Data Fields and relationships payloads.
- [ ] **Content Resource API:** Implement `POST /api/v1/content/{type}` to create/update Content Resources, including relationships.
- [ ] **Validation & Error Handling:** Add strict validation for all API payloads with clear, non-sensitive error messages.
- [ ] **Example Flows:** Create at least one example n8n (or curl/Postman) flow that creates/updates a Service and its related FAQs via the API, verifying end-to-end behavior.

### Phase 7: Frontend Templating (The "Vibe" Phase)
- [ ] **Base Layout:** Create `layouts/app.blade.php` and wire in SEO head tags, global scripts, and structured data output.
- [ ] **Atomic Components:** Implement core Blade components (e.g., `<x-container>`, `<x-button>`, `<x-hero>`) styled with SCSS + CSS variables.
- [ ] **Routing & Controllers:** Implement controllers/routes for static and custom pages, resolving slugs and hierarchy, and passing fully-hydrated view models to Blade.
- [ ] **Flexible Content Rendering:** Implement the rendering loop that reads JSON/`jsonb` content blocks and maps them to Blade components, with at least a few example sections (hero, CTA, FAQs).

### Phase 8: Performance, Caching & QA
- [ ] **Response Caching:** Install and configure `spatie/laravel-responsecache` (or similar) for public pages.
- [ ] **Media & Asset Tuning:** Verify CloudFront caching, image sizes, and CLS/LCP behavior on example pages.
- [ ] **SEO & Schema QA:** Validate structured data, sitemaps, redirects, and meta tags using external tools (e.g., Rich Results Test, Search Console).
- [ ] **Lighthouse Audit:** Run Lighthouse on the example site and implement any necessary tweaks to hit the targeted scores.

---

## 6. Developer Guidelines (Context for AI)

1.  **Strict Typing:** All PHP classes must declare `strict_types=1`.
2.  **Filament Best Practice:** Use `schema()` within Resource files. Do not rely on auto-discovery.
3.  **Media Output:** In Blade, always use the `<figure>` tag or the Spatie helper to ensure the WebP version and responsive `srcset` are used.
4.  **No Logic in Views:** Blade files should only display data. Logic belongs in ViewModels or Components.
5.  **Security:** All API endpoints must be guarded by Sanctum. All Admin routes guarded by Filament Auth.
6.  **Styling Separation:** Use Tailwind CSS only within the Admin/Filament UI; public-facing pages must use SCSS + CSS variables for layout and components.
7.  **Per-Project Frontend Styles:** For each new site built on Blueprint, create a fresh SCSS structure, class names, and CSS variables for the public frontend, while reusing the shared admin styling.
8.  **Content Schema Discipline:** Define all fields for pages and Content Resources explicitly as Fixed, Custom, Reusable, or Structured Data Fields in code; avoid ad-hoc JSON blobs or new "meta" tables outside this pattern.
9.  **Relationship Engine Only:** When linking content (pages, resources, media), always use the generic Relationship Engine and its configured pairs; do not introduce custom pivot tables unless this PRD is explicitly extended.
10. **Structured Data Implementation:** Generate all JSON-LD via the Structured Data Engine templates in code; never hard-code schema JSON directly inside Blade views, and always reuse existing fields before adding schema-only fields.
11. **Media Pipeline Discipline:** All media must go through the Media Engine (S3 `/temp` → `/permanent`, conversions, optimization) and be served via CloudFront URLs; never bypass this pipeline or serve files directly from local storage.
12. **Example Data for Each Feature:** Every new feature must ship with minimal seed/example data (example pages, resources, media, templates) so its behavior can be verified end-to-end by humans and AI tools.
13. **Laravel Boost MCP First:** When implementing Laravel/PHP code in this project, follow the conventions and recommendations provided by the `laravel/boost` package (Laravel Boost MCP) as the primary source of patterns and best practices, extending this PRD only where necessary.