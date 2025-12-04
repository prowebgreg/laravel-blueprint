# Common CSS Mistakes to Avoid

Based on Kevin Geary's "Cardinal Sins" and industry best practices.

## Critical Errors

### ❌ Magic Numbers

```css
/* Bad */
.element {
  margin-top: 37px;
  padding-left: 23px;
}

/* Good - use tokens */
.element {
  margin-block-start: var(--space-m);
  padding-inline-start: var(--space-s);
}
```

### ❌ Raw Values Instead of Tokens

```css
/* Bad */
.card {
  background: #f5f5f5;
  padding: 24px;
  border-radius: 8px;
}

/* Good */
.card {
  background: var(--surface-secondary);
  padding: var(--space-m);
  border-radius: var(--radius-m);
}
```

### ❌ Using !important

```css
/* Bad */
.button {
  color: red !important;
}

/* Good - fix specificity or use layers */
@layer components {
  .button {
    color: var(--button-color);
  }
}
```

### ❌ IDs for Styling

```css
/* Bad */
#header {
  background: var(--surface);
}

/* Good */
.header {
  background: var(--surface);
}
```

### ❌ Overly Specific Selectors

```css
/* Bad */
div.container > ul.nav > li.nav-item > a.nav-link {
  color: blue;
}

/* Good */
.nav__link {
  color: var(--nav-link-color);
}
```

## Layout Errors

### ❌ min-height for Section Spacing

```css
/* Bad */
.section {
  min-height: 400px;
}

/* Good - use padding */
.section {
  padding-block: var(--space-xl);
}
```

### ❌ Spacer Elements

```html
<!-- Bad -->
<div class="spacer"></div>

<!-- Good - use gap or margin -->
```

```css
.stack {
  display: flex;
  flex-direction: column;
  gap: var(--space-m);
}
```

### ❌ Fixed Heights on Content

```css
/* Bad */
.card__body {
  height: 200px;
}

/* Good */
.card__body {
  min-block-size: 200px; /* If minimum needed */
}
```

### ❌ Absolute Positioning for Layout

```css
/* Bad */
.sidebar {
  position: absolute;
  left: 0;
  top: 0;
  width: 250px;
}

/* Good */
.layout {
  display: grid;
  grid-template-columns: 250px 1fr;
}
```

## Responsive Errors

### ❌ Fixed Device Breakpoints

```css
/* Bad */
@media (min-width: 768px) {
} /* "tablet" */
@media (min-width: 1024px) {
} /* "desktop" */

/* Good - content-based */
@media (width >= 45rem) {
}

/* Better - container queries */
@container (min-width: 400px) {
}
```

### ❌ Media Queries for Components

```css
/* Bad */
.card {
  @media (min-width: 600px) {
    flex-direction: row;
  }
}

/* Good - container queries */
.card {
  :has(> &) {
    container-type: inline-size;
  }

  @container (min-width: 400px) {
    flex-direction: row;
  }
}
```

### ❌ Fixed Widths

```css
/* Bad */
.container {
  width: 1200px;
}

/* Good */
.container {
  inline-size: min(90%, 1200px);
  margin-inline: auto;
}
```

## Animation Errors

### ❌ Animating Layout Properties

```css
/* Bad - causes layout recalculation */
.element {
  transition:
    width 0.3s,
    height 0.3s,
    margin 0.3s;
}

/* Good - GPU accelerated */
.element {
  transition:
    transform 0.3s,
    opacity 0.3s;
}
```

### ❌ Ignoring Reduced Motion

```css
/* Bad */
.element {
  animation: bounce 1s infinite;
}

/* Good */
@media (prefers-reduced-motion: no-preference) {
  .element {
    animation: bounce 1s infinite;
  }
}
```

## Color Errors

### ❌ Numbered Color Shades

```css
/* Bad */
--primary-100: ...;
--primary-200: ...;
--primary-300: ...;

/* Good - descriptive names */
--primary-ultra-light: ...;
--primary-light: ...;
--primary-semi-light: ...;
--primary: ...;
--primary-semi-dark: ...;
--primary-dark: ...;
```

### ❌ Hardcoded Colors

```css
/* Bad */
.button {
  background: #3498db;
  color: white;
}

/* Good */
.button {
  background: var(--primary);
  color: var(--text-on-primary);
}
```

## Naming Errors

### ❌ Appearance-Based Names

```css
/* Bad */
.red-button {
}
.left-sidebar {
}
.big-text {
}

/* Good */
.button--danger {
}
.sidebar--primary {
}
.text--heading {
}
```

### ❌ Inconsistent BEM

```css
/* Bad - mixing conventions */
.card {
}
.card-header {
}
.cardBody {
}
.card__footer {
}

/* Good - consistent BEM */
.card {
}
.card__header {
}
.card__body {
}
.card__footer {
}
```

## Physical Properties

### ❌ Physical Instead of Logical

```css
/* Bad */
.element {
  margin-left: 1rem;
  margin-right: 1rem;
  width: 100%;
  text-align: left;
}

/* Good */
.element {
  margin-inline: 1rem;
  inline-size: 100%;
  text-align: start;
}
```

## Miscellaneous

### ❌ outline: none Without Alternative

```css
/* Bad */
button:focus {
  outline: none;
}

/* Good */
button:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}
```

### ❌ Not Using CSS Custom Properties

```css
/* Bad - repeated values */
.header {
  padding: 16px 24px;
}
.footer {
  padding: 16px 24px;
}
.card {
  padding: 16px 24px;
}

/* Good */
:root {
  --space-block: 1rem;
  --space-inline: 1.5rem;
}

.header,
.footer,
.card {
  padding: var(--space-block) var(--space-inline);
}
```

### ❌ z-index Wars

```css
/* Bad */
.modal {
  z-index: 99999;
}
.dropdown {
  z-index: 999999;
}

/* Good - defined scale */
:root {
  --z-dropdown: 100;
  --z-modal: 200;
  --z-toast: 300;
}
```

### ❌ Overusing Flexbox for Everything

```css
/* Bad - using flex when not needed */
.container {
  display: flex;
  flex-direction: column;
}

/* Good - block is fine for simple stacking */
.container {
  /* Block is default, just add gap if needed */
  & > * + * {
    margin-block-start: var(--space-m);
  }
}
```

### ❌ Not Testing Overflow

Always check:

- Long words in narrow containers
- Missing images
- Extreme content lengths
- Different languages

```css
.text-container {
  overflow-wrap: break-word;
  hyphens: auto;
}
```
