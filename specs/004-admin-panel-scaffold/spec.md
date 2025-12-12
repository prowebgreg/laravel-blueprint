# Feature Specification: Admin Panel Scaffold

**Feature Branch**: `004-admin-panel-scaffold`
**Created**: 2025-12-12
**Status**: Draft
**Input**: User description: "Create the Admin Panel Scaffold for The Blueprint CMS - a visual foundation with placeholder pages, consistent styling, and shared component patterns."

## Overview

This specification defines the **scaffold phase** of the Blueprint CMS admin panel: creating all admin pages with proper navigation, consistent theming inspired by shadcn/ui design system, and placeholder content. Full functionality for each page will be implemented through separate specifications.

The admin panel must feel cohesive - all listings share the same visual patterns, all edit screens follow identical layouts, and all UI components (buttons, modals, tables, form fields) are visually consistent throughout.

**Primary Users**: CMS Administrators who manage website content via `/admin`
**Secondary Users**: Developers extending the admin panel with new resources

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Access Admin Panel and Dashboard (Priority: P1)

An administrator navigates to `/admin`, sees a login screen styled consistently with the admin theme. After logging in, they land on the Dashboard showing placeholder stat cards and quick links to common actions.

**Why this priority**: The dashboard is the entry point for all admin interactions. Without it, no other functionality is accessible.

**Independent Test**: Can be fully tested by navigating to `/admin`, logging in, and verifying the dashboard displays with welcome message, placeholder stats, and quick action links.

**Acceptance Scenarios**:

1. **Given** an unauthenticated user, **When** they navigate to `/admin`, **Then** they see a login page styled with the admin theme
2. **Given** valid credentials, **When** the administrator logs in, **Then** they are redirected to the Dashboard
3. **Given** a logged-in administrator, **When** they view the Dashboard, **Then** they see a welcome message with their name, placeholder stat cards (Total Pages, Total Posts, Media Items), and quick action links (Create Page, Create Post, Upload Media)
4. **Given** a logged-in administrator, **When** they view the Dashboard, **Then** they see a recent activity section with placeholder entries (e.g., "Page updated", "Post created", "Media uploaded")
5. **Given** a logged-in administrator, **When** they view the Dashboard, **Then** all visual elements use the defined color palette and Geist font

---

### User Story 2 - Navigate Content Sections via Sidebar (Priority: P1)

From the sidebar, the administrator can access any content section. The sidebar is organized into logical groups (Public Pages, Content Resources, Media Library, SEO, Settings, Account). Each group expands to show its items.

**Why this priority**: Navigation is fundamental to accessing all admin features. Users cannot manage content without working navigation.

**Independent Test**: Can be tested by clicking each sidebar group and verifying all items are present and link to their respective pages.

**Acceptance Scenarios**:

1. **Given** a logged-in administrator, **When** they view the sidebar, **Then** they see these navigation groups: Public Pages, Content Resources, Media Library, SEO, Settings, Account
2. **Given** a collapsed navigation group, **When** the administrator clicks the group, **Then** it expands to show its items
3. **Given** the Public Pages group, **When** expanded, **Then** it shows: All Public Pages, Static Pages, Services, Blog Posts
4. **Given** the Content Resources group, **When** expanded, **Then** it shows: FAQs, Testimonials
5. **Given** the Media Library group, **When** expanded, **Then** it shows: Images, Videos, SVG, Brand Assets
6. **Given** the SEO group, **When** expanded, **Then** it shows: Sitemap, Redirects, Structured Data, 404 Pages
7. **Given** the Settings group, **When** expanded, **Then** it shows: Website Details, Scripts & Integrations, Users
8. **Given** the Account group, **When** expanded, **Then** it shows: Profile, Logout
9. **Given** any navigation item, **When** clicked, **Then** the administrator is taken to the corresponding page with breadcrumbs showing the current location

---

### User Story 3 - View Content Listings with Consistent Layout (Priority: P2)

When accessing any content type (Pages, Services, Blog Posts, FAQs, Testimonials), the administrator sees a consistent table layout with columns, filters, and action buttons. All listing pages share the same visual structure regardless of content type.

**Why this priority**: Content listings are the primary way administrators find and access records. Consistent patterns reduce learning curve.

**Independent Test**: Can be tested by navigating to any two listing pages (e.g., Static Pages and FAQs) and verifying they share identical visual structure.

**Acceptance Scenarios**:

1. **Given** the Static Pages listing, **When** an administrator views it, **Then** they see a table with columns: Title, Slug, Status, Updated
2. **Given** the Services listing, **When** an administrator views it, **Then** the table structure matches Static Pages exactly
3. **Given** the Blog Posts listing, **When** an administrator views it, **Then** the table structure matches Static Pages exactly
4. **Given** the FAQs listing, **When** an administrator views it, **Then** they see a table with columns: Question, Status, Updated
5. **Given** the Testimonials listing, **When** an administrator views it, **Then** they see a table with columns: Name/Author, Status, Updated
6. **Given** any content listing, **When** an administrator views it, **Then** status badges use consistent styling (Published=green, Draft=gray)
7. **Given** any content listing, **When** an administrator views it, **Then** action buttons (Edit, Delete) appear in the same positions with consistent styling
8. **Given** the "All Public Pages" view, **When** an administrator views it, **Then** they see records from Pages, Services, and Blog Posts with a Type indicator column

---

### User Story 4 - Edit Complex Content with Tabbed Layout (Priority: P2)

For complex content (Pages, Services, Blog Posts), clicking edit opens a full page with header showing the item name and back link, main area with tabbed sections, and a right sidebar with Save button and metadata.

**Why this priority**: Edit pages are where administrators spend most of their time. A predictable layout improves efficiency.

**Independent Test**: Can be tested by navigating to any complex content edit page and verifying the layout includes header, tabbed sections, and right sidebar.

**Acceptance Scenarios**:

1. **Given** the Static Pages listing, **When** an administrator clicks "Edit" on a page, **Then** they see a full-page edit layout
2. **Given** a complex content edit page, **When** an administrator views it, **Then** the header shows: item name, back link to listing, status selector
3. **Given** a complex content edit page, **When** an administrator views it, **Then** the main area has tabbed sections (e.g., "Page Content", "SEO Data")
4. **Given** a complex content edit page, **When** an administrator views it, **Then** the right sidebar shows: Save button, slug field placeholder, timestamps, record ID
5. **Given** the Services edit page, **When** an administrator views it, **Then** the layout structure matches Static Pages edit page exactly
6. **Given** the Blog Posts edit page, **When** an administrator views it, **Then** the layout structure matches Static Pages edit page exactly

---

### User Story 5 - Create and Edit Simple Content with Modal (Priority: P2)

For simple content (FAQs, Testimonials), creation happens via a quick modal with basic fields, then redirects to a simplified edit form without tabs or right sidebar.

**Why this priority**: Simple content types need streamlined workflows to enable quick data entry.

**Independent Test**: Can be tested by clicking "Create" on FAQs listing, filling the modal, and verifying redirect to simplified edit form.

**Acceptance Scenarios**:

1. **Given** the FAQs listing, **When** an administrator clicks "Create", **Then** a modal appears with basic fields (Question, Answer, Status)
2. **Given** a filled FAQ creation modal, **When** the administrator submits, **Then** they are redirected to the FAQ edit page
3. **Given** the FAQ edit page, **When** an administrator views it, **Then** they see a simplified single-section form (no tabs, no right sidebar)
4. **Given** the Testimonials listing, **When** an administrator clicks "Create", **Then** a modal appears with basic fields (Author Name, Content, Status)
5. **Given** the Testimonial edit page, **When** an administrator views it, **Then** the layout matches the FAQ edit page structure

---

### User Story 6 - Browse Media Library by Type (Priority: P2)

The Media Library section shows different views filtered by content type (Images, Videos, SVG, Brand Assets). Each view offers grid and table display options with placeholder content.

**Why this priority**: Media management is essential for content-rich websites. Type-filtered views help administrators find assets quickly.

**Independent Test**: Can be tested by navigating to each Media Library page and verifying distinct filtering and view toggle functionality.

**Acceptance Scenarios**:

1. **Given** the Images page, **When** an administrator views it, **Then** they see a grid view (default) with placeholder thumbnails and a table view toggle
2. **Given** the Videos page, **When** an administrator views it, **Then** they see a table view (default) with video metadata columns
3. **Given** the SVG page, **When** an administrator views it, **Then** they see a grid view showing SVG previews
4. **Given** the Brand Assets page, **When** an administrator views it, **Then** they see grid/table toggle with placeholder logos and favicons
5. **Given** any Media Library page, **When** an administrator views it, **Then** they see a placeholder upload area

---

### User Story 7 - Manage SEO Settings (Priority: P3)

The SEO section provides pages for Sitemap, Redirects, Structured Data, and 404 Pages configuration with placeholder content.

**Why this priority**: SEO settings are important but used less frequently than content management. Placeholder structure establishes future functionality.

**Independent Test**: Can be tested by navigating to each SEO page and verifying placeholder content is present.

**Acceptance Scenarios**:

1. **Given** the Sitemap page, **When** an administrator views it, **Then** they see placeholder sitemap controls and a "Generate Sitemap" button
2. **Given** the Redirects page, **When** an administrator views it, **Then** they see a table with columns: Source URL, Target URL, Type, Status
3. **Given** the Structured Data page, **When** an administrator views it, **Then** they see sectioned form fields for global structured data
4. **Given** the 404 Pages page, **When** an administrator views it, **Then** they see configuration area for 404 handling

---

### User Story 8 - Configure Website Settings (Priority: P3)

Settings pages (Website Details, Scripts & Integrations) display sectioned forms. Scripts & Integrations uses tabs to separate Scripts, APIs, and Webhooks sections.

**Why this priority**: Settings are configured once and rarely changed. Placeholder structure establishes future functionality.

**Independent Test**: Can be tested by navigating to each Settings page and verifying form structure and tabs.

**Acceptance Scenarios**:

1. **Given** the Website Details page, **When** an administrator views it, **Then** they see sections: Identity (Logo, Site Name, Tagline), Contact (Phone, Email, Address), Social Links
2. **Given** the Scripts & Integrations page, **When** an administrator views it, **Then** they see three tabs: Scripts, APIs, Webhooks
3. **Given** the Scripts tab, **When** an administrator views it, **Then** they see code input areas for Head scripts, Body scripts, Footer scripts
4. **Given** the APIs tab, **When** an administrator views it, **Then** they see placeholder API key fields
5. **Given** the Webhooks tab, **When** an administrator views it, **Then** they see placeholder webhook URL fields
6. **Given** the Users page, **When** an administrator views it, **Then** they see a table listing with columns: Name, Email, Created

---

### User Story 9 - Toggle Dark/Light Theme (Priority: P3)

The administrator can toggle between dark and light themes. The toggle is accessible from the top navigation area. Both themes use the defined color variables for consistent appearance.

**Why this priority**: Theme preference improves user comfort but is not essential for functionality.

**Independent Test**: Can be tested by clicking the theme toggle and verifying colors change correctly across all elements.

**Acceptance Scenarios**:

1. **Given** the admin panel in light mode, **When** an administrator clicks the theme toggle, **Then** the interface switches to dark mode
2. **Given** dark mode, **When** the administrator clicks the theme toggle, **Then** the interface switches back to light mode
3. **Given** a theme switch, **When** completed, **Then** all colors update according to the defined dark/light palette
4. **Given** a theme preference, **When** the administrator returns later, **Then** their preference is persisted

---

### User Story 10 - Collapse Sidebar for More Workspace (Priority: P3)

The administrator can collapse the sidebar to icons-only view to gain more workspace when editing content.

**Why this priority**: Workspace optimization is a quality-of-life improvement, not core functionality.

**Independent Test**: Can be tested by clicking the collapse toggle and verifying sidebar transitions to icons-only.

**Acceptance Scenarios**:

1. **Given** an expanded sidebar, **When** the administrator clicks the collapse toggle, **Then** the sidebar collapses to show only icons
2. **Given** a collapsed sidebar, **When** the administrator hovers over an icon, **Then** they see a tooltip with the item name
3. **Given** a collapsed sidebar, **When** the administrator clicks the expand toggle, **Then** the sidebar returns to full width

---

### Edge Cases

- What happens when the database has no placeholder data? Empty state messages should appear with clear instructions.
- What happens on tablet-sized screens (768px)? Sidebar should auto-collapse to icons; tables should scroll horizontally.
- What happens if Geist font fails to load? System sans-serif should be used as fallback without breaking layout.
- What happens when navigating to a non-existent page? Standard 404 handling should apply.

## Requirements *(mandatory)*

### Functional Requirements

**Panel Configuration**
- **FR-001**: System MUST provide an admin panel accessible at `/admin`
- **FR-002**: System MUST display a login page with styling consistent with the admin theme
- **FR-003**: System MUST support dark mode with user toggle in top navigation
- **FR-004**: System MUST display a custom placeholder logo SVG in the sidebar
- **FR-005**: System MUST display breadcrumbs on all pages showing the navigation hierarchy
- **FR-006**: System MUST display a user menu in the top-right corner
- **FR-007**: System MUST display a notification bell placeholder

**Navigation**
- **FR-008**: System MUST display sidebar navigation with collapsible groups
- **FR-009**: System MUST organize navigation into groups: Public Pages, Content Resources, Media Library, SEO, Settings, Account
- **FR-010**: System MUST allow sidebar to collapse to icons-only view

**Dashboard**
- **FR-011**: System MUST display a welcome message with the user's name on the Dashboard
- **FR-012**: System MUST display placeholder stat cards on the Dashboard (Total Pages, Total Posts, Media Items)
- **FR-013**: System MUST display quick action links on the Dashboard: "Create Page" (→ PageResource create), "Create Post" (→ BlogPostResource create), "Upload Media" (→ ImagesPage)
- **FR-014**: System MUST display a recent activity placeholder on the Dashboard with 5 sample entries showing action type, target, and timestamp

**Content Resources**
- **FR-015**: System MUST provide listing pages for Static Pages, Services, Blog Posts with consistent table structure
- **FR-016**: System MUST provide an "All Public Pages" view consolidating all page types with a Type indicator column
- **FR-017**: System MUST provide listing pages for FAQs and Testimonials with simplified table structure
- **FR-018**: System MUST provide full-page edit layouts for complex content with header, tabbed main area, and right sidebar
- **FR-019**: System MUST provide modal-based creation for simple content (FAQs, Testimonials)
- **FR-020**: System MUST provide simplified edit forms for simple content (no tabs, no right sidebar)

**Media Library**
- **FR-021**: System MUST provide separate pages for Images, Videos, SVG, and Brand Assets
- **FR-022**: System MUST provide grid and table view toggles on media pages
- **FR-023**: System MUST display placeholder upload areas on media pages

**SEO**
- **FR-024**: System MUST provide placeholder pages for Sitemap, Redirects, Structured Data, and 404 Pages
- **FR-025**: System MUST display a placeholder Redirects table with columns: Source URL, Target URL, Type, Status

**Settings**
- **FR-026**: System MUST provide a Website Details page with sections: Identity, Contact, Social Links
- **FR-027**: System MUST provide a Scripts & Integrations page with tabs: Scripts, APIs, Webhooks
- **FR-028**: System MUST provide a Users listing and edit page

**Visual Consistency**
- **FR-029**: System MUST apply the same status badge styling across all listing pages
- **FR-030**: System MUST apply the same action button styling and positioning across all listing pages
- **FR-031**: System MUST apply consistent form field spacing and label styling across all forms
- **FR-032**: System MUST use the defined color palette for all UI elements in both light and dark modes

**Responsiveness**
- **FR-033**: System MUST function on tablet-sized screens (768px+)
- **FR-034**: System MUST collapse sidebar to icons on smaller screens
- **FR-035**: System MUST make tables horizontally scrollable on constrained widths

### Key Entities

- **Page**: Static pages with name, slug, status (draft/published), content_blocks placeholder, SEO fields placeholder
- **Service**: Custom page type with name, slug, status, content_blocks placeholder, SEO fields placeholder
- **BlogPost**: Custom page type with name, slug, status, content_blocks placeholder, published_at, SEO fields placeholder
- **Faq**: Content resource with question, answer, status, sort_order
- **Testimonial**: Content resource with author_name, content, status, sort_order
- **User**: Standard user model (existing)
- **Redirect**: Redirect rules with source_path, target_path, type (301/302), is_active

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All 23 sidebar navigation items are present and link to their respective pages
- **SC-002**: Any two listing pages (e.g., Static Pages and Blog Posts) are visually indistinguishable in table structure
- **SC-003**: All complex content edit pages (Pages, Services, Blog Posts) have identical layout structure
- **SC-004**: All simple content edit pages (FAQs, Testimonials) have identical layout structure
- **SC-005**: Theme toggle switches between dark and light modes with all colors updating correctly
- **SC-006**: Admin panel functions correctly on 768px viewport width
- **SC-007**: Each listing page displays 3-5 placeholder records demonstrating the UI
- **SC-008**: All form fields have associated labels and maintain consistent spacing
- **SC-009**: All interactive elements are keyboard accessible
- **SC-010**: All status badges use consistent styling: Published (green), Draft (gray)

## Assumptions

1. **Authentication**: Basic Filament authentication is used. No custom authentication flows or role-based permissions are needed for this scaffold.
2. **Font Loading**: Geist font files will be placed in `resources/fonts/admin/`. If unavailable, system sans-serif is an acceptable fallback.
3. **Placeholder Data**: Database seeders will populate 3-5 records per content type for demonstration purposes.
4. **No CRUD Functionality**: This scaffold does not include actual create, read, update, or delete operations. Forms are structural only.
5. **No Data Persistence**: Settings and form inputs do not save to the database in this phase.
6. **Theme Colors**: The provided shadcn/ui-inspired color palette (oklch-based) will be adapted for Filament's theming system.
7. **Existing Models**: Page, Service, BlogPost, Faq, Testimonial models may already exist from Phase 2. This spec defines UI representation only.

## Out of Scope

- Actual CRUD functionality (create, read, update, delete operations)
- Form validation and error handling
- Media upload and processing
- Settings persistence to database
- API endpoints
- Real data queries (using placeholders/seeders only)
- Full SEO trait implementation
- Relationship Engine integration
- Content blocks/builder functionality
- Frontend public pages
- Role-based permissions
- Email notifications
- Queue processing
- Caching implementation
