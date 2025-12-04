# Pseudo-Classes & Pseudo-Elements Guide

## Pseudo-Elements

Pseudo-elements create virtual elements for styling.

### ::before and ::after

```css
.element::before {
  content: ''; /* Required - can be empty string */
  display: block;
  /* styles */
}

.element::after {
  content: attr(data-label); /* Use attribute value */
}

/* Common uses */
.quote::before {
  content: '"';
}

.external-link::after {
  content: ' ↗';
}

/* Decorative elements */
.card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent, var(--overlay));
}
```

### ::first-letter and ::first-line

```css
.article p:first-of-type::first-letter {
  font-size: 3em;
  float: left;
  line-height: 1;
  margin-inline-end: 0.1em;
}

.intro::first-line {
  font-weight: bold;
}
```

### ::marker

```css
li::marker {
  color: var(--accent);
  font-size: 1.2em;
}
```

### ::placeholder

```css
input::placeholder {
  color: var(--text-muted);
  opacity: 1;
}
```

### ::selection

```css
::selection {
  background: var(--accent);
  color: var(--text-on-accent);
}
```

## State Pseudo-Classes

### :hover, :active, :focus

```css
.button {
  &:hover {
    background: var(--primary-dark);
  }

  &:active {
    transform: scale(0.98);
  }

  &:focus {
    outline: 2px solid var(--focus-ring);
  }
}
```

### :focus-visible vs :focus

```css
/* :focus-visible - only keyboard focus */
.button:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

/* Hide outline for mouse clicks */
.button:focus:not(:focus-visible) {
  outline: none;
}
```

### :focus-within

```css
/* Style parent when any child has focus */
.input-group:focus-within {
  border-color: var(--focus-ring);
  box-shadow: 0 0 0 3px var(--focus-ring-alpha);
}

.dropdown:focus-within .dropdown__menu {
  display: block;
}
```

### :disabled, :enabled

```css
.button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

.input:enabled {
  background: var(--surface);
}
```

### :checked

```css
.checkbox:checked {
  background: var(--primary);
  border-color: var(--primary);
}

/* Style adjacent label */
.checkbox:checked + label {
  color: var(--primary);
}
```

### :valid, :invalid

```css
.input:valid {
  border-color: var(--success);
}

.input:invalid {
  border-color: var(--error);
}

/* Only show after interaction */
.input:not(:placeholder-shown):invalid {
  border-color: var(--error);
}
```

### :required, :optional

```css
.input:required {
  border-inline-start: 3px solid var(--accent);
}
```

## Structural Pseudo-Classes

### :first-child, :last-child

```css
.list li:first-child {
  border-start-start-radius: var(--radius);
  border-start-end-radius: var(--radius);
}

.list li:last-child {
  border-end-start-radius: var(--radius);
  border-end-end-radius: var(--radius);
}
```

### :nth-child()

```css
/* Every odd row */
tr:nth-child(odd) {
  background: var(--surface-alt);
}

/* Every 3rd item */
li:nth-child(3n) {
  color: var(--accent);
}

/* First 3 items */
li:nth-child(-n + 3) {
  font-weight: bold;
}

/* From 4th onwards */
li:nth-child(n + 4) {
  opacity: 0.8;
}
```

### :only-child

```css
.card:only-child {
  max-inline-size: 600px;
  margin-inline: auto;
}
```

### :empty

```css
.container:empty {
  display: none;
}

.message:empty::before {
  content: 'No messages';
  color: var(--text-muted);
}
```

## Modern Selectors

### :has() - Parent Selector

```css
/* Card with image */
.card:has(> img) {
  padding-block-start: 0;
}

/* Form with invalid inputs */
.form:has(:invalid) {
  border-color: var(--error);
}

/* Container with specific child */
.grid:has(.featured) {
  grid-template-columns: 2fr 1fr 1fr;
}

/* Previous sibling styling */
.item:has(+ .item--active) {
  opacity: 0.5;
}

/* If checkbox is checked */
body:has(#dark-mode:checked) {
  --bg: var(--dark-bg);
  --text: var(--dark-text);
}
```

### :is() - Matches Any

```css
/* Group selectors with same specificity */
:is(h1, h2, h3, h4, h5, h6) {
  line-height: 1.2;
}

/* Nested grouping */
.card :is(h1, h2, h3) {
  color: var(--heading);
}

/* Complex selectors simplified */
:is(article, section, aside) :is(h1, h2) {
  margin-block-end: var(--space-s);
}
```

### :where() - Zero Specificity

```css
/* Reset styles that are easy to override */
:where(ul, ol) {
  list-style: none;
  padding: 0;
  margin: 0;
}

:where(a) {
  color: inherit;
  text-decoration: none;
}
```

**:is() vs :where():**

- `:is()` takes specificity of most specific selector in list
- `:where()` always has 0 specificity

### :not() - Negation

```css
/* All buttons except disabled */
.button:not(:disabled) {
  cursor: pointer;
}

/* Links that aren't internal */
a:not([href^='#']):not([href^='/']) {
  /* External link styles */
}

/* All children except last */
.stack > *:not(:last-child) {
  margin-block-end: var(--space-m);
}
```

## Combining Pseudo-Selectors

```css
/* First valid input that's focused */
input:first-of-type:valid:focus {
  outline-color: var(--success);
}

/* Hover only when not disabled */
.button:not(:disabled):hover {
  background: var(--primary-dark);
}

/* Last child that's also odd */
li:last-child:nth-child(odd) {
  background: var(--surface-alt);
}

/* Empty optional inputs */
input:optional:placeholder-shown {
  border-style: dashed;
}
```

## Accessibility Pseudo-Classes

### :target

```css
/* Highlight linked section */
section:target {
  animation: highlight 1s ease-out;
}

@keyframes highlight {
  from {
    background: var(--highlight);
  }
  to {
    background: transparent;
  }
}
```

### :lang()

```css
:lang(ar) {
  direction: rtl;
}

:lang(ja) {
  font-family: var(--font-japanese);
}
```

### Form Validation States

```css
/* Show validation after user interaction */
.input:user-valid {
  border-color: var(--success);
}

.input:user-invalid {
  border-color: var(--error);
}
```
