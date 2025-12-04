# CSS/SCSS File Structure Reference

## Recommended Project Structure

```
styles/
├── main.scss                 # Main entry point (imports only)
│
├── abstracts/                # No CSS output, only tools
│   ├── _index.scss           # @forward all abstracts
│   ├── _variables.scss       # CSS custom properties
│   ├── _mixins.scss          # Reusable mixins
│   ├── _functions.scss       # SCSS functions
│   └── _breakpoints.scss     # Breakpoint definitions
│
├── base/                     # Foundation styles
│   ├── _index.scss           # @forward all base
│   ├── _reset.scss           # CSS reset (layered)
│   ├── _typography.scss      # Base type styles
│   └── _global.scss          # html, body, root styles
│
├── components/               # Reusable UI components
│   ├── _index.scss           # @forward all components
│   ├── _button.scss
│   ├── _card.scss
│   ├── _form.scss
│   ├── _modal.scss
│   └── _nav.scss
│
├── layout/                   # Structural layouts
│   ├── _index.scss           # @forward all layouts
│   ├── _header.scss
│   ├── _footer.scss
│   ├── _sidebar.scss
│   └── _grid.scss
│
├── pages/                    # Page-specific styles
│   ├── _index.scss
│   ├── _home.scss
│   └── _contact.scss
│
├── utilities/                # Utility/helper classes
│   ├── _index.scss
│   └── _helpers.scss
│
└── vendors/                  # Third-party CSS
    ├── _index.scss
    └── _normalize.scss
```

## Main Entry File (main.scss)

### With Cascade Layers (Recommended)

```scss
/* Define layer order - first declared = lowest priority */
@layer reset, base, layout, components, utilities;

/* Import each layer */
@layer reset {
  @use 'base/reset';
}

@layer base {
  @use 'abstracts' as *;
  @use 'base/typography';
  @use 'base/global';
}

@layer layout {
  @use 'layout';
}

@layer components {
  @use 'components';
}

@layer utilities {
  @use 'utilities';
}

/* Vendors outside layers (or in their own layer) */
@use 'vendors';
```

### Without Layers (Legacy)

```scss
// Abstracts (no output)
@use 'abstracts' as *;

// Vendors
@use 'vendors';

// Base
@use 'base';

// Layout
@use 'layout';

// Components
@use 'components';

// Pages (optional)
@use 'pages';

// Utilities (highest specificity)
@use 'utilities';
```

## Index Files Pattern

Each folder should have an `_index.scss` that forwards its contents:

### abstracts/\_index.scss

```scss
@forward 'variables';
@forward 'functions';
@forward 'mixins';
@forward 'breakpoints';
```

### components/\_index.scss

```scss
@forward 'button';
@forward 'card';
@forward 'form';
@forward 'modal';
@forward 'nav';
```

## Modern SCSS (@use and @forward)

### @use (Import and Namespace)

```scss
// Import with namespace
@use 'abstracts/variables' as vars;
.element {
  color: vars.$primary;
}

// Import without namespace
@use 'abstracts/variables' as *;
.element {
  color: $primary;
}

// Import and configure
@use 'abstracts/variables' with (
  $primary: #custom-color
);
```

### @forward (Re-export)

```scss
// abstracts/_index.scss
@forward 'variables';
@forward 'mixins';

// components/_button.scss
@use '../abstracts' as *; // Gets all forwarded members
```

## File Naming Conventions

### Partials

- Prefix with underscore: `_button.scss`
- Lowercase with dashes: `_nav-mobile.scss`
- One component per file

### BEM in Files

```
components/
├── _button.scss          # .button, .button__icon, .button--primary
├── _card.scss            # .card, .card__header, .card--featured
└── _form.scss            # .form, .form__field, .form--inline
```

## Variables File Structure

### \_variables.scss

```scss
// ==========================================================================
// Design Tokens
// ==========================================================================

// Colors
:root {
  // Brand
  --primary: oklch(65% 0.2 250);
  --secondary: oklch(60% 0.15 180);
  --accent: oklch(70% 0.25 40);

  // Semantic
  --success: oklch(65% 0.2 145);
  --warning: oklch(75% 0.18 85);
  --error: oklch(55% 0.22 25);

  // Neutrals
  --surface: oklch(98% 0.01 250);
  --surface-alt: oklch(95% 0.01 250);
  --text: oklch(15% 0.01 250);
  --text-muted: oklch(40% 0.01 250);

  // Borders
  --border: oklch(85% 0.01 250);
  --border-focus: var(--primary);
}

// Spacing Scale
:root {
  --space-3xs: clamp(0.25rem, 0.5vw, 0.375rem);
  --space-2xs: clamp(0.5rem, 1vw, 0.75rem);
  --space-xs: clamp(0.75rem, 1.5vw, 1rem);
  --space-s: clamp(1rem, 2vw, 1.25rem);
  --space-m: clamp(1.5rem, 3vw, 2rem);
  --space-l: clamp(2rem, 4vw, 3rem);
  --space-xl: clamp(3rem, 6vw, 4.5rem);
  --space-2xl: clamp(4rem, 8vw, 6rem);
}

// Typography
:root {
  --font-sans: system-ui, -apple-system, sans-serif;
  --font-mono: ui-monospace, monospace;

  --text-xs: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);
  --text-sm: clamp(0.875rem, 0.8rem + 0.35vw, 1rem);
  --text-base: clamp(1rem, 0.9rem + 0.5vw, 1.125rem);
  --text-lg: clamp(1.125rem, 1rem + 0.6vw, 1.375rem);
  --text-xl: clamp(1.25rem, 1.1rem + 0.75vw, 1.625rem);
  --text-2xl: clamp(1.5rem, 1.3rem + 1vw, 2rem);
  --text-3xl: clamp(2rem, 1.7rem + 1.5vw, 2.75rem);
  --text-4xl: clamp(2.5rem, 2rem + 2.5vw, 4rem);
}

// Borders & Radius
:root {
  --radius-sm: 0.25rem;
  --radius-md: 0.5rem;
  --radius-lg: 1rem;
  --radius-full: 9999px;
}

// Shadows
:root {
  --shadow-sm: 0 1px 2px oklch(0% 0 0 / 0.05);
  --shadow-md: 0 4px 6px oklch(0% 0 0 / 0.1);
  --shadow-lg: 0 10px 15px oklch(0% 0 0 / 0.1);
}

// Z-Index Scale
:root {
  --z-dropdown: 100;
  --z-sticky: 200;
  --z-modal-backdrop: 300;
  --z-modal: 400;
  --z-toast: 500;
}

// Transitions
:root {
  --duration-fast: 150ms;
  --duration-normal: 250ms;
  --duration-slow: 400ms;
  --ease-out: cubic-bezier(0, 0, 0.2, 1);
  --ease-in: cubic-bezier(0.4, 0, 1, 1);
  --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
}
```

## Mixins File Structure

### \_mixins.scss

```scss
// ==========================================================================
// Mixins
// ==========================================================================

// Visually Hidden (Screen Reader Only)
@mixin sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

// Focus Ring
@mixin focus-ring {
  outline: 2px solid var(--border-focus);
  outline-offset: 2px;
}

// Truncate Text
@mixin truncate($lines: 1) {
  @if $lines == 1 {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  } @else {
    display: -webkit-box;
    -webkit-line-clamp: $lines;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}

// Container
@mixin container($max-width: 1200px) {
  inline-size: min(90%, $max-width);
  margin-inline: auto;
}
```

## Component File Template

### \_button.scss

```scss
/* ==========================================================================
   Component: Button
   ========================================================================== */

.button {
  // Local variables
  --_bg: var(--primary);
  --_color: white;
  --_padding-block: 0.75em;
  --_padding-inline: 1.5em;

  // Base styles
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5em;

  padding: var(--_padding-block) var(--_padding-inline);
  background: var(--_bg);
  color: var(--_color);

  border: none;
  border-radius: var(--radius-md);
  cursor: pointer;

  font: inherit;
  font-weight: 500;
  text-decoration: none;

  transition:
    transform var(--duration-fast) var(--ease-out),
    background var(--duration-fast) var(--ease-out);

  // States
  &:hover {
    --_bg: var(--primary-dark);
  }

  &:active {
    transform: scale(0.98);
  }

  &:focus-visible {
    @include focus-ring;
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
  }

  // Modifiers
  &--secondary {
    --_bg: var(--secondary);
  }

  &--outline {
    --_bg: transparent;
    --_color: var(--primary);
    border: 2px solid currentColor;
  }

  &--small {
    --_padding-block: 0.5em;
    --_padding-inline: 1em;
    font-size: var(--text-sm);
  }

  &--large {
    --_padding-block: 1em;
    --_padding-inline: 2em;
    font-size: var(--text-lg);
  }

  // Elements
  &__icon {
    flex-shrink: 0;
    inline-size: 1.25em;
    block-size: 1.25em;
  }
}
```

## Import Order Importance

1. **Reset** - Normalize browser defaults
2. **Base** - Typography, global styles
3. **Layout** - Structural components
4. **Components** - UI components
5. **Utilities** - Override helpers

Later imports override earlier ones (unless using layers).
