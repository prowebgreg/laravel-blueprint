# CSS Animations Reference

## Performance Rules

### Only Animate These (GPU-Accelerated)

- `transform` (translate, scale, rotate, skew)
- `opacity`
- `filter` (with caution)

### Never Animate These (Cause Layout/Paint)

- `width`, `height`
- `top`, `right`, `bottom`, `left`
- `margin`, `padding`
- `border-width`
- `font-size`

## Keyframe Animations

### Basic Syntax

```css
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.element {
  animation: fade-in 0.3s ease-out forwards;
}
```

### Animation Properties

```css
.element {
  animation-name: slide-in;
  animation-duration: 0.3s;
  animation-timing-function: ease-out;
  animation-delay: 0.1s;
  animation-iteration-count: 1; /* or infinite */
  animation-direction: normal; /* reverse, alternate */
  animation-fill-mode: forwards; /* none, backwards, both */
  animation-play-state: running; /* paused */

  /* Shorthand */
  animation: slide-in 0.3s ease-out 0.1s 1 normal forwards;
}
```

### Common Animations

#### Fade Slide In

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
```

#### Scale In

```css
@keyframes scale-in {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
```

#### Pulse

```css
@keyframes pulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.pulse {
  animation: pulse 2s ease-in-out infinite;
}
```

#### Shake

```css
@keyframes shake {
  0%,
  100% {
    transform: translateX(0);
  }
  20%,
  60% {
    transform: translateX(-5px);
  }
  40%,
  80% {
    transform: translateX(5px);
  }
}
```

#### Spin

```css
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.spinner {
  animation: spin 1s linear infinite;
}
```

#### Skeleton Loading

```css
@keyframes shimmer {
  from {
    background-position: -200% 0;
  }
  to {
    background-position: 200% 0;
  }
}

.skeleton {
  background: linear-gradient(
    90deg,
    var(--skeleton-base) 25%,
    var(--skeleton-shine) 50%,
    var(--skeleton-base) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}
```

## Transitions

### Basic Syntax

```css
.element {
  transition: property duration timing-function delay;
  transition: transform 0.3s ease-out;

  /* Multiple properties */
  transition:
    transform 0.3s ease-out,
    opacity 0.2s ease-in;

  /* All properties (use sparingly) */
  transition: all 0.3s ease-out;
}
```

### Timing Functions

```css
.element {
  /* Built-in */
  transition-timing-function: ease;
  transition-timing-function: ease-in;
  transition-timing-function: ease-out;
  transition-timing-function: ease-in-out;
  transition-timing-function: linear;

  /* Custom cubic-bezier */
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);

  /* Steps */
  transition-timing-function: steps(4, end);
}
```

### Recommended Easings

```css
:root {
  --ease-out: cubic-bezier(0, 0, 0.2, 1);
  --ease-in: cubic-bezier(0.4, 0, 1, 1);
  --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
  --ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
```

### Hover Transitions

```css
.button {
  transform: translateY(0);
  transition: transform 0.2s var(--ease-out);

  &:hover {
    transform: translateY(-2px);
  }

  &:active {
    transform: translateY(0);
    transition-duration: 0.1s;
  }
}
```

## Hardware Acceleration

### Force GPU Layer

```css
.element {
  /* Modern way */
  will-change: transform;

  /* Legacy hack */
  transform: translateZ(0);
}
```

### will-change Best Practices

```css
/* ❌ Don't apply permanently */
.element {
  will-change: transform;
}

/* ✅ Apply on interaction */
.element:hover {
  will-change: transform;
}

/* ✅ Or via JavaScript before animation */
```

## Accessibility

### Respect Reduced Motion

```css
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
```

### Safe Animations (Motion-Safe)

```css
@media (prefers-reduced-motion: no-preference) {
  .element {
    animation: fade-slide-in 0.3s ease-out;
  }
}
```

## Duration Guidelines

| Animation Type     | Duration  |
| ------------------ | --------- |
| Micro-interactions | 100-200ms |
| Buttons, toggles   | 150-300ms |
| Page transitions   | 300-500ms |
| Complex animations | 500ms-1s  |
| Background loops   | 2s+       |

## Scroll-Driven Animations

### View Timeline

```css
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.element {
  animation: fade-in linear both;
  animation-timeline: view();
  animation-range: entry 0% cover 40%;
}
```

### Scroll Timeline

```css
.progress-bar {
  animation: grow-width linear;
  animation-timeline: scroll();
}

@keyframes grow-width {
  from {
    transform: scaleX(0);
  }
  to {
    transform: scaleX(1);
  }
}
```

## Debugging

### Slow Down Animations

```css
/* Temporarily add to debug */
* {
  animation-duration: 3s !important;
  transition-duration: 3s !important;
}
```

### Pause on Hover

```css
.animated:hover {
  animation-play-state: paused;
}
```
