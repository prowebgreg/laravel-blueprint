# Embedded Content Elements

Comprehensive reference for HTML elements that embed external content from other sources into the document.

## Overview

Embedded content elements allow integration of:

- **Third-party content** (YouTube videos, maps, social media)
- **External documents** (PDFs, other HTML pages)
- **Plugins** (Flash - obsolete, Java applets - obsolete)
- **Interactive content** (games, applications)
- **Cross-origin resources** (with security considerations)

Key considerations:

- **Security**: XSS risks, clickjacking, data leakage
- **Performance**: Embedded content affects page load time
- **Accessibility**: Screen readers may have difficulty with embedded content
- **Privacy**: Third-party embeds can track users

**Core Principle**: Use embedded content judiciously with proper security attributes and fallback content.

---

## `<iframe>`

**Category**: Flow Content, Phrasing Content, Embedded Content, Interactive Content  
**Content Model**: Nothing (void-like, but see fallback content)  
**Permitted Parents**: Any element that accepts embedded content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None (or `application`, `document`, `img`)

### Purpose & Semantic Meaning

Represents a **nested browsing context**, embedding another HTML document within the current page. Creates an independent document environment.

**Key characteristic**: Displays external or internal HTML documents as a rectangle within the page.

### When to Use

Use `<iframe>` for:

- Embedding YouTube/Vimeo videos
- Embedding Google Maps
- Third-party widgets (social media feeds, comments)
- Sandboxed untrusted content
- Cross-domain content integration
- Web-based applications (editor, preview)
- Payment forms (PCI compliance)

### When NOT to Use

❌ Don't use for:

- Navigation between pages (use `<a>` links)
- Layout structure (use CSS Grid or Flexbox)
- Including same-site content (use AJAX or components instead)
- When iframe content can be native HTML instead

### Attributes

**Element-Specific**:

- `src` - URL of page to embed
- `srcdoc` - Inline HTML content (overrides `src`)
- `name` - Name for targeting (e.g., `target="frameName"`)
- `sandbox` - Security restrictions (allows specific capabilities)
- `allow` - Permissions Policy (feature policy)
- `loading` - Lazy loading (`lazy` or `eager`)
- `width`, `height` - Dimensions (use CSS instead when possible)
- `referrerpolicy` - Referrer header behavior

**Deprecated attributes**:

- `frameborder`, `scrolling`, `marginwidth`, `marginheight` - Use CSS instead

**Global Attributes**: All [global attributes](../global-attributes.md) apply

**Important**: `title` attribute highly recommended for accessibility.

### Sandbox Attribute

**Default behavior** (empty sandbox): Maximum restrictions

- Blocks JavaScript
- Blocks forms
- Blocks popups
- Blocks plugins
- Blocks same-origin access
- Blocks downloads
- Blocks pointer lock
- Treats origin as unique

**Sandbox values** (space-separated, grant permissions):

- `allow-same-origin` - Treat content as from same origin (enables storage access)
- `allow-scripts` - Allow JavaScript execution
- `allow-forms` - Allow form submission
- `allow-popups` - Allow popups (window.open, target="\_blank")
- `allow-popups-to-escape-sandbox` - Popups don't inherit sandbox
- `allow-top-navigation` - Allow navigating top-level context
- `allow-modals` - Allow modal dialogs (alert, confirm, prompt)
- `allow-downloads` - Allow downloads
- `allow-presentation` - Allow Presentation API

**Warning**: `allow-same-origin` + `allow-scripts` = same as no sandbox (security risk)

### Allow Attribute (Permissions Policy)

Controls browser features iframe can access:

```html
<iframe src="..." allow="camera; microphone; geolocation"></iframe>
```

**Common features**:

- `camera` - Camera access
- `microphone` - Microphone access
- `geolocation` - Geolocation API
- `fullscreen` - Fullscreen mode
- `payment` - Payment Request API
- `autoplay` - Autoplay media
- `encrypted-media` - Encrypted Media Extensions
- `picture-in-picture` - Picture-in-Picture
- `accelerometer`, `gyroscope`, `magnetometer` - Device sensors
- `usb`, `bluetooth` - Device hardware

**Syntax**: `feature 'origin'` or just `feature` (all origins)

```html
<iframe src="..." allow="geolocation 'self'; camera 'none'"></iframe>
```

### Content Model Rules

**Can contain**:

- Fallback content (displayed if iframe not supported)
- Text and elements shown when iframe fails to load

**Note**: Modern browsers support iframes, so fallback rarely displays

### Examples

#### Good Example: YouTube Video Embed

```html
<iframe
  width="560"
  height="315"
  src="https://www.youtube-nocookie.com/embed/VIDEO_ID"
  title="Video Title"
  frameborder="0"
  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
  allowfullscreen
  loading="lazy"
>
</iframe>
```

**Better with CSS and lazy loading**:

```html
<div class="video-container">
  <iframe
    src="https://www.youtube-nocookie.com/embed/VIDEO_ID"
    title="How to Build a Website"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
    allowfullscreen
    loading="lazy"
  >
  </iframe>
</div>

<style>
  .video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    height: 0;
    overflow: hidden;
  }

  .video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
  }
</style>
```

#### Good Example: Google Maps Embed

```html
<iframe
  src="https://www.google.com/maps/embed?pb=!1m18!1m12!..."
  width="600"
  height="450"
  style="border:0;"
  allowfullscreen=""
  loading="lazy"
  referrerpolicy="no-referrer-when-downgrade"
  title="Company Location Map"
>
</iframe>
```

#### Good Example: Sandboxed User Content

```html
<iframe
  src="user-generated-content.html"
  sandbox="allow-scripts"
  title="User Generated Content"
  loading="lazy"
>
  <p>Your browser does not support iframes.</p>
</iframe>
```

#### Good Example: Sandboxed Payment Form

```html
<iframe
  src="https://secure.payment-provider.com/checkout"
  sandbox="allow-forms allow-scripts allow-same-origin"
  allow="payment"
  title="Secure Payment Form"
  style="width: 100%; height: 500px; border: none;"
>
</iframe>
```

#### Good Example: Inline HTML Content

```html
<iframe srcdoc="<p>Hello from inline HTML!</p>" title="Inline Content"></iframe>
```

#### Good Example: Lazy Loading

```html
<!-- Load immediately (above fold) -->
<iframe src="important.html" loading="eager" title="Important Content"></iframe>

<!-- Load when near viewport (below fold) -->
<iframe src="video.html" loading="lazy" title="Video Player"></iframe>
```

#### Bad Example: No Title

```html
<!-- ❌ WRONG: Missing title (inaccessible) -->
<iframe src="https://example.com"></iframe>

<!-- ✅ CORRECT: Always include descriptive title -->
<iframe src="https://example.com" title="Descriptive Title"></iframe>
```

#### Bad Example: Unsecured Third-Party Content

```html
<!-- ❌ WRONG: No sandbox for untrusted content -->
<iframe src="https://untrusted-site.com"></iframe>

<!-- ✅ CORRECT: Sandbox untrusted content -->
<iframe
  src="https://untrusted-site.com"
  sandbox="allow-scripts"
  title="Third Party Content"
></iframe>
```

#### Bad Example: Inline Styles for Dimensions

```html
<!-- ❌ WRONG: Inline styles, deprecated attributes -->
<iframe src="..." width="600" height="400" frameborder="0" scrolling="no"></iframe>

<!-- ✅ CORRECT: CSS for styling -->
<iframe src="..." style="width: 100%; height: 400px; border: 0;" title="Content"></iframe>
```

### Accessibility

**Low accessibility**: iframes can be problematic for screen readers

**Best practices**:

- **Always include `title` attribute** (describes iframe purpose)
- Make title descriptive and unique
- Consider if iframe is necessary (native HTML often better)
- Ensure iframe content itself is accessible
- Provide fallback content

```html
<iframe
  src="video.html"
  title="Product demonstration video: How to assemble the XYZ widget"
  allow="fullscreen"
>
  <p>
    Your browser does not support iframes.
    <a href="video.html">Watch the video in a new window</a>.
  </p>
</iframe>
```

**Screen reader behavior**:

- Announces iframe by title
- May announce "frame" or "iframe"
- User can choose to enter or skip iframe

### Security Considerations

**Clickjacking Protection**:

```html
<!-- HTTP Header (server-side) -->
X-Frame-Options: DENY
<!-- or -->
X-Frame-Options: SAMEORIGIN
<!-- or -->
Content-Security-Policy: frame-ancestors 'self'
```

**Sandbox Security**:

```html
<!-- Most restrictive (no capabilities) -->
<iframe src="..." sandbox></iframe>

<!-- Allow only specific capabilities -->
<iframe src="..." sandbox="allow-scripts allow-forms"></iframe>

<!-- ⚠️ DANGER: Same as no sandbox -->
<iframe src="..." sandbox="allow-same-origin allow-scripts"></iframe>
```

**Permissions Policy**:

```html
<!-- Restrict features -->
<iframe src="..." allow="camera 'none'; microphone 'none'"></iframe>

<!-- Allow specific features -->
<iframe src="..." allow="geolocation 'self'; payment"></iframe>
```

### SEO Impact

**Low SEO value**: Content inside iframes not indexed by search engines

**Considerations**:

- Iframe content not attributed to parent page
- Links inside iframe don't pass PageRank
- Use native HTML when possible for SEO
- Google can crawl iframe src URL separately

**Best practice**: Don't use iframes for primary content you want indexed.

### Performance Impact

**High performance cost**: Each iframe is a separate document

**Considerations**:

- Each iframe = separate HTTP request + rendering context
- Blocks page load until iframe loads
- Use `loading="lazy"` for below-fold iframes
- Consider intersection observer for manual lazy loading

**Lazy loading example**:

```html
<!-- Native lazy loading -->
<iframe src="..." loading="lazy" title="Below Fold Content"></iframe>

<!-- Manual lazy loading with Intersection Observer -->
<iframe data-src="expensive-content.html" class="lazy-iframe" title="Lazy Loaded Content"> </iframe>

<script>
  const lazyIframes = document.querySelectorAll('.lazy-iframe');

  const iframeObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const iframe = entry.target;
        iframe.src = iframe.dataset.src;
        iframeObserver.unobserve(iframe);
      }
    });
  });

  lazyIframes.forEach((iframe) => iframeObserver.observe(iframe));
</script>
```

### Browser Support

✅ Universal support in all browsers  
✅ `sandbox` attribute: IE10+, all modern browsers  
✅ `loading` attribute: Chrome 77+, Edge 79+, Firefox 121+, Safari 16.4+  
✅ Permissions Policy (`allow`): Modern browsers

---

## `<embed>`

**Category**: Flow Content, Phrasing Content, Embedded Content, Interactive Content  
**Content Model**: Empty (void element)  
**Permitted Parents**: Any element that accepts embedded content  
**Tag Omission**: No end tag (void element)  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Embeds **external content** from a plugin or external application. Historically used for Flash, Java applets, now primarily for PDFs and multimedia.

**Key characteristic**: Plugin-based content integration (largely obsolete).

### When to Use

Use `<embed>` for:

- PDF documents (though `<object>` or `<iframe>` often better)
- SVG files (though inline `<svg>` better)
- Legacy plugin content (rare)

**Note**: Flash and Java applets are obsolete. Modern web uses native HTML5 APIs.

### When NOT to Use

❌ Don't use for:

- Images (use `<img>`)
- Videos (use `<video>`)
- Audio (use `<audio>`)
- HTML pages (use `<iframe>`)
- Flash content (Flash is dead since 2020)

**Recommendation**: Use `<object>` with `<embed>` as fallback if needed.

### Attributes

**Element-Specific**:

- `src` - URL of embedded resource (required)
- `type` - MIME type of embedded content
- `width`, `height` - Dimensions

**Global Attributes**: All [global attributes](../global-attributes.md) apply

**Custom attributes**: Plugin-specific parameters can be added

### Examples

#### Good Example: PDF Embed

```html
<embed
  src="/documents/report.pdf"
  type="application/pdf"
  width="100%"
  height="600"
  title="Annual Report"
/>
```

**Better alternative using `<object>`**:

```html
<object
  data="/documents/report.pdf"
  type="application/pdf"
  width="100%"
  height="600"
  title="Annual Report"
>
  <p>
    Your browser doesn't support PDF embedding.
    <a href="/documents/report.pdf">Download the PDF</a>.
  </p>
</object>
```

#### Good Example: SVG Embed (Legacy)

```html
<embed src="diagram.svg" type="image/svg+xml" width="400" height="300" title="Diagram" />
```

**Better alternative**:

```html
<!-- Inline SVG (best for interactivity and styling) -->
<svg width="400" height="300">
  <!-- SVG content -->
</svg>

<!-- Or as image (if no interactivity needed) -->
<img src="diagram.svg" alt="Diagram" width="400" height="300" />
```

#### Bad Example: Video (Obsolete)

```html
<!-- ❌ WRONG: Don't use embed for video -->
<embed src="video.mp4" width="640" height="480" />

<!-- ✅ CORRECT: Use <video> -->
<video width="640" height="480" controls>
  <source src="video.mp4" type="video/mp4" />
  <source src="video.webm" type="video/webm" />
  Your browser doesn't support the video tag.
</video>
```

### Accessibility

**Low accessibility**: No fallback content mechanism

**Best practices**:

- Include `title` attribute
- Prefer `<object>` (supports fallback content)
- Provide text alternative or link to content

### SEO Impact

**No SEO value**: Content inside embed not indexed

### Browser Support

✅ Universal support for PDFs and images  
⚠️ Plugin support varies (Flash/Java no longer supported)

---

## `<object>`

**Category**: Flow Content, Phrasing Content, Embedded Content, Interactive Content  
**Content Model**: Zero or more `<param>` elements, then transparent content  
**Permitted Parents**: Any element that accepts embedded content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Represents an **external resource**, which can be treated as an image, a nested browsing context, or a resource to be processed by a plugin.

**Key characteristic**: Supports fallback content and parameters.

### When to Use

Use `<object>` for:

- PDF documents (with fallback)
- SVG images (with fallback)
- Flash content (obsolete, but with fallback)
- Multimedia requiring parameters
- Any embedded content needing fallback

### When NOT to Use

❌ Don't use for:

- Images (use `<img>` unless fallback needed)
- Videos (use `<video>`)
- Audio (use `<audio>`)
- HTML pages (use `<iframe>`)

### Attributes

**Element-Specific**:

- `data` - URL of resource
- `type` - MIME type of resource
- `width`, `height` - Dimensions
- `name` - Name for form submission or scripting
- `form` - Associates object with form (form ID)
- `typemustmatch` - `type` and actual content-type must match

**Deprecated**: `usemap`, `declare`, `standby`, `archive`, `classid`, `codebase`, `codetype`

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Zero or more `<param>` elements (must come first)
- Fallback content (transparent - any content allowed in parent)

**Fallback behavior**: If object can't be rendered, fallback content displays

### Examples

#### Good Example: PDF with Fallback

```html
<object
  data="/documents/annual-report.pdf"
  type="application/pdf"
  width="100%"
  height="800"
  title="Annual Report 2025"
>
  <p>
    It appears you don't have a PDF plugin for this browser.
    <a href="/documents/annual-report.pdf">Click here to download the PDF</a>.
  </p>
</object>
```

#### Good Example: SVG with Image Fallback

```html
<object data="logo.svg" type="image/svg+xml" width="200" height="100" title="Company Logo">
  <img src="logo.png" alt="Company Logo" width="200" height="100" />
</object>
```

#### Good Example: Nested Fallbacks

```html
<object data="video.mp4" type="video/mp4" width="640" height="480">
  <!-- First fallback: Flash (obsolete, but example of pattern) -->
  <object data="video.swf" type="application/x-shockwave-flash" width="640" height="480">
    <param name="movie" value="video.swf" />
    <!-- Second fallback: Download link -->
    <p>
      Your browser doesn't support this video format.
      <a href="video.mp4">Download the video</a>.
    </p>
  </object>
</object>

<!-- Modern approach: Just use <video> -->
<video width="640" height="480" controls>
  <source src="video.mp4" type="video/mp4" />
  <source src="video.webm" type="video/webm" />
  <p>
    Your browser doesn't support the video tag.
    <a href="video.mp4">Download the video</a>.
  </p>
</video>
```

#### Good Example: Interactive Map

```html
<object
  data="interactive-map.svg"
  type="image/svg+xml"
  width="100%"
  height="600"
  title="Interactive Campus Map"
>
  <img src="static-map.png" alt="Campus Map" width="800" height="600" />
</object>
```

### Accessibility

**Moderate accessibility**: Supports fallback content

**Best practices**:

- Always include `title` attribute
- Provide meaningful fallback content
- Ensure fallback is accessible (alt text, descriptions)
- Test with screen readers

```html
<object data="chart.svg" type="image/svg+xml" title="Sales Chart">
  <table>
    <caption>
      Q3 Sales Data
    </caption>
    <thead>
      <tr>
        <th scope="col">Month</th>
        <th scope="col">Sales</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th scope="row">July</th>
        <td>$45,000</td>
      </tr>
      <!-- More data -->
    </tbody>
  </table>
</object>
```

### SEO Impact

**Low SEO value**: Fallback content may be indexed

**Best practice**: Include semantic fallback content for SEO

### Browser Support

✅ Universal support for images and PDFs  
⚠️ Plugin support ended (Flash, Java)

---

## `<param>`

**Category**: None (object child only)  
**Content Model**: Empty (void element)  
**Permitted Parents**: `<object>` (must be before any other content)  
**Tag Omission**: No end tag (void element)  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Defines **parameters** for an `<object>` element. Used to pass configuration to plugins.

**Key characteristic**: Configures object behavior.

### When to Use

Use `<param>` for:

- Plugin parameters (Flash - obsolete)
- Object configuration
- Resource-specific settings

**Note**: Rarely used in modern web development (plugins are obsolete).

### Attributes

**Element-Specific**:

- `name` - Parameter name (required)
- `value` - Parameter value (required)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Examples

#### Example: Flash Parameters (Historical)

```html
<object data="animation.swf" type="application/x-shockwave-flash" width="550" height="400">
  <param name="movie" value="animation.swf" />
  <param name="quality" value="high" />
  <param name="bgcolor" value="#FFFFFF" />
  <param name="play" value="true" />
  <param name="loop" value="true" />
  <p>Flash is no longer supported. <a href="video.mp4">Watch video instead</a>.</p>
</object>
```

**Modern equivalent**: Use `<video>` with HTML5 video controls.

### Accessibility

**No accessibility impact**: Parameters are configuration, not content

### SEO Impact

**No SEO value**: Parameters are not content

### Browser Support

✅ Universal support (but plugins themselves no longer supported)

---

## `<portal>` (Experimental)

**Category**: Flow Content, Embedded Content  
**Content Model**: Nothing  
**Permitted Parents**: Any element that accepts embedded content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None  
**Status**: ⚠️ Experimental - Not standardized

### Purpose & Semantic Meaning

Enables embedding another HTML page, similar to `<iframe>`, but designed for **seamless navigation** between pages. Allows "preview" of destination page.

**Key characteristic**: Instant navigation by "activating" portal to replace current page.

### When to Use (Future)

Potential use cases:

- Preview links before navigation
- Infinite scroll with full pages
- SPA-like navigation between separate documents
- Pre-render next page in carousel

### Attributes

**Element-Specific**:

- `src` - URL of page to embed
- `referrerpolicy` - Referrer policy

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Examples

#### Experimental Example: Portal Navigation

```html
<!-- Portal embeds page for preview -->
<portal id="myPortal" src="/next-page.html"></portal>

<button onclick="activatePortal()">Go to Next Page</button>

<script>
  function activatePortal() {
    const portal = document.getElementById('myPortal');
    // Activating portal navigates to embedded page
    portal.activate();
  }

  // Listen for portal activation
  window.addEventListener('portalactivate', (e) => {
    console.log('Portal was activated from:', e.adoptPredecessor());
  });
</script>
```

#### Experimental Example: Infinite Scroll

```html
<article>
  <h1>Article 1</h1>
  <p>Content...</p>
</article>

<!-- Next article embedded as portal -->
<portal src="/article-2.html" id="nextArticle"></portal>

<script>
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      document.getElementById('nextArticle').activate();
    }
  });

  observer.observe(document.getElementById('nextArticle'));
</script>
```

### Accessibility

**Unknown**: Specification still in development

### SEO Impact

**Unknown**: Too early to determine

### Browser Support

⚠️ **Extremely limited**: Chrome 85+ with flag enabled  
⚠️ **Not in Firefox, Safari, or Edge yet**  
⚠️ **Experimental API - subject to change**

**Do not use in production**.

---

## Embedded Content Best Practices

### 1. Security First

**Always sandbox untrusted content**:

```html
<iframe src="untrusted.com" sandbox="allow-scripts" title="Third Party"></iframe>
```

**Use Permissions Policy**:

```html
<iframe src="..." allow="camera 'none'; microphone 'none'" title="Content"></iframe>
```

**Set CSP headers**:

```
Content-Security-Policy: frame-ancestors 'self'
```

### 2. Accessibility

**Always include titles**:

```html
<iframe src="..." title="Descriptive title of iframe content"></iframe>
```

**Provide fallbacks**:

```html
<object data="content.pdf" type="application/pdf">
  <p><a href="content.pdf">Download PDF</a></p>
</object>
```

### 3. Performance

**Lazy load below-fold embeds**:

```html
<iframe src="..." loading="lazy" title="Below Fold Content"></iframe>
```

**Use facades for heavy embeds** (YouTube, etc.):

```html
<!-- Show thumbnail, load iframe on click -->
<div class="video-facade" onclick="loadVideo(this)">
  <img src="thumbnail.jpg" alt="Video thumbnail" />
  <button>Play Video</button>
</div>

<script>
  function loadVideo(facade) {
    const iframe = document.createElement('iframe');
    iframe.src = 'https://youtube.com/embed/VIDEO_ID';
    iframe.allow = 'autoplay; encrypted-media';
    iframe.allowfullscreen = true;
    facade.replaceWith(iframe);
  }
</script>
```

### 4. SEO

**Don't put important content in iframes** (not indexed)

**Use native HTML when possible**:

```html
<!-- ❌ Iframe for content -->
<iframe src="about.html" title="About"></iframe>

<!-- ✅ Native HTML -->
<section>
  <h2>About</h2>
  <p>Content...</p>
</section>
```

### 5. Responsive Embeds

**Aspect ratio containers**:

```html
<div class="embed-container">
  <iframe src="..." title="Content"></iframe>
</div>

<style>
  .embed-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
  }

  .embed-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }
</style>
```

### 6. Privacy

**Use privacy-enhanced embeds**:

```html
<!-- YouTube: Use youtube-nocookie.com -->
<iframe src="https://www.youtube-nocookie.com/embed/VIDEO_ID"></iframe>

<!-- Block third-party cookies in iframe -->
<iframe src="..." sandbox="allow-scripts" title="Content"></iframe>
```

---

## Common Embed Patterns

### YouTube Video (Privacy-Enhanced)

```html
<div class="video-container">
  <iframe
    src="https://www.youtube-nocookie.com/embed/VIDEO_ID"
    title="Video Title"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
    allowfullscreen
    loading="lazy"
  >
  </iframe>
</div>

<style>
  .video-container {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
  }

  .video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
  }
</style>
```

### Google Maps

```html
<iframe
  src="https://www.google.com/maps/embed?pb=..."
  width="600"
  height="450"
  style="border:0;"
  allowfullscreen=""
  loading="lazy"
  referrerpolicy="no-referrer-when-downgrade"
  title="Office Location"
>
</iframe>
```

### PDF Viewer

```html
<object
  data="/document.pdf"
  type="application/pdf"
  width="100%"
  height="800"
  title="Document Title"
>
  <p>
    Your browser doesn't support PDFs.
    <a href="/document.pdf">Download the PDF</a>.
  </p>
</object>
```

### Social Media Embed (Twitter)

```html
<!-- Use Twitter's official embed code -->
<blockquote class="twitter-tweet">
  <p lang="en" dir="ltr">Tweet text...</p>
  &mdash; Account (@username) <a href="https://twitter.com/username/status/ID">Date</a>
</blockquote>
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
```

---

## Security Checklist

When embedding third-party content:

- [ ] Use `sandbox` attribute with minimal permissions
- [ ] Use `allow` attribute to restrict features
- [ ] Include `title` attribute for accessibility
- [ ] Set `referrerpolicy` appropriately
- [ ] Use HTTPS URLs only
- [ ] Implement CSP headers on server
- [ ] Test with different user agents
- [ ] Monitor for security vulnerabilities
- [ ] Consider privacy implications
- [ ] Provide fallback content

---

## Further Reading

- WHATWG HTML Standard - Embedded Content: https://html.spec.whatwg.org/multipage/iframe-embed-object.html
- MDN Web Docs - `<iframe>`: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/iframe
- MDN Web Docs - Permissions Policy: https://developer.mozilla.org/en-US/docs/Web/HTTP/Permissions_Policy
- OWASP - Clickjacking Defense: https://cheatsheetseries.owasp.org/cheatsheets/Clickjacking_Defense_Cheat_Sheet.html
