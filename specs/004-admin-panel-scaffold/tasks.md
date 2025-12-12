# Tasks: Admin Panel Scaffold

**Input**: Design documents from `/specs/004-admin-panel-scaffold/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, quickstart.md

**Tests**: Included as specified in plan.md (minimal testing for scaffold phase)

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story?] Description → @subagent`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3...)
- **Subagent**: Agent(s) assigned to implement the task

## Path Conventions

- **Web app (Laravel)**: `app/`, `resources/`, `database/`, `tests/` at repository root
- Admin panel components: `app/Filament/`

---

## Phase 1: Setup (Theme Infrastructure)

**Purpose**: Create Filament theme foundation with Geist fonts and shadcn-style colors

- [X] T001 Generate Filament theme files via `sail artisan make:filament-theme admin` in `resources/css/filament/admin/` → @laravel-specialist
- [X] T002 Copy Geist font files from `resources/fonts/admin/` to `public/fonts/admin/` directory (all weights 100-900 + variable fonts) → @devops-engineer
- [X] T003 [P] Create @font-face declarations for Geist Sans (weights 100-900 + variable) in `resources/css/filament/admin/theme.css` → @frontend-developer
- [X] T004 [P] Create @font-face declarations for Geist Mono (weights 100-900 + variable) in `resources/css/filament/admin/theme.css` → @frontend-developer
- [X] T005 [P] Add shadcn oklch CSS custom properties (light mode) in `resources/css/filament/admin/theme.css` → @frontend-developer
- [X] T006 [P] Add shadcn oklch CSS custom properties (dark mode .dark class) in `resources/css/filament/admin/theme.css` → @frontend-developer
- [X] T007 [P] Configure Tailwind v3 fontFamily extend for Geist in `resources/css/filament/admin/tailwind.config.js` → @frontend-developer
- [X] T008 [P] Create CSS bridge mapping oklch variables to Filament utility classes in `resources/css/filament/admin/theme.css` → @frontend-developer
- [X] T009 Add theme.css to Vite inputs in `vite.config.js` → @frontend-developer
- [X] T010 Build frontend assets with `sail npm run build` and verify Geist fonts load correctly → @frontend-developer

---

## Phase 2: Foundational (Panel Configuration & Database)

**Purpose**: Core AdminPanelProvider configuration and Redirect model - MUST complete before user stories

**CRITICAL**: No user story work can begin until this phase is complete

- [X] T011 Configure `->viteTheme()` registration in `app/Providers/Filament/AdminPanelProvider.php` → @laravel-specialist
- [X] T012 [P] Configure `->colors()` mapping for Filament color slots (primary, danger, gray, warning, success, info) in `app/Providers/Filament/AdminPanelProvider.php` → @laravel-specialist
- [X] T013 [P] Configure `->darkMode(true)` and `->sidebarCollapsibleOnDesktop()` in `app/Providers/Filament/AdminPanelProvider.php` → @laravel-specialist
- [X] T014 [P] Create placeholder logo Blade view with light/dark variants in `resources/views/filament/logo.blade.php` → @frontend-developer
- [X] T015 Configure `->brandLogo()` registration in `app/Providers/Filament/AdminPanelProvider.php` → @laravel-specialist
- [X] T016 [P] Create RedirectType enum with label() and description() methods in `app/Enums/RedirectType.php` → @laravel-specialist
- [X] T017 [P] Create Redirect model with casts in `app/Models/Redirect.php` → @laravel-specialist
- [X] T018 [P] Create redirects migration with indexes in `database/migrations/2025_12_12_000001_create_redirects_table.php` → @laravel-specialist, @postgres-pro
- [X] T019 [P] Create RedirectFactory with states (permanent, temporary, active, inactive) in `database/factories/RedirectFactory.php` → @laravel-specialist
- [X] T020 Create AdminScaffoldSeeder with placeholder data (5 pages, 4 services, 4 posts, 4 faqs, 4 testimonials, 4 redirects) in `database/seeders/AdminScaffoldSeeder.php` → @laravel-specialist
- [X] T021 Run migrations and seed database with placeholder data via `sail artisan migrate && sail artisan db:seed --class=AdminScaffoldSeeder` → @laravel-specialist
- [X] T022 Configure navigation groups (Public Pages, Content Resources, Media Library, SEO, Settings, Account) in `app/Providers/Filament/AdminPanelProvider.php` → @laravel-specialist

**Checkpoint**: Foundation ready - panel loads at /admin with theme, navigation groups visible, database seeded

---

## Phase 3: User Story 1 - Access Admin Panel and Dashboard (Priority: P1) MVP

**Goal**: Administrator can access /admin, see login, and view Dashboard with placeholder stats

**Independent Test**: Navigate to /admin, login, verify dashboard displays welcome message and stat cards

### Implementation for User Story 1

- [X] T023 [US1] Create custom Dashboard page extending BaseDashboard in `app/Filament/Pages/Dashboard.php` → @laravel-specialist
- [X] T024 [P] [US1] Create StatsOverviewWidget with placeholder stats (Total Pages, Total Posts, Media Items) in `app/Filament/Widgets/StatsOverviewWidget.php` → @laravel-specialist
- [X] T025 [P] [US1] Create RecentActivityWidget with placeholder activity feed in `app/Filament/Widgets/RecentActivityWidget.php` → @laravel-specialist
- [X] T026 [US1] Register custom Dashboard in AdminPanelProvider pages array and configure widgets → @laravel-specialist
- [X] T027 [US1] Verify dashboard displays welcome message with user name, stat cards, and quick action links → @laravel-specialist

**Checkpoint**: User Story 1 complete - Dashboard accessible with stats and quick actions

---

## Phase 4: User Story 2 - Navigate Content Sections via Sidebar (Priority: P1)

**Goal**: All 23 navigation items accessible in organized groups with breadcrumbs

**Independent Test**: Click each sidebar group, verify all items present and link to pages

### Implementation for User Story 2

- [X] T028 [US2] Create AllPublicPagesPage in `app/Filament/Pages/AllPublicPagesPage.php` with combined listing → @laravel-specialist
- [X] T029 [P] [US2] Create ImagesPage in `app/Filament/Pages/Media/ImagesPage.php` with placeholder grid (default view) → @laravel-specialist
- [X] T030 [P] [US2] Create VideosPage in `app/Filament/Pages/Media/VideosPage.php` with placeholder table (default view) → @laravel-specialist
- [X] T031 [P] [US2] Create SvgPage in `app/Filament/Pages/Media/SvgPage.php` with placeholder grid (default view) → @laravel-specialist
- [X] T032 [P] [US2] Create BrandAssetsPage in `app/Filament/Pages/Media/BrandAssetsPage.php` with grid/table toggle → @laravel-specialist
- [X] T033 [P] [US2] Create SitemapPage in `app/Filament/Pages/Seo/SitemapPage.php` with placeholder Generate button → @laravel-specialist
- [X] T034 [P] [US2] Create StructuredDataPage in `app/Filament/Pages/Seo/StructuredDataPage.php` with sectioned form skeleton → @laravel-specialist
- [X] T035 [P] [US2] Create NotFoundPagesPage in `app/Filament/Pages/Seo/NotFoundPagesPage.php` with placeholder config area → @laravel-specialist
- [X] T036 [P] [US2] Create WebsiteDetailsPage in `app/Filament/Pages/Settings/WebsiteDetailsPage.php` with Identity/Contact/Social sections → @laravel-specialist
- [X] T037 [P] [US2] Create ScriptsIntegrationsPage in `app/Filament/Pages/Settings/ScriptsIntegrationsPage.php` with Scripts/APIs/Webhooks tabs → @laravel-specialist
- [X] T038 [US2] Verify all 23 navigation items accessible with correct breadcrumbs and icons → @laravel-specialist

**Checkpoint**: User Story 2 complete - All navigation groups expand with correct items

---

## Phase 5: User Story 3 - View Content Listings with Consistent Layout (Priority: P2)

**Goal**: Consistent table layouts for Pages, Services, Blog Posts, FAQs, Testimonials

**Independent Test**: Navigate to any two listing pages and verify identical visual structure

### Implementation for User Story 3

- [X] T039 [US3] Create PageResource in `app/Filament/Resources/PageResource.php` with table columns (Title, Slug, Status, Updated) → @laravel-specialist
- [X] T040 [P] [US3] Create PageResource pages (ListPages, CreatePage, EditPage) in `app/Filament/Resources/PageResource/Pages/` → @laravel-specialist
- [X] T041 [US3] Create ServiceResource in `app/Filament/Resources/ServiceResource.php` cloning PageResource table structure exactly → @laravel-specialist
- [X] T042 [P] [US3] Create ServiceResource pages in `app/Filament/Resources/ServiceResource/Pages/` → @laravel-specialist
- [X] T043 [US3] Create BlogPostResource in `app/Filament/Resources/BlogPostResource.php` cloning PageResource table structure exactly → @laravel-specialist
- [X] T044 [P] [US3] Create BlogPostResource pages in `app/Filament/Resources/BlogPostResource/Pages/` → @laravel-specialist
- [X] T045 [US3] Create FaqResource in `app/Filament/Resources/FaqResource.php` with table columns (Question, Status, Updated) → @laravel-specialist
- [X] T046 [P] [US3] Create FaqResource pages in `app/Filament/Resources/FaqResource/Pages/` → @laravel-specialist
- [X] T047 [US3] Create TestimonialResource in `app/Filament/Resources/TestimonialResource.php` with table columns (Name/Author, Status, Updated) → @laravel-specialist
- [X] T048 [P] [US3] Create TestimonialResource pages in `app/Filament/Resources/TestimonialResource/Pages/` → @laravel-specialist
- [X] T049 [US3] Configure consistent status badge styling (Published=success/green, Draft=gray) across all Resources → @laravel-specialist
- [X] T050 [US3] Update AllPublicPagesPage to show records from Pages, Services, BlogPosts with Type indicator column → @laravel-specialist

**Checkpoint**: User Story 3 complete - All listings display with consistent structure and status badges

---

## Phase 6: User Story 4 - Edit Complex Content with Tabbed Layout (Priority: P2)

**Goal**: Tabbed edit layouts with right sidebar for Pages, Services, Blog Posts

**Independent Test**: Navigate to edit page for any complex content and verify header, tabs, and sidebar

### Implementation for User Story 4

- [X] T051 [US4] Implement tabbed form schema with "Page Content" and "SEO Data" tabs in `PageResource.php` → @laravel-specialist
- [X] T052 [US4] Implement right sidebar Section with Save button, slug field, timestamps, record ID in `PageResource.php` → @laravel-specialist
- [X] T053 [US4] Configure header with back link to listing and status selector in PageResource EditPage → @laravel-specialist
- [X] T054 [US4] Clone tabbed layout to ServiceResource form schema (identical structure) → @laravel-specialist
- [X] T055 [US4] Clone tabbed layout to BlogPostResource form schema (identical structure) → @laravel-specialist
- [ ] T056 [US4] Verify all three edit pages have identical layout structure (tabs + sidebar) → @laravel-specialist

**Checkpoint**: User Story 4 complete - Complex content edit pages have tabs + sidebar

---

## Phase 7: User Story 5 - Create and Edit Simple Content with Modal (Priority: P2)

**Goal**: Modal-based creation for FAQs and Testimonials, simplified edit forms

**Independent Test**: Click Create on FAQs, fill modal, verify redirect to simplified edit form

### Implementation for User Story 5

- [ ] T057 [US5] Configure modal create action with basic fields (Question, Answer, Status) in FaqResource → @laravel-specialist
- [ ] T058 [US5] Implement simplified single-section edit form in FaqResource (no tabs, no sidebar) → @laravel-specialist
- [ ] T059 [US5] Configure modal create action with basic fields (Author Name, Content, Status) in TestimonialResource → @laravel-specialist
- [ ] T060 [US5] Implement simplified single-section edit form in TestimonialResource (matching FaqResource structure) → @laravel-specialist
- [ ] T061 [US5] Verify modal → edit redirect workflow for both Resources → @laravel-specialist

**Checkpoint**: User Story 5 complete - Simple content uses modal create + simplified edit

---

## Phase 8: User Story 6 - Browse Media Library by Type (Priority: P2)

**Goal**: Media pages with grid/table toggles and placeholder content

**Independent Test**: Navigate to each Media page and verify distinct filtering and view toggle

### Implementation for User Story 6

- [ ] T062 [US6] Implement grid view with placeholder thumbnails in ImagesPage → @laravel-specialist, @frontend-developer
- [ ] T063 [US6] Implement table/grid toggle functionality in ImagesPage → @laravel-specialist, @frontend-developer
- [ ] T064 [US6] Implement table view with video metadata columns (Name, Duration, Size, Uploaded) in VideosPage → @laravel-specialist
- [ ] T065 [US6] Implement grid view with SVG previews in SvgPage → @laravel-specialist, @frontend-developer
- [ ] T066 [US6] Implement grid/table toggle with placeholder logos/favicons in BrandAssetsPage → @laravel-specialist, @frontend-developer
- [ ] T067 [US6] Add placeholder upload area component to all Media Library pages → @laravel-specialist, @frontend-developer

**Checkpoint**: User Story 6 complete - Media pages show view toggles and placeholders

---

## Phase 9: User Story 7 - Manage SEO Settings (Priority: P3)

**Goal**: SEO pages with placeholder content for Sitemap, Redirects, Structured Data, 404 Pages

**Independent Test**: Navigate to each SEO page and verify placeholder content present

### Implementation for User Story 7

- [ ] T068 [US7] Implement Generate Sitemap button with status indicator in SitemapPage → @laravel-specialist
- [ ] T069 [US7] Create RedirectResource in `app/Filament/Resources/RedirectResource.php` with table columns (Source URL, Target URL, Type, Status) → @laravel-specialist
- [ ] T070 [P] [US7] Create RedirectResource pages (ListRedirects, CreateRedirect, EditRedirect) in `app/Filament/Resources/RedirectResource/Pages/` → @laravel-specialist
- [ ] T071 [US7] Implement sectioned form fields for global structured data (Organization, Website, Breadcrumbs) in StructuredDataPage → @laravel-specialist
- [ ] T072 [US7] Implement configuration area for 404 handling (Custom 404 page, Logging options) in NotFoundPagesPage → @laravel-specialist

**Checkpoint**: User Story 7 complete - SEO pages display placeholder controls

---

## Phase 10: User Story 8 - Configure Website Settings (Priority: P3)

**Goal**: Settings pages with sectioned forms and Users management

**Independent Test**: Navigate to Settings pages and verify form structure and tabs

### Implementation for User Story 8

- [ ] T073 [US8] Implement Identity section (Logo, Site Name, Tagline) in WebsiteDetailsPage → @laravel-specialist
- [ ] T074 [US8] Implement Contact section (Phone, Email, Address) in WebsiteDetailsPage → @laravel-specialist
- [ ] T075 [US8] Implement Social Links section in WebsiteDetailsPage → @laravel-specialist
- [ ] T076 [US8] Implement Scripts tab (Head, Body, Footer scripts) in ScriptsIntegrationsPage → @laravel-specialist
- [ ] T077 [US8] Implement APIs tab (placeholder API key fields) in ScriptsIntegrationsPage → @laravel-specialist
- [ ] T078 [US8] Implement Webhooks tab (placeholder webhook URL fields) in ScriptsIntegrationsPage → @laravel-specialist
- [ ] T079 [US8] Create UserResource in `app/Filament/Resources/UserResource.php` with table columns (Name, Email, Created) and full CRUD → @laravel-specialist
- [ ] T080 [P] [US8] Create UserResource pages (ListUsers, CreateUser, EditUser) in `app/Filament/Resources/UserResource/Pages/` → @laravel-specialist
- [ ] T081 [US8] Verify Settings pages display correct form structures and tabs work correctly → @laravel-specialist

**Checkpoint**: User Story 8 complete - Settings pages functional with forms and tabs

---

## Phase 11: User Story 9 - Toggle Dark/Light Theme (Priority: P3)

**Goal**: Theme toggle accessible with correct color updates

**Independent Test**: Click theme toggle and verify colors change correctly

### Implementation for User Story 9

- [ ] T082 [US9] Verify dark mode toggle appears in user menu (top-right) → @laravel-specialist
- [ ] T083 [US9] Test light → dark → light transitions with correct oklch color palette → @laravel-specialist, @frontend-developer
- [ ] T084 [US9] Verify theme preference persists via localStorage across sessions → @laravel-specialist

**Checkpoint**: User Story 9 complete - Theme toggle works with persistence

---

## Phase 12: User Story 10 - Collapse Sidebar for More Workspace (Priority: P3)

**Goal**: Sidebar collapses to icons-only with tooltips

**Independent Test**: Click collapse toggle, verify icons-only view with tooltips

### Implementation for User Story 10

- [ ] T085 [US10] Verify sidebar collapse toggle appears and functions correctly → @laravel-specialist
- [ ] T086 [US10] Verify tooltips appear on hover for collapsed navigation items → @laravel-specialist, @frontend-developer
- [ ] T087 [US10] Verify collapse preference persists via localStorage → @laravel-specialist

**Checkpoint**: User Story 10 complete - Sidebar collapses with tooltips

---

## Phase 13: Polish & Cross-Cutting Concerns

**Purpose**: Testing, code quality, and final verification

- [ ] T088 [P] Create AdminAccessTest in `tests/Feature/AdminPanel/AdminAccessTest.php` (login, redirect, dashboard access) → @laravel-specialist, @test-automator
- [ ] T089 [P] Create ModelInstantiationTest for Redirect model in `tests/Feature/AdminPanel/ModelInstantiationTest.php` → @laravel-specialist, @test-automator
- [ ] T090 [P] Create NavigationTest to verify all 23 navigation items in `tests/Feature/AdminPanel/NavigationTest.php` → @laravel-specialist, @test-automator
- [ ] T091 Run Laravel Pint to format all new files via `sail pint` → @laravel-specialist
- [ ] T092 Run PHPStan Level 6 analysis via `./vendor/bin/phpstan analyse` and fix any issues → @laravel-specialist
- [ ] T093 Run quickstart.md validation steps (theme, navigation, resources, responsiveness) → @laravel-specialist
- [ ] T094 Verify 768px responsive behavior (sidebar auto-collapse, horizontal table scroll) → @frontend-developer, @laravel-specialist
- [ ] T095 Final verification: all 23 navigation items accessible with correct layouts and breadcrumbs → @laravel-specialist

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 - BLOCKS all user stories
- **User Stories (Phase 3-12)**: All depend on Phase 2 completion
  - US1 and US2 are P1 priority - complete first
  - US3-US6 are P2 priority - complete second
  - US7-US10 are P3 priority - complete last
- **Polish (Phase 13)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (Dashboard)**: Can start after Phase 2 - No dependencies
- **US2 (Navigation)**: Can start after Phase 2 - No dependencies, parallel with US1
- **US3 (Listings)**: Can start after Phase 2 - No dependencies
- **US4 (Edit Complex)**: Depends on US3 (Resources must exist)
- **US5 (Edit Simple)**: Depends on US3 (Resources must exist)
- **US6 (Media)**: Can start after Phase 2 - Pages created in US2
- **US7 (SEO)**: Can start after Phase 2 - Pages created in US2
- **US8 (Settings)**: Can start after Phase 2 - Pages created in US2
- **US9 (Dark Mode)**: Can start after Phase 2 - Configured in Phase 2
- **US10 (Sidebar)**: Can start after Phase 2 - Configured in Phase 2

### Parallel Opportunities

**Phase 1 (Setup)**:
- T003, T004, T005, T006, T007, T008 can run in parallel (different sections of theme.css and tailwind.config.js)

**Phase 2 (Foundational)**:
- T012, T013, T014 can run in parallel (panel configuration)
- T016, T017, T018, T019 can run in parallel (enum, model, migration, factory)

**Phase 3-4 (US1 + US2)**:
- T024, T025 (widgets) can run in parallel
- T029-T037 (all custom pages) can run in parallel

**Phase 5 (US3)**:
- T040, T042, T044, T046, T048 (resource pages) can run in parallel

**Phase 13 (Polish)**:
- T088, T089, T090 (tests) can run in parallel

---

## Parallel Example: User Story 2 Custom Pages

```bash
# Launch all Media Library pages together (T029-T032):
Task: "T029 Create ImagesPage in app/Filament/Pages/Media/ImagesPage.php"
Task: "T030 Create VideosPage in app/Filament/Pages/Media/VideosPage.php"
Task: "T031 Create SvgPage in app/Filament/Pages/Media/SvgPage.php"
Task: "T032 Create BrandAssetsPage in app/Filament/Pages/Media/BrandAssetsPage.php"

# Launch all SEO pages together (T033-T035):
Task: "T033 Create SitemapPage in app/Filament/Pages/Seo/SitemapPage.php"
Task: "T034 Create StructuredDataPage in app/Filament/Pages/Seo/StructuredDataPage.php"
Task: "T035 Create NotFoundPagesPage in app/Filament/Pages/Seo/NotFoundPagesPage.php"

# Launch all Settings pages together (T036-T037):
Task: "T036 Create WebsiteDetailsPage in app/Filament/Pages/Settings/WebsiteDetailsPage.php"
Task: "T037 Create ScriptsIntegrationsPage in app/Filament/Pages/Settings/ScriptsIntegrationsPage.php"
```

---

## Implementation Strategy

### MVP First (US1 + US2 Only)

1. Complete Phase 1: Setup (theme infrastructure)
2. Complete Phase 2: Foundational (panel config + database)
3. Complete Phase 3: User Story 1 (Dashboard)
4. Complete Phase 4: User Story 2 (Navigation)
5. **STOP and VALIDATE**: Admin panel accessible with theme, navigation, dashboard
6. Deploy/demo if ready - this is the minimum viable scaffold

### Incremental Delivery

1. Setup + Foundational → Panel accessible with theme
2. Add US1 + US2 → Dashboard + Navigation (MVP!)
3. Add US3 → Content listings
4. Add US4 + US5 → Edit pages (complex + simple)
5. Add US6 → Media library views
6. Add US7 + US8 → SEO + Settings pages
7. Add US9 + US10 → Theme toggle + sidebar collapse
8. Add Polish → Tests + code quality

### Suggested MVP Scope

**Minimum**: US1 (Dashboard) + US2 (Navigation) = 16 tasks after foundation
**Recommended MVP**: US1-US5 = Complete CRUD scaffolds for all content types (45 tasks after foundation)

---

## Summary

| Metric | Value |
|--------|-------|
| **Total Tasks** | 95 |
| **Setup Tasks** | 10 |
| **Foundational Tasks** | 12 |
| **User Story Tasks** | 65 |
| **Polish Tasks** | 8 |
| **Parallel Opportunities** | 45+ tasks marked [P] |

### Tasks by User Story

| User Story | Task Count | Priority |
|------------|------------|----------|
| US1 - Dashboard | 5 | P1 |
| US2 - Navigation | 11 | P1 |
| US3 - Listings | 12 | P2 |
| US4 - Edit Complex | 6 | P2 |
| US5 - Edit Simple | 5 | P2 |
| US6 - Media Library | 6 | P2 |
| US7 - SEO Settings | 5 | P3 |
| US8 - Website Settings | 9 | P3 |
| US9 - Dark Mode | 3 | P3 |
| US10 - Sidebar Collapse | 3 | P3 |

### Subagent Assignment Summary

| Subagent | Task Count | Primary Areas |
|----------|------------|---------------|
| @laravel-specialist | 82 | Filament Resources, Pages, Panel config |
| @frontend-developer | 18 | Theme CSS, fonts, UI components, view toggles |
| @devops-engineer | 1 | File operations |
| @postgres-pro | 1 | Migration with indexes |
| @test-automator | 3 | Pest tests |

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story
- Each user story independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Admin panel excluded from Lighthouse performance requirements per constitution
