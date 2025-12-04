# Media Elements

Comprehensive reference for HTML5 media elements including images, video, audio, canvas, SVG, and responsive media techniques.

## Overview

Media elements embed visual and audio content into web pages. HTML5 introduced powerful features for responsive images, native video/audio playback, and programmatic graphics.

**Core Principles**:

- **Accessibility First**: Always include alt text for images and captions for video
- **Performance**: Use responsive images to serve optimal file sizes
- **Progressive Enhancement**: Provide fallbacks for older browsers
- **Native Controls**: Prefer HTML5 media over plugins (Flash, etc.)

---

## `<img>` - Image

**Category**: Flow Content, Phrasing Content, Embedded Content  
**Content Model**: Empty (void element)  
**Tag Omission**: No end tag  
**Implicit ARIA Role**: `img` (or `presentation` if alt="")

### Purpose & Semantic Meaning

Embeds an image into the document.

### Attributes

**Required**:

- `src` - Image URL (required, or use `srcset`)
- `alt` - Alternative text (required for accessibility)

**Responsive Images**:

- `srcset` - Multiple image sources for different resolutions/sizes
- `sizes` - Image sizes for different viewport widths

**Dimensions**:

- `width` - Intrinsic width in pixels
- `height` - Intrinsic height in pixels

**Loading**:

- `loading` - Lazy loading behavior (lazy, eager)
- `decoding` - Decode priority (sync, async, auto)
- `fetchpriority` - Resource fetch priority (high, low, auto)

**Security/CORS**:

- `crossorigin` - CORS settings (anonymous, use-credentials)
- `referrerpolicy` - Referrer policy

**Deprecated** (don't use):

- `align`, `border`, `hspace`, `vspace` - Use CSS instead

### Alt Text Guidelines

**Critical for accessibility**: Alt text is read by screen readers

**Rules**:

- **Always include `alt` attribute** (even if empty)
- **Decorative images**: Use `alt=""` (empty string)
- **Informative images**: Describe the content/function
- **Complex images**: Provide detailed description nearby
- **Images of text**: Include the text in alt
- **Don't include**: "image of", "picture of" (redundant)

**Examples**:

```html
<!-- Informative image -->
<img src="dog.jpg" alt="Golden retriever playing fetch in park" />

<!-- Logo (functional) -->
<img src="logo.png" alt="Acme Corporation" />

<!-- Decorative image (no information) -->
<img src="decoration.png" alt="" />

<!-- Image containing text -->
<img src="sale-banner.jpg" alt="50% Off Sale - This Weekend Only" />

<!-- Complex image (chart/diagram) -->
<img src="sales-chart.jpg" alt="Sales data for Q1 2025" />
<p>Detailed description: Sales increased from $1M in January...</p>
```

### Width and Height Attributes

**Best practice**: Always include width and height

**Benefits**:

- Prevents layout shift (CLS)
- Browser reserves space before image loads
- Improves page performance

```html
<img src="photo.jpg" alt="Description" width="800" height="600" />
```

**Note**: CSS can still resize the image; these are intrinsic dimensions

### Loading Attribute

**Purpose**: Control when images load

**Values**:

- `lazy` - Defer loading until near viewport (default for below-the-fold)
- `eager` - Load immediately (default for above-the-fold)

```html
<!-- Lazy load below-the-fold images -->
<img src="image.jpg" alt="Description" loading="lazy" />

<!-- Critical above-fold image -->
<img src="hero.jpg" alt="Hero image" loading="eager" />
```

**Performance tip**: Use `loading="lazy"` for all below-the-fold images

### Basic Example

```html
<img src="photo.jpg" alt="Sunset over mountains" width="1200" height="800" loading="lazy" />
```

---

## Responsive Images with `srcset` and `sizes`

### Problem: One Image, Multiple Devices

Traditional `src` attribute serves same image to all devices:

- Large file downloaded on mobile (slow, expensive)
- Low-resolution image on desktop (blurry)

**Solution**: `srcset` and `sizes` attributes

### Resolution Switching: `srcset` with x-descriptors

**Use case**: Serve different resolutions for different pixel densities

```html
<img
  src="photo.jpg"
  srcset="photo.jpg 1x, photo-2x.jpg 2x, photo-3x.jpg 3x"
  alt="Description"
  width="400"
  height="300"
/>
```

**How it works**:

- `1x` - Standard displays (1 CSS pixel = 1 device pixel)
- `2x` - Retina/high-DPI displays (1 CSS pixel = 2 device pixels)
- `3x` - Ultra high-DPI displays

**Browser chooses** the best image based on screen pixel density

### Resolution Switching: `srcset` with w-descriptors

**Use case**: Serve different sizes based on viewport width

```html
<img
  src="photo-800.jpg"
  srcset="photo-400.jpg 400w, photo-800.jpg 800w, photo-1200.jpg 1200w"
  sizes="100vw"
  alt="Description"
/>
```

**How it works**:

- `400w` - Image is 400 pixels wide
- `800w` - Image is 800 pixels wide
- `1200w` - Image is 1200 pixels wide
- `sizes="100vw"` - Image displays at 100% viewport width

**Browser calculates** which image provides best quality-to-filesize ratio

### The `sizes` Attribute

**Purpose**: Tell browser how much space image will occupy

**Syntax**: Media queries with slot widths

```html
<img
  src="photo.jpg"
  srcset="photo-400.jpg 400w, photo-800.jpg 800w, photo-1200.jpg 1200w"
  sizes="(max-width: 600px) 100vw, 
            (max-width: 1000px) 50vw, 
            800px"
  alt="Description"
/>
```

**How to read**:

1. `(max-width: 600px) 100vw` - On small screens, image is 100% viewport width
2. `(max-width: 1000px) 50vw` - On medium screens, image is 50% viewport width
3. `800px` - On large screens, image is fixed 800px (default, no media query)

**Browser process**:

1. Check viewport width
2. Find first matching media query in `sizes`
3. Use that size to pick best image from `srcset`

### Complete Responsive Example

```html
<img
  src="fallback.jpg"
  srcset="small.jpg 400w, medium.jpg 800w, large.jpg 1200w, xlarge.jpg 1600w"
  sizes="(max-width: 640px) 100vw, 
            (max-width: 1024px) 50vw, 
            800px"
  alt="Responsive landscape photo"
  width="1600"
  height="900"
  loading="lazy"
/>
```

**What happens**:

- Mobile (320px viewport): Browser loads `small.jpg` (400w)
- Tablet (768px viewport): Browser loads `medium.jpg` (800w) for 50vw = 384px
- Desktop (1920px viewport): Browser loads `large.jpg` (1200w) for fixed 800px

---

## `<picture>` - Picture Element

**Category**: Flow Content, Phrasing Content, Embedded Content  
**Content Model**: Zero or more `<source>` elements, followed by one `<img>` element  
**Implicit ARIA Role**: None

### Purpose

Container for multiple image sources, providing art direction and format fallbacks.

### When to Use `<picture>`

Use `<picture>` for:

1. **Art direction** - Different images for different layouts
2. **Format fallback** - Serve modern formats (WebP, AVIF) with JPEG/PNG fallback
3. **Media queries** - Complex responsive image scenarios

**Don't use** for simple resolution switching (use `img` with `srcset` instead)

### Attributes

None on `<picture>` itself; functionality in `<source>` and `<img>` children

### Structure

```html
<picture>
  <source />
  <!-- Optional, multiple allowed -->
  <source />
  <!-- Optional, multiple allowed -->
  <img />
  <!-- Required, must be last -->
</picture>
```

**Required**: One `<img>` element as last child (provides fallback and alt text)

---

## `<source>` (within `<picture>`)

**Purpose**: Specifies alternative image sources

### Attributes

**Required**:

- `srcset` - Image URL(s)

**Optional**:

- `media` - Media query for this source
- `type` - MIME type for format selection
- `sizes` - Image sizes (with srcset w-descriptors)
- `width`, `height` - Intrinsic dimensions

### Art Direction Example

**Use case**: Different crops for different screen sizes

```html
<picture>
  <!-- Landscape for desktop (wide) -->
  <source media="(min-width: 1000px)" srcset="landscape-wide.jpg" />

  <!-- Square crop for tablet -->
  <source media="(min-width: 600px)" srcset="square-crop.jpg" />

  <!-- Portrait for mobile (narrow) -->
  <img src="portrait-narrow.jpg" alt="Product photo" />
</picture>
```

### Format Fallback Example

**Use case**: Serve modern formats with fallbacks

```html
<picture>
  <!-- Try AVIF first (best compression) -->
  <source srcset="photo.avif" type="image/avif" />

  <!-- Try WebP second (good compression, better support) -->
  <source srcset="photo.webp" type="image/webp" />

  <!-- Fallback to JPEG (universal support) -->
  <img src="photo.jpg" alt="Photo description" />
</picture>
```

**Browser behavior**: Uses first format it supports

### Combined: Art Direction + Format Fallback

```html
<picture>
  <!-- Desktop: Wide crop, modern formats -->
  <source media="(min-width: 1000px)" srcset="wide.avif" type="image/avif" />
  <source media="(min-width: 1000px)" srcset="wide.webp" type="image/webp" />
  <source media="(min-width: 1000px)" srcset="wide.jpg" />

  <!-- Mobile: Square crop, modern formats -->
  <source srcset="square.avif" type="image/avif" />
  <source srcset="square.webp" type="image/webp" />

  <!-- Final fallback -->
  <img src="square.jpg" alt="Product showcase" width="600" height="600" />
</picture>
```

### Complete Responsive Picture Example

```html
<picture>
  <!-- Large screens: wide landscape, high-res -->
  <source
    media="(min-width: 1200px)"
    srcset="hero-wide-1600.webp 1600w, hero-wide-2400.webp 2400w"
    sizes="100vw"
    type="image/webp"
  />

  <!-- Medium screens: standard crop -->
  <source
    media="(min-width: 768px)"
    srcset="hero-medium-1200.webp 1200w, hero-medium-1600.webp 1600w"
    sizes="100vw"
    type="image/webp"
  />

  <!-- Small screens: portrait crop for mobile -->
  <source
    srcset="hero-mobile-600.webp 600w, hero-mobile-900.webp 900w"
    sizes="100vw"
    type="image/webp"
  />

  <!-- Fallback for all sizes (JPEG) -->
  <img
    src="hero-medium-1200.jpg"
    alt="Hero image showing our product in use"
    width="1200"
    height="675"
    loading="eager"
  />
</picture>
```

---

## `<video>` - Video Player

**Category**: Flow Content, Phrasing Content, Embedded Content  
**Content Model**: `<source>` and `<track>` elements, followed by fallback content  
**Implicit ARIA Role**: None

### Purpose

Embeds a video player with native controls.

### Attributes

**Source**:

- `src` - Video file URL (or use `<source>` children)

**Playback**:

- `controls` - Show playback controls (Boolean)
- `autoplay` - Auto-start playback (Boolean, use carefully)
- `loop` - Loop playback (Boolean)
- `muted` - Mute audio (Boolean)
- `playsinline` - Play inline on mobile (Boolean)

**Preloading**:

- `preload` - Hint for preloading (none, metadata, auto)

**Display**:

- `poster` - Placeholder image before playback
- `width`, `height` - Display dimensions

**Advanced**:

- `crossorigin` - CORS settings
- `controlslist` - Customize controls (nodownload, nofullscreen, noremoteplayback)
- `disablepictureinpicture` - Disable PiP (Boolean)

### Basic Video Example

```html
<video src="movie.mp4" controls width="640" height="360" poster="thumbnail.jpg">
  Your browser doesn't support HTML5 video.
</video>
```

### Multiple Source Formats

**Best practice**: Provide multiple formats for browser compatibility

```html
<video controls width="640" height="360" poster="poster.jpg">
  <!-- Browser tries formats in order -->
  <source src="movie.webm" type="video/webm" />
  <source src="movie.mp4" type="video/mp4" />

  <!-- Fallback text for unsupported browsers -->
  <p>
    Your browser doesn't support HTML5 video. <a href="movie.mp4">Download the video</a> instead.
  </p>
</video>
```

### Video Formats

| Format       | Extension | MIME Type  | Browser Support |
| ------------ | --------- | ---------- | --------------- |
| MP4 (H.264)  | .mp4      | video/mp4  | ✅ Universal    |
| WebM (VP9)   | .webm     | video/webm | ✅ Most modern  |
| Ogg (Theora) | .ogv      | video/ogg  | ⚠️ Limited      |

**Recommendation**: Provide both MP4 and WebM for best compatibility

### Autoplay Considerations

**Autoplay restrictions**: Browsers block autoplay with sound

**Allowed autoplay scenarios**:

- `muted` video
- User has interacted with site
- User has media preference enabled

```html
<!-- ✅ Allowed: Muted autoplay -->
<video autoplay muted loop playsinline>
  <source src="background.mp4" type="video/mp4" />
</video>

<!-- ❌ Blocked: Autoplay with sound -->
<video autoplay controls>
  <source src="video.mp4" type="video/mp4" />
</video>
```

### Accessibility

**Best practices**:

- Provide captions with `<track>` (see below)
- Include transcripts for deaf/hard-of-hearing users
- Use `poster` attribute for visual preview
- Provide fallback content

### Complete Video Example

```html
<video controls width="854" height="480" poster="poster.jpg" preload="metadata">
  <source src="video.webm" type="video/webm" />
  <source src="video.mp4" type="video/mp4" />

  <!-- Captions -->
  <track kind="captions" src="captions-en.vtt" srclang="en" label="English" default />
  <track kind="captions" src="captions-es.vtt" srclang="es" label="Español" />

  <!-- Fallback -->
  <p>Your browser doesn't support HTML5 video. <a href="video.mp4">Download MP4</a></p>
</video>
```

---

## `<audio>` - Audio Player

**Category**: Flow Content, Phrasing Content, Embedded Content  
**Content Model**: `<source>` and `<track>` elements, followed by fallback content  
**Implicit ARIA Role**: None

### Purpose

Embeds an audio player with native controls.

### Attributes

Same as `<video>` except:

- No `poster`, `width`, `height`, `playsinline`

**Key attributes**:

- `src` - Audio file URL
- `controls` - Show controls (Boolean)
- `autoplay` - Auto-start (Boolean, restricted)
- `loop` - Loop playback (Boolean)
- `muted` - Mute (Boolean)
- `preload` - Preload hint (none, metadata, auto)

### Basic Audio Example

```html
<audio src="song.mp3" controls>Your browser doesn't support HTML5 audio.</audio>
```

### Multiple Audio Formats

```html
<audio controls>
  <source src="audio.opus" type="audio/opus" />
  <source src="audio.ogg" type="audio/ogg" />
  <source src="audio.mp3" type="audio/mpeg" />

  <p>Your browser doesn't support HTML5 audio. <a href="audio.mp3">Download MP3</a></p>
</audio>
```

### Audio Formats

| Format     | Extension | MIME Type  | Browser Support           |
| ---------- | --------- | ---------- | ------------------------- |
| MP3        | .mp3      | audio/mpeg | ✅ Universal              |
| Opus       | .opus     | audio/opus | ✅ Modern browsers        |
| Ogg Vorbis | .ogg      | audio/ogg  | ✅ Most modern            |
| AAC        | .aac      | audio/aac  | ✅ Good support           |
| WAV        | .wav      | audio/wav  | ✅ Universal, large files |

**Recommendation**: MP3 for universal support, Opus for best quality/compression

### Podcast Example

```html
<audio controls preload="none">
  <source src="episode-42.mp3" type="audio/mpeg" />
  <p>Download <a href="episode-42.mp3">Episode 42 (MP3)</a></p>
</audio>

<div>
  <h3>Episode 42: HTML5 Media Elements</h3>
  <p><a href="transcript.html">Read transcript</a></p>
</div>
```

---

## `<track>` - Text Tracks (Captions/Subtitles)

**Purpose**: Provides timed text tracks for `<video>` and `<audio>` elements

**Used for**:

- Subtitles
- Captions (for deaf/hard-of-hearing)
- Descriptions (for blind/low-vision)
- Chapters
- Metadata

### Attributes

**Required**:

- `src` - WebVTT file URL
- `kind` - Track type

**Optional**:

- `srclang` - Language code (required for subtitles)
- `label` - User-visible title
- `default` - Default track (Boolean)

### Kind Attribute Values

| Value          | Purpose                  | When Displayed              |
| -------------- | ------------------------ | --------------------------- |
| `subtitles`    | Translations of dialogue | Always visible              |
| `captions`     | Dialogue + sound effects | For deaf/hard-of-hearing    |
| `descriptions` | Visual descriptions      | Screen reader announcements |
| `chapters`     | Chapter markers          | Navigation menu             |
| `metadata`     | Metadata (not displayed) | For scripts                 |

### Examples

#### Subtitles (Multiple Languages)

```html
<video controls>
  <source src="movie.mp4" type="video/mp4" />

  <track kind="subtitles" src="subtitles-en.vtt" srclang="en" label="English" default />
  <track kind="subtitles" src="subtitles-es.vtt" srclang="es" label="Español" />
  <track kind="subtitles" src="subtitles-fr.vtt" srclang="fr" label="Français" />
</video>
```

#### Captions (Accessibility)

```html
<video controls>
  <source src="interview.mp4" type="video/mp4" />

  <track kind="captions" src="captions.vtt" srclang="en" label="English Captions" default />
</video>
```

### WebVTT File Format

**Example** (`captions.vtt`):

```
WEBVTT

00:00:00.000 --> 00:00:02.500
Welcome to our video tutorial.

00:00:03.000 --> 00:00:06.000
Today we'll learn about HTML5.

00:00:06.500 --> 00:00:10.000
Let's start with the basics.
```

---

## `<canvas>` - Graphics Canvas

**Category**: Flow Content, Phrasing Content, Embedded Content  
**Content Model**: Transparent (fallback content)  
**Implicit ARIA Role**: None

### Purpose

Provides a bitmap drawing surface for JavaScript-driven graphics, animations, and visualizations.

### Attributes

**Dimensions**:

- `width` - Canvas width in pixels (default: 300)
- `height` - Canvas height in pixels (default: 150)

**Accessibility**:

- Provide fallback content between tags

### Basic Structure

```html
<canvas id="myCanvas" width="800" height="600">
  Your browser doesn't support HTML5 canvas.
  <p>Canvas shows a chart of sales data for Q1 2025.</p>
</canvas>
```

### Drawing with JavaScript

```html
<canvas id="example" width="400" height="200"></canvas>

<script>
  const canvas = document.getElementById('example');
  const ctx = canvas.getContext('2d');

  // Draw rectangle
  ctx.fillStyle = 'blue';
  ctx.fillRect(50, 50, 100, 75);

  // Draw circle
  ctx.beginPath();
  ctx.arc(250, 100, 50, 0, Math.PI * 2);
  ctx.fillStyle = 'red';
  ctx.fill();

  // Draw text
  ctx.font = '20px Arial';
  ctx.fillStyle = 'black';
  ctx.fillText('Hello Canvas', 50, 180);
</script>
```

### Common Use Cases

- Charts and graphs
- Data visualizations
- Game graphics
- Image editing
- Generative art
- Animations
- Signatures

### Canvas vs SVG

| Feature       | Canvas                    | SVG                     |
| ------------- | ------------------------- | ----------------------- |
| Type          | Bitmap (raster)           | Vector                  |
| Rendering     | Pixel-based               | Shape-based             |
| Performance   | Better for many objects   | Better for few objects  |
| Scalability   | Pixelated when scaled     | Crisp at any size       |
| Interactivity | Manual hit detection      | Built-in events         |
| Best for      | Games, complex animations | Charts, icons, diagrams |

### Accessibility Considerations

**Canvas is not accessible by default**

**Best practices**:

- Provide text alternative in canvas content
- Use ARIA labels: `aria-label` or `aria-labelledby`
- Consider providing data tables for charts
- Announce dynamic changes with ARIA live regions

```html
<canvas
  id="chart"
  width="600"
  height="400"
  role="img"
  aria-label="Bar chart showing quarterly sales data"
>
  <p>Quarterly sales: Q1: $100K, Q2: $150K, Q3: $200K, Q4: $180K</p>
</canvas>
```

---

## `<svg>` - Scalable Vector Graphics

**Category**: Flow Content, Phrasing Content, Embedded Content  
**Content Model**: SVG elements  
**Implicit ARIA Role**: `graphics-document` or `img`

### Purpose

Embeds vector graphics that scale without quality loss.

### Inline SVG Example

```html
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
  <!-- Rectangle -->
  <rect x="10" y="10" width="180" height="180" fill="blue" stroke="black" stroke-width="2" />

  <!-- Circle -->
  <circle cx="100" cy="100" r="50" fill="red" />

  <!-- Text -->
  <text x="100" y="105" font-size="20" text-anchor="middle" fill="white">Hello SVG</text>
</svg>
```

### External SVG File

```html
<!-- Link to SVG file -->
<img src="logo.svg" alt="Company logo" width="200" height="100" />

<!-- Embed SVG file -->
<object data="diagram.svg" type="image/svg+xml" width="600" height="400">
  <img src="diagram-fallback.png" alt="System diagram" />
</object>
```

### SVG for Icons

```html
<svg
  width="24"
  height="24"
  viewBox="0 0 24 24"
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  aria-hidden="true"
>
  <path
    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
  />
</svg>
```

### Accessibility

**For informative SVG**:

```html
<svg role="img" aria-labelledby="title-id">
  <title id="title-id">Chart showing sales trends</title>
  <desc>Sales increased from $10K to $50K over 12 months</desc>
  <!-- SVG content -->
</svg>
```

**For decorative SVG**:

```html
<svg aria-hidden="true">
  <!-- Decorative icon -->
</svg>
```

### Benefits of SVG

✅ Scalable without quality loss  
✅ Small file size for simple graphics  
✅ Styleable with CSS  
✅ Animatable with CSS/JavaScript  
✅ Accessible (when properly marked up)  
✅ Search engine indexable

---

## Responsive Images Best Practices

### 1. Always Include Width and Height

```html
<img src="photo.jpg" alt="Description" width="1200" height="800" />
```

**Prevents layout shift** and improves Core Web Vitals

### 2. Use Lazy Loading

```html
<img src="image.jpg" alt="Description" loading="lazy" />
```

**Above-the-fold images**: Use `loading="eager"` or omit (default)  
**Below-the-fold images**: Use `loading="lazy"`

### 3. Provide Multiple Formats

```html
<picture>
  <source srcset="image.avif" type="image/avif" />
  <source srcset="image.webp" type="image/webp" />
  <img src="image.jpg" alt="Description" />
</picture>
```

**Format recommendation**:

1. AVIF (best compression, growing support)
2. WebP (good compression, wide support)
3. JPEG/PNG (fallback)

### 4. Optimize Images

- **Compress images** - Use tools like ImageOptim, Squoosh
- **Choose right format** - JPEG for photos, PNG for graphics, SVG for logos
- **Size appropriately** - Don't serve 4K image for 400px display
- **Use CDN** - Image CDN with automatic optimization

### 5. Responsive Image Sizes

```html
<img
  src="photo.jpg"
  srcset="photo-400.jpg 400w, photo-800.jpg 800w, photo-1200.jpg 1200w"
  sizes="(max-width: 600px) 100vw, 
            (max-width: 1000px) 50vw, 
            800px"
  alt="Description"
  width="1200"
  height="800"
  loading="lazy"
/>
```

### 6. Preload Critical Images

```html
<!-- In <head> -->
<link
  rel="preload"
  as="image"
  href="hero-image.jpg"
  imagesrcset="hero-400.jpg 400w, 
                   hero-800.jpg 800w, 
                   hero-1200.jpg 1200w"
  imagesizes="100vw"
/>
```

---

## Accessibility Checklist

### Images

✅ **Always include alt attribute**  
✅ **Decorative images**: Use `alt=""`  
✅ **Informative images**: Describe content  
✅ **Functional images** (buttons, links): Describe function  
✅ **Complex images**: Provide long description nearby  
✅ **Include width/height** to prevent layout shift

### Video

✅ **Provide captions** with `<track kind="captions">`  
✅ **Provide transcripts** for deaf users  
✅ **Provide audio descriptions** with `<track kind="descriptions">`  
✅ **Include controls** attribute  
✅ **Don't autoplay with sound** (violates WCAG)

### Audio

✅ **Provide transcripts**  
✅ **Include controls** attribute  
✅ **Label audio content** clearly

### Canvas

✅ **Provide text alternative** in canvas content  
✅ **Use ARIA labels**: `aria-label` or `role="img"`  
✅ **Provide data table** alternative for charts

### SVG

✅ **For informative SVG**: Use `role="img"` and `<title>`  
✅ **For decorative SVG**: Use `aria-hidden="true"`  
✅ **Provide text alternatives**

---

## SEO Considerations

### Image SEO

1. **Descriptive file names** - `blue-widget-product.jpg` not `IMG_1234.jpg`
2. **Alt text with keywords** - Natural, descriptive, includes relevant keywords
3. **Image dimensions** - Include width/height
4. **Optimize file size** - Faster loading = better SEO
5. **Use modern formats** - WebP, AVIF for performance
6. **Structured data** - Use schema.org ImageObject
7. **Image sitemaps** - Help search engines discover images

### Video SEO

1. **Video sitemap** - Submit to Google Search Console
2. **Structured data** - Use VideoObject schema
3. **Descriptive title** - In HTML and video metadata
4. **Transcript** - Full text transcript on page
5. **Thumbnail** - High-quality poster image
6. **Host on your domain** - Or use YouTube/Vimeo embed

---

## Performance Optimization

### Image Performance

**Critical optimizations**:

1. **Lazy loading** - `loading="lazy"` for below-fold images
2. **Responsive images** - `srcset` and `sizes`
3. **Modern formats** - WebP, AVIF
4. **Compression** - Optimize before upload
5. **CDN delivery** - Use image CDN (Cloudinary, Imgix)
6. **Dimensions specified** - Prevent layout shift

### Video Performance

**Best practices**:

1. **Compress videos** - Use H.264/H.265 or VP9
2. **Use preload="none"** - Don't preload unless critical
3. **Poster images** - Show preview without loading video
4. **Consider YouTube/Vimeo** - Offload hosting and bandwidth
5. **Adaptive streaming** - For long videos (HLS, DASH)

---

## Common Patterns

### Hero Image (Responsive)

```html
<picture>
  <source media="(min-width: 1200px)" srcset="hero-large.webp" type="image/webp" />
  <source media="(min-width: 768px)" srcset="hero-medium.webp" type="image/webp" />
  <source srcset="hero-small.webp" type="image/webp" />
  <img
    src="hero-medium.jpg"
    alt="Our product in action"
    width="1200"
    height="675"
    loading="eager"
  />
</picture>
```

### Background Video

```html
<video
  autoplay
  muted
  loop
  playsinline
  poster="poster.jpg"
  aria-label="Background video showing our workspace"
>
  <source src="bg-video.webm" type="video/webm" />
  <source src="bg-video.mp4" type="video/mp4" />
</video>
```

### Image Gallery

```html
<div class="gallery">
  <img src="thumb1.jpg" alt="Gallery image 1" width="400" height="300" loading="lazy" />
  <img src="thumb2.jpg" alt="Gallery image 2" width="400" height="300" loading="lazy" />
  <!-- More images -->
</div>
```

---

## Browser Support

### Image Features

| Feature          | Chrome | Firefox | Safari | Edge |
| ---------------- | ------ | ------- | ------ | ---- |
| `<img>` basic    | ✅     | ✅      | ✅     | ✅   |
| `srcset`/`sizes` | ✅     | ✅      | ✅     | ✅   |
| `<picture>`      | ✅     | ✅      | ✅     | ✅   |
| `loading="lazy"` | ✅     | ✅      | ✅     | ✅   |
| WebP             | ✅     | ✅      | ✅     | ✅   |
| AVIF             | ✅     | ✅      | ✅     | ✅   |

### Video/Audio

| Feature   | Chrome | Firefox | Safari | Edge |
| --------- | ------ | ------- | ------ | ---- |
| `<video>` | ✅     | ✅      | ✅     | ✅   |
| `<audio>` | ✅     | ✅      | ✅     | ✅   |
| `<track>` | ✅     | ✅      | ✅     | ✅   |
| MP4/H.264 | ✅     | ✅      | ✅     | ✅   |
| WebM/VP9  | ✅     | ✅      | ⚠️     | ✅   |

### Canvas/SVG

| Feature        | Chrome | Firefox | Safari | Edge |
| -------------- | ------ | ------- | ------ | ---- |
| `<canvas>`     | ✅     | ✅      | ✅     | ✅   |
| Inline `<svg>` | ✅     | ✅      | ✅     | ✅   |

---

## Further Reading

- **WHATWG HTML Media**: [https://html.spec.whatwg.org/multipage/media.html](https://html.spec.whatwg.org/multipage/media.html)
- **WHATWG Embedded Content**: [https://html.spec.whatwg.org/multipage/embedded-content.html](https://html.spec.whatwg.org/multipage/embedded-content.html)
- **MDN Responsive Images**: [https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images](https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images)
- **Web.dev Images**: [https://web.dev/learn/images/](https://web.dev/learn/images/)
- **WebVTT Specification**: [https://www.w3.org/TR/webvtt1/](https://www.w3.org/TR/webvtt1/)

---

**Last Updated**: Based on WHATWG HTML Living Standard (2025)
