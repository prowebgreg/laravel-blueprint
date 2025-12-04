# Layout Patterns Reference

## Display Properties Overview

| Display        | Use Case                                   |
| -------------- | ------------------------------------------ |
| `block`        | Full-width elements, stacking vertically   |
| `inline`       | Text-level elements within a line          |
| `inline-block` | Inline elements needing width/height       |
| `flex`         | One-dimensional layouts (row OR column)    |
| `grid`         | Two-dimensional layouts (rows AND columns) |

## Flexbox Patterns

### Basic Flex Container

```css
.flex-container {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: var(--space-m);
  justify-content: center;
  align-items: center;
}
```

### Flex Items

```css
.flex-item {
  flex: 1; /* Equal width items */
  flex: 0 0 auto; /* Fixed size */
  flex: 1 1 300px; /* Grow/shrink from 300px */
}
```

### Auto-Wrapping Cards

```css
.card-layout {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-m);

  & > * {
    flex: 1 1 300px;
  }
}
```

## Grid Patterns

### Responsive Grid (auto-fit vs auto-fill)

```css
/* auto-fit: Items stretch to fill space */
.grid-fit {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(250px, 100%), 1fr));
  gap: var(--space-m);
}

/* auto-fill: Consistent item sizes */
.grid-fill {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(250px, 100%), 1fr));
  gap: var(--space-m);
}
```

### Grid Template Areas

```css
.layout {
  display: grid;
  grid-template-areas:
    'header header'
    'sidebar main'
    'footer footer';
  grid-template-columns: 250px 1fr;
  grid-template-rows: auto 1fr auto;
  min-block-size: 100dvh;
}

.header {
  grid-area: header;
}
.sidebar {
  grid-area: sidebar;
}
.main {
  grid-area: main;
}
.footer {
  grid-area: footer;
}
```

### Bento Grid

```css
.bento {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  grid-auto-rows: 150px;
  gap: var(--space-m);
}

.bento__item--large {
  grid-column: span 2;
  grid-row: span 2;
}
```

### Overlapping Elements

```css
.overlap {
  display: grid;

  & > * {
    grid-area: 1 / 1;
  }
}
```

### Subgrid

```css
.parent {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
}

.child {
  grid-column: span 3;
  display: grid;
  grid-template-columns: subgrid;
}
```

## Common Recipes

### Holy Grail

```css
.holy-grail {
  display: grid;
  grid-template:
    'header header header' auto
    'nav main aside' 1fr
    'footer footer footer' auto
    / 200px 1fr 200px;
  min-block-size: 100dvh;
}
```

### Centered Container

```css
.container {
  inline-size: min(90%, 1200px);
  margin-inline: auto;
}
```

### Sticky Footer

```css
.page {
  display: grid;
  grid-template-rows: auto 1fr auto;
  min-block-size: 100dvh;
}
```

### Full-Bleed Layout

```css
.full-bleed-wrapper {
  display: grid;
  grid-template-columns: 1fr min(65ch, 90%) 1fr;

  & > * {
    grid-column: 2;
  }
  & > .full-bleed {
    grid-column: 1 / -1;
  }
}
```
