# Modern CSS Reset

Based on Josh W. Comeau and Andy Bell's modern CSS reset approaches.

## Complete Reset

Place in a cascade layer for easy overriding:

```css
@layer reset {
  /* ==========================================================================
     Modern CSS Reset
     Based on Josh W. Comeau & Andy Bell
     ========================================================================== */

  /* Box sizing */
  *,
  *::before,
  *::after {
    box-sizing: border-box;
  }

  /* Remove default margin and padding */
  * {
    margin: 0;
    padding: 0;
  }

  /* Prevent font size inflation on mobile */
  html {
    -moz-text-size-adjust: none;
    -webkit-text-size-adjust: none;
    text-size-adjust: none;

    /* Better punctuation */
    hanging-punctuation: first last;

    /* Enable keyword animations */
    @media (prefers-reduced-motion: no-preference) {
      interpolate-size: allow-keywords;
    }
  }

  /* Core body defaults */
  body {
    min-block-size: 100dvh;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  /* Improve media defaults */
  img,
  picture,
  video,
  canvas,
  svg {
    display: block;
    max-inline-size: 100%;
    block-size: auto;
  }

  /* Remove built-in form typography styles */
  input,
  button,
  textarea,
  select {
    font: inherit;
    color: inherit;
  }

  /* Avoid text overflows */
  p,
  h1,
  h2,
  h3,
  h4,
  h5,
  h6 {
    overflow-wrap: break-word;
  }

  /* Improve text wrapping */
  h1,
  h2,
  h3,
  h4 {
    text-wrap: balance;
  }

  p {
    text-wrap: pretty;
  }

  /* Remove list styles on ul, ol with list role */
  ul[role='list'],
  ol[role='list'] {
    list-style: none;
  }

  /* Anchor defaults */
  a {
    text-decoration-skip-ink: auto;
    color: currentColor;
  }

  /* Make sure textareas without rows attribute are not tiny */
  textarea:not([rows]) {
    min-block-size: 10em;
  }

  /* Anything targeted should have scroll margin */
  :target {
    scroll-margin-block: 5ex;
  }

  /* Remove animations for reduced motion preference */
  @media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
      scroll-behavior: auto !important;
    }
  }
}
```

## SCSS Version

```scss
// _reset.scss
@layer reset {
  *,
  *::before,
  *::after {
    box-sizing: border-box;
  }

  * {
    margin: 0;
    padding: 0;
  }

  html {
    -moz-text-size-adjust: none;
    -webkit-text-size-adjust: none;
    text-size-adjust: none;
    hanging-punctuation: first last;

    @media (prefers-reduced-motion: no-preference) {
      interpolate-size: allow-keywords;
    }
  }

  body {
    min-block-size: 100dvh;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  img,
  picture,
  video,
  canvas,
  svg {
    display: block;
    max-inline-size: 100%;
    block-size: auto;
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

  :is(h1, h2, h3, h4) {
    text-wrap: balance;
  }

  p {
    text-wrap: pretty;
  }

  :is(ul, ol)[role='list'] {
    list-style: none;
  }

  a {
    text-decoration-skip-ink: auto;
    color: currentColor;
  }

  textarea:not([rows]) {
    min-block-size: 10em;
  }

  :target {
    scroll-margin-block: 5ex;
  }

  @media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
      scroll-behavior: auto !important;
    }
  }
}
```

## Rule Explanations

### Box Sizing

```css
*,
*::before,
*::after {
  box-sizing: border-box;
}
```

Padding and border included in element's total width/height.

### Remove Margins

```css
* {
  margin: 0;
  padding: 0;
}
```

Start fresh - add spacing intentionally.

### Body Min Height

```css
body {
  min-block-size: 100dvh;
}
```

Uses dynamic viewport height for mobile browser chrome handling.

### Font Smoothing

```css
body {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
```

Smoother font rendering on macOS/iOS.

### Media Defaults

```css
img,
picture,
video,
canvas,
svg {
  display: block;
  max-inline-size: 100%;
}
```

Prevents weird spacing and overflow.

### Form Typography

```css
input,
button,
textarea,
select {
  font: inherit;
  color: inherit;
}
```

Forms use parent font instead of browser defaults.

### Text Wrapping

```css
h1,
h2,
h3,
h4 {
  text-wrap: balance;
}
p {
  text-wrap: pretty;
}
```

- `balance`: Evens out line lengths in headings
- `pretty`: Prevents orphans in paragraphs

### interpolate-size

```css
html {
  interpolate-size: allow-keywords;
}
```

Enables animating to `auto`, `fit-content`, etc.

## Optional Additions

### Root Stacking Context

```css
#root,
#__next {
  isolation: isolate;
}
```

Creates new stacking context for React/Next.js apps.

### Better Focus

```css
:focus-visible {
  outline: 2px solid var(--focus-ring, currentColor);
  outline-offset: 2px;
}

:focus:not(:focus-visible) {
  outline: none;
}
```

### Table Defaults

```css
table {
  border-collapse: collapse;
  border-spacing: 0;
}
```

### Selection Color

```css
::selection {
  background: var(--selection-bg, Highlight);
  color: var(--selection-text, HighlightText);
}
```

### Smooth Scroll

```css
@media (prefers-reduced-motion: no-preference) {
  html {
    scroll-behavior: smooth;
  }
}
```

## Why Layer Your Reset

```css
@layer reset {
  /* Reset rules */
}
```

Benefits:

- Any unlayered CSS automatically overrides reset
- Easy to override without specificity battles
- Future-proof for when more code uses layers
