# Research: Admin Panel Scaffold

**Branch**: `004-admin-panel-scaffold` | **Date**: 2025-12-12

This document captures research findings for the Admin Panel Scaffold implementation, resolving all "NEEDS CLARIFICATION" items from the Technical Context.

---

## 1. Filament V3 Theme Customization

### Decision: Use `->viteTheme()` with custom CSS file

### Rationale
Filament v3 provides a built-in theme command (`php artisan make:filament-theme`) that:
- Creates CSS file at `resources/css/filament/{panel}/theme.css`
- Creates Tailwind config at `resources/css/filament/{panel}/tailwind.config.js`
- Integrates with Vite for hot reloading

### Implementation Steps
1. Run `php artisan make:filament-theme admin`
2. Add to `vite.config.js` inputs: `resources/css/filament/admin/theme.css`
3. Register in AdminPanelProvider: `->viteTheme('resources/css/filament/admin/theme.css')`
4. Run `sail npm run build`

### Alternatives Considered
- **Direct CSS injection via `->styles()`**: Less integrated, no Tailwind processing
- **Tailwind CLI separate build**: More complex, Filament docs mention this for Tailwind v4 only

### Key Findings
- Filament v3 uses **Tailwind CSS v3** (not v4) - critical for compatibility
- The `make:filament-theme` command auto-installs Tailwind v3 dependencies
- Custom properties can be added to theme.css for shadcn oklch variables

---

## 2. Self-Hosted Geist Font Integration

### Decision: Use @font-face declarations in theme.css

### Rationale
- Fonts already exist at `resources/fonts/admin/`
- Vite will process and serve fonts from public directory
- No external font loading (aligns with Asset Sovereignty principle)

### Implementation
```css
/* In theme.css */
@font-face {
    font-family: 'Geist';
    src: url('/fonts/admin/Geist-Regular.woff2') format('woff2');
    font-weight: 400;
    font-style: normal;
    font-display: swap;
}

@font-face {
    font-family: 'Geist';
    src: url('/fonts/admin/Geist-Medium.woff2') format('woff2');
    font-weight: 500;
    font-style: normal;
    font-display: swap;
}

/* ... additional weights */
```

### Font Weight Mapping
| File | CSS Weight |
|------|------------|
| Geist-Thin.woff2 | 100 |
| Geist-ExtraLight.woff2 | 200 |
| Geist-Light.woff2 | 300 |
| Geist-Regular.woff2 | 400 |
| Geist-Medium.woff2 | 500 |
| Geist-SemiBold.woff2 | 600 |
| Geist-Bold.woff2 | 700 |
| Geist-ExtraBold.woff2 | 800 |
| Geist-Black.woff2 | 900 |

### Tailwind Config Update
```js
// tailwind.config.js
module.exports = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Geist', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
}
```

---

## 3. Shadcn OKLCH Color Mapping to Filament

### Decision: Map via CSS custom properties + Filament's `->colors()` method

### Rationale
Filament uses 6 color slots internally. We map shadcn variables to Filament slots and extend with CSS custom properties for additional tokens.

### Color Slot Mapping

| Shadcn Token | Filament Slot | Usage |
|--------------|---------------|-------|
| `--primary` | `primary` | Buttons, links, active states |
| `--destructive` | `danger` | Delete actions, errors |
| `--muted` | `gray` | Backgrounds, borders, disabled |
| `--accent` | `info` | Highlights, tooltips |
| N/A | `success` | Keep Filament default (green) |
| N/A | `warning` | Keep Filament default (orange) |

### Implementation: AdminPanelProvider
```php
use Filament\Support\Colors\Color;

->colors([
    'danger' => [
        50 => '254, 242, 242',   // oklch(0.97 0.02 15)
        100 => '254, 226, 226',
        // ... generate full palette from oklch(0.63 0.19 23.03)
        500 => '239, 68, 68',
        900 => '127, 29, 29',
        950 => '69, 10, 10',
    ],
    'gray' => [
        // Map from --muted oklch values
        50 => '250, 250, 250',
        // ...
    ],
    'primary' => [
        // For light mode: oklch(0 0 0) = black
        // Create grayscale palette
        50 => '250, 250, 250',
        100 => '245, 245, 245',
        // ...
        900 => '23, 23, 23',
        950 => '10, 10, 10',
    ],
])
```

### Additional CSS Custom Properties
```css
:root {
    /* Light mode shadcn tokens */
    --background: oklch(0.99 0 0);
    --foreground: oklch(0 0 0);
    --card: oklch(0.99 0 0);
    --card-foreground: oklch(0 0 0);
    --popover: oklch(0.99 0 0);
    --popover-foreground: oklch(0 0 0);
    --primary: oklch(0 0 0);
    --primary-foreground: oklch(1 0 0);
    --secondary: oklch(0.94 0 0);
    --secondary-foreground: oklch(0 0 0);
    --muted: oklch(0.97 0 0);
    --muted-foreground: oklch(0.44 0 0);
    --accent: oklch(0.94 0 0);
    --accent-foreground: oklch(0 0 0);
    --destructive: oklch(0.63 0.19 23.03);
    --destructive-foreground: oklch(1 0 0);
    --border: oklch(0.92 0 0);
    --input: oklch(0.92 0 0);
    --ring: oklch(0 0 0);
    --radius: 0.5rem;
}

.dark {
    /* Dark mode shadcn tokens */
    --background: oklch(0 0 0);
    --foreground: oklch(1 0 0);
    --card: oklch(0.14 0 0);
    --card-foreground: oklch(1 0 0);
    --popover: oklch(0.14 0 0);
    --popover-foreground: oklch(1 0 0);
    --primary: oklch(1 0 0);
    --primary-foreground: oklch(0 0 0);
    --secondary: oklch(0.21 0 0);
    --secondary-foreground: oklch(1 0 0);
    --muted: oklch(0.21 0 0);
    --muted-foreground: oklch(0.63 0 0);
    --accent: oklch(0.21 0 0);
    --accent-foreground: oklch(1 0 0);
    --destructive: oklch(0.63 0.19 23.03);
    --destructive-foreground: oklch(1 0 0);
    --border: oklch(0.26 0 0);
    --input: oklch(0.26 0 0);
    --ring: oklch(0.87 0 0);
}
```

---

## 4. Navigation Groups Configuration

### Decision: Use `->navigationGroups()` with `NavigationGroup` objects

### Rationale
Filament v3 supports custom navigation groups with icons, collapsible behavior, and ordering via `NavigationGroup` objects.

### Implementation
```php
use Filament\Navigation\NavigationGroup;

->navigationGroups([
    NavigationGroup::make()
        ->label('Public Pages')
        ->icon('heroicon-o-document-text')
        ->collapsible(),
    NavigationGroup::make()
        ->label('Content Resources')
        ->icon('heroicon-o-rectangle-stack')
        ->collapsible(),
    NavigationGroup::make()
        ->label('Media Library')
        ->icon('heroicon-o-photo')
        ->collapsible(),
    NavigationGroup::make()
        ->label('SEO')
        ->icon('heroicon-o-globe-alt')
        ->collapsible(),
    NavigationGroup::make()
        ->label('Settings')
        ->icon('heroicon-o-cog-6-tooth')
        ->collapsible(),
    NavigationGroup::make()
        ->label('Account')
        ->icon('heroicon-o-user')
        ->collapsible(false), // Always expanded
])
```

### Icon Mapping (Heroicons v2)
| Group | Icon |
|-------|------|
| Public Pages | `heroicon-o-document-text` |
| Content Resources | `heroicon-o-rectangle-stack` |
| Media Library | `heroicon-o-photo` |
| SEO | `heroicon-o-globe-alt` |
| Settings | `heroicon-o-cog-6-tooth` |
| Account | `heroicon-o-user` |

---

## 5. Resource Form Layouts

### Decision: Use Filament's built-in `Tabs` component and `Section::aside()`

### Complex Content (Page, Service, BlogPost)
```php
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Tabs::make('Content')
                ->tabs([
                    Tabs\Tab::make('Page Content')
                        ->schema([
                            // Content fields
                        ]),
                    Tabs\Tab::make('SEO Data')
                        ->schema([
                            // SEO fields
                        ]),
                ])
                ->columnSpan(['lg' => 2]),

            // Right sidebar using Section with aside
            Section::make()
                ->schema([
                    // Save button, slug, timestamps, ID
                ])
                ->columnSpan(['lg' => 1]),
        ])
        ->columns(3);
}
```

### Simple Content (Faq, Testimonial)
```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Simple single-column form
            TextInput::make('question'),
            Textarea::make('answer'),
            Select::make('status'),
        ]);
}
```

### Modal Create Action
Resources with modal create use `->slideOver()` for a slide-in modal:
```php
protected static function getCreateFormAction(): Action
{
    return parent::getCreateFormAction()
        ->slideOver();
}
```

---

## 6. Custom Dashboard Implementation

### Decision: Create custom Dashboard page replacing Filament default

### Implementation
```php
// app/Filament/Pages/Dashboard.php
namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RecentActivityWidget::class,
        ];
    }
}
```

### Register in AdminPanelProvider
```php
->pages([
    Pages\Dashboard::class, // Our custom dashboard
])
```

---

## 7. Dark Mode Toggle

### Decision: Use Filament's built-in dark mode with `->darkMode()`

### Implementation
```php
// AdminPanelProvider
->darkMode(true) // Enable toggle
->darkModePreference(Filament\Support\Enums\DarkMode::AUTO) // Or LIGHT/DARK/AUTO
```

The toggle appears automatically in the user menu when dark mode is enabled.

---

## 8. Sidebar Collapse

### Decision: Use `->sidebarCollapsibleOnDesktop()`

### Implementation
```php
->sidebarCollapsibleOnDesktop()
```

This provides:
- Collapse to icons-only on click
- Tooltip on hover for collapsed items
- Persists preference in localStorage

---

## 9. Custom Logo

### Decision: Create Blade view for logo

### Implementation
1. Create `resources/views/filament/logo.blade.php`
2. Register in AdminPanelProvider:
```php
->brandLogo(fn () => view('filament.logo'))
->darkModeBrandLogo(fn () => view('filament.logo-dark')) // Optional
->brandLogoHeight('2rem')
```

### Logo View Example
```blade
{{-- resources/views/filament/logo.blade.php --}}
<div class="flex items-center gap-2">
    <svg class="h-8 w-8" viewBox="0 0 32 32" fill="currentColor">
        <!-- Placeholder SVG -->
        <rect width="32" height="32" rx="4" />
    </svg>
    <span class="font-semibold text-lg">Blueprint</span>
</div>
```

---

## 10. Breadcrumbs

### Decision: Use Filament's built-in breadcrumbs (enabled by default)

### Customization
Breadcrumbs are automatically generated from navigation hierarchy. Custom pages can override:
```php
protected function getBreadcrumbs(): array
{
    return [
        '/admin' => 'Dashboard',
        '/admin/media/images' => 'Images',
    ];
}
```

---

## 11. Existing Models Verification

### Confirmed Existing Models (from Phase 2)
| Model | Factory | Table |
|-------|---------|-------|
| Page | PageFactory | pages |
| Service | ServiceFactory | services |
| BlogPost | BlogPostFactory | blog_posts |
| Faq | FaqFactory | faqs |
| Testimonial | TestimonialFactory | testimonials |
| User | UserFactory | users |
| MediaAsset | MediaAssetFactory | media_assets |
| MediaVariant | MediaVariantFactory | media_variants |
| Setting | N/A | settings |

### New Model Required
| Model | Factory | Table |
|-------|---------|-------|
| Redirect | RedirectFactory | redirects |

### Existing Enum: ContentStatus
```php
enum ContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
```

---

## 12. Testing Patterns for Admin Panel

### Verified Pest + Livewire Pattern
```php
use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get('/admin/pages')
        ->assertSuccessful();
});

it('can list pages', function () {
    $pages = Page::factory()->count(3)->create();

    livewire(PageResource\Pages\ListPages::class)
        ->assertCanSeeTableRecords($pages);
});
```

### Authentication in Tests
```php
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});
```

---

## Summary of Key Decisions

| Area | Decision |
|------|----------|
| Theme System | Vite theme with custom CSS |
| Font Loading | @font-face in theme.css |
| Colors | CSS custom properties + Filament colors() |
| Navigation | NavigationGroup objects with icons |
| Form Layout | Tabs + Section aside for complex; simple for content resources |
| Dashboard | Custom page replacing default |
| Dark Mode | Built-in toggle enabled |
| Sidebar | Collapsible on desktop |
| Logo | Blade view component |
| Breadcrumbs | Built-in (automatic) |
