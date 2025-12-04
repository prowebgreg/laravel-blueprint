# BEM Methodology Guide

BEM (Block, Element, Modifier) is a naming convention for CSS classes that creates clear, modular, and reusable components.

## Core Concepts

### Block

A standalone, meaningful entity.

```css
/* Blocks */
.header {
}
.menu {
}
.search-form {
}
.card {
}
.button {
}
```

### Element

A part of a block with no standalone meaning. Prefixed with `__`.

```css
/* Elements belong to their block */
.card__header {
}
.card__body {
}
.card__footer {
}
.card__image {
}
.card__title {
}

.menu__item {
}
.menu__link {
}

.search-form__input {
}
.search-form__button {
}
```

### Modifier

A flag on a block or element for appearance/behavior. Prefixed with `--`.

```css
/* Block modifiers */
.card--featured {
}
.card--horizontal {
}
.button--primary {
}
.button--large {
}
.menu--vertical {
}

/* Element modifiers */
.card__title--large {
}
.menu__item--active {
}
.button__icon--left {
}
```

## Naming Rules

### Characters Allowed

- Lowercase letters
- Numbers
- Dashes (for multi-word names)

```css
/* ✅ Good */
.search-form {
}
.nav-menu {
}
.user-profile {
}

/* ❌ Bad */
.searchForm {
} /* No camelCase */
.Search-form {
} /* No uppercase */
.search_form {
} /* No underscores in block names */
```

### Multi-Word Names

Use dashes within blocks/elements/modifiers:

```css
.site-header {
}
.site-header__nav-menu {
}
.site-header__nav-menu--is-open {
}

.user-profile {
}
.user-profile__avatar-image {
}
.user-profile__display-name {
}
```

## Complete Examples

### Card Component

```html
<article class="card card--featured">
  <div class="card__image-wrapper">
    <img class="card__image" src="..." alt="..." />
  </div>
  <div class="card__content">
    <h2 class="card__title">Title</h2>
    <p class="card__description">Description text</p>
  </div>
  <footer class="card__footer">
    <a href="#" class="card__link">Read more</a>
  </footer>
</article>
```

```css
.card {
  display: flex;
  flex-direction: column;
  background: var(--surface);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.card--featured {
  border: 2px solid var(--accent);
}

.card--horizontal {
  flex-direction: row;
}

.card__image-wrapper {
  aspect-ratio: 16 / 9;
  overflow: hidden;
}

.card__image {
  inline-size: 100%;
  block-size: 100%;
  object-fit: cover;
}

.card__content {
  padding: var(--space-m);
  flex: 1;
}

.card__title {
  font-size: var(--text-lg);
  margin-block-end: var(--space-xs);
}

.card__description {
  color: var(--text-muted);
}

.card__footer {
  padding: var(--space-s) var(--space-m);
  border-block-start: 1px solid var(--border);
}

.card__link {
  color: var(--primary);
  text-decoration: none;

  &:hover {
    text-decoration: underline;
  }
}
```

### Button Component

```html
<button class="button button--primary button--large">
  <svg class="button__icon button__icon--left">...</svg>
  <span class="button__text">Submit</span>
</button>
```

```css
.button {
  --_bg: var(--surface);
  --_color: var(--text);

  display: inline-flex;
  align-items: center;
  gap: 0.5em;
  padding: 0.75em 1.5em;
  background: var(--_bg);
  color: var(--_color);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  cursor: pointer;
}

.button--primary {
  --_bg: var(--primary);
  --_color: white;
  border-color: var(--primary);
}

.button--secondary {
  --_bg: var(--secondary);
  --_color: white;
}

.button--outline {
  --_bg: transparent;
  --_color: var(--primary);
  border-color: currentColor;
}

.button--small {
  padding: 0.5em 1em;
  font-size: var(--text-sm);
}

.button--large {
  padding: 1em 2em;
  font-size: var(--text-lg);
}

.button__icon {
  flex-shrink: 0;
  inline-size: 1.25em;
  block-size: 1.25em;
}

.button__icon--left {
  order: -1;
}

.button__text {
  /* Styling if needed */
}
```

### Navigation Component

```html
<nav class="nav nav--vertical">
  <ul class="nav__list">
    <li class="nav__item nav__item--active">
      <a href="/" class="nav__link">Home</a>
    </li>
    <li class="nav__item">
      <a href="/about" class="nav__link">About</a>
    </li>
    <li class="nav__item nav__item--disabled">
      <span class="nav__link">Coming Soon</span>
    </li>
  </ul>
</nav>
```

```css
.nav {
  /* Base navigation styles */
}

.nav--vertical {
  .nav__list {
    flex-direction: column;
  }
}

.nav__list {
  display: flex;
  gap: var(--space-xs);
  list-style: none;
}

.nav__item {
  /* Item styles */
}

.nav__item--active {
  .nav__link {
    color: var(--primary);
    font-weight: 600;
  }
}

.nav__item--disabled {
  opacity: 0.5;
  pointer-events: none;
}

.nav__link {
  display: block;
  padding: var(--space-xs) var(--space-s);
  color: var(--text);
  text-decoration: none;

  &:hover {
    color: var(--primary);
  }
}
```

## BEM with CSS Nesting

```css
.card {
  background: var(--surface);

  &__header {
    padding: var(--space-m);
    border-block-end: 1px solid var(--border);
  }

  &__body {
    padding: var(--space-m);
  }

  &__footer {
    padding: var(--space-s) var(--space-m);
  }

  &--featured {
    border: 2px solid var(--accent);
  }

  &--horizontal {
    flex-direction: row;

    .card__image {
      max-inline-size: 300px;
    }
  }
}
```

## State Classes

For JavaScript-controlled states, use `is-` or `has-` prefixes:

```css
.menu {
  &.is-open {
    display: block;
  }
}

.nav__item {
  &.is-active {
    color: var(--primary);
  }
}

.form {
  &.has-error {
    border-color: var(--error);
  }
}

.card {
  &.is-loading {
    opacity: 0.5;
  }
}
```

## Utility Class Prefixes

For utility/helper classes, use `u-` prefix:

```css
.u-visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
}

.u-text-center {
  text-align: center;
}

.u-mt-m {
  margin-block-start: var(--space-m);
}
```

## Common Mistakes

### ❌ Element of Element

```css
/* Bad - elements can't have elements */
.card__header__title {
}

/* Good - flatten the structure */
.card__title {
}
```

### ❌ Modifier Without Base

```html
<!-- Bad -->
<div class="card--featured">
  <!-- Good -->
  <div class="card card--featured"></div>
</div>
```

### ❌ Too Deep Nesting

```css
/* Bad */
.site-header__nav__list__item__link {
}

/* Good - create new blocks */
.site-header {
}
.nav-menu {
}
.nav-menu__item {
}
.nav-menu__link {
}
```

### ❌ Styling Tags Instead of Classes

```css
/* Bad */
.card h2 {
}
.nav ul li a {
}

/* Good */
.card__title {
}
.nav__link {
}
```

## When to Create a New Block

Create a new block when:

1. Component can be reused independently
2. Nesting becomes too deep (3+ levels)
3. Component has its own modifiers

```css
/* Before: deeply nested */
.header__nav__dropdown__item {
}

/* After: separate blocks */
.header {
}
.main-nav {
}
.dropdown {
}
.dropdown__item {
}
```
