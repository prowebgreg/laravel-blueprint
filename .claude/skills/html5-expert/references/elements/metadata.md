# Metadata Elements

Comprehensive reference for HTML document metadata elements that define page information, SEO properties, and external resource relationships.

## Overview

Metadata elements provide information **about** the HTML document, not content visible in the page body. These elements are placed in the `<head>` section and are critical for:

- **SEO**: Search engine optimization and ranking
- **Social sharing**: Open Graph, Twitter Cards
- **Browser behavior**: Character encoding, viewport, compatibility
- **Resource loading**: Stylesheets, icons, preloading
- **Structured data**: JSON-LD, microdata

**Core Principle**: Proper metadata is essential for discoverability, sharing, and performance.

---

## `<head>`

**Category**: None (document structure)  
**Content Model**: Metadata content (meta, link, title, style, script, base, noscript, template)  
**Permitted Parents**: `<html>` (must be first child after optional doctype)  
**Tag Omission**: Start tag omissible if first thing inside is an element. End tag omissible if not followed by space/comment  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Contains **machine-readable metadata** about the document. Everything in `<head>` is not displayed in the page content but affects how the page is processed, indexed, and displayed.

**Key characteristic**: Non-visible information about the page.

### When to Use

Use `<head>` for:

- Every HTML document (required)
- Containing all metadata elements
- SEO tags and meta information
- Links to external resources
- Character encoding declaration

### When NOT to Use

❌ Don't use for:

- Visible page content (use `<body>`)
- Multiple head elements (only one per document)

### Attributes

**Element-Specific**: None (historical `profile` attribute obsolete)

**Global Attributes**: All [global attributes](../global-attributes.md) apply (though rarely used on `<head>`)

### Content Model Rules

**Can contain**:

- Zero or one `<title>` element (required unless part of larger protocol)
- Zero or one `<base>` element (if present, must come before any URL-referencing elements)
- Zero or more `<link>` elements
- Zero or more `<meta>` elements
- Zero or more `<style>` elements
- Zero or more `<script>` elements
- Zero or more `<noscript>` elements
- Zero or more `<template>` elements

**Required children**:

- `<title>` element (unless document is part of iframe or email)

**Cannot contain**:

- Body content elements
- Visible content

### Examples

#### Good Example: Complete Head Structure

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Character encoding (must be first) -->
    <meta charset="UTF-8" />

    <!-- Viewport for responsive design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Page title (required) -->
    <title>Page Title - Site Name</title>

    <!-- SEO meta tags -->
    <meta name="description" content="Compelling page description for search results" />
    <meta name="keywords" content="html, metadata, seo" />
    <meta name="author" content="Author Name" />

    <!-- Open Graph for social sharing -->
    <meta property="og:title" content="Page Title" />
    <meta property="og:description" content="Description for social media" />
    <meta property="og:image" content="https://example.com/image.jpg" />
    <meta property="og:url" content="https://example.com/page" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Page Title" />
    <meta name="twitter:description" content="Description for Twitter" />
    <meta name="twitter:image" content="https://example.com/image.jpg" />

    <!-- Canonical URL -->
    <link rel="canonical" href="https://example.com/page" />

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/styles/main.css" />

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />

    <!-- Structured data -->
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Page Title",
        "description": "Page description"
      }
    </script>
  </head>
  <body>
    <!-- Page content -->
  </body>
</html>
```

### Accessibility

**No direct accessibility impact**: Content in `<head>` is not read by screen readers

**Indirect impact**:

- `<title>` is announced when page loads
- `lang` attribute on `<html>` affects pronunciation
- Proper metadata improves page identification

### SEO Impact

**Critical SEO value**: The `<head>` section contains most SEO-critical elements

**Essential elements**:

- `<title>` - Most important SEO element
- `<meta name="description">` - Search result snippet
- `<link rel="canonical">` - Duplicate content prevention
- Structured data - Rich results in search
- `<meta name="robots">` - Crawling instructions

### Browser Support

✅ Universal support in all browsers  
✅ Optional tag omission supported but not recommended

---

## `<title>`

**Category**: Metadata Content  
**Content Model**: Text (no other elements allowed)  
**Permitted Parents**: `<head>` (one per document)  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Defines the **document title** shown in browser tabs, search results, and bookmarks. The most important single element for SEO and usability.

**Key characteristic**: Identifies the page in all contexts outside the page itself.

### When to Use

Use `<title>` for:

- Every HTML page (required)
- Browser tab titles
- Search engine results
- Social media sharing
- Browser bookmarks
- Browser history

### When NOT to Use

❌ Don't use for:

- On-page headings (use `<h1>`)
- Multiple titles (only one per document)
- Long descriptions (use meta description)

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply (though rarely used)

### Content Model Rules

**Can contain**:

- Text only (no HTML elements)
- Character entities (e.g., `&amp;`, `&copy;`)

**Cannot contain**:

- HTML tags
- Line breaks

**Length recommendations**:

- **Optimal**: 50-60 characters
- **Maximum**: 70 characters (Google truncates around 60-70)
- **Minimum**: 30 characters for meaningful titles

### Examples

#### Good Example: Homepage Title

```html
<title>Company Name - Leading Provider of Solutions</title>
```

#### Good Example: Product Page Title

```html
<title>Product Name | Category | Brand Name</title>
```

#### Good Example: Blog Post Title

```html
<title>How to Write Better HTML - Web Development Blog</title>
```

#### Good Example: Dynamic Title with Template

```html
<!-- For blog posts -->
<title>{{ post.title }} - {{ site.name }}</title>

<!-- Renders as: -->
<title>Understanding HTML Metadata - Web Dev Blog</title>
```

#### Bad Example: Too Long

```html
<!-- ❌ WRONG: Over 100 characters, will be truncated -->
<title>
  Best Affordable High-Quality Professional Web Design and Development Services for Small Businesses
  in the United States and Canada
</title>

<!-- ✅ CORRECT: Concise and descriptive -->
<title>Professional Web Design Services for Small Businesses</title>
```

#### Bad Example: Generic Title

```html
<!-- ❌ WRONG: Not descriptive -->
<title>Home</title>
<title>Page</title>
<title>Untitled</title>

<!-- ✅ CORRECT: Descriptive and specific -->
<title>Acme Corp - Home Automation Solutions</title>
```

#### Bad Example: Keyword Stuffing

```html
<!-- ❌ WRONG: Keyword stuffing -->
<title>Shoes, Running Shoes, Athletic Shoes, Sports Shoes, Buy Shoes, Cheap Shoes</title>

<!-- ✅ CORRECT: Natural keyword usage -->
<title>Running Shoes - Athletic Footwear | SportsGear</title>
```

### Accessibility

**High accessibility value**: Screen readers announce title when page loads

**Best practices**:

- Make title descriptive and unique
- Start with most important information (front-loading)
- Indicate current page location on multi-page sites
- Update title dynamically for SPA (Single Page Apps)

**Dynamic title update example**:

```javascript
// Update title when content changes (SPA)
document.title = `${newPageTitle} - ${siteName}`;
```

### SEO Impact

**CRITICAL SEO value**: Single most important on-page SEO element

**Benefits**:

- Primary factor in search result click-through rate
- Appears as blue link in Google search results
- Used in social media shares
- Influences search rankings (especially keywords in first 50 chars)

**SEO Best Practices**:

1. **Include target keyword** (preferably at beginning)
2. **Make it unique** for every page
3. **Include brand name** (usually at end)
4. **Write for humans first**, search engines second
5. **Match content** - title should reflect page content
6. **Use separators** - Pipe `|`, dash `-`, or colon `:` for structure

**Title Formula Patterns**:

```html
<!-- Primary Keyword | Secondary Keyword | Brand -->
<title>Learn HTML Metadata | SEO Guide | WebDev Academy</title>

<!-- Page Name - Category - Brand -->
<title>iPhone 15 Pro - Smartphones - TechStore</title>

<!-- Question - Answer/Topic - Brand -->
<title>How to Optimize Images? Complete Guide - DevBlog</title>

<!-- Location-based Service -->
<title>Plumber in Chicago, IL | 24/7 Emergency Service | QuickFix</title>
```

### Browser Support

✅ Universal support in all browsers  
✅ Required element, present in all valid HTML documents

---

## `<meta>`

**Category**: Metadata Content  
**Content Model**: Empty (void element)  
**Permitted Parents**: `<head>`, `<noscript>` (in `<head>`)  
**Tag Omission**: No end tag (void element)  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Provides **metadata** about the HTML document that cannot be represented by other meta elements (title, base, link, style, script).

**Key characteristic**: Defines various types of metadata using name-value pairs.

### When to Use

Use `<meta>` for:

- Character encoding declaration
- Viewport configuration
- Search engine directives
- Page descriptions
- Author information
- Social media metadata (Open Graph, Twitter Cards)
- HTTP headers simulation

### When NOT to Use

❌ Don't use for:

- Links to resources (use `<link>`)
- Page title (use `<title>`)
- Excessive keywords (obsolete for SEO)

### Attributes

**Element-Specific**:

- `charset` - Character encoding (e.g., `UTF-8`)
- `name` - Metadata name (pairs with `content`)
- `content` - Metadata value (pairs with `name` or `http-equiv`)
- `http-equiv` - HTTP header simulation (pairs with `content`)
- `property` - Used for Open Graph protocol (pairs with `content`)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Meta Types

#### 1. Charset (Character Encoding)

**Must be first meta tag**, within first 1024 bytes:

```html
<meta charset="UTF-8" />
```

#### 2. Viewport (Responsive Design)

Essential for mobile responsiveness:

```html
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
```

Viewport options:

- `width=device-width` - Match device width
- `initial-scale=1.0` - Initial zoom level
- `maximum-scale=5.0` - Max zoom (don't disable!)
- `user-scalable=yes` - Allow zoom (accessibility)

#### 3. SEO Meta Tags

**Description** (critical for SEO):

```html
<meta
  name="description"
  content="Compelling 150-160 character description that appears in search results"
/>
```

**Keywords** (obsolete, ignored by major search engines):

```html
<!-- Not recommended - no longer used by Google -->
<meta name="keywords" content="html, meta, tags" />
```

**Author**:

```html
<meta name="author" content="John Doe" />
```

**Robots** (crawling instructions):

```html
<!-- Default: index, follow -->
<meta name="robots" content="index, follow" />

<!-- Don't index this page -->
<meta name="robots" content="noindex, follow" />

<!-- Don't follow links on this page -->
<meta name="robots" content="index, nofollow" />

<!-- Don't index or follow -->
<meta name="robots" content="noindex, nofollow" />

<!-- Don't show cached version -->
<meta name="robots" content="noarchive" />

<!-- Don't show snippet in search results -->
<meta name="robots" content="nosnippet" />
```

**Google-specific**:

```html
<!-- Don't translate this page -->
<meta name="google" content="notranslate" />

<!-- Don't show sitelinks search box -->
<meta name="google" content="nositelinkssearchbox" />
```

#### 4. Open Graph (Social Media)

Facebook, LinkedIn, and other platforms:

```html
<!-- Required -->
<meta property="og:title" content="Page Title" />
<meta property="og:type" content="website" />
<meta property="og:url" content="https://example.com/page" />
<meta property="og:image" content="https://example.com/image.jpg" />

<!-- Recommended -->
<meta property="og:description" content="Page description for social sharing" />
<meta property="og:site_name" content="Site Name" />
<meta property="og:locale" content="en_US" />

<!-- Image specifications -->
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="Image description" />

<!-- Article-specific -->
<meta property="og:type" content="article" />
<meta property="article:author" content="Author Name" />
<meta property="article:published_time" content="2025-11-04T12:00:00Z" />
<meta property="article:modified_time" content="2025-11-04T15:30:00Z" />
<meta property="article:section" content="Technology" />
<meta property="article:tag" content="HTML" />
```

**Image size recommendations**:

- **Minimum**: 200 × 200 pixels
- **Optimal**: 1200 × 630 pixels (1.91:1 ratio)
- **Maximum**: 8MB file size

#### 5. Twitter Cards

```html
<!-- Card type -->
<meta name="twitter:card" content="summary_large_image" />

<!-- Content -->
<meta name="twitter:title" content="Page Title" />
<meta name="twitter:description" content="Description for Twitter" />
<meta name="twitter:image" content="https://example.com/image.jpg" />
<meta name="twitter:image:alt" content="Image description" />

<!-- Creator/site -->
<meta name="twitter:site" content="@website_account" />
<meta name="twitter:creator" content="@author_account" />
```

**Card types**:

- `summary` - Small image (144×144)
- `summary_large_image` - Large image (1200×628)
- `app` - Mobile app
- `player` - Video/audio player

#### 6. HTTP-Equiv Headers

```html
<!-- Content-Type (use charset instead) -->
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<!-- Refresh/redirect -->
<meta http-equiv="refresh" content="30" />
<meta http-equiv="refresh" content="0; url=https://example.com" />

<!-- Cache control -->
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />

<!-- X-UA-Compatible (IE compatibility) -->
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<!-- Content Security Policy -->
<meta http-equiv="Content-Security-Policy" content="default-src 'self'" />
```

### Examples

#### Good Example: Complete Meta Tags for Blog Post

```html
<head>
  <!-- Essential -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>How to Master HTML Metadata - Web Development Blog</title>

  <!-- SEO -->
  <meta
    name="description"
    content="Learn how to optimize HTML metadata for better SEO, social sharing, and user experience. Complete guide with examples."
  />
  <meta name="author" content="Jane Developer" />
  <meta name="robots" content="index, follow" />

  <!-- Open Graph -->
  <meta property="og:title" content="How to Master HTML Metadata" />
  <meta property="og:description" content="Complete guide to HTML metadata optimization" />
  <meta property="og:type" content="article" />
  <meta property="og:url" content="https://webdevblog.com/html-metadata-guide" />
  <meta property="og:image" content="https://webdevblog.com/images/metadata-guide.jpg" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:site_name" content="Web Development Blog" />
  <meta property="article:published_time" content="2025-11-04T10:00:00Z" />
  <meta property="article:author" content="Jane Developer" />
  <meta property="article:section" content="HTML" />
  <meta property="article:tag" content="HTML" />
  <meta property="article:tag" content="SEO" />
  <meta property="article:tag" content="Metadata" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="How to Master HTML Metadata" />
  <meta name="twitter:description" content="Complete guide to HTML metadata optimization" />
  <meta name="twitter:image" content="https://webdevblog.com/images/metadata-guide.jpg" />
  <meta name="twitter:creator" content="@janedev" />

  <!-- Canonical -->
  <link rel="canonical" href="https://webdevblog.com/html-metadata-guide" />
</head>
```

#### Bad Example: Missing Critical Tags

```html
<!-- ❌ WRONG: Missing essential meta tags -->
<head>
  <title>My Page</title>
</head>

<!-- ✅ CORRECT: Include charset, viewport, and description at minimum -->
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Clear page description" />
  <title>Descriptive Page Title - Site Name</title>
</head>
```

### Accessibility

**Indirect accessibility impact**: Viewport meta affects zoom capability

**Best practices**:

- **Never disable zoom**: Don't use `user-scalable=no` or `maximum-scale=1.0`
- Allow users to zoom for readability
- Use `viewport` for responsive design, not zoom control

```html
<!-- ✅ CORRECT: Allows zoom -->
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<!-- ❌ WRONG: Disables zoom (accessibility violation) -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
```

### SEO Impact

**CRITICAL SEO value**: Meta description and other tags significantly affect SEO

**Most important**:

1. **description** - Appears in search results, affects CTR
2. **robots** - Controls indexing
3. **og:image** - Social sharing appearance

**Description best practices**:

- **Length**: 150-160 characters
- **Include keywords** naturally
- **Compelling copy** - write for humans
- **Call to action** when appropriate
- **Unique** for every page

**Examples**:

```html
<!-- Good: Compelling, includes keywords, call to action -->
<meta
  name="description"
  content="Master HTML5 with our comprehensive tutorial. Learn semantic elements, forms, and SEO best practices. Start building better websites today!"
/>

<!-- Bad: Too short -->
<meta name="description" content="HTML tutorial" />

<!-- Bad: Keyword stuffing -->
<meta
  name="description"
  content="HTML tutorial HTML guide learn HTML HTML5 HTML course HTML training HTML lessons HTML tips HTML tricks"
/>

<!-- Bad: Too long, will be truncated -->
<meta
  name="description"
  content="Learn HTML5 with this incredibly detailed and comprehensive tutorial covering every single aspect of HTML markup language including all elements, attributes, best practices, and advanced techniques for building modern web applications..."
/>
```

### Browser Support

✅ Universal support in all browsers  
✅ Different meta names supported to varying degrees (check specific features)

---

## `<link>`

**Category**: Metadata Content  
**Content Model**: Empty (void element)  
**Permitted Parents**: `<head>`, `<body>` (if `itemprop` present), `<noscript>` (in `<head>`)  
**Tag Omission**: No end tag (void element)  
**Implicit ARIA Role**: `link` (if has `href`)

### Purpose & Semantic Meaning

Specifies **relationships between the current document and external resources**. Most commonly used for stylesheets, icons, and preloading resources.

**Key characteristic**: Links to external resources without creating hyperlinks in content.

### When to Use

Use `<link>` for:

- Loading CSS stylesheets
- Specifying favicons
- Preloading/prefetching resources
- Defining alternate versions of page
- RSS/Atom feeds
- Canonical URLs
- Webfonts
- DNS prefetching

### When NOT to Use

❌ Don't use for:

- Navigation links in content (use `<a>`)
- JavaScript files (use `<script src="">`)
- Importing other HTML (not supported)

### Attributes

**Element-Specific**:

- `rel` - Relationship type (required) - defines what the linked resource is
- `href` - URL of linked resource (required for most rel values)
- `type` - MIME type of linked resource
- `media` - Media query for when resource applies
- `sizes` - Icon sizes (for `rel="icon"`)
- `as` - Type of content being preloaded (with `rel="preload"`)
- `crossorigin` - CORS settings
- `integrity` - Subresource integrity hash
- `hreflang` - Language of linked resource
- `title` - Title of linked stylesheet (alternate stylesheets)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Link Types (`rel` values)

#### 1. Stylesheets

```html
<!-- Main stylesheet -->
<link rel="stylesheet" href="/styles/main.css" />

<!-- Media-specific stylesheet -->
<link rel="stylesheet" href="/styles/print.css" media="print" />
<link rel="stylesheet" href="/styles/mobile.css" media="screen and (max-width: 600px)" />

<!-- Alternate stylesheet (user-selectable) -->
<link rel="alternate stylesheet" href="/styles/high-contrast.css" title="High Contrast" />
```

#### 2. Icons

```html
<!-- Modern SVG favicon (preferred) -->
<link rel="icon" type="image/svg+xml" href="/favicon.svg" />

<!-- PNG favicon with sizes -->
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />

<!-- Apple Touch Icon -->
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />

<!-- Android/Chrome -->
<link rel="manifest" href="/site.webmanifest" />

<!-- Legacy ICO -->
<link rel="shortcut icon" href="/favicon.ico" />
```

#### 3. Canonical URL

```html
<!-- Preferred URL for this page (prevents duplicate content issues) -->
<link rel="canonical" href="https://example.com/page" />
```

#### 4. Alternate Versions

```html
<!-- Language alternatives -->
<link rel="alternate" hreflang="es" href="https://example.com/es/page" />
<link rel="alternate" hreflang="fr" href="https://example.com/fr/page" />
<link rel="alternate" hreflang="x-default" href="https://example.com/page" />

<!-- Mobile version -->
<link
  rel="alternate"
  media="only screen and (max-width: 640px)"
  href="https://m.example.com/page"
/>

<!-- RSS/Atom feeds -->
<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="/feed.xml" />
<link rel="alternate" type="application/atom+xml" title="Atom Feed" href="/atom.xml" />

<!-- AMP version -->
<link rel="amphtml" href="https://example.com/page.amp.html" />
```

#### 5. Preloading and Prefetching

```html
<!-- Preload critical resources (loaded immediately) -->
<link rel="preload" href="/fonts/main.woff2" as="font" type="font/woff2" crossorigin />
<link rel="preload" href="/styles/critical.css" as="style" />
<link rel="preload" href="/scripts/app.js" as="script" />
<link rel="preload" href="/images/hero.jpg" as="image" />

<!-- Prefetch (loaded when idle, for next navigation) -->
<link rel="prefetch" href="/page2.html" />
<link rel="prefetch" href="/scripts/next-page.js" />

<!-- DNS prefetch (resolve domain name early) -->
<link rel="dns-prefetch" href="https://fonts.googleapis.com" />

<!-- Preconnect (DNS + TCP + TLS handshake) -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

<!-- Prerender (load entire page in background) -->
<link rel="prerender" href="/next-page.html" />
```

**Preload `as` values**:

- `audio` - Audio file
- `document` - HTML document
- `embed` - Embedded content
- `fetch` - Fetch/XHR request
- `font` - Font file (use `crossorigin`)
- `image` - Image
- `object` - Object/embed content
- `script` - JavaScript
- `style` - CSS stylesheet
- `track` - Video track
- `video` - Video file
- `worker` - Web worker

#### 6. Module Preloading

```html
<!-- Preload ES modules -->
<link rel="modulepreload" href="/scripts/app.js" />
<link rel="modulepreload" href="/scripts/utils.js" />
```

#### 7. Other Relationships

```html
<!-- Search -->
<link
  rel="search"
  type="application/opensearchdescription+xml"
  href="/opensearch.xml"
  title="Site Search"
/>

<!-- License -->
<link rel="license" href="/license.html" />

<!-- Author -->
<link rel="author" href="/humans.txt" />

<!-- Help -->
<link rel="help" href="/help.html" />

<!-- Pingback -->
<link rel="pingback" href="https://example.com/pingback" />
```

### Examples

#### Good Example: Complete Link Structure

```html
<head>
  <!-- Essential meta -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- SEO -->
  <link rel="canonical" href="https://example.com/page" />
  <link rel="alternate" hreflang="es" href="https://example.com/es/page" />

  <!-- Icons -->
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />

  <!-- Performance: Preconnect to external origins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://cdn.example.com" />

  <!-- Performance: Preload critical resources -->
  <link rel="preload" href="/fonts/main.woff2" as="font" type="font/woff2" crossorigin />
  <link rel="preload" href="/styles/critical.css" as="style" />

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/styles/main.css" />
  <link rel="stylesheet" href="/styles/print.css" media="print" />

  <!-- RSS Feed -->
  <link rel="alternate" type="application/rss+xml" title="Blog RSS" href="/feed.xml" />
</head>
```

#### Good Example: Performance-Optimized Loading

```html
<head>
  <!-- Critical CSS inline -->
  <style>
    /* Critical above-the-fold CSS here */
  </style>

  <!-- Preconnect to external fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

  <!-- Preload font files -->
  <link rel="preload" href="/fonts/main.woff2" as="font" type="font/woff2" crossorigin />

  <!-- Async load non-critical CSS -->
  <link rel="stylesheet" href="/styles/main.css" media="print" onload="this.media='all'" />
  <noscript><link rel="stylesheet" href="/styles/main.css" /></noscript>

  <!-- Prefetch next page -->
  <link rel="prefetch" href="/next-page.html" />
</head>
```

#### Bad Example: Missing Crossorigin

```html
<!-- ❌ WRONG: Preloading font without crossorigin -->
<link rel="preload" href="/fonts/main.woff2" as="font" type="font/woff2" />

<!-- ✅ CORRECT: Include crossorigin for fonts -->
<link rel="preload" href="/fonts/main.woff2" as="font" type="font/woff2" crossorigin />
```

### Accessibility

**Indirect accessibility impact**: Proper resource loading affects page usability

**Best practices**:

- Preload fonts to prevent FOIT (Flash of Invisible Text)
- Use `media` queries for print stylesheets
- Don't overuse preload (HTTP/2 can handle many requests)

### SEO Impact

**High SEO value**: Several link types critical for SEO

**Most important**:

1. **canonical** - Prevents duplicate content penalties
2. **alternate hreflang** - Multi-language SEO
3. **alternate (RSS)** - Content syndication
4. **amphtml** - Mobile search results

**Canonical URL best practices**:

```html
<!-- Always use absolute URLs -->
<link rel="canonical" href="https://example.com/page" />

<!-- Self-referencing canonical (recommended) -->
<link rel="canonical" href="https://example.com/page" />

<!-- Cross-domain canonical (consolidates signals) -->
<link rel="canonical" href="https://originalsite.com/page" />
```

**Hreflang best practices**:

```html
<!-- Include all language versions + x-default -->
<link rel="alternate" hreflang="en" href="https://example.com/page" />
<link rel="alternate" hreflang="es" href="https://example.com/es/page" />
<link rel="alternate" hreflang="fr" href="https://example.com/fr/page" />
<link rel="alternate" hreflang="x-default" href="https://example.com/page" />

<!-- Region-specific -->
<link rel="alternate" hreflang="en-US" href="https://example.com/us/page" />
<link rel="alternate" hreflang="en-GB" href="https://example.com/uk/page" />
```

### Browser Support

✅ Universal support for `stylesheet`, `icon`, `canonical`, `alternate`  
✅ Modern browsers support `preload`, `prefetch`, `preconnect` (Chrome 50+, Firefox 56+, Safari 11.1+)  
⚠️ `modulepreload` has limited support (Chrome 66+, Edge 79+, not in Firefox/Safari yet)

---

## `<base>`

**Category**: Metadata Content  
**Content Model**: Empty (void element)  
**Permitted Parents**: `<head>` (only one per document)  
**Tag Omission**: No end tag (void element)  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Specifies the **base URL** for all relative URLs in the document, and/or the **default target** for links.

**Key characteristic**: Affects all relative URLs in the page.

### When to Use

Use `<base>` for:

- Setting base URL for all relative links
- Simplifying URLs in dynamically generated pages
- Changing default link target (e.g., open in new window)

### When NOT to Use

❌ Don't use for:

- Most websites (can cause confusion)
- When you need fine control over individual URLs
- Single-page applications (conflicts with routing)

**Warning**: `<base>` affects ALL relative URLs (href, src, action, etc.). Use with caution.

### Attributes

**Element-Specific**:

- `href` - Base URL for relative URLs (must be absolute URL)
- `target` - Default target for links (`_self`, `_blank`, `_parent`, `_top`, or frame name)

**At least one** of `href` or `target` must be present.

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Must be**:

- Before any element with URL attribute
- Only one `<base>` per document
- In `<head>` section

### Examples

#### Good Example: Base URL

```html
<head>
  <base href="https://example.com/app/" />

  <!-- These relative URLs resolve to: -->
  <link rel="stylesheet" href="styles/main.css" />
  <!-- → https://example.com/app/styles/main.css -->

  <script src="scripts/app.js"></script>
  <!-- → https://example.com/app/scripts/app.js -->
</head>
<body>
  <a href="page2.html">Next Page</a>
  <!-- → https://example.com/app/page2.html -->

  <img src="images/logo.png" alt="Logo" />
  <!-- → https://example.com/app/images/logo.png -->
</body>
```

#### Good Example: Default Target

```html
<head>
  <base target="_blank" />
</head>
<body>
  <!-- All links open in new tab by default -->
  <a href="https://example.com">Example</a>

  <!-- Override default target for specific link -->
  <a href="https://example.com" target="_self">Same Tab</a>
</body>
```

#### Good Example: Both URL and Target

```html
<head>
  <base href="https://example.com/docs/" target="_blank" />
</head>
```

#### Bad Example: Relative Base URL

```html
<!-- ❌ WRONG: Base href must be absolute -->
<base href="/app/" />

<!-- ✅ CORRECT: Use full URL -->
<base href="https://example.com/app/" />
```

#### Pitfall Example: Fragment Links

```html
<head>
  <base href="https://example.com/app/" />
</head>
<body>
  <!-- ⚠️ PITFALL: This goes to https://example.com/app/#section, not current page -->
  <a href="#section">Jump to Section</a>

  <!-- Solution: Use full URL or JavaScript -->
  <a href="https://example.com/current-page#section">Jump to Section</a>
</body>
```

### Accessibility

**No direct accessibility impact**: Affects URL resolution only

### SEO Impact

**Minimal SEO value**: Can cause issues if used incorrectly

**Risks**:

- Canonical URLs may be affected
- Relative canonical becomes absolute based on base
- Can confuse crawlers if misconfigured

**Recommendation**: Avoid `<base>` unless you have specific need. Use absolute URLs instead.

### Browser Support

✅ Universal support in all browsers  
⚠️ Can cause unexpected behavior with fragment links and form actions

---

## `<style>`

**Category**: Metadata Content  
**Content Model**: Text (CSS)  
**Permitted Parents**: `<head>`, `<body>` (if `scoped` - deprecated), `<noscript>` (in `<head>`)  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Contains **CSS style information** for the document. Allows inline CSS within HTML instead of external stylesheet.

**Key characteristic**: Embeds CSS directly in HTML.

### When to Use

Use `<style>` for:

- Critical CSS (above-the-fold styles)
- Page-specific styles (not reusable across site)
- Dynamically generated styles
- Reducing HTTP requests (inline critical CSS)
- Development and prototyping

### When NOT to Use

❌ Don't use for:

- Site-wide styles (use external CSS)
- Large amounts of CSS (affects HTML parsing)
- Reusable styles across multiple pages

**Best practice**: External CSS for main styles, inline critical CSS only.

### Attributes

**Element-Specific**:

- `media` - Media query for when styles apply (optional)
- `nonce` - Cryptographic nonce for CSP (Content Security Policy)
- `title` - Advisory title (for alternate stylesheets)

**Obsolete attributes**:

- `type` - No longer needed (assumed to be `text/css`)
- `scoped` - Removed from spec

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Text (CSS rules)
- CSS comments

**Cannot contain**:

- HTML tags
- Script tags (use `<script>` for JavaScript)

### Examples

#### Good Example: Critical CSS

```html
<head>
  <!-- Inline critical above-the-fold CSS -->
  <style>
    /* Critical styles for first render */
    body {
      font-family: -apple-system, sans-serif;
      margin: 0;
      padding: 0;
    }

    header {
      background: #333;
      color: #fff;
      padding: 1rem;
    }

    .hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>

  <!-- Load remaining styles async -->
  <link rel="stylesheet" href="/styles/main.css" media="print" onload="this.media='all'" />
</head>
```

#### Good Example: Media-Specific Styles

```html
<style media="print">
  @page {
    margin: 2cm;
  }

  header,
  footer,
  nav,
  aside {
    display: none;
  }

  a[href^='http']:after {
    content: ' (' attr(href) ')';
  }
</style>

<style media="screen and (max-width: 600px)">
  .desktop-only {
    display: none;
  }

  .mobile-menu {
    display: block;
  }
</style>
```

#### Good Example: Dynamic Styles (Template)

```html
<style>
  :root {
    --brand-color: {{ theme.primaryColor }};
    --font-family: {{ theme.fontFamily }};
  }

  .btn-primary {
    background-color: var(--brand-color);
  }
</style>
```

#### Good Example: CSP with Nonce

```html
<!-- HTTP Header: Content-Security-Policy: style-src 'nonce-abc123' -->
<style nonce="abc123">
  /* Styles allowed by nonce */
  body {
    font-family: sans-serif;
  }
</style>
```

#### Bad Example: Large Inline Styles

```html
<!-- ❌ WRONG: Too much CSS inline (thousands of lines) -->
<style>
  /* Entire CSS framework inline... */
  /* Blocks HTML parsing */
  /* Makes HTML file huge */
</style>

<!-- ✅ CORRECT: External CSS with small critical CSS inline -->
<style>
  /* Only critical above-the-fold CSS */
</style>
<link rel="stylesheet" href="/styles/main.css" />
```

### Accessibility

**No direct accessibility impact**: Styles affect presentation, not semantics

**Best practices**:

- Ensure sufficient color contrast (WCAG AA: 4.5:1)
- Don't hide content with `display: none` that should be read
- Use `visibility: hidden` or `aria-hidden` appropriately

### SEO Impact

**No direct SEO value**: Search engines primarily index content, not styles

**Indirect benefits**:

- Faster page load (inlined critical CSS) improves rankings
- Better mobile experience with responsive styles

### Browser Support

✅ Universal support in all browsers  
✅ `media` attribute fully supported

---

## Structured Data

While not a single element, structured data is critical for SEO and should be included in `<head>`.

### JSON-LD (Recommended)

```html
<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "Article Headline",
    "description": "Article description",
    "image": "https://example.com/image.jpg",
    "author": {
      "@type": "Person",
      "name": "Author Name",
      "url": "https://example.com/author"
    },
    "publisher": {
      "@type": "Organization",
      "name": "Publisher Name",
      "logo": {
        "@type": "ImageObject",
        "url": "https://example.com/logo.jpg"
      }
    },
    "datePublished": "2025-11-04T10:00:00Z",
    "dateModified": "2025-11-04T15:00:00Z"
  }
</script>
```

**Common schema types**:

- `Article` - Blog posts, news articles
- `Product` - E-commerce products
- `Organization` - Company information
- `Person` - Author/person profiles
- `WebSite` - Website metadata
- `BreadcrumbList` - Navigation breadcrumbs
- `FAQPage` - FAQ pages
- `HowTo` - How-to guides
- `Recipe` - Cooking recipes
- `Event` - Events and tickets
- `Review` - Product/business reviews

### Testing Structured Data

- **Google Rich Results Test**: https://search.google.com/test/rich-results
- **Schema Markup Validator**: https://validator.schema.org/

---

## Complete Head Template

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- ===== ESSENTIAL ===== -->
    <!-- Character encoding (MUST be first) -->
    <meta charset="UTF-8" />

    <!-- Viewport for responsive design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Page title (REQUIRED) -->
    <title>Page Title - Site Name</title>

    <!-- ===== SEO ===== -->
    <!-- Meta description -->
    <meta name="description" content="Compelling 150-160 character description" />

    <!-- Canonical URL -->
    <link rel="canonical" href="https://example.com/page" />

    <!-- Language alternatives -->
    <link rel="alternate" hreflang="en" href="https://example.com/page" />
    <link rel="alternate" hreflang="es" href="https://example.com/es/page" />
    <link rel="alternate" hreflang="x-default" href="https://example.com/page" />

    <!-- Robots -->
    <meta name="robots" content="index, follow" />

    <!-- Author -->
    <meta name="author" content="Author Name" />

    <!-- ===== OPEN GRAPH ===== -->
    <meta property="og:title" content="Page Title" />
    <meta property="og:description" content="Page description for social sharing" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://example.com/page" />
    <meta property="og:image" content="https://example.com/image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt" content="Image description" />
    <meta property="og:site_name" content="Site Name" />
    <meta property="og:locale" content="en_US" />

    <!-- ===== TWITTER CARD ===== -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Page Title" />
    <meta name="twitter:description" content="Description for Twitter" />
    <meta name="twitter:image" content="https://example.com/image.jpg" />
    <meta name="twitter:image:alt" content="Image description" />
    <meta name="twitter:site" content="@site_account" />
    <meta name="twitter:creator" content="@author_account" />

    <!-- ===== ICONS ===== -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    <!-- ===== PERFORMANCE ===== -->
    <!-- Preconnect to external origins -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://cdn.example.com" />

    <!-- Preload critical resources -->
    <link rel="preload" href="/fonts/main.woff2" as="font" type="font/woff2" crossorigin />
    <link rel="preload" href="/styles/critical.css" as="style" />

    <!-- Critical CSS inline -->
    <style>
      /* Critical above-the-fold CSS */
    </style>

    <!-- ===== STYLESHEETS ===== -->
    <link rel="stylesheet" href="/styles/main.css" />
    <link rel="stylesheet" href="/styles/print.css" media="print" />

    <!-- ===== FEEDS ===== -->
    <link rel="alternate" type="application/rss+xml" title="RSS Feed" href="/feed.xml" />

    <!-- ===== STRUCTURED DATA ===== -->
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Page Title",
        "description": "Page description",
        "url": "https://example.com/page"
      }
    </script>
  </head>
  <body>
    <!-- Page content -->
  </body>
</html>
```

---

## Metadata Best Practices Summary

1. **Always include**: charset, viewport, title, description
2. **Use absolute URLs**: For canonical, og:image, og:url
3. **Test social sharing**: Facebook Debugger, Twitter Card Validator
4. **Optimize images**: 1200×630 for og:image, under 8MB
5. **Keep titles unique**: Every page needs unique title and description
6. **Include structured data**: JSON-LD for rich results
7. **Test performance**: Preload critical resources, minimize blocking
8. **Validate**: Check with HTML validator and SEO tools
9. **Don't overuse preload**: Only critical resources (2-3 max)
10. **Monitor SEO**: Use Google Search Console

---

## Further Reading

- WHATWG HTML Standard - Document Metadata: https://html.spec.whatwg.org/multipage/semantics.html#semantics
- Open Graph Protocol: https://ogp.me/
- Twitter Cards: https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards
- Schema.org: https://schema.org/
- Google Search Central: https://developers.google.com/search
