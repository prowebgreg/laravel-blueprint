# Responsive Techniques Reference

## The Escalation Workflow

1. **Intrinsic Responsiveness** (no queries) → First choice
2. **Container Queries** (component-based) → When intrinsic isn't enough
3. **Media Queries** (viewport-based) → Only for viewport-anchored elements

## Level 0: Intrinsic Responsiveness

### Fluid Typography

```css
h1 {
  font-size: clamp(2rem, 5vw + 1rem, 4rem);
}
body {
  font-size: clamp(1rem, 0.5vw + 0.875rem, 1.25rem);
}
```

### Fluid Spacing

```css
:root {
  --space-s: clamp(1rem, 0.5rem + 2vw, 1.5rem);
  --space-m: clamp(1.5rem, 1rem + 3vw, 2.5rem);
}
```

### Flexible Containers

```css
.container {
  inline-size: min(90%, 1200px);
  margin-inline: auto;
}
```

### Auto-Wrapping Grid

```css
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(250px, 100%), 1fr));
  gap: var(--space-m);
}
```

## Level 1: Container Queries

### Basic Setup

```css
.wrapper {
  container-type: inline-size;
}

.card {
  @container (min-width: 400px) {
    flex-direction: row;
  }
}
```

### The "Has Me" Selector (Self-Contained Components)

```css
.card {
  :has(> &) {
    container-type: inline-size;
  }

  @container (min-width: 400px) {
    grid-template-columns: 200px 1fr;
  }
}
```

### Container Query Units

```css
.element {
  font-size: 5cqi; /* % of container inline size */
  padding: 2cqb; /* % of container block size */
}
```

### Gotchas

1. **Parent must declare container-type**
2. **Grid cells need wrapper divs** (implicit cells won't work)

## Level 2: Media Queries

### Modern Range Syntax

```css
@media (width >= 768px) {
}
@media (400px <= width <= 800px) {
}
```

### When to Use

- Modals/dialogs (viewport-anchored)
- Sticky headers
- Navigation drawers
- User preferences

### Preference Queries

```css
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
  }
}

@media (prefers-color-scheme: dark) {
  :root {
    --bg: oklch(15% 0.02 250);
  }
}

@media (hover: none) {
  /* Touch devices */
}

@media (pointer: coarse) {
  .button {
    min-block-size: 44px;
  }
}
```

## Dynamic Viewport Units

```css
.hero {
  min-block-size: 100dvh; /* Dynamic - recommended */
  /* 100svh = small viewport, 100lvh = large viewport */
}
```
