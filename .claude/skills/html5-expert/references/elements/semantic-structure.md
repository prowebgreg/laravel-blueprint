# Semantic Structure Elements

Comprehensive reference for HTML5 semantic structural elements that define the meaningful sections and organization of web documents.

## Overview

Semantic structural elements replace generic `<div>` containers with meaningful markup that describes the purpose and hierarchy of content. These elements improve:

- **Accessibility**: Screen readers navigate by landmarks and structure
- **SEO**: Search engines understand content hierarchy and importance
- **Maintainability**: Clear, self-documenting code structure
- **Document Outline**: Automatic generation of page structure

**Core Principle**: Use semantic elements to describe content meaning, not visual presentation.

---

## `<article>`

**Category**: Sectioning Content  
**Content Model**: Flow content  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `article`

### Purpose & Semantic Meaning

Represents a **self-contained, independently distributable composition** that could stand alone and make sense when syndicated, reused, or distributed separately from the rest of the page.

**Key characteristic**: The content is complete and independent.

### When to Use

Use `<article>` for:

- Blog posts
- News articles
- Forum posts
- Product cards in e-commerce
- Social media posts
- Comments (nested in parent article)
- Interactive widgets that are self-contained

### When NOT to Use

❌ Don't use for:

- Generic grouping of related content (use `<section>`)
- Sidebars or complementary content (use `<aside>`)
- Navigation blocks (use `<nav>`)
- Content that requires context from parent page

### Attributes

**Element-Specific**: None  
**Global Attributes**: All [global attributes](../global-attributes.md) apply

Common global attributes:

- `id` - Unique identifier for linking and JavaScript
- `class` - Styling and grouping
- `aria-label` or `aria-labelledby` - Accessible name for screen readers

### Content Model Rules

**Can contain**:

- Flow content (headings, paragraphs, sections, etc.)
- Zero or more `<article>` elements (for nested independent content like comments)
- `<header>` and `<footer>` elements

**Cannot contain**:

- No specific restrictions beyond standard flow content rules

**Nesting**:

- `<article>` can be nested inside another `<article>` when the nested article is related to but independent from the parent (e.g., blog post → comments)
- Can contain `<section>` elements to divide the article into thematic parts

### Examples

#### Good Example: Blog Post

```html
<article>
  <header>
    <h2>Understanding HTML5 Semantics</h2>
    <p>Published on <time datetime="2025-11-03">November 3, 2025</time> by Jane Doe</p>
  </header>

  <section>
    <h3>Introduction</h3>
    <p>Semantic HTML provides meaning to web content...</p>
  </section>

  <section>
    <h3>Key Benefits</h3>
    <p>Improved accessibility, SEO, and maintainability...</p>
  </section>

  <footer>
    <p>Tags: <a href="/tags/html">HTML</a>, <a href="/tags/semantics">Semantics</a></p>
  </footer>
</article>
```

#### Good Example: Nested Articles (Comments)

```html
<article>
  <h2>Main Article Title</h2>
  <p>Article content goes here...</p>

  <section>
    <h3>Comments</h3>

    <article>
      <header>
        <p><strong>User123</strong> commented:</p>
      </header>
      <p>Great article! Very informative.</p>
    </article>

    <article>
      <header>
        <p><strong>DevGuru</strong> commented:</p>
      </header>
      <p>Thanks for sharing this.</p>
    </article>
  </section>
</article>
```

#### Bad Example: Non-Independent Content

```html
<!-- ❌ WRONG: This is just a thematic section, not independent -->
<article>
  <h2>Our Services</h2>
  <p>List of services we offer...</p>
</article>

<!-- ✅ CORRECT: Use <section> for thematic grouping -->
<section>
  <h2>Our Services</h2>
  <p>List of services we offer...</p>
</section>
```

### Accessibility

**Implicit role**: `article` landmark  
**Screen reader behavior**: Announced as "article" landmark; users can navigate directly to articles

**Best practices**:

- Include a heading as the first child (h1-h6) to label the article
- Use `aria-labelledby` to reference the article's heading for explicit labeling
- When multiple articles exist, ensure each has a unique accessible name

```html
<article aria-labelledby="post-title">
  <h2 id="post-title">Article Title</h2>
  <p>Content...</p>
</article>
```

### SEO Impact

**High SEO value**: Search engines recognize `<article>` as primary content

**Benefits**:

- Signals main content to search crawlers
- Increases likelihood of article snippets in search results
- Supports structured data markup (JSON-LD) for rich results
- Helps Google understand content syndication and original source

**Best practices for SEO**:

- Always include a clear heading (h1 or h2)
- Include publication date using `<time datetime="">`
- Add author information in header or footer
- Use microdata or JSON-LD for Article schema

### Browser Support

✅ Universal support in all modern browsers  
✅ Treated as `<div>` in legacy browsers (graceful degradation)

---

## `<section>`

**Category**: Sectioning Content  
**Content Model**: Flow content  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `region` (when labeled)

### Purpose & Semantic Meaning

Represents a **thematic grouping of content**, typically with a heading. Used to divide content into meaningful sections based on theme or topic.

**Key characteristic**: Content is grouped by theme, not independence.

### When to Use

Use `<section>` for:

- Chapters or themed divisions in long articles
- Tabbed content panels
- Numbered sections of a document
- Thematic grouping of related content
- Different topics on a single page (About, Services, Contact)

### When NOT to Use

❌ Don't use for:

- Generic styling containers (use `<div>`)
- Content that stands alone and is independently distributable (use `<article>`)
- Navigation blocks (use `<nav>`)
- Complementary sidebars (use `<aside>`)

**Rule of thumb**: If you can't describe the section's theme with a heading, use `<div>` instead.

### Attributes

**Element-Specific**: None  
**Global Attributes**: All [global attributes](../global-attributes.md) apply

**Important for accessibility**:

- `aria-label` or `aria-labelledby` - Required to expose as `region` landmark

### Content Model Rules

**Can contain**:

- Flow content (headings, paragraphs, lists, etc.)
- Typically includes a heading element (h1-h6) as first child
- Can contain nested `<section>` elements
- Can contain `<article>` elements

**Cannot contain**:

- No specific restrictions

**Nesting**:

- Can nest `<section>` within `<section>` for subsections
- Can nest `<article>` within `<section>` to group related independent content
- Can be nested within `<article>` to divide article content

### Examples

#### Good Example: Thematic Sections on Landing Page

```html
<main>
  <section aria-labelledby="features">
    <h2 id="features">Features</h2>
    <p>Our product includes powerful features...</p>
  </section>

  <section aria-labelledby="pricing">
    <h2 id="pricing">Pricing</h2>
    <p>Choose the plan that works for you...</p>
  </section>

  <section aria-labelledby="testimonials">
    <h2 id="testimonials">Customer Testimonials</h2>
    <p>See what our customers say...</p>
  </section>
</main>
```

#### Good Example: Chapters in Article

```html
<article>
  <h1>Complete Guide to HTML5</h1>

  <section>
    <h2>Chapter 1: Introduction</h2>
    <p>HTML5 is the latest version...</p>
  </section>

  <section>
    <h2>Chapter 2: Semantic Elements</h2>
    <p>Semantic elements provide meaning...</p>
  </section>

  <section>
    <h2>Chapter 3: Advanced Features</h2>
    <p>HTML5 introduces powerful APIs...</p>
  </section>
</article>
```

#### Bad Example: Generic Container

```html
<!-- ❌ WRONG: No thematic grouping, just a styling wrapper -->
<section class="container">
  <div class="row">
    <div class="col">Content</div>
  </div>
</section>

<!-- ✅ CORRECT: Use div for non-semantic containers -->
<div class="container">
  <div class="row">
    <div class="col">Content</div>
  </div>
</div>
```

### Accessibility

**Implicit role**: `region` (only when labeled with aria-label or aria-labelledby)  
**Without label**: Treated as generic section, not a landmark

**Best practices**:

- Always include a heading (h2-h6) to label the section theme
- Add `aria-labelledby` pointing to the heading ID for explicit landmark
- Use visible headings rather than aria-label when possible

```html
<!-- ✅ Creates accessible region landmark -->
<section aria-labelledby="about-heading">
  <h2 id="about-heading">About Us</h2>
  <p>Company information...</p>
</section>
```

### SEO Impact

**Moderate SEO value**: Helps search engines understand content organization

**Benefits**:

- Clarifies document structure and hierarchy
- Headings within sections contribute to page outline
- Improves topical relevance for section content

**Best practices for SEO**:

- Always include a descriptive heading (h2-h6)
- Use meaningful section themes that match search intent
- Avoid excessive nesting (keep hierarchy simple)

### Browser Support

✅ Universal support in all modern browsers  
✅ Treated as `<div>` in legacy browsers

---

## `<nav>`

**Category**: Sectioning Content / Flow Content  
**Content Model**: Flow content  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `navigation`

### Purpose & Semantic Meaning

Represents a **section containing navigation links** for navigating the website, current document, or related documents. Major navigation blocks only.

**Key characteristic**: Primary navigation mechanism.

### When to Use

Use `<nav>` for:

- Primary site navigation (header menu)
- Table of contents for long articles
- Pagination controls (previous/next page)
- Footer site navigation
- Breadcrumb navigation
- Skip links for accessibility

**Multiple `<nav>` elements**: You can have multiple `<nav>` elements on a page (site nav, page nav, footer nav)

### When NOT to Use

❌ Don't use for:

- All groups of links (only major navigation blocks)
- Social media link lists (these are not navigation)
- Tag clouds or related links (use regular links)
- Links within article content

**Rule**: Only major navigation blocks should use `<nav>`. Not every link group requires it.

### Attributes

**Element-Specific**: None  
**Global Attributes**: All [global attributes](../global-attributes.md) apply

**Important for multiple nav elements**:

- `aria-label` or `aria-labelledby` - Distinguish multiple nav sections

### Content Model Rules

**Can contain**:

- Flow content (typically unordered lists `<ul>` with links)
- Headings (h2-h6) to label navigation section
- Links (`<a>` elements)

**Cannot contain**:

- No specific restrictions, but should contain navigation-related content

**Typical structure**:

```html
<nav>
  <ul>
    <li><a href="/">Link</a></li>
  </ul>
</nav>
```

### Examples

#### Good Example: Primary Site Navigation

```html
<header>
  <nav aria-label="Primary">
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/about">About</a></li>
      <li><a href="/services">Services</a></li>
      <li><a href="/contact">Contact</a></li>
    </ul>
  </nav>
</header>
```

#### Good Example: Multiple Navigation Sections

```html
<!-- Site navigation in header -->
<header>
  <nav aria-label="Primary Navigation">
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/products">Products</a></li>
    </ul>
  </nav>
</header>

<!-- Table of contents in article -->
<article>
  <nav aria-label="Table of Contents">
    <h2>Contents</h2>
    <ul>
      <li><a href="#intro">Introduction</a></li>
      <li><a href="#methods">Methods</a></li>
      <li><a href="#results">Results</a></li>
    </ul>
  </nav>

  <section id="intro">...</section>
</article>

<!-- Footer navigation -->
<footer>
  <nav aria-label="Footer Navigation">
    <ul>
      <li><a href="/privacy">Privacy</a></li>
      <li><a href="/terms">Terms</a></li>
    </ul>
  </nav>
</footer>
```

#### Bad Example: Social Media Links

```html
<!-- ❌ WRONG: Social links are not primary navigation -->
<nav>
  <a href="https://twitter.com/example">Twitter</a>
  <a href="https://facebook.com/example">Facebook</a>
</nav>

<!-- ✅ CORRECT: Regular links, no nav element -->
<div class="social-links">
  <a href="https://twitter.com/example">Twitter</a>
  <a href="https://facebook.com/example">Facebook</a>
</div>
```

### Accessibility

**Implicit role**: `navigation` landmark  
**Screen reader behavior**: Announced as "navigation" landmark; users can jump directly to nav sections

**Best practices**:

- Label multiple `<nav>` elements with `aria-label` to distinguish them
- Use semantic list markup (`<ul>/<li>`) inside nav for structure
- Include skip link for keyboard users to bypass navigation
- Indicate current page with `aria-current="page"`

```html
<nav aria-label="Primary Navigation">
  <ul>
    <li><a href="/">Home</a></li>
    <li><a href="/about" aria-current="page">About</a></li>
    <li><a href="/contact">Contact</a></li>
  </ul>
</nav>
```

### SEO Impact

**Moderate SEO value**: Helps search engines identify site structure

**Benefits**:

- Signals site architecture to crawlers
- Internal links in nav pass link equity
- Breadcrumb nav improves search result appearance

**Best practices for SEO**:

- Include descriptive anchor text in links
- Use breadcrumb navigation for complex sites
- Keep navigation hierarchy shallow (3-4 levels max)

### Browser Support

✅ Universal support in all modern browsers  
✅ Treated as `<div>` in legacy browsers

---

## `<main>`

**Category**: Flow Content  
**Content Model**: Flow content  
**Permitted Parents**: `<html>`, `<body>` (or container elements in specific cases)  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `main`

### Purpose & Semantic Meaning

Represents the **primary content** of the document. Content that is unique to the page and not repeated across pages (unlike headers, footers, and navigation).

**Key characteristic**: Only one `<main>` per page, containing the page's unique content.

### When to Use

Use `<main>` for:

- The primary content area of the page
- Content that is unique to this specific page
- The "main story" or central purpose of the page

**One per page rule**: Only **one** `<main>` element per document (not hidden)

### When NOT to Use

❌ Don't use for:

- Content repeated across pages (headers, footers, navigation)
- Sidebars with complementary content
- Multiple main content areas (only one allowed)
- Site-wide search boxes or navigation

### Attributes

**Element-Specific**: None  
**Global Attributes**: All [global attributes](../global-attributes.md) apply

**Note**: Do NOT add `role="main"` as it's redundant (already implicit)

### Content Model Rules

**Can contain**:

- Flow content (sections, articles, headings, paragraphs, etc.)
- Multiple `<article>` or `<section>` elements

**Cannot be nested within**:

- `<article>`
- `<aside>`
- `<footer>`
- `<header>`
- `<nav>`
- Another `<main>`

**Position**: Typically direct child of `<body>`, but can be inside `<div>` or other container

### Examples

#### Good Example: Basic Page Structure

```html
<body>
  <header>
    <h1>Site Title</h1>
    <nav><!-- Site navigation --></nav>
  </header>

  <main>
    <article>
      <h2>Welcome to Our Site</h2>
      <p>This is the unique content for this page...</p>
    </article>
  </main>

  <aside>
    <!-- Sidebar content -->
  </aside>

  <footer>
    <p>&copy; 2025 Company</p>
  </footer>
</body>
```

#### Good Example: Main with Multiple Sections

```html
<main>
  <section aria-labelledby="intro">
    <h2 id="intro">Introduction</h2>
    <p>Overview of the topic...</p>
  </section>

  <section aria-labelledby="details">
    <h2 id="details">Detailed Information</h2>
    <p>In-depth content...</p>
  </section>
</main>
```

#### Bad Example: Multiple Main Elements

```html
<!-- ❌ WRONG: Only one main allowed -->
<body>
  <main>
    <article>First article</article>
  </main>

  <main>
    <article>Second article</article>
  </main>
</body>

<!-- ✅ CORRECT: Use main once, with multiple articles inside -->
<body>
  <main>
    <article>First article</article>
    <article>Second article</article>
  </main>
</body>
```

### Accessibility

**Implicit role**: `main` landmark  
**Screen reader behavior**: "main" landmark; users can jump directly to primary content

**Best practices**:

- Use exactly one `<main>` per page (not hidden)
- Place after site header/navigation so skip links can jump to it
- Don't add `role="main"` (redundant)
- Don't nest within article, aside, footer, header, or nav

```html
<!-- ✅ Accessible page structure -->
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <header><!-- Site header --></header>
  <nav><!-- Navigation --></nav>

  <main id="main-content">
    <!-- Primary page content -->
  </main>

  <footer><!-- Footer --></footer>
</body>
```

### SEO Impact

**High SEO value**: Signals primary content to search engines

**Benefits**:

- Helps search crawlers identify most important content
- Improves content indexing accuracy
- Reduces processing of repeated elements (nav, footer)

**Best practices for SEO**:

- Place h1 heading inside `<main>` (primary page topic)
- Keep most important content in main (not sidebar)
- Avoid duplicating content across main sections of different pages

### Browser Support

✅ Universal support in all modern browsers  
⚠️ IE11 requires polyfill or `role="main"` attribute for accessibility

---

## `<header>`

**Category**: Flow Content  
**Content Model**: Flow content (excluding header and footer)  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `banner` (when child of body), `generic` (when nested)

### Purpose & Semantic Meaning

Represents **introductory content** for its nearest ancestor sectioning content or sectioning root. Typically contains headings, logos, search forms, author info, and navigation.

**Key characteristic**: Context-dependent - meaning changes based on parent element.

### When to Use

Use `<header>` for:

- Site-wide header (logo, site title, primary navigation)
- Article header (title, author, publication date)
- Section header (section heading and metadata)
- Introductory content for any sectioning element

**Multiple headers**: You can have multiple `<header>` elements (page header, article headers, section headers)

### When NOT to Use

❌ Don't use for:

- Headings alone (use h1-h6 directly)
- Content that isn't introductory
- Nested inside another `<header>`, `<footer>`, or `<address>`

### Attributes

**Element-Specific**: None  
**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Flow content
- Headings (h1-h6)
- Navigation (`<nav>`)
- Logos, search forms, author information

**Cannot contain**:

- Another `<header>` element
- `<footer>` element
- `<address>` element (when it represents document contact info)

**Context behavior**:

- **Direct child of `<body>`**: Acts as page header (banner landmark)
- **Inside sectioning element** (`<article>`, `<section>`): Acts as section header (not a landmark)

### Examples

#### Good Example: Site-Wide Header

```html
<body>
  <header>
    <img src="logo.png" alt="Company Logo" />
    <h1>Company Name</h1>
    <nav>
      <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/about">About</a></li>
      </ul>
    </nav>
  </header>

  <main><!-- Content --></main>
</body>
```

#### Good Example: Article Header

```html
<article>
  <header>
    <h2>Article Title</h2>
    <p>By <a href="/author">Jane Doe</a></p>
    <p>Published on <time datetime="2025-11-03">November 3, 2025</time></p>
    <p>Reading time: 5 minutes</p>
  </header>

  <p>Article content...</p>

  <footer>
    <p>Tags: HTML, Semantics</p>
  </footer>
</article>
```

#### Bad Example: Just a Heading

```html
<!-- ❌ WRONG: Header for single heading is overkill -->
<header>
  <h1>Page Title</h1>
</header>

<!-- ✅ CORRECT: Just use the heading -->
<h1>Page Title</h1>
```

### Accessibility

**Implicit role**:

- `banner` (page header, direct child of body) - landmark role
- `generic` (sectioning content header) - not a landmark

**Screen reader behavior**: Page header announced as "banner" landmark

**Best practices**:

- Only one page-level header (banner) per page
- Include site navigation in page header for consistency
- Section headers provide structure but aren't landmarks
- Always include a heading (h1-h6) in header

### SEO Impact

**Moderate SEO value**: Clarifies page structure

**Benefits**:

- Helps distinguish site-wide header from content
- Site header signals consistent navigation
- Article headers provide metadata (author, date) for rich results

**Best practices for SEO**:

- Include site h1 in page header (site name/title)
- Place primary navigation in header
- Include schema markup for article headers (author, date)

### Browser Support

✅ Universal support in all modern browsers  
✅ Treated as `<div>` in legacy browsers

---

## `<footer>`

**Category**: Flow Content  
**Content Model**: Flow content (excluding footer and header)  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `contentinfo` (when child of body), `generic` (when nested)

### Purpose & Semantic Meaning

Represents **concluding content** for its nearest ancestor sectioning content or sectioning root. Typically contains author info, copyright, related links, or document metadata.

**Key characteristic**: Context-dependent footer for its parent section.

### When to Use

Use `<footer>` for:

- Site-wide footer (copyright, legal, footer navigation)
- Article footer (tags, categories, author bio)
- Section footer (section metadata, related links)
- Any concluding content for a sectioning element

**Multiple footers**: You can have multiple `<footer>` elements (page footer, article footers, section footers)

### When NOT to Use

❌ Don't use for:

- Generic containers at page bottom (use `<div>`)
- Content that isn't concluding information
- Nested inside another `<footer>`, `<header>`, or `<address>`

### Attributes

**Element-Specific**: None  
**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Flow content
- Contact information (`<address>`)
- Navigation (`<nav>`)
- Copyright notices, legal info
- Related links

**Cannot contain**:

- Another `<footer>` element
- `<header>` element
- `<main>` element

**Context behavior**:

- **Direct child of `<body>`**: Acts as page footer (contentinfo landmark)
- **Inside sectioning element**: Acts as section footer (not a landmark)

### Examples

#### Good Example: Site-Wide Footer

```html
<footer>
  <nav aria-label="Footer Navigation">
    <ul>
      <li><a href="/privacy">Privacy Policy</a></li>
      <li><a href="/terms">Terms of Service</a></li>
      <li><a href="/contact">Contact Us</a></li>
    </ul>
  </nav>

  <p>&copy; 2025 Company Name. All rights reserved.</p>

  <address>Contact: <a href="mailto:info@example.com">info@example.com</a></address>
</footer>
```

#### Good Example: Article Footer

```html
<article>
  <header>
    <h2>Article Title</h2>
    <p>By Jane Doe</p>
  </header>

  <p>Article content...</p>

  <footer>
    <p>
      Categories: <a href="/category/web-dev">Web Development</a>,
      <a href="/category/html">HTML</a>
    </p>
    <p>
      <a href="#comments">12 Comments</a> |
      <a href="#share">Share</a>
    </p>
  </footer>
</article>
```

#### Bad Example: Generic Bottom Container

```html
<!-- ❌ WRONG: Footer used just for positioning -->
<footer class="container">
  <div class="grid">
    <div>Random content</div>
  </div>
</footer>

<!-- ✅ CORRECT: Use div for layout -->
<div class="container">
  <div class="grid">
    <div>Random content</div>
  </div>
</div>
```

### Accessibility

**Implicit role**:

- `contentinfo` (page footer, direct child of body) - landmark role
- `generic` (sectioning content footer) - not a landmark

**Screen reader behavior**: Page footer announced as "contentinfo" landmark

**Best practices**:

- Only one page-level footer (contentinfo) per page
- Section footers provide metadata but aren't landmarks
- Include copyright and contact info in page footer
- Use `<address>` for contact information

### SEO Impact

**Low to moderate SEO value**: Signals secondary information

**Benefits**:

- Site footer links contribute to internal linking structure
- Copyright info validates authenticity
- Contact information improves local SEO

**Best practices for SEO**:

- Include footer navigation (sitemap links)
- Add copyright notice with current year
- Include contact information for local businesses
- Use `<address>` for structured contact data

### Browser Support

✅ Universal support in all modern browsers  
✅ Treated as `<div>` in legacy browsers

---

## `<aside>`

**Category**: Sectioning Content / Flow Content  
**Content Model**: Flow content  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `complementary`

### Purpose & Semantic Meaning

Represents content that is **tangentially related** to the content around it. Can be considered separate from but complementary to the main content.

**Key characteristic**: Related but not essential; could be removed without losing main content meaning.

### When to Use

Use `<aside>` for:

- Sidebars with related links or information
- Pull quotes or callout boxes
- Glossaries or definitions
- Related articles or "You may also like" sections
- Advertisements (contextually related)
- Author biography boxes

### When NOT to Use

❌ Don't use for:

- Main content (use `<article>` or `<section>`)
- Parenthetical content within main text (use `<small>` or parentheses)
- Navigation (use `<nav>`)
- Content essential to understanding main content

### Attributes

**Element-Specific**: None  
**Global Attributes**: All [global attributes](../global-attributes.md) apply

**Common attributes**:

- `aria-label` or `aria-labelledby` - Label the aside for screen readers

### Content Model Rules

**Can contain**:

- Flow content (headings, paragraphs, lists, etc.)
- Can include its own `<header>` and `<footer>`

**Cannot contain**:

- No specific restrictions

**Position**:

- Can be used at page level (sidebar)
- Can be used within `<article>` (related callout)

### Examples

#### Good Example: Sidebar with Related Content

```html
<main>
  <article>
    <h1>Main Article Title</h1>
    <p>Article content...</p>
  </article>

  <aside aria-label="Related Articles">
    <h2>You May Also Like</h2>
    <ul>
      <li><a href="/article1">Related Article 1</a></li>
      <li><a href="/article2">Related Article 2</a></li>
      <li><a href="/article3">Related Article 3</a></li>
    </ul>
  </aside>
</main>
```

#### Good Example: Pull Quote in Article

```html
<article>
  <h1>Understanding Web Accessibility</h1>

  <p>Accessibility is crucial for inclusive web design...</p>

  <aside>
    <blockquote>
      <p>
        "The power of the Web is in its universality. Access by everyone regardless of disability is
        an essential aspect."
      </p>
      <cite>— Tim Berners-Lee, W3C Director</cite>
    </blockquote>
  </aside>

  <p>Continuing with main content...</p>
</article>
```

#### Bad Example: Essential Navigation

```html
<!-- ❌ WRONG: Primary navigation is not complementary content -->
<aside>
  <nav>
    <a href="/">Home</a>
    <a href="/about">About</a>
  </nav>
</aside>

<!-- ✅ CORRECT: Use nav directly -->
<nav>
  <a href="/">Home</a>
  <a href="/about">About</a>
</nav>
```

### Accessibility

**Implicit role**: `complementary` landmark  
**Screen reader behavior**: Announced as "complementary" landmark

**Best practices**:

- Label with `aria-label` or `aria-labelledby` to describe aside purpose
- Include a heading to identify aside content
- Don't overuse - too many complementary landmarks confuse users
- Ensure aside content is truly optional/supplementary

```html
<aside aria-labelledby="glossary-heading">
  <h2 id="glossary-heading">Glossary</h2>
  <dl>
    <dt>HTML</dt>
    <dd>HyperText Markup Language</dd>
  </dl>
</aside>
```

### SEO Impact

**Low SEO value**: Secondary content with less search weight

**Benefits**:

- Related links improve internal linking structure
- Provides context to main content
- "Related articles" improve user engagement metrics

**Best practices for SEO**:

- Keep aside content relevant to main topic
- Use descriptive headings in aside
- Don't place critical SEO content only in aside

### Browser Support

✅ Universal support in all modern browsers  
✅ Treated as `<div>` in legacy browsers

---

## `<address>`

**Category**: Flow Content  
**Content Model**: Flow content (excluding certain elements)  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `group`

### Purpose & Semantic Meaning

Represents **contact information** for a person, organization, or document. Indicates how to reach the author or owner.

**Key characteristic**: Contact information, not physical addresses (unless they're contact addresses).

### When to Use

Use `<address>` for:

- Author contact info (email, phone, social)
- Business contact information
- Document author details
- Copyright holder contact information

**Context-dependent**:

- Inside `<article>`: Contact info for article author
- Inside `<body>` (outside article): Contact info for site/organization

### When NOT to Use

❌ Don't use for:

- Physical addresses that aren't contact points (use `<p>`)
- Arbitrary addresses in content (use `<p>`)
- Postal addresses in e-commerce checkout (use form fields)

**Rule**: Only use when the address is a way to **contact** the related person/organization.

### Attributes

**Element-Specific**: None  
**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Flow content (paragraphs, links, etc.)
- Text, links, line breaks

**Cannot contain**:

- Headings (h1-h6)
- Sectioning content (`<article>`, `<section>`, `<nav>`, `<aside>`)
- `<header>` or `<footer>`
- Another `<address>` element

**Typical content**: Email links, phone numbers, social media links, physical contact addresses

### Examples

#### Good Example: Site Footer Contact Info

```html
<footer>
  <address>
    <p>Contact us:</p>
    <p>Email: <a href="mailto:info@example.com">info@example.com</a></p>
    <p>Phone: <a href="tel:+15551234567">+1 (555) 123-4567</a></p>
    <p>
      123 Main Street, Suite 100<br />
      City, State 12345<br />
      United States
    </p>
  </address>

  <p>&copy; 2025 Company Name</p>
</footer>
```

#### Good Example: Article Author Contact

```html
<article>
  <header>
    <h1>Article Title</h1>
    <address>
      By <a href="mailto:jane@example.com">Jane Doe</a><br />
      Follow on Twitter: <a href="https://twitter.com/janedoe">@janedoe</a>
    </address>
  </header>

  <p>Article content...</p>
</article>
```

#### Bad Example: Random Address in Content

```html
<!-- ❌ WRONG: This address is not contact info -->
<p>The store is located at:</p>
<address>
  456 Market Street<br />
  Los Angeles, CA 90012
</address>

<!-- ✅ CORRECT: Use paragraph for non-contact addresses -->
<p>
  The store is located at:<br />
  456 Market Street<br />
  Los Angeles, CA 90012
</p>
```

### Accessibility

**Implicit role**: `group` (generic grouping)  
**Screen reader behavior**: Announced as group; address content read normally

**Best practices**:

- Use semantic links for email (`mailto:`) and phone (`tel:`)
- Break lines with `<br>` for multi-line addresses
- Place in context (footer for site contact, article header for author)

### SEO Impact

**Low SEO value**: Minimal direct SEO benefit

**Benefits**:

- Structured contact data can be extracted by search engines
- Local SEO benefits for businesses (NAP: Name, Address, Phone)
- Can support schema markup (Organization or Person schema)

**Best practices for SEO**:

- Use consistent NAP across web (local SEO)
- Include `mailto:` and `tel:` links for crawling
- Consider schema.org markup for structured contact data

### Browser Support

✅ Universal support in all modern browsers  
✅ Default styling: Italic text in most browsers

---

## Decision Tree: Choosing the Right Element

```
Is the content a standalone, independently distributable composition?
  YES → Use <article>

Is it thematic grouping with a common topic/purpose?
  YES → Does it have a heading describing the theme?
    YES → Use <section>
    NO  → Use <div>

Is it the primary/unique content of the page?
  YES → Use <main> (only one per page)

Is it introductory content (logo, heading, metadata)?
  YES → Use <header>

Is it concluding content (copyright, footer links, metadata)?
  YES → Use <footer>

Is it major navigation links?
  YES → Use <nav>

Is it tangentially related/complementary content?
  YES → Use <aside>

Is it contact information for a person/organization?
  YES → Use <address>

None of the above?
  → Use <div> for generic container
```

---

## Common Patterns & Combinations

### Pattern 1: Complete Page Structure

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Page Title</title>
  </head>
  <body>
    <header>
      <h1>Site Name</h1>
      <nav aria-label="Primary">
        <ul>
          <li><a href="/">Home</a></li>
          <li><a href="/about">About</a></li>
        </ul>
      </nav>
    </header>

    <main>
      <article>
        <header>
          <h2>Article Title</h2>
          <p>By Author Name</p>
        </header>

        <section>
          <h3>Section 1</h3>
          <p>Content...</p>
        </section>

        <footer>
          <p>Tags: HTML, Semantic</p>
        </footer>
      </article>
    </main>

    <aside aria-label="Related Links">
      <h2>Related Articles</h2>
      <ul>
        <li><a href="/article1">Article 1</a></li>
      </ul>
    </aside>

    <footer>
      <p>&copy; 2025 Company</p>
      <address>Contact: <a href="mailto:info@example.com">info@example.com</a></address>
    </footer>
  </body>
</html>
```

### Pattern 2: Blog Post with Comments

```html
<main>
  <article>
    <header>
      <h1>Blog Post Title</h1>
      <p>Published <time datetime="2025-11-03">November 3, 2025</time></p>
    </header>

    <p>Blog post content...</p>

    <footer>
      <p>Categories: <a href="/cat/web">Web Development</a></p>
    </footer>

    <!-- Comments are nested articles -->
    <section aria-labelledby="comments-heading">
      <h2 id="comments-heading">Comments</h2>

      <article>
        <header>
          <p><strong>User1</strong> commented:</p>
        </header>
        <p>Great post!</p>
      </article>

      <article>
        <header>
          <p><strong>User2</strong> commented:</p>
        </header>
        <p>Very informative.</p>
      </article>
    </section>
  </article>
</main>
```

### Pattern 3: Landing Page Sections

```html
<main>
  <section aria-labelledby="hero">
    <h1 id="hero">Welcome to Our Product</h1>
    <p>Transform your workflow...</p>
  </section>

  <section aria-labelledby="features">
    <h2 id="features">Features</h2>
    <div class="feature-grid">
      <article>
        <h3>Feature 1</h3>
        <p>Description...</p>
      </article>
      <article>
        <h3>Feature 2</h3>
        <p>Description...</p>
      </article>
    </div>
  </section>

  <section aria-labelledby="testimonials">
    <h2 id="testimonials">What Customers Say</h2>
    <article>
      <blockquote>
        <p>"This product changed my life!"</p>
        <footer>— Jane Smith, CEO</footer>
      </blockquote>
    </article>
  </section>
</main>
```

---

## Accessibility Quick Reference

| Element     | Implicit Role               | Landmark?          | Multiple Allowed? | Label Required?    |
| ----------- | --------------------------- | ------------------ | ----------------- | ------------------ |
| `<article>` | article                     | Yes                | Yes               | Recommended        |
| `<section>` | region (if labeled)         | Only if labeled    | Yes               | Yes (for landmark) |
| `<nav>`     | navigation                  | Yes                | Yes               | Yes (if multiple)  |
| `<main>`    | main                        | Yes                | **No** (one only) | No                 |
| `<header>`  | banner (if body child)      | Only at body level | Yes               | No                 |
| `<footer>`  | contentinfo (if body child) | Only at body level | Yes               | No                 |
| `<aside>`   | complementary               | Yes                | Yes               | Recommended        |
| `<address>` | group                       | No                 | Yes               | No                 |

---

## SEO Impact Summary

| Element     | SEO Value        | Primary Benefit                                 |
| ----------- | ---------------- | ----------------------------------------------- |
| `<article>` | **High**         | Signals primary content, supports rich results  |
| `<section>` | **Moderate**     | Improves content organization and outline       |
| `<nav>`     | **Moderate**     | Clarifies site structure and internal linking   |
| `<main>`    | **High**         | Identifies unique primary content               |
| `<header>`  | **Moderate**     | Provides document/section metadata              |
| `<footer>`  | **Low-Moderate** | Secondary info, but important for trust/contact |
| `<aside>`   | **Low**          | Supplementary content, less search weight       |
| `<address>` | **Low**          | Contact data, local SEO benefit                 |

---

## Further Reading

- **WHATWG HTML Specification**: [https://html.spec.whatwg.org/multipage/sections.html](https://html.spec.whatwg.org/multipage/sections.html)
- **MDN Web Docs - Semantic HTML**: [https://developer.mozilla.org/en-US/docs/Glossary/Semantics#semantics_in_html](https://developer.mozilla.org/en-US/docs/Glossary/Semantics#semantics_in_html)
- **W3C ARIA Authoring Practices**: [https://www.w3.org/WAI/ARIA/apg/](https://www.w3.org/WAI/ARIA/apg/)

---

**Last Updated**: Based on WHATWG HTML Living Standard (2025)
