# Quickstart: Admin Panel Scaffold

**Branch**: `004-admin-panel-scaffold` | **Date**: 2025-12-12

This document provides step-by-step setup and verification instructions for the Admin Panel Scaffold feature.

---

## Prerequisites

Before starting implementation, verify:

```bash
# Ensure Docker is running
docker info

# Start Laravel Sail environment
./vendor/bin/sail up -d

# Verify services are running
./vendor/bin/sail ps
```

### Required Services
- PHP 8.3 (via Sail)
- PostgreSQL 17
- Redis 7
- Node.js 20.x (via Sail)

### Verify Filament Installation
```bash
./vendor/bin/sail artisan about | grep -i filament
```

Expected output should show Filament v3.x installed.

---

## Setup Steps

### Step 1: Create Theme Infrastructure

```bash
# Generate Filament theme files
./vendor/bin/sail artisan make:filament-theme admin

# Verify files created
ls -la resources/css/filament/admin/
```

Expected files:
- `theme.css`
- `tailwind.config.js`

### Step 2: Copy Fonts to Public Directory

```bash
# Create fonts directory in public
mkdir -p public/fonts/admin

# Copy font files
cp resources/fonts/admin/*.woff2 public/fonts/admin/
```

### Step 3: Update Vite Configuration

Add to `vite.config.js` input array:
```js
'resources/css/filament/admin/theme.css'
```

### Step 4: Build Frontend Assets

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### Step 5: Run Migrations

```bash
./vendor/bin/sail artisan migrate
```

### Step 6: Seed Database

```bash
./vendor/bin/sail artisan db:seed --class=AdminScaffoldSeeder
```

### Step 7: Create Admin User (if not exists)

```bash
./vendor/bin/sail artisan make:filament-user
```

---

## Verification Checklist

### Theme Verification

| Check | Command/Action | Expected Result |
|-------|----------------|-----------------|
| Fonts load | Open DevTools → Network → Filter "woff2" | Geist fonts loaded from /fonts/admin/ |
| Custom colors | Inspect button element | Uses defined color variables |
| Dark mode | Click theme toggle in user menu | Colors switch correctly |

### Navigation Verification

| Group | Items Expected |
|-------|----------------|
| Public Pages | All Public Pages, Static Pages, Services, Blog Posts |
| Content Resources | FAQs, Testimonials |
| Media Library | Images, Videos, SVG, Brand Assets |
| SEO | Sitemap, Redirects, Structured Data, 404 Pages |
| Settings | Website Details, Scripts & Integrations, Users |
| Account | Profile, Logout |

**Total navigation items**: 23

### Resource Verification

| Resource | List Page | Edit Page | Data Count |
|----------|-----------|-----------|------------|
| Pages | `/admin/pages` | Tabbed layout | 5 |
| Services | `/admin/services` | Tabbed layout | 4 |
| Blog Posts | `/admin/blog-posts` | Tabbed layout | 4 |
| FAQs | `/admin/faqs` | Simple form | 4 |
| Testimonials | `/admin/testimonials` | Simple form | 4 |
| Redirects | `/admin/seo/redirects` | Simple form | 4 |
| Users | `/admin/settings/users` | Simple form | 1+ |

### Custom Page Verification

| Page | URL | Key Elements |
|------|-----|--------------|
| Dashboard | `/admin` | Welcome message, stat cards, quick actions |
| All Public Pages | `/admin/all-public-pages` | Combined listing with type indicator |
| Images | `/admin/media/images` | Grid view, placeholder content |
| Videos | `/admin/media/videos` | Table view, placeholder content |
| SVG | `/admin/media/svg` | Grid view, placeholder content |
| Brand Assets | `/admin/media/brand-assets` | Grid/table toggle |
| Sitemap | `/admin/seo/sitemap` | Generate button, status |
| Structured Data | `/admin/seo/structured-data` | Sectioned form |
| 404 Pages | `/admin/seo/404-pages` | Configuration form |
| Website Details | `/admin/settings/website-details` | Identity/Contact/Social sections |
| Scripts & Integrations | `/admin/settings/scripts-integrations` | Tabs: Scripts, APIs, Webhooks |

---

## Manual Testing Script

Run through these steps manually after implementation:

### 1. Authentication Flow
```
1. Navigate to /admin
2. Verify login page has custom theme styling
3. Login with admin credentials
4. Verify redirect to Dashboard
```

### 2. Dashboard Verification
```
1. Verify welcome message shows user name
2. Verify stat cards display (Total Pages, Posts, Media)
3. Verify quick action links work
4. Click theme toggle - verify dark mode
```

### 3. Navigation Walk-through
```
For each navigation group:
1. Click group to expand
2. Click each item
3. Verify page loads
4. Verify breadcrumbs show correct path
```

### 4. Content Listing Verification
```
For Pages, Services, Blog Posts:
1. Navigate to listing
2. Verify table shows Name, Slug, Status, Updated columns
3. Verify status badges use correct colors
4. Click Edit on a record
5. Verify tabbed layout loads
```

### 5. Content Resource Verification
```
For FAQs, Testimonials:
1. Navigate to listing
2. Click "New" button
3. Verify modal appears
4. Submit modal
5. Verify redirect to edit page
6. Verify simplified form (no tabs)
```

### 6. Responsive Testing
```
1. Resize browser to 768px width
2. Verify sidebar collapses to icons
3. Verify tables scroll horizontally
4. Verify all elements remain accessible
```

---

## Automated Test Commands

```bash
# Run all admin panel tests
./vendor/bin/sail artisan test --filter=AdminPanel

# Run specific test file
./vendor/bin/sail artisan test tests/Feature/AdminPanel/AdminAccessTest.php

# Run with coverage
./vendor/bin/sail artisan test --filter=AdminPanel --coverage
```

### Expected Test Results

| Test File | Tests | Assertions |
|-----------|-------|------------|
| AdminAccessTest | 3 | 6 |
| ModelInstantiationTest | 1 | 1 |
| NavigationTest | 6 | 23 |

---

## Code Quality Verification

```bash
# Run Laravel Pint
./vendor/bin/sail pint

# Run PHPStan
./vendor/bin/sail composer phpstan

# Verify no errors
echo "Exit code: $?"
```

---

## Troubleshooting

### Fonts Not Loading

**Symptom**: System font displays instead of Geist

**Solutions**:
1. Verify font files copied to `public/fonts/admin/`
2. Run `sail npm run build` to rebuild assets
3. Check browser DevTools for 404 errors on font files
4. Verify @font-face paths in theme.css

### Theme Not Applied

**Symptom**: Default Filament styling shows

**Solutions**:
1. Verify vite.config.js includes theme.css
2. Run `sail npm run build`
3. Verify AdminPanelProvider has `->viteTheme()` registered
4. Clear browser cache

### Navigation Groups Missing Icons

**Symptom**: Icons don't show in collapsed sidebar

**Solutions**:
1. Verify NavigationGroup objects have `->icon()` set
2. Confirm Heroicons package installed
3. Check icon names match Heroicons v2

### Dark Mode Not Persisting

**Symptom**: Theme resets on page refresh

**Solutions**:
1. Verify `->darkMode(true)` in AdminPanelProvider
2. Check localStorage for filament theme preference
3. Clear browser storage and retry

### Database Seeder Errors

**Symptom**: AdminScaffoldSeeder fails

**Solutions**:
1. Run migrations first: `sail artisan migrate`
2. Check for unique constraint violations
3. Run `sail artisan db:seed --class=AdminScaffoldSeeder` with `--force` if needed

---

## Environment URLs

| Environment | URL | Credentials |
|-------------|-----|-------------|
| Local (Sail) | http://localhost/admin | Created via make:filament-user |

---

## Post-Implementation Checklist

After completing implementation:

- [ ] All 23 navigation items accessible
- [ ] Geist font renders correctly
- [ ] Dark/light mode toggle works
- [ ] Sidebar collapses on desktop
- [ ] All listing pages show seeded data
- [ ] Complex edit pages have tabs + sidebar
- [ ] Simple edit pages have basic form
- [ ] Dashboard displays stats and quick actions
- [ ] Responsive behavior works at 768px
- [ ] All tests pass
- [ ] Pint reports no issues
- [ ] PHPStan reports no errors
