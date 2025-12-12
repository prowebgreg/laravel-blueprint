# Implementation Plan: Admin Panel Scaffold

**Branch**: `004-admin-panel-scaffold` | **Date**: 2025-12-12 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/004-admin-panel-scaffold/spec.md`

## Summary

Create the foundational admin panel for The Blueprint CMS using Filament V3 with a shadcn/ui-inspired design system. This scaffold establishes visual patterns, navigation structure, and placeholder pages that will be enhanced with full functionality in subsequent specifications. The panel features custom Geist typography, oklch-based color tokens with dark mode support, and consistent layout templates for all content management screens.

## Technical Context

**Language/Version**: PHP 8.3.x with `strict_types=1`
**Primary Dependencies**: Laravel 12.x, Filament v3.x, Livewire v3.x, Tailwind CSS v3.x
**Storage**: PostgreSQL 17 (JSONB for content blocks), Redis 7 (queues/cache)
**Testing**: Pest PHP with Laravel plugin
**Target Platform**: Web application (Laravel Sail / Docker)
**Project Type**: Web (Laravel monolith with Filament admin)
**Performance Goals**: Admin panel excluded from Lighthouse requirements per Constitution
**Constraints**: Self-hosted fonts for GDPR compliance, Asset Sovereignty principle
**Scale/Scope**: 23 navigation items, 6 Filament Resources, 11 Custom Pages

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Code-Defined Structure | PASS | Schema defined in PHP models, admin panel reflects code-defined structure |
| II. Performance First | N/A | Admin panel excluded from Lighthouse requirements |
| III. Asset Sovereignty | PASS | Self-hosted Geist fonts, no external CDNs |
| IV. Testing Excellence | PASS | Minimal tests for scaffold phase, full coverage in functional specs |
| V. Clarity Over Cleverness | PASS | Standard Filament patterns, no custom abstractions |
| VI. Example-Driven Development | PASS | AdminScaffoldSeeder provides 3-5 records per model |

**Post-Design Re-Check**: All principles satisfied. Scaffold establishes patterns without violating architecture rules.

## Project Structure

### Documentation (this feature)

```text
specs/004-admin-panel-scaffold/
├── spec.md              # Feature specification
├── plan.md              # This file
├── research.md          # Phase 0 - Filament theming research
├── data-model.md        # Phase 1 - Redirect model definition
├── quickstart.md        # Phase 1 - Setup and verification guide
├── contracts/           # Phase 1 - N/A (no API contracts for scaffold)
└── tasks.md             # Phase 2 - Implementation tasks
```

### Source Code (repository root)

```text
app/
├── Enums/
│   └── RedirectType.php                    # NEW - 301/302 redirect types
├── Filament/
│   ├── Pages/
│   │   ├── Dashboard.php                   # Custom dashboard
│   │   ├── AllPublicPagesPage.php          # Consolidated page listing
│   │   ├── Media/
│   │   │   ├── ImagesPage.php
│   │   │   ├── VideosPage.php
│   │   │   ├── SvgPage.php
│   │   │   └── BrandAssetsPage.php
│   │   ├── Seo/
│   │   │   ├── SitemapPage.php
│   │   │   ├── StructuredDataPage.php
│   │   │   └── NotFoundPagesPage.php
│   │   └── Settings/
│   │       ├── WebsiteDetailsPage.php
│   │       └── ScriptsIntegrationsPage.php
│   ├── Resources/
│   │   ├── PageResource.php                # Template for complex content
│   │   ├── ServiceResource.php             # Clones PageResource pattern
│   │   ├── BlogPostResource.php            # Clones PageResource pattern
│   │   ├── FaqResource.php                 # Template for simple content
│   │   ├── TestimonialResource.php         # Clones FaqResource pattern
│   │   ├── RedirectResource.php            # SEO redirects
│   │   └── UserResource.php                # User management
│   └── Widgets/
│       ├── StatsOverviewWidget.php
│       └── RecentActivityWidget.php
├── Models/
│   └── Redirect.php                        # NEW - URL redirect model
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php          # Panel configuration

database/
├── factories/
│   └── RedirectFactory.php                 # NEW
├── migrations/
│   └── 2025_12_12_000001_create_redirects_table.php  # NEW
└── seeders/
    └── AdminScaffoldSeeder.php             # NEW - Placeholder data

resources/
├── css/
│   └── filament/
│       └── admin/
│           ├── theme.css                   # NEW - Custom theme
│           └── tailwind.config.js          # NEW - Font config
├── fonts/
│   ├── admin/                              # Geist fonts (ALL UPLOADED)
│   │   ├── Geist-*.woff2                   # All weights 100-900 + italics
│   │   └── GeistMono-*.woff2               # All weights 100-900 + italics
│   └── frontend/                           # Empty - future use
└── views/
    └── filament/
        └── logo.blade.php                  # NEW - Custom logo

public/
└── fonts/
    └── admin/                              # Copied from resources/fonts/admin/

tests/
└── Feature/
    └── AdminPanel/
        ├── AdminAccessTest.php
        ├── ModelInstantiationTest.php
        └── NavigationTest.php
```

**Structure Decision**: Laravel web application with Filament admin panel at `/admin`. All admin components follow Filament v3 conventions with custom theming.

---

## Design Tokens

### Complete CSS Variables

The admin panel uses a shadcn/ui-inspired design system with oklch color space for perceptual uniformity.

**File**: `resources/css/filament/admin/theme.css`

```css
:root {
  /* Core semantic colors */
  --background: oklch(0.9900 0 0);
  --foreground: oklch(0 0 0);
  --card: oklch(1 0 0);
  --card-foreground: oklch(0 0 0);
  --popover: oklch(0.9900 0 0);
  --popover-foreground: oklch(0 0 0);
  --primary: oklch(0 0 0);
  --primary-foreground: oklch(1 0 0);
  --secondary: oklch(0.9400 0 0);
  --secondary-foreground: oklch(0 0 0);
  --muted: oklch(0.9700 0 0);
  --muted-foreground: oklch(0.4400 0 0);
  --accent: oklch(0.9400 0 0);
  --accent-foreground: oklch(0 0 0);
  --destructive: oklch(0.6300 0.1900 23.0300);
  --destructive-foreground: oklch(1 0 0);
  --border: oklch(0.9200 0 0);
  --input: oklch(0.9400 0 0);
  --ring: oklch(0 0 0);

  /* Chart colors */
  --chart-1: oklch(0.8100 0.1700 75.3500);
  --chart-2: oklch(0.5500 0.2200 264.5300);
  --chart-3: oklch(0.7200 0 0);
  --chart-4: oklch(0.9200 0 0);
  --chart-5: oklch(0.5600 0 0);

  /* Sidebar-specific colors */
  --sidebar: oklch(0.9900 0 0);
  --sidebar-foreground: oklch(0 0 0);
  --sidebar-primary: oklch(0 0 0);
  --sidebar-primary-foreground: oklch(1 0 0);
  --sidebar-accent: oklch(0.9400 0 0);
  --sidebar-accent-foreground: oklch(0 0 0);
  --sidebar-border: oklch(0.9400 0 0);
  --sidebar-ring: oklch(0 0 0);

  /* Typography */
  --font-sans: Geist, sans-serif;
  --font-serif: Georgia, serif;
  --font-mono: Geist Mono, monospace;

  /* Spacing and sizing */
  --radius: 0.5rem;
  --spacing: 0.25rem;
  --tracking-normal: 0em;

  /* Shadow primitives */
  --shadow-x: 0px;
  --shadow-y: 1px;
  --shadow-blur: 2px;
  --shadow-spread: 0px;
  --shadow-opacity: 0.18;
  --shadow-color: hsl(0 0% 0%);

  /* Shadow scale */
  --shadow-2xs: 0px 1px 2px 0px hsl(0 0% 0% / 0.09);
  --shadow-xs: 0px 1px 2px 0px hsl(0 0% 0% / 0.09);
  --shadow-sm: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 1px 2px -1px hsl(0 0% 0% / 0.18);
  --shadow: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 1px 2px -1px hsl(0 0% 0% / 0.18);
  --shadow-md: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 2px 4px -1px hsl(0 0% 0% / 0.18);
  --shadow-lg: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 4px 6px -1px hsl(0 0% 0% / 0.18);
  --shadow-xl: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 8px 10px -1px hsl(0 0% 0% / 0.18);
  --shadow-2xl: 0px 1px 2px 0px hsl(0 0% 0% / 0.45);
}

.dark {
  /* Core semantic colors - dark mode */
  --background: oklch(0 0 0);
  --foreground: oklch(1 0 0);
  --card: oklch(0.1400 0 0);
  --card-foreground: oklch(1 0 0);
  --popover: oklch(0.1800 0 0);
  --popover-foreground: oklch(1 0 0);
  --primary: oklch(1 0 0);
  --primary-foreground: oklch(0 0 0);
  --secondary: oklch(0.2500 0 0);
  --secondary-foreground: oklch(1 0 0);
  --muted: oklch(0.2300 0 0);
  --muted-foreground: oklch(0.7200 0 0);
  --accent: oklch(0.3200 0 0);
  --accent-foreground: oklch(1 0 0);
  --destructive: oklch(0.6900 0.2000 23.9100);
  --destructive-foreground: oklch(0 0 0);
  --border: oklch(0.2600 0 0);
  --input: oklch(0.3200 0 0);
  --ring: oklch(0.7200 0 0);

  /* Chart colors - dark mode */
  --chart-1: oklch(0.8100 0.1700 75.3500);
  --chart-2: oklch(0.5800 0.2100 260.8400);
  --chart-3: oklch(0.5600 0 0);
  --chart-4: oklch(0.4400 0 0);
  --chart-5: oklch(0.9200 0 0);

  /* Sidebar-specific colors - dark mode */
  --sidebar: oklch(0.1800 0 0);
  --sidebar-foreground: oklch(1 0 0);
  --sidebar-primary: oklch(1 0 0);
  --sidebar-primary-foreground: oklch(0 0 0);
  --sidebar-accent: oklch(0.3200 0 0);
  --sidebar-accent-foreground: oklch(1 0 0);
  --sidebar-border: oklch(0.3200 0 0);
  --sidebar-ring: oklch(0.7200 0 0);

  /* Typography - same as light */
  --font-sans: Geist, sans-serif;
  --font-serif: Georgia, serif;
  --font-mono: Geist Mono, monospace;

  /* Spacing and sizing - same as light */
  --radius: 0.5rem;

  /* Shadow primitives - same as light */
  --shadow-x: 0px;
  --shadow-y: 1px;
  --shadow-blur: 2px;
  --shadow-spread: 0px;
  --shadow-opacity: 0.18;
  --shadow-color: hsl(0 0% 0%);

  /* Shadow scale - same as light */
  --shadow-2xs: 0px 1px 2px 0px hsl(0 0% 0% / 0.09);
  --shadow-xs: 0px 1px 2px 0px hsl(0 0% 0% / 0.09);
  --shadow-sm: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 1px 2px -1px hsl(0 0% 0% / 0.18);
  --shadow: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 1px 2px -1px hsl(0 0% 0% / 0.18);
  --shadow-md: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 2px 4px -1px hsl(0 0% 0% / 0.18);
  --shadow-lg: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 4px 6px -1px hsl(0 0% 0% / 0.18);
  --shadow-xl: 0px 1px 2px 0px hsl(0 0% 0% / 0.18), 0px 8px 10px -1px hsl(0 0% 0% / 0.18);
  --shadow-2xl: 0px 1px 2px 0px hsl(0 0% 0% / 0.45);
}

/* Tailwind v4 theme mapping (for reference - adapt for Filament's Tailwind v3 setup) */
@theme inline {
  --color-background: var(--background);
  --color-foreground: var(--foreground);
  --color-card: var(--card);
  --color-card-foreground: var(--card-foreground);
  --color-popover: var(--popover);
  --color-popover-foreground: var(--popover-foreground);
  --color-primary: var(--primary);
  --color-primary-foreground: var(--primary-foreground);
  --color-secondary: var(--secondary);
  --color-secondary-foreground: var(--secondary-foreground);
  --color-muted: var(--muted);
  --color-muted-foreground: var(--muted-foreground);
  --color-accent: var(--accent);
  --color-accent-foreground: var(--accent-foreground);
  --color-destructive: var(--destructive);
  --color-destructive-foreground: var(--destructive-foreground);
  --color-border: var(--border);
  --color-input: var(--input);
  --color-ring: var(--ring);
  --color-chart-1: var(--chart-1);
  --color-chart-2: var(--chart-2);
  --color-chart-3: var(--chart-3);
  --color-chart-4: var(--chart-4);
  --color-chart-5: var(--chart-5);
  --color-sidebar: var(--sidebar);
  --color-sidebar-foreground: var(--sidebar-foreground);
  --color-sidebar-primary: var(--sidebar-primary);
  --color-sidebar-primary-foreground: var(--sidebar-primary-foreground);
  --color-sidebar-accent: var(--sidebar-accent);
  --color-sidebar-accent-foreground: var(--sidebar-accent-foreground);
  --color-sidebar-border: var(--sidebar-border);
  --color-sidebar-ring: var(--sidebar-ring);

  --font-sans: var(--font-sans);
  --font-mono: var(--font-mono);
  --font-serif: var(--font-serif);

  --radius-sm: calc(var(--radius) - 4px);
  --radius-md: calc(var(--radius) - 2px);
  --radius-lg: var(--radius);
  --radius-xl: calc(var(--radius) + 4px);

  --shadow-2xs: var(--shadow-2xs);
  --shadow-xs: var(--shadow-xs);
  --shadow-sm: var(--shadow-sm);
  --shadow: var(--shadow);
  --shadow-md: var(--shadow-md);
  --shadow-lg: var(--shadow-lg);
  --shadow-xl: var(--shadow-xl);
  --shadow-2xl: var(--shadow-2xl);
}
```

### Theme Integration Notes

1. **Filament Color Mapping**: Map CSS variables to Filament's `->colors()` method in AdminPanelProvider
2. **Tailwind v3**: Filament v3 uses Tailwind CSS v3, NOT v4. The `@theme inline` block is reference only - adapt patterns for Tailwind v3's `extend.colors` config
3. **Bridge CSS**: Create CSS that applies variables to Filament's expected utility classes
4. **Custom Shadows**: Apply via CSS overrides on Filament components
5. **oklch Support**: All modern browsers support oklch color space (fallback to hex if needed)

---

## Font Structure

### Directory Layout

```
resources/fonts/
├── admin/              # Geist fonts for admin panel (ALL UPLOADED)
│   ├── Geist-Thin.woff2           # weight: 100
│   ├── Geist-ExtraLight.woff2     # weight: 200
│   ├── Geist-Light.woff2          # weight: 300
│   ├── Geist-Regular.woff2        # weight: 400
│   ├── Geist-Medium.woff2         # weight: 500
│   ├── Geist-SemiBold.woff2       # weight: 600
│   ├── Geist-Bold.woff2           # weight: 700
│   ├── Geist-ExtraBold.woff2      # weight: 800
│   ├── Geist-Black.woff2          # weight: 900
│   ├── GeistMono-Thin.woff2       # weight: 100
│   ├── GeistMono-ExtraLight.woff2 # weight: 200
│   ├── GeistMono-Light.woff2      # weight: 300
│   ├── GeistMono-Regular.woff2    # weight: 400
│   ├── GeistMono-Medium.woff2     # weight: 500
│   ├── GeistMono-SemiBold.woff2   # weight: 600
│   ├── GeistMono-Bold.woff2       # weight: 700
│   ├── GeistMono-ExtraBold.woff2  # weight: 800
│   └── GeistMono-Black.woff2      # weight: 900
└── frontend/           # Empty folder - reserved for future public site fonts
```

### Font Implementation Notes

- **Uploaded**: Geist Sans (all weights 100-900 plus italic variants) ✅
- **Uploaded**: GeistMono (all weights 100-900 plus italic variants) ✅
- **Italic Variants**: Both Geist and GeistMono have italic variants - uploaded as `*-*Italic.woff2`
- **Variable Fonts**: `Geist[wght].woff2`, `GeistMono[wght].woff2` and italic variants available for optimized loading
- **Self-hosted**: Required for GDPR compliance per Asset Sovereignty principle
- **Fallback**: System sans-serif / monospace if fonts fail to load

### @font-face Declarations

Create complete declarations for all weights in `theme.css`. Use variable font files when possible for smaller bundle size.

---

## Confirmed Technical Decisions

### Authentication

| Setting | Value |
|---------|-------|
| Panel path | `/admin` |
| Login path | `/admin/login` |
| Guard | `web` (default Laravel guard) |
| Role system | Single admin role (no RBAC in scaffold phase) |

### Dark Mode

| Setting | Value |
|---------|-------|
| Enabled | Yes |
| Toggle location | User menu (top-right) |
| Persistence | LocalStorage via Filament built-in |
| Theme variables | Both light and dark use provided CSS variables |

### Media Library

| Setting | Value |
|---------|-------|
| Views | Images, Videos, SVG, Brand Assets |
| Filtering method | **Tags** (not media_type metadata field) |
| View modes | Grid/table toggle per view |
| Upload | Placeholder upload areas only |

### Create Flows

| Content Type | Create Flow |
|--------------|-------------|
| Content Resources (Faq, Testimonial) | Modal create → redirect to edit page |
| Pages (Page, Service, BlogPost) | Direct navigation to edit page (no modal) |

### UI Features

| Feature | Status | Notes |
|---------|--------|-------|
| User menu | Yes | Filament default (top-right) |
| Breadcrumbs | Yes | All pages |
| Collapsible sidebar | Yes | With icon-only mode |
| Search/command palette | Placeholder | Non-functional in scaffold |
| "View page" link | Yes | On edit screens, links to frontend URL format: `/{slug}` for Pages, `/services/{slug}` for Services, `/blog/{slug}` for BlogPosts |
| Notification bell | Placeholder | Non-functional in scaffold |

---

## Navigation Structure

Navigation groups with "All Public Pages" as the first item under Public Pages:

```
Public Pages (icon: heroicon-o-document-text)
├── All Public Pages      ← FIRST ITEM (consolidated view)
├── Static Pages          → PageResource
├── Services              → ServiceResource
└── Blog Posts            → BlogPostResource

Content Resources (icon: heroicon-o-rectangle-stack)
├── FAQs                  → FaqResource
└── Testimonials          → TestimonialResource

Media Library (icon: heroicon-o-photo)
├── Images                → ImagesPage (grid default)
├── Videos                → VideosPage (table default)
├── SVG                   → SvgPage (grid default)
└── Brand Assets          → BrandAssetsPage (grid/table toggle)

SEO (icon: heroicon-o-globe-alt)
├── Sitemap               → SitemapPage (placeholder)
├── Redirects             → RedirectResource
├── Structured Data       → StructuredDataPage (placeholder)
└── 404 Pages             → NotFoundPagesPage (placeholder)

Settings (icon: heroicon-o-cog-6-tooth)
├── Website Details       → WebsiteDetailsPage (placeholder)
├── Scripts & Integrations → ScriptsIntegrationsPage (placeholder)
└── Users                 → UserResource (FUNCTIONAL)

Account (icon: heroicon-o-user)
├── Profile               → Filament built-in
└── Logout                → Filament built-in
```

**Total Navigation Items**: 23

---

## Settings Pages Scope

Settings pages are **placeholder only** in this scaffold phase - no backend functionality.

| Page | Status | Content Structure |
|------|--------|-------------------|
| Website Details | Placeholder | Empty sectioned form (Identity, Contact, Social) |
| Scripts & Integrations | Placeholder | Tabbed interface (Scripts, APIs, Webhooks) - empty tabs |
| Sitemap | Placeholder | Generation controls UI only |
| Structured Data | Placeholder | Empty form sections |
| Redirects | Placeholder | Table structure + Redirect model/resource (forms visible, no persistence) |
| 404 Pages | Placeholder | Configuration form skeleton |
| Users | **FUNCTIONAL** | Basic Filament UserResource with full CRUD |

**Note**: Each settings page will receive its own separate specification for actual functionality implementation.

---

## Pattern Consistency

### Critical Rule

**Establish visual patterns ONCE, then reuse everywhere.**

All similar screens must be visually indistinguishable in their structural elements.

### Listing Pages Pattern

All Public Pages listings (Static Pages, Services, Blog Posts) use **identical table template**:

| Element | Specification |
|---------|---------------|
| Columns | Title/Name, Slug, Status, Updated |
| Status badges | Published (green/success), Draft (gray/muted) |
| Action buttons | Edit, Delete - same positions for all |
| Bulk actions | Same set across all resources |
| Filters | Same filter components |

The "All Public Pages" consolidated view adds only a **Type** indicator column.

### Edit Pages Pattern (Complex Content)

All Public Pages edit screens (Pages, Services, Blog Posts) use **identical layout template**:

| Area | Elements |
|------|----------|
| Header | Item name (editable), back link, status selector |
| Main area | Tabbed sections: "Page Content", "SEO Data" |
| Right sidebar | Save button, slug field, timestamps, record ID |

### Edit Pages Pattern (Simple Content)

Content Resources (FAQs, Testimonials) use **simplified layout**:

| Area | Elements |
|------|----------|
| Header | Item name (editable), back link, status selector |
| Main area | Single-section form (no tabs) |
| Layout | No right sidebar |

### Component Patterns

| Component | Specification |
|-----------|---------------|
| Tables | Identical column alignment, consistent widths |
| Status badges | Published=green (`success`), Draft=gray (`gray`) |
| Forms | Consistent field spacing, label positioning, section headers |
| Buttons | Primary (Save/Create), Secondary (Cancel/Back), Destructive (Delete) |
| Modals | Consistent header/body/footer structure |

### Implementation Approach

1. **Build PageResource first** with full attention to every detail
2. **Copy pattern exactly** for ServiceResource and BlogPostResource
3. **Build FaqResource** with simplified pattern
4. **Copy pattern exactly** for TestimonialResource

---

## Placeholder Depth

**Definition**: Form structure visible, no backend functionality.

### What "Placeholder" Means

| Include | Exclude |
|---------|---------|
| Field labels with descriptive names | Validation logic |
| Input fields (text, textarea, select, etc.) | Database persistence (except Users) |
| Tabs and sections structure | Form submission handling |
| Buttons (Save, Cancel, Delete) | Error messages |
| Seeded placeholder data in listings | Real data queries |

### Placeholder Data

- Use seeded placeholder data for listings (3-5 records per model)
- AdminScaffoldSeeder creates demonstration records
- Records visible in UI but forms don't save changes

### Buttons Behavior

- Save/Create buttons: Visible but non-functional (no toast, no redirect)
- Delete buttons: Visible but non-functional (no confirmation, no action)
- Cancel/Back buttons: Functional (navigation works)

---

## Complexity Tracking

No constitution violations requiring justification.

| Check | Status |
|-------|--------|
| Project count | 1 (single Laravel monolith) |
| Pattern complexity | Standard Filament patterns only |
| External dependencies | None beyond existing stack |
| Custom abstractions | None (direct Filament usage) |

---

## Key Technical Decisions from Research

Summarized from `research.md`:

| Area | Decision | Rationale |
|------|----------|-----------|
| Theme system | `->viteTheme()` with custom CSS | Built-in Filament v3 approach |
| Font loading | @font-face in theme.css | Self-hosted for GDPR/performance |
| Color mapping | CSS variables + Filament `->colors()` | Bridge shadcn tokens to Filament |
| Navigation | `NavigationGroup` objects | Native Filament v3 API |
| Form layouts | `Tabs` + `Section` components | Standard Filament components |
| Dashboard | Custom page extending `BaseDashboard` | Allows custom widgets |
| Dark mode | `->darkMode(true)` built-in | Automatic toggle in user menu |
| Sidebar | `->sidebarCollapsibleOnDesktop()` | Built-in with localStorage persistence |
| Logo | Blade view via `->brandLogo()` | Supports light/dark variants |

---

## Data Model Summary

From `data-model.md`:

### New Entities

| Entity | Purpose |
|--------|---------|
| Redirect | URL redirect rules for SEO (301/302) |
| RedirectType | Enum for redirect HTTP status codes |

### Existing Entities (Phase 2)

- Page, Service, BlogPost (with ContentStatus enum)
- Faq, Testimonial
- User
- MediaAsset, MediaVariant (with MediaState, MediaType, MediaFolder enums)

---

## Implementation Phases

Detailed in `tasks.md`. High-level overview:

1. **Phase 1: Setup** - Theme infrastructure (fonts, CSS variables)
2. **Phase 2: Foundational** - Panel configuration, Redirect model, seeder
3. **Phases 3-12: User Stories** - Implement each user story independently
4. **Phase 13: Polish** - Tests, code quality, final verification

### MVP Definition

- **Minimum**: User Story 1 (Dashboard) + User Story 2 (Navigation)
- **Recommended MVP**: User Stories 1-5 (complete CRUD scaffolds for all content types)

---

## Verification

Full checklist in `quickstart.md`. Key acceptance criteria:

- [ ] All 23 navigation items accessible
- [ ] Geist font renders correctly
- [ ] Dark/light mode toggle works
- [ ] Sidebar collapses to icons
- [ ] All listing pages show seeded data (3-5 records each)
- [ ] Complex edit pages have tabs + right sidebar
- [ ] Simple edit pages have basic single-section form
- [ ] Dashboard displays stats and quick actions
- [ ] Responsive behavior works at 768px viewport
- [ ] All tests pass
- [ ] Pint reports no issues
- [ ] PHPStan Level 6 reports no errors
