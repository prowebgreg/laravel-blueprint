---
name: css-expert
description: Comprehensive CSS/SCSS expertise for frontend development. Use when writing, reviewing, or architecting CSS/SCSS code, creating responsive layouts, animations, design systems, or any styling task. Provides modern CSS best practices inspired by best methodologies including BEM naming, container queries, CSS nesting, custom properties, animations, accessibility, and professional file organization.
---

# CSS Expert Skill

Expert-level CSS/SCSS guidance for Claude Code and subagents. Write modern, maintainable, performant, and accessible stylesheets.

## Core Principles

1. **Semantic First**: Use meaningful class names that describe purpose, not appearance
2. **Progressive Enhancement**: Start with intrinsic responsiveness, escalate to queries only when needed
3. **DRY Code**: Use custom properties, mixins, and component patterns to avoid repetition
4. **Performance**: Animate only transform and opacity; avoid layout thrashing
5. **Accessibility**: Always consider reduced motion, color contrast, focus states

## CSS Writing Standards

### Formatting

```css
/* 2-space indentation */
.block {
  property: value;
}

/* Blank line between rule sets */
.block-one {
  color: var(--text-primary);
}

.block-two {
  color: var(--text-secondary);
}

/* Section comments */
/* ==========================================================================
   Component: Card
   ========================================================================== */
```

### BEM Methodology

Always use BEM for class naming. See [references/bem-guide.md](references/bem-guide.md).

```css
/* Block */
.card {
}

/* Element (block__element) */
.card__header {
}
.card__body {
}
.card__footer {
}

/* Modifier (block--modifier or block__element--modifier) */
.card--featured {
}
.card__header--compact {
}
```

**Naming Rules:**

- Lowercase letters, numbers, dashes only
- Use descriptive, purpose-based names
- Never duplicate class names across project
- Prefix utility classes: `u-`, state classes: `is-`, `has-`

### CSS Nesting (Native)

Use native CSS nesting for cleaner, more maintainable code:

```css
.card {
  background: var(--surface);

  /* Nested elements */
  & .card__header {
    padding: var(--space-m);
  }

  /* Pseudo-classes */
  &:hover {
    transform: translateY(-2px);
  }

  /* Pseudo-elements */
  &::before {
    content: '';
  }

  /* Media/Container queries inside selector */
  @container (min-width: 400px) {
    flex-direction: row;
  }
}
```

**The "Has Me" Selector** - Auto-declare parent as container:

```css
.card {
  /* Makes any parent of .card a container automatically */
  :has(> &) {
    container-type: inline-size;
  }

  @container (min-width: 300px) {
    /* responsive styles */
  }
}
```

### Custom Properties (Variables)

**Global Variables** (`:root`):

```css
:root {
  /* Colors - Use descriptive naming (Kevin Geary approach) */
  --primary: oklch(65% 0.25 250);
  --primary-dark: oklch(from var(--primary) calc(l - 0.15) c h);
  --primary-semi-dark: oklch(from var(--primary) calc(l - 0.08) c h);
  --primary-semi-light: oklch(from var(--primary) calc(l + 0.08) c h);
  --primary-light: oklch(from var(--primary) calc(l + 0.15) c h);
  --primary-ultra-light: oklch(from var(--primary) calc(l + 0.25) c h);

  /* Spacing scale */
  --space-3xs: clamp(0.25rem, 0.5vw, 0.375rem);
  --space-2xs: clamp(0.5rem, 1vw, 0.75rem);
  --space-xs: clamp(0.75rem, 1.5vw, 1rem);
  --space-s: clamp(1rem, 2vw, 1.25rem);
  --space-m: clamp(1.5rem, 3vw, 2rem);
  --space-l: clamp(2rem, 4vw, 3rem);
  --space-xl: clamp(3rem, 6vw, 4.5rem);

  /* Typography */
  --font-base: clamp(1rem, 0.5vw + 0.875rem, 1.125rem);
  --font-sm: clamp(0.875rem, 0.4vw + 0.8rem, 1rem);
  --font-lg: clamp(1.125rem, 0.6vw + 1rem, 1.375rem);

  /* Line height calculation (intelligent) */
  --lh-tight: calc(2px + 2ex + 2px);
  --lh-normal: calc(4px + 2ex + 4px);
  --lh-loose: calc(8px + 2ex + 8px);
}
```

**Locally Scoped Variables** (component-level):

```css
.button {
  --_padding-block: 0.75em;
  --_padding-inline: 1.5em;
  --_bg: var(--primary);
  --_color: var(--text-on-primary);

  padding: var(--_padding-block) var(--_padding-inline);
  background: var(--_bg);
  color: var(--_color);

  &--small {
    --_padding-block: 0.5em;
    --_padding-inline: 1em;
  }

  &--secondary {
    --_bg: var(--secondary);
  }
}
```

### Modern Color System (OKLCH + Relative Color Syntax)

**Prefer OKLCH** for perceptually uniform colors:

```css
:root {
  --brand: oklch(65% 0.2 250);
}
```

**Relative Color Syntax** for transparency and variations:

```css
.overlay {
  /* Add transparency to any color */
  background: oklch(from var(--brand) l c h / 0.5);

  /* Lighten */
  border-color: oklch(from var(--brand) calc(l + 0.2) c h);

  /* Desaturate */
  color: oklch(from var(--brand) l calc(c * 0.5) h);
}
```

**color-mix() for older browser support:**

```css
.element {
  /* Mix with transparent for opacity */
  background: color-mix(in oklch, var(--brand) 50%, transparent);
}
```

### Logical Properties

Always prefer logical properties for internationalization:

```css
.element {
  /* Use these */
  margin-block: var(--space-m);
  margin-inline: var(--space-s);
  padding-block-start: var(--space-xs);
  inline-size: 100%;
  max-inline-size: 60ch;

  /* Instead of */
  /* margin-top, margin-bottom, margin-left, margin-right */
  /* width, max-width */
}
```

## Responsive Development (Escalation Workflow)

Follow this priority order - solve problems at lowest level possible:

### Level 0: Intrinsic Responsiveness (No Queries)

```css
/* Fluid typography with clamp() */
h1 {
  font-size: clamp(2rem, 5vw + 1rem, 4rem);
}

/* Flexible layouts */
.container {
  max-inline-size: min(90%, 1200px);
}

/* Auto-wrapping flex */
.flex-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-m);
}

/* Auto-fit grid */
.auto-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(250px, 100%), 1fr));
  gap: var(--space-m);
}
```

### Level 1: Container Queries (Component-based)

Use when component behavior depends on available space:

```css
.card {
  :has(> &) {
    container-type: inline-size;
  }

  display: flex;
  flex-direction: column;

  @container (min-width: 400px) {
    flex-direction: row;
  }
}
```

### Level 2: Media Queries (Viewport-based)

Use only for viewport-anchored elements (modals, drawers, sticky headers):

```css
.modal {
  @media (width >= 768px) {
    max-inline-size: 600px;
  }
}
```

**Range syntax** (modern):

```css
@media (400px <= width <= 800px) {
}
@container (inline-size >= 300px) {
}
```

## Layout Patterns

See [references/layout-patterns.md](references/layout-patterns.md) for comprehensive grid and flexbox patterns.

### Quick Reference

```css
/* Holy Grail Layout */
.layout {
  display: grid;
  grid-template:
    'header header' auto
    'sidebar main' 1fr
    'footer footer' auto
    / minmax(200px, 300px) 1fr;
  min-block-size: 100dvh;
}

/* Responsive card grid */
.cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(280px, 100%), 1fr));
  gap: var(--space-m);
}
```

## Animations

See [references/animations.md](references/animations.md) for detailed animation patterns.

### Performance Rules

1. **Only animate**: `transform`, `opacity` (GPU-accelerated)
2. **Never animate**: `width`, `height`, `margin`, `padding`, `top/left/right/bottom`
3. Use `will-change` sparingly and only when needed
4. Keep durations under 300ms for micro-interactions

```css
@keyframes fade-slide-in {
  from {
    opacity: 0;
    transform: translateY(1rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: fade-slide-in 0.3s ease-out forwards;
}
```

## Accessibility

```css
/* Respect motion preferences */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Color scheme support */
:root {
  color-scheme: light dark;
}

@media (prefers-color-scheme: dark) {
  :root {
    --surface: oklch(20% 0.02 250);
    --text: oklch(95% 0 0);
  }
}

/* Focus states - always visible for keyboard */
:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

/* Hide focus for mouse users */
:focus:not(:focus-visible) {
  outline: none;
}
```

## Pseudo-Classes & Pseudo-Elements

See [references/pseudo-guide.md](references/pseudo-guide.md) for complete reference.

### Modern Selectors

```css
/* :has() - parent selector */
.form:has(:invalid) {
  border-color: var(--error);
}
.card:has(> img) {
  padding-block-start: 0;
}

/* :is() - matches any of the selectors */
:is(h1, h2, h3):is(:hover, :focus) {
  color: var(--accent);
}

/* :where() - zero specificity */
:where(ul, ol) {
  list-style: none;
}

/* :not() - negation */
.button:not(:disabled):hover {
  transform: scale(1.02);
}

/* :focus-within - child focus */
.input-group:focus-within {
  border-color: var(--focus-ring);
}
```

## File Organization

See [references/file-structure.md](references/file-structure.md) for detailed guidance.

### Recommended Structure (SCSS/CSS)

```
styles/
├── main.scss              # Main entry - imports only
├── abstracts/
│   ├── _index.scss        # @forward all abstracts
│   ├── _variables.scss    # Global custom properties
│   ├── _mixins.scss       # Reusable mixins
│   └── _functions.scss    # SCSS functions
├── base/
│   ├── _index.scss
│   ├── _reset.scss        # CSS reset (layered)
│   └── _typography.scss   # Base typography
├── components/
│   ├── _index.scss
│   ├── _button.scss
│   ├── _card.scss
│   └── _form.scss
├── layout/
│   ├── _index.scss
│   ├── _header.scss
│   ├── _footer.scss
│   └── _grid.scss
└── utilities/
    ├── _index.scss
    └── _helpers.scss
```

### main.scss Structure

```scss
/* Layered for proper cascade control */
@layer reset, base, layout, components, utilities;

@layer reset {
  @use 'base/reset';
}

@layer base {
  @use 'abstracts' as *;
  @use 'base/typography';
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
```

## Common Mistakes to Avoid

See [references/common-mistakes.md](references/common-mistakes.md) for comprehensive list.

**Critical Errors:**

- ❌ Using `!important` (fix specificity instead)
- ❌ Magic numbers (use variables/tokens)
- ❌ Animating layout properties
- ❌ Fixed breakpoints (use content-based)
- ❌ IDs for styling (use classes)
- ❌ `min-height` for section spacing (use padding)
- ❌ Spacer elements (use gap/margin)
- ❌ Raw values instead of tokens

## CSS Reset

Include a modern CSS reset in every project. See [references/css-reset.md](references/css-reset.md).

Essential reset rules:

```css
@layer reset {
  *,
  *::before,
  *::after {
    box-sizing: border-box;
  }
  * {
    margin: 0;
  }
  html {
    hanging-punctuation: first last;
  }
  body {
    min-block-size: 100dvh;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
  }
  img,
  picture,
  video,
  canvas,
  svg {
    display: block;
    max-inline-size: 100%;
  }
  input,
  button,
  textarea,
  select {
    font: inherit;
    color: inherit;
  }
  p,
  h1,
  h2,
  h3,
  h4,
  h5,
  h6 {
    overflow-wrap: break-word;
  }
  h1,
  h2,
  h3,
  h4 {
    text-wrap: balance;
  }
  p {
    text-wrap: pretty;
  }
}
```

## Reference Files

- [references/layout-patterns.md](references/layout-patterns.md) - Grid, Flexbox, advanced layouts
- [references/responsive-techniques.md](references/responsive-techniques.md) - Container queries, Has Me selector
- [references/animations.md](references/animations.md) - Keyframes, transitions, performance
- [references/common-mistakes.md](references/common-mistakes.md) - Anti-patterns to avoid
- [references/pseudo-guide.md](references/pseudo-guide.md) - Pseudo-classes and pseudo-elements
- [references/color-system.md](references/color-system.md) - OKLCH, relative colors, theming
- [references/file-structure.md](references/file-structure.md) - Project organization
- [references/css-reset.md](references/css-reset.md) - Modern CSS reset
- [references/bem-guide.md](references/bem-guide.md) - BEM naming methodology
