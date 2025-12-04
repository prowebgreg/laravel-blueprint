# Color System Reference

## Modern Color Spaces

### OKLCH (Recommended)

OKLCH provides perceptually uniform colors - equal changes in values produce equal perceived changes.

```css
/* oklch(lightness chroma hue) */
:root {
  --primary: oklch(65% 0.2 250);
  /* L: 0-100% (lightness)
     C: 0-0.4 (chroma/saturation)
     H: 0-360 (hue angle) */
}
```

**Why OKLCH:**

- Perceptually uniform (50% lightness actually looks 50%)
- Wider gamut than sRGB
- Predictable color manipulation
- Better gradients (no gray dead zone)

### Other Modern Color Functions

```css
/* Lab - perceptual lightness */
color: lab(50% 40 60);

/* LCH - like Lab but with hue angle */
color: lch(50% 50 250);

/* OKLab - improved Lab */
color: oklab(0.65 0.1 -0.15);

/* Display-P3 - wider gamut */
color: color(display-p3 1 0.5 0);
```

## Relative Color Syntax

Create color variations from a base color dynamically.

### Syntax

```css
color-function(from origin-color channel1 channel2 channel3 / alpha)
```

### Transparency

```css
:root {
  --primary: oklch(65% 0.2 250);
}

.overlay {
  /* 50% transparent primary */
  background: oklch(from var(--primary) l c h / 0.5);
}

.border {
  /* 20% opacity */
  border-color: oklch(from var(--primary) l c h / 0.2);
}
```

### Lightness Variations

```css
:root {
  --primary: oklch(65% 0.2 250);

  /* Lighter variations */
  --primary-light: oklch(from var(--primary) calc(l + 0.15) c h);
  --primary-ultra-light: oklch(from var(--primary) calc(l + 0.25) c h);

  /* Darker variations */
  --primary-dark: oklch(from var(--primary) calc(l - 0.15) c h);
  --primary-ultra-dark: oklch(from var(--primary) calc(l - 0.25) c h);
}
```

### Saturation/Chroma Adjustments

```css
/* Desaturate */
--muted: oklch(from var(--primary) l calc(c * 0.5) h);

/* More vibrant */
--vibrant: oklch(from var(--primary) l calc(c * 1.2) h);

/* Grayscale */
--gray: oklch(from var(--primary) l 0 h);
```

### Hue Shifting

```css
/* Complementary (opposite) */
--complement: oklch(from var(--primary) l c calc(h + 180));

/* Analogous (adjacent) */
--analogous: oklch(from var(--primary) l c calc(h + 30));

/* Triadic */
--triadic: oklch(from var(--primary) l c calc(h + 120));
```

## color-mix() Function

Fallback for browsers without relative color syntax.

```css
/* Mix with transparent for opacity */
.element {
  background: color-mix(in oklch, var(--primary) 50%, transparent);
}

/* Mix two colors */
.element {
  background: color-mix(in oklch, var(--primary), var(--secondary));
}

/* Weighted mix */
.element {
  background: color-mix(in oklch, var(--primary) 75%, var(--secondary));
}
```

## Descriptive Color Naming (Kevin Geary Approach)

### ❌ Avoid Numbered Shades

```css
/* Bad - not mutable, unclear purpose */
--primary-100: ...;
--primary-200: ...;
--primary-900: ...;
```

### ✅ Use Descriptive Names

```css
:root {
  --primary: oklch(65% 0.2 250);

  /* Lighter */
  --primary-ultra-light: oklch(from var(--primary) calc(l + 0.3) c h);
  --primary-light: oklch(from var(--primary) calc(l + 0.2) c h);
  --primary-semi-light: oklch(from var(--primary) calc(l + 0.1) c h);

  /* Darker */
  --primary-semi-dark: oklch(from var(--primary) calc(l - 0.1) c h);
  --primary-dark: oklch(from var(--primary) calc(l - 0.2) c h);
  --primary-ultra-dark: oklch(from var(--primary) calc(l - 0.3) c h);
}
```

## Complete Color System Example

```css
:root {
  /* Base colors */
  --brand: oklch(60% 0.2 250);
  --accent: oklch(70% 0.25 150);

  /* Semantic colors */
  --success: oklch(65% 0.2 145);
  --warning: oklch(75% 0.18 85);
  --error: oklch(55% 0.22 25);
  --info: oklch(60% 0.15 240);

  /* Neutrals */
  --gray-base: oklch(50% 0.01 250);
  --gray-50: oklch(from var(--gray-base) calc(l + 0.45) c h);
  --gray-100: oklch(from var(--gray-base) calc(l + 0.4) c h);
  --gray-200: oklch(from var(--gray-base) calc(l + 0.3) c h);
  --gray-300: oklch(from var(--gray-base) calc(l + 0.2) c h);
  --gray-400: oklch(from var(--gray-base) calc(l + 0.1) c h);
  --gray-500: var(--gray-base);
  --gray-600: oklch(from var(--gray-base) calc(l - 0.1) c h);
  --gray-700: oklch(from var(--gray-base) calc(l - 0.2) c h);
  --gray-800: oklch(from var(--gray-base) calc(l - 0.3) c h);
  --gray-900: oklch(from var(--gray-base) calc(l - 0.4) c h);

  /* Surfaces */
  --surface: var(--gray-50);
  --surface-alt: var(--gray-100);
  --surface-elevated: white;

  /* Text */
  --text: var(--gray-900);
  --text-muted: var(--gray-600);
  --text-on-primary: white;
}
```

## Dark Mode

### Using Relative Colors

```css
:root {
  color-scheme: light dark;

  /* Light mode defaults */
  --surface: oklch(98% 0.01 250);
  --text: oklch(15% 0.01 250);
}

@media (prefers-color-scheme: dark) {
  :root {
    --surface: oklch(15% 0.02 250);
    --text: oklch(95% 0 0);

    /* Adjust primary for dark bg */
    --primary: oklch(70% 0.18 250);
  }
}
```

### Manual Toggle

```css
[data-theme='dark'] {
  --surface: oklch(15% 0.02 250);
  --text: oklch(95% 0 0);
}
```

### light-dark() Function

```css
:root {
  color-scheme: light dark;

  --surface: light-dark(oklch(98% 0.01 250), oklch(15% 0.02 250));

  --text: light-dark(oklch(15% 0.01 250), oklch(95% 0 0));
}
```

## Contrast & Accessibility

### Minimum Contrast Ratios

- Normal text: 4.5:1 (WCAG AA)
- Large text (18px+ bold, 24px+): 3:1
- UI components: 3:1

### High Contrast Mode

```css
@media (prefers-contrast: more) {
  :root {
    --text: black;
    --surface: white;
    --border: black;
  }
}
```

## Browser Support Fallbacks

```css
.element {
  /* Fallback */
  background: hsl(220 80% 60%);

  /* Modern */
  background: oklch(60% 0.2 250);
}

/* Feature detection */
@supports (color: oklch(0% 0 0)) {
  .element {
    background: oklch(60% 0.2 250);
  }
}
```
