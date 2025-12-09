# Feature Specification: Core Content Models & Architecture

**Feature Branch**: `001-core-content-models`
**Created**: 2025-12-08
**Status**: Draft
**Phase**: 2 of Blueprint CMS Development

## Overview

This specification defines the core content architecture for Blueprint CMS - the data models, content storage mechanisms, and relationship system that form the backbone of the CMS. This phase creates the foundational structures that enable editors to create and manage all website content.

**Why this matters**: Without these core models, no content can be created or managed. This phase unblocks all future development - admin interfaces, API endpoints, frontend templates, and SEO features all depend on this content architecture.

## Primary Users

**Content Editor**: A non-technical user who creates and manages website content through the admin interface. They need to create pages with different layouts, add supporting content like FAQs and testimonials, and link related content together. They expect content to be organized logically and saved reliably.

**Developer**: A technical user who extends the CMS for client projects. They need well-structured models with clear patterns, predictable data storage, and a flexible system that can accommodate custom page types and content resources without modifying core code.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Create and Publish a Static Page (Priority: P1)

An editor creates a new static page (like Home, About, or Contact). They select a template that determines the page's layout structure. They fill in the page name, which auto-generates a URL slug. They add content blocks (sections) specific to that page's design - perhaps a hero section, then a call-to-action section. They complete the SEO information (meta title, description, Open Graph data). They save the page as a draft, preview it, then publish when ready.

**Why this priority**: Static pages are the foundation of any website. Without the ability to create pages, the CMS cannot serve its primary purpose. This story validates the entire content model architecture.

**Independent Test**: Can be fully tested by creating a static page with content blocks, setting SEO data, and toggling between draft/published status. Delivers a complete page creation workflow.

**Acceptance Scenarios**:

1. **Given** an editor is logged into the admin panel, **When** they create a new static page with name "About Us", **Then** the system auto-generates slug "about-us" and saves the page in draft status
2. **Given** a draft page exists, **When** the editor adds a Hero block with heading, lede, and image reference, **Then** the block data is saved and retrievable
3. **Given** a draft page with content, **When** the editor changes status to published, **Then** the page becomes accessible at its URL path
4. **Given** a published page, **When** a visitor accesses a draft page URL, **Then** they receive a 404 response

---

### User Story 2 - Create Custom Page Type Records (Priority: P1)

An editor creates multiple records of a custom page type (like Services or Blog Posts). Each record shares the same template/structure as other records of that type, but with unique content. For example, all Service pages have the same layout, but each service has its own hero content, descriptions, and related FAQs. The editor fills in the content blocks, adds SEO data, and publishes.

**Why this priority**: Custom page types enable repeatable content structures - essential for business websites with services, portfolios, or blogs. This validates that the architecture supports multiple content models.

**Independent Test**: Can be tested by creating multiple Service records with different content, verifying they share the same structure but have unique URLs at /services/{slug}.

**Acceptance Scenarios**:

1. **Given** an editor wants to add a new service, **When** they create a Service record with name "Web Design", **Then** the system creates it with slug "web-design" accessible at /services/web-design
2. **Given** multiple Service records exist, **When** querying all services, **Then** all records have the same database columns (name, slug, status, content_blocks, all SEO fields) with identical data types
3. **Given** a Service record exists, **When** the editor adds content blocks and SEO data, **Then** the data is stored in the same JSONB format and same SEO columns as static Page records (content_blocks as JSONB array, SEO fields as individual columns)

---

### User Story 3 - Create Content Resources (Priority: P2)

An editor creates supporting content that doesn't have its own public page - like FAQs or Testimonials. These are simple records with a name for internal reference, a status (draft/published), and content fields specific to that resource type. A FAQ has a question and answer. A Testimonial has author name, title, location, quote, and optionally a rating and photo.

**Why this priority**: Content resources are building blocks that populate page sections. While pages can exist without them, rich content experiences require these supporting elements.

**Independent Test**: Can be tested by creating FAQ and Testimonial records, verifying they have no public URL and only published resources are available for relationships.

**Acceptance Scenarios**:

1. **Given** an editor wants to add an FAQ, **When** they create an FAQ with question and answer, **Then** the FAQ is saved with an internal name and draft status
2. **Given** a Testimonial is created with author info and quote, **When** attempting to access it via URL, **Then** no public route exists for the resource
3. **Given** a draft FAQ exists, **When** it is linked to a published page, **Then** the FAQ does not appear on the frontend

---

### User Story 4 - Link Related Content (Priority: P2)

An editor connects content together. They're editing a Service page and want to show related FAQs at the bottom. They open the relationships panel, search for relevant FAQs, select them, and arrange them in the desired display order. Later, they also link this Service to related Blog Posts. The relationship system is the same regardless of what content types are being connected.

**Why this priority**: Relationships enable rich content experiences and content reuse. Without relationships, each page would be isolated, requiring content duplication.

**Independent Test**: Can be tested by linking FAQs to a Service, reordering them, and verifying the relationship data is retrievable with correct order. Also test linking Services to Blog Posts to verify cross-type relationships work.

**Acceptance Scenarios**:

1. **Given** a Service page and multiple FAQs exist, **When** the editor links 3 FAQs to the Service with specific order (FAQ-B, FAQ-A, FAQ-C), **Then** retrieving the Service's related FAQs returns them in that exact order
2. **Given** a Service is linked to Blog Posts, **When** querying relationships, **Then** the same relationship mechanism works for page-to-page links as for page-to-resource links
3. **Given** FAQ-A is linked to Service-1, **When** querying from FAQ-A's perspective, **Then** the relationship to Service-1 is discoverable (bidirectional awareness)

---

### User Story 5 - Manage SEO Data (Priority: P2)

An editor manages SEO data for their pages so that they rank well in search engines. They edit meta title, description, and author. They toggle indexability. They set Open Graph and Twitter card data, which defaults from meta fields until manually edited. They configure breadcrumbs for site structure.

**Why this priority**: SEO is critical for website success but pages can technically function without it. It's essential for production use but not for validating core architecture.

**Independent Test**: Can be tested by setting SEO fields on a page, verifying defaults work correctly, and confirming that manual edits break the mirroring relationship.

**Acceptance Scenarios**:

1. **Given** a page with meta title "Our Services", **When** checking OG Title without manual edit, **Then** OG Title mirrors meta title as "Our Services"
2. **Given** a page with mirrored OG Title, **When** the editor manually sets OG Title to "Services | Our Company", **Then** OG Title becomes independent and no longer mirrors meta title
3. **Given** a page needs breadcrumbs, **When** the editor adds breadcrumb trail items, **Then** each item stores name, URL, and position in correct order

---

### User Story 6 - Delete and Recover Content (Priority: P3)

An editor works with draft content, publishes it, and later needs to remove it. When they delete content, it goes to a recovery state rather than being permanently removed. They have a time window to restore accidentally deleted content. After that window, the content is permanently purged.

**Why this priority**: Soft delete is a safety feature. Core functionality works without it, but it prevents data loss in production.

**Independent Test**: Can be tested by deleting a page, verifying it's hidden from normal queries, restoring it within the recovery period, and confirming permanent purge after expiration.

**Acceptance Scenarios**:

1. **Given** a published page exists, **When** the editor deletes it, **Then** the page is hidden from public access and normal admin queries but not permanently removed
2. **Given** a soft-deleted page within recovery period, **When** the editor restores it, **Then** the page returns to its previous state with all content and relationships intact
3. **Given** a soft-deleted page past recovery period, **When** the purge process runs, **Then** the page and all its relationships are permanently deleted

---

### User Story 7 - URL Slug Management (Priority: P3)

An editor expects URL slugs to be automatically handled so they don't create duplicate URLs. Slugs are auto-generated from page names, editable afterward, and unique within each page type.

**Why this priority**: Slug management is convenience and data integrity. Core functionality can work with manual slug entry, but auto-generation improves user experience.

**Independent Test**: Can be tested by creating pages with duplicate names and verifying automatic suffix addition (-2, -3), and by editing slugs manually.

**Acceptance Scenarios**:

1. **Given** a page named "Contact Us" exists with slug "contact-us", **When** creating another page named "Contact Us", **Then** the system auto-generates slug "contact-us-2"
2. **Given** a Service with slug "consulting" exists, **When** creating a static Page with the same slug "consulting", **Then** both can coexist (same slug allowed across different page types)
3. **Given** a page with slug "old-name", **When** the editor manually changes it to "new-name", **Then** the slug updates and the page is accessible at the new URL

---

### Edge Cases

- What happens when a page's slug conflicts with a reserved system route? → **Maintain reserved slug blocklist; reject with validation error if slug matches reserved route**
- How does the system handle relationships when the target content is deleted? → **Relationships are preserved during soft-delete (hidden but queryable for restore); cascade deleted only on permanent purge**
- What happens when content blocks contain malformed data? → **Validate structure and data types at write-time only; allow partial/incomplete blocks (no required fields enforced)**
- How does the system behave when SEO fields exceed character limits? → **Standard Laravel validation; truncate at database level if validation bypassed**
- What happens when a user attempts to create a content block of an undefined type? → **Reject with validation error if block type class not found in `app/Blocks/`; prevent save**
- What happens when the name field is empty during slug generation? → **Throw validation error; name is required for slug generation**
- What happens when the name exceeds 255 characters during slug generation? → **Str::slug truncates to reasonable length; slug max 255 chars enforced at database**
- What is the maximum number of content blocks per page? → **Unlimited; no artificial limit imposed**
- What is the maximum number of relationships per model? → **Unlimited; no artificial limit imposed**
- What happens when creating a duplicate relationship (same source-target pair)? → **Unique constraint prevents duplicates; update order if relationship exists**
- What happens when both source and target of a relationship are soft-deleted? → **Relationship preserved; hidden from normal queries but restorable**
- What is the maximum suffix attempts for duplicate slugs? → **1000 attempts max; throw exception if all exhausted (edge case)**
- What happens with empty content_blocks? → **NULL and empty array [] are both valid; NULL indicates no blocks defined, [] indicates explicitly empty**
- What happens when querying relationships where target is soft-deleted? → **Target excluded from relationship queries unless withTrashed() is used**

## Requirements *(mandatory)*

### Functional Requirements

**Content Models**

- **FR-001**: System MUST support static pages with unique templates (home, about, contact, etc.)
- **FR-002**: System MUST support custom page types where all records share the same template structure
- **FR-003**: System MUST support content resources (FAQ, Testimonial) without public URLs
- **FR-004**: All page-like models MUST have name, slug, status (draft/published), and timestamps
- **FR-005**: All publicly routable content MUST include SEO fields

**Content Blocks**

- **FR-006**: System MUST store page content as ordered list of typed blocks
- **FR-007**: Each content block MUST have a type identifier and data payload
- **FR-008**: System MUST support adding, removing, and reordering blocks
- **FR-009**: System MUST validate block data structure and types at write-time; reject structurally invalid data with clear error messages but allow partial/incomplete blocks (no required fields enforced)
- **FR-009a**: Block types MUST be defined as PHP classes in `app/Blocks/` directory, each class containing schema definition and validation rules (code-defined, not database-configured)
- **FR-009b**: System MUST reject blocks with undefined type (no matching class in `app/Blocks/`) with validation error; prevent save

**Relationship Engine**

- **FR-010**: System MUST provide generic many-to-many relationships between any content types
- **FR-011**: Relationships MUST preserve manual sort order
- **FR-012**: Relationships MUST be queryable from either side (bidirectional awareness)
- **FR-013**: System MUST NOT enforce predefined "allowed pairs" - any model can relate to any model

**SEO Management**

- **FR-014**: System MUST store meta information (title, description, author, robots, canonical URL)
- **FR-015**: System MUST store Open Graph data (title, description, type, image)
- **FR-016**: System MUST store Twitter Card data (title, description, image)
- **FR-017**: System MUST store breadcrumb data (current page name/URL, ordered trail items)
- **FR-018**: OG and Twitter title/description MUST mirror meta fields until manually edited

**URL Slug Management**

- **FR-019**: System MUST auto-generate slugs from page names
- **FR-020**: System MUST allow manual slug editing after creation
- **FR-021**: Slugs MUST be unique within each page type
- **FR-022**: System MUST auto-append suffix (-2, -3, etc.) for duplicate slugs
- **FR-022a**: System MUST maintain a reserved slug blocklist and reject slugs matching reserved system routes (e.g., admin, api, login) with validation error

**Soft Delete**

- **FR-023**: System MUST soft-delete content rather than permanent deletion
- **FR-024**: Soft-deleted content MUST be hidden from normal queries and public access
- **FR-025**: System MUST support restoring soft-deleted content
- **FR-026**: System MUST permanently purge content after configurable recovery period (default: 30 days via environment variable)
- **FR-026a**: Relationships MUST be preserved when content is soft-deleted (enabling full restore); relationships MUST be cascade deleted only when content is permanently purged

### Key Entities

- **Page**: Static page with unique template, name, slug, status, SEO fields, and ordered content blocks. Accessible at /{slug}
- **Service**: Custom page type record representing a service offering. Shares template with other services. Accessible at /services/{slug}
- **BlogPost**: Custom page type record representing a blog article. Shares template with other blog posts. Accessible at /blog/{slug}
- **Faq**: Content resource with question and answer fields. No public URL. Linked to pages via relationship engine
- **Testimonial**: Content resource with author info, quote, optional rating and avatar. No public URL. Linked to pages via relationship engine
- **ContentRelation**: Generic relationship record linking any source model to any target model with display order

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All page-like models can be created, read, updated, and soft-deleted without errors
- **SC-002**: Content blocks are correctly stored as structured data and retrieved with identical structure
- **SC-003**: Relationships can link any model to any model without custom code or configuration
- **SC-004**: Relationship order is preserved exactly as set (position 1, 2, 3 returns in that sequence)
- **SC-005**: SEO field mirroring correctly defaults OG/Twitter fields from meta fields
- **SC-006**: SEO mirroring stops when a field is manually edited (independence is maintained)
- **SC-007**: Slug auto-generation creates valid URL-safe slugs from page names
- **SC-008**: Duplicate slug detection correctly appends unique suffix (-2, -3, etc.)
- **SC-009**: Soft-deleted content is hidden from all public queries
- **SC-010**: Soft-deleted content can be restored with all data and relationships intact
- **SC-011**: Draft content resources do not appear on frontend even when linked to published pages
- **SC-012**: 100% test coverage on content block validation logic
- **SC-013**: 100% test coverage on relationship creation/ordering/deletion

## Assumptions

- Admin authentication is already in place (Phase 1 complete)
- Database infrastructure (PostgreSQL) is configured and operational
- Soft delete recovery period defaults to 30 days, configurable via `CONTENT_RECOVERY_DAYS` environment variable
- Content block validation rejects invalid data with clear error messages rather than storing with warnings
- Reserved system routes will be documented and slug validation will check against them
- When soft-deleted content is permanently purged, all its relationships are cascade deleted
- The system will use Laravel's built-in SoftDeletes trait for soft delete functionality

## Scope

### In Scope

- Page model with template support and content blocks
- Service model (custom page type example)
- BlogPost model (custom page type example)
- Faq model (content resource example)
- Testimonial model (content resource example)
- ContentRelation model for generic relationships
- HasSeo trait for SEO field management
- HasContentBlocks trait for content block management
- HasRelations trait for relationship management
- Slug auto-generation and uniqueness validation
- Soft delete with recovery period
- Database migrations for all models
- Model factories and seeders for testing
- Unit and feature tests for all functionality

### Out of Scope

- Filament admin resources (Phase 5)
- Media library integration (Phase 3)
- API endpoints (Phase 6)
- Frontend Blade templates (Phase 7)
- Structured data/JSON-LD generation (separate spec)
- Response caching (Phase 8)
- Hierarchical page types (future enhancement)
- Content scheduling (future enhancement)
- Content versioning/revision history (future enhancement)
- Breadcrumb auto-generation logic (implemented in Phase 5 admin)

## Dependencies

- Phase 1 completion (environment, authentication, queue system)
- PostgreSQL database with JSONB support for content blocks
- Laravel's SoftDeletes trait
- Laravel's Str helper for slug generation

## Clarifications

### Session 2025-12-08

- Q: Where should content block type definitions live? → A: PHP Classes - Block types defined as PHP classes in `app/Blocks/` with schema and validation
- Q: How should the system handle relationships when target content is deleted? → A: Preserve Until Purge - Relationships kept during soft-delete, cascade deleted only on permanent purge
- Q: How should content block validation handle malformed data? → A: Validate structure only, no required fields - validate data types and structure at write-time but allow partial/incomplete block data
- Q: What happens when a page's slug conflicts with a reserved system route? → A: Blocklist Reject - Maintain reserved slug list; reject with validation error if slug matches
- Q: What happens when a user attempts to create a content block of an undefined type? → A: Reject - Validation error if block type class not found; prevent save
