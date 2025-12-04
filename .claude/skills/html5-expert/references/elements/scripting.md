# Scripting Elements

Comprehensive reference for HTML elements related to JavaScript execution, dynamic content, and web components.

## Overview

Scripting elements enable:

- **JavaScript execution** (`<script>`)
- **Fallback content** for non-JavaScript environments (`<noscript>`)
- **Reusable HTML templates** (`<template>`)
- **Web Components** (`<slot>`, custom elements)
- **Dynamic content generation**
- **Progressive enhancement**

Key considerations:

- **Performance**: Script loading affects page render
- **Security**: XSS vulnerabilities, CSP policies
- **Accessibility**: JavaScript should enhance, not require
- **Progressive enhancement**: Core functionality without JavaScript

**Core Principle**: Use JavaScript to enhance user experience, not as a requirement for basic functionality.

---

## `<script>`

**Category**: Metadata Content, Flow Content, Phrasing Content  
**Content Model**: If `src` present: empty or contains only documentation comments. Otherwise: text (JavaScript code)  
**Permitted Parents**: Any element that accepts metadata, phrasing, or flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Embeds or references **JavaScript code** to be executed in the page. Can contain inline JavaScript or link to external script files.

**Key characteristic**: Enables dynamic behavior and interactivity.

### When to Use

Use `<script>` for:

- Loading external JavaScript files
- Inline JavaScript code
- Structured data (JSON-LD)
- Module scripts (ES6 modules)
- Web Workers
- Service Workers registration
- Analytics and tracking

### When NOT to Use

❌ Don't use for:

- Styling (use CSS)
- Static content (use HTML)
- Server-side logic (use server-side language)

### Attributes

**Element-Specific**:

- `src` - URL of external script file
- `type` - Script MIME type
  - `text/javascript` (default, can be omitted)
  - `module` - ES6 module
  - `importmap` - Import map for modules
  - `application/ld+json` - Structured data (not executed)
- `async` - Load asynchronously, execute when ready (external scripts only)
- `defer` - Load asynchronously, execute after DOM parsed (external scripts only)
- `crossorigin` - CORS settings for external scripts
- `integrity` - Subresource Integrity (SRI) hash
- `nonce` - Cryptographic nonce for CSP
- `referrerpolicy` - Referrer header behavior
- `blocking` - Blocks rendering (`render` value)

**Deprecated**:

- `charset` - Use UTF-8 in HTTP headers instead
- `language` - Obsolete

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Script Loading Strategies

#### 1. Default (Blocking)

```html
<!-- Blocks HTML parsing, executes immediately -->
<script src="script.js"></script>
```

**Behavior**:

- Stops HTML parsing
- Downloads script
- Executes immediately
- Resumes HTML parsing

**Use for**: Critical scripts needed before page render

#### 2. Defer (Non-blocking, Order Preserved)

```html
<!-- Downloads in parallel, executes after HTML parsing -->
<script src="script.js" defer></script>
```

**Behavior**:

- Downloads in parallel with HTML parsing
- Executes after DOM ready, before `DOMContentLoaded`
- Maintains script order (multiple defer scripts)

**Use for**: Scripts that need full DOM (most scripts)

#### 3. Async (Non-blocking, Execute ASAP)

```html
<!-- Downloads in parallel, executes when ready -->
<script src="script.js" async></script>
```

**Behavior**:

- Downloads in parallel with HTML parsing
- Executes as soon as downloaded (pauses parsing)
- No guaranteed order (multiple async scripts)

**Use for**: Independent scripts (analytics, ads)

#### 4. Module (ES6 Modules)

```html
<!-- ES6 module - deferred by default -->
<script type="module" src="module.js"></script>
```

**Behavior**:

- Deferred by default (like `defer`)
- Supports `import` and `export`
- Isolated scope (not global)
- Can use `async` with modules

**Use for**: Modern JavaScript with modules

### Loading Comparison

| Attribute       | Download | Execution | Order          | Use Case              |
| --------------- | -------- | --------- | -------------- | --------------------- |
| (none)          | Blocks   | Immediate | Guaranteed     | Critical scripts      |
| `defer`         | Parallel | After DOM | Guaranteed     | DOM-dependent scripts |
| `async`         | Parallel | ASAP      | Not guaranteed | Independent scripts   |
| `type="module"` | Parallel | After DOM | Guaranteed     | ES6 modules           |

### Examples

#### Good Example: External Script with Defer

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Page Title</title>

    <!-- Defer: loads after DOM ready -->
    <script src="/scripts/app.js" defer></script>
  </head>
  <body>
    <h1>Hello World</h1>
    <!-- DOM is ready when app.js executes -->
  </body>
</html>
```

#### Good Example: Async for Analytics

```html
<!-- Analytics loads independently, doesn't block page -->
<script src="https://www.google-analytics.com/analytics.js" async></script>
```

#### Good Example: ES6 Module

```html
<!-- Module script -->
<script type="module">
  import { init } from './modules/app.js';
  init();
</script>

<!-- External module -->
<script type="module" src="/modules/main.js"></script>
```

#### Good Example: Import Map

```html
<!-- Define module specifiers -->
<script type="importmap">
  {
    "imports": {
      "lodash": "https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js",
      "utils/": "/scripts/utils/"
    }
  }
</script>

<!-- Use mapped imports -->
<script type="module">
  import _ from 'lodash';
  import { helper } from 'utils/helper.js';
</script>
```

#### Good Example: Inline Script

```html
<script>
  // Inline JavaScript
  document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM ready!');
  });
</script>
```

#### Good Example: JSON-LD Structured Data

```html
<!-- Not executed as JavaScript, just data -->
<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "Article Title",
    "author": {
      "@type": "Person",
      "name": "Author Name"
    }
  }
</script>
```

#### Good Example: Subresource Integrity (SRI)

```html
<!-- Verify script hasn't been tampered with -->
<script
  src="https://cdn.example.com/library.js"
  integrity="sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K/uxy9rx7HNQlGYl1kPzQho1wx4JwY8wC"
  crossorigin="anonymous"
></script>
```

#### Good Example: Content Security Policy with Nonce

```html
<!-- HTTP Header: Content-Security-Policy: script-src 'nonce-random123' -->
<script nonce="random123">
  // This script is allowed by nonce
  console.log('Secure script');
</script>
```

#### Bad Example: Multiple Blocking Scripts

```html
<!-- ❌ WRONG: Blocks rendering multiple times -->
<head>
  <script src="script1.js"></script>
  <script src="script2.js"></script>
  <script src="script3.js"></script>
</head>

<!-- ✅ CORRECT: Defer or bundle scripts -->
<head>
  <script src="bundle.js" defer></script>
  <!-- Or multiple defer scripts -->
  <script src="script1.js" defer></script>
  <script src="script2.js" defer></script>
  <script src="script3.js" defer></script>
</head>
```

#### Bad Example: Async for Dependent Scripts

```html
<!-- ❌ WRONG: script2.js may execute before script1.js -->
<script src="script1.js" async></script>
<script src="script2.js" async></script>
<!-- depends on script1 -->

<!-- ✅ CORRECT: Use defer for dependent scripts -->
<script src="script1.js" defer></script>
<script src="script2.js" defer></script>
```

### Accessibility

**Indirect accessibility impact**: JavaScript should enhance, not break accessibility

**Best practices**:

- Ensure core functionality works without JavaScript
- Provide `<noscript>` fallback for critical content
- Use ARIA attributes appropriately
- Maintain focus management
- Support keyboard navigation

### Security

**Critical security considerations**:

1. **XSS Prevention**:

```html
<!-- ❌ NEVER: Inject user input directly -->
<script>
  const userInput = '<%= userInput %>';
  document.write(userInput); // XSS vulnerability
</script>

<!-- ✅ CORRECT: Sanitize and use safe APIs -->
<script>
  const userInput = document.getElementById('input').value;
  const sanitized = DOMPurify.sanitize(userInput);
  element.textContent = sanitized;
</script>
```

2. **Content Security Policy**:

```html
<!-- HTTP Header -->
Content-Security-Policy: script-src 'self' https://trusted-cdn.com
```

3. **Subresource Integrity**:

```html
<!-- Verify CDN scripts haven't been modified -->
<script
  src="https://cdn.example.com/lib.js"
  integrity="sha384-..."
  crossorigin="anonymous"
></script>
```

### SEO Impact

**Minimal direct SEO value**: JavaScript executed, not indexed

**Considerations**:

- Google can execute JavaScript, but with limitations
- Core content should be in HTML (server-side rendering)
- Critical rendering path affects rankings
- Page speed impacted by JavaScript size/execution

### Performance Best Practices

1. **Load scripts efficiently**:

```html
<!-- Critical: inline and small -->
<script>
  // Critical above-the-fold code
</script>

<!-- Important: defer -->
<script src="app.js" defer></script>

<!-- Non-critical: async -->
<script src="analytics.js" async></script>
```

2. **Code splitting**:

```html
<!-- Load only what's needed -->
<script type="module">
  const { feature } = await import('./features/feature.js');
  feature.init();
</script>
```

3. **Lazy loading**:

```html
<script>
  // Load heavy feature when needed
  button.addEventListener('click', async () => {
    const module = await import('./heavy-feature.js');
    module.init();
  });
</script>
```

### Browser Support

✅ Universal support for basic `<script>`  
✅ `defer`, `async`: IE10+, all modern browsers  
✅ `type="module"`: Chrome 61+, Edge 16+, Firefox 60+, Safari 11+  
✅ Import maps: Chrome 89+, Edge 89+, Firefox 108+, Safari 16.4+

---

## `<noscript>`

**Category**: Metadata Content (in `<head>`), Flow Content, Phrasing Content  
**Content Model**: When in `<head>`: zero or more `<link>`, `<style>`, `<meta>` elements. When in `<body>`: transparent content  
**Permitted Parents**: If metadata content: `<head>`. If flow content: where flow content expected  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Defines **fallback content** to display when JavaScript is disabled or not supported. Provides alternative content or instructions.

**Key characteristic**: Content only visible when scripts are disabled.

### When to Use

Use `<noscript>` for:

- Critical instructions for non-JS users
- Alternative content when JS is required
- Loading CSS for non-JS scenarios
- Accessibility fallbacks
- Progressive enhancement messages

### When NOT to Use

❌ Don't use for:

- Forcing users to enable JavaScript (provide alternative)
- Hiding content from JavaScript users
- As primary content (use HTML with JS enhancement)

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**In `<head>`** (metadata context):

- Can only contain `<link>`, `<style>`, `<meta>`

**In `<body>`** (flow context):

- Can contain any flow content

### Examples

#### Good Example: Critical Functionality Notice

```html
<noscript>
  <div class="alert">
    <strong>JavaScript Required:</strong>
    This application requires JavaScript to function properly. Please enable JavaScript in your
    browser settings.
  </div>
</noscript>
```

#### Good Example: Alternative Content

```html
<!-- JavaScript-powered image gallery -->
<div id="gallery">
  <!-- JS will populate this -->
</div>

<noscript>
  <!-- Fallback: simple list of images -->
  <div class="gallery-fallback">
    <h2>Photo Gallery</h2>
    <ul>
      <li><a href="image1.jpg">Photo 1</a></li>
      <li><a href="image2.jpg">Photo 2</a></li>
      <li><a href="image3.jpg">Photo 3</a></li>
    </ul>
  </div>
</noscript>
```

#### Good Example: Load Full Styles When No JS

```html
<head>
  <!-- Load critical CSS async (requires JS) -->
  <script>
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/styles/main.css';
    document.head.appendChild(link);
  </script>

  <!-- Fallback: load synchronously if no JS -->
  <noscript>
    <link rel="stylesheet" href="/styles/main.css" />
  </noscript>
</head>
```

#### Good Example: Form Fallback

```html
<!-- JavaScript-enhanced form with validation -->
<form id="contact-form" action="/submit" method="post">
  <label for="email">Email:</label>
  <input type="email" id="email" name="email" required />

  <button type="submit">Submit</button>

  <noscript>
    <p><em>Note: JavaScript is disabled. Form validation and instant feedback unavailable.</em></p>
  </noscript>
</form>
```

#### Good Example: Analytics Fallback

```html
<!-- JavaScript tracking pixel -->
<script>
  // Send analytics event
  gtag('event', 'page_view');
</script>

<!-- Fallback: image pixel -->
<noscript>
  <img
    src="https://analytics.example.com/pixel?page=homepage"
    alt=""
    width="1"
    height="1"
    style="display:none;"
  />
</noscript>
```

#### Bad Example: Forcing JavaScript

```html
<!-- ❌ WRONG: No alternative provided -->
<noscript>
  <h1>JavaScript Required</h1>
  <p>This site will not work without JavaScript. Enable it now.</p>
</noscript>

<!-- ✅ CORRECT: Provide alternative or graceful degradation -->
<noscript>
  <div class="alert">
    <p>For the best experience, enable JavaScript. However, you can still:</p>
    <ul>
      <li><a href="/browse">Browse products</a></li>
      <li><a href="/search">Search our catalog</a></li>
      <li><a href="/contact">Contact us</a></li>
    </ul>
  </div>
</noscript>
```

### Accessibility

**High accessibility value**: Ensures content accessible to all users

**Best practices**:

- Provide equivalent functionality, not just error message
- Test site with JavaScript disabled
- Use progressive enhancement (HTML → CSS → JS)
- Don't rely solely on JavaScript for critical content

### SEO Impact

**Moderate SEO value**: Content in `<noscript>` is indexed

**Best practices**:

- Don't hide content in `<noscript>` (cloaking)
- Use server-side rendering for critical content
- `<noscript>` should provide genuine alternatives

### Browser Support

✅ Universal support in all browsers

---

## `<template>`

**Category**: Metadata Content, Flow Content, Phrasing Content, Script-supporting Element  
**Content Model**: Transparent (anything that's valid in parent, but not rendered)  
**Permitted Parents**: Any element that accepts metadata, phrasing, flow, or script-supporting content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Declares **reusable HTML fragments** that are not rendered when page loads. Content is inert (scripts don't run, images don't load) until cloned via JavaScript.

**Key characteristic**: Invisible markup template for JavaScript-based content generation.

### When to Use

Use `<template>` for:

- Reusable HTML components
- Dynamic list items
- Client-side rendering templates
- Web Components shadow DOM content
- JavaScript-populated UI patterns
- Repeated HTML structures

### When NOT to Use

❌ Don't use for:

- Static content (use regular HTML)
- Content that should be visible initially
- Server-side templates (use templating engine)

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Any content (transparent)
- Content is inert (not rendered, scripts not executed)

**Accessing content**:

- Use `template.content` to get DocumentFragment
- Clone and append to DOM to activate

### Examples

#### Good Example: List Item Template

```html
<template id="item-template">
  <li class="item">
    <h3 class="item-title"></h3>
    <p class="item-description"></p>
    <button class="item-action">Action</button>
  </li>
</template>

<ul id="item-list"></ul>

<script>
  const template = document.getElementById('item-template');
  const list = document.getElementById('item-list');

  const items = [
    { title: 'Item 1', description: 'First item' },
    { title: 'Item 2', description: 'Second item' },
  ];

  items.forEach((item) => {
    // Clone template content
    const clone = template.content.cloneNode(true);

    // Populate with data
    clone.querySelector('.item-title').textContent = item.title;
    clone.querySelector('.item-description').textContent = item.description;

    // Add to DOM
    list.appendChild(clone);
  });
</script>
```

#### Good Example: Card Component Template

```html
<template id="card-template">
  <article class="card">
    <img class="card-image" alt="" />
    <div class="card-body">
      <h2 class="card-title"></h2>
      <p class="card-text"></p>
      <a class="card-link" href="#">Read more</a>
    </div>
  </article>
</template>

<div id="cards-container"></div>

<script>
  function createCard(data) {
    const template = document.getElementById('card-template');
    const clone = template.content.cloneNode(true);

    clone.querySelector('.card-image').src = data.image;
    clone.querySelector('.card-image').alt = data.title;
    clone.querySelector('.card-title').textContent = data.title;
    clone.querySelector('.card-text').textContent = data.description;
    clone.querySelector('.card-link').href = data.url;

    return clone;
  }

  const container = document.getElementById('cards-container');
  container.appendChild(
    createCard({
      image: 'card1.jpg',
      title: 'Card Title',
      description: 'Card description',
      url: '/article-1',
    })
  );
</script>
```

#### Good Example: Table Row Template

```html
<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="users-tbody">
    <!-- Rows inserted here -->
  </tbody>
</table>

<template id="user-row-template">
  <tr>
    <td class="user-name"></td>
    <td class="user-email"></td>
    <td>
      <button class="edit-btn">Edit</button>
      <button class="delete-btn">Delete</button>
    </td>
  </tr>
</template>

<script>
  function addUser(user) {
    const template = document.getElementById('user-row-template');
    const clone = template.content.cloneNode(true);

    clone.querySelector('.user-name').textContent = user.name;
    clone.querySelector('.user-email').textContent = user.email;

    clone.querySelector('.edit-btn').addEventListener('click', () => editUser(user.id));
    clone.querySelector('.delete-btn').addEventListener('click', () => deleteUser(user.id));

    document.getElementById('users-tbody').appendChild(clone);
  }
</script>
```

#### Good Example: Nested Templates

```html
<template id="comment-template">
  <div class="comment">
    <div class="comment-header">
      <strong class="comment-author"></strong>
      <time class="comment-date"></time>
    </div>
    <p class="comment-text"></p>
    <button class="reply-btn">Reply</button>
    <div class="comment-replies"></div>
  </div>
</template>

<script>
  function createComment(data, isReply = false) {
    const template = document.getElementById('comment-template');
    const clone = template.content.cloneNode(true);

    clone.querySelector('.comment-author').textContent = data.author;
    clone.querySelector('.comment-date').textContent = data.date;
    clone.querySelector('.comment-text').textContent = data.text;

    if (isReply) {
      clone.querySelector('.comment').classList.add('comment-reply');
    }

    // Recursively add replies
    if (data.replies) {
      const repliesContainer = clone.querySelector('.comment-replies');
      data.replies.forEach((reply) => {
        repliesContainer.appendChild(createComment(reply, true));
      });
    }

    return clone;
  }
</script>
```

### Accessibility

**No direct accessibility impact**: Template content is inert

**Best practices**:

- Ensure cloned content is accessible
- Include proper ARIA attributes in template
- Test keyboard navigation after cloning

### SEO Impact

**No SEO value**: Template content not indexed (not rendered)

**Best practice**: Use server-side rendering for SEO-critical content

### Browser Support

✅ Universal support in modern browsers (IE11+, all modern browsers)  
✅ `template.content` returns DocumentFragment

---

## `<slot>`

**Category**: Flow Content, Phrasing Content  
**Content Model**: Transparent  
**Permitted Parents**: Any element that accepts phrasing or flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Defines a **placeholder** in a Web Component's shadow DOM where content from the light DOM can be inserted. Enables component customization.

**Key characteristic**: Part of Web Components API for content projection.

### When to Use

Use `<slot>` for:

- Web Components content projection
- Reusable component placeholders
- Component customization points
- Shadow DOM templates

### When NOT to Use

❌ Don't use for:

- Regular HTML (slots only work in shadow DOM)
- Server-side templates
- Non-component contexts

### Attributes

**Element-Specific**:

- `name` - Named slot identifier (unnamed slot = default slot)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Examples

#### Good Example: Custom Card Component

```html
<!-- Define component -->
<template id="card-component">
  <style>
    .card {
      border: 1px solid #ccc;
      padding: 1rem;
      border-radius: 8px;
    }
    .card-header {
      font-weight: bold;
      margin-bottom: 0.5rem;
    }
  </style>

  <div class="card">
    <div class="card-header">
      <slot name="header">Default Header</slot>
    </div>
    <div class="card-body">
      <slot>Default content</slot>
    </div>
    <div class="card-footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<!-- Use component -->
<custom-card>
  <span slot="header">My Custom Header</span>
  <p>This is the main content of the card.</p>
  <button slot="footer">Action</button>
</custom-card>

<script>
  class CustomCard extends HTMLElement {
    constructor() {
      super();
      const template = document.getElementById('card-component');
      const shadowRoot = this.attachShadow({ mode: 'open' });
      shadowRoot.appendChild(template.content.cloneNode(true));
    }
  }

  customElements.define('custom-card', CustomCard);
</script>
```

#### Good Example: Default Slot with Fallback

```html
<template id="button-component">
  <button class="custom-button">
    <slot>Click me</slot>
  </button>
</template>

<!-- Usage -->
<custom-button>Custom Text</custom-button>
<!-- Shows: Custom Text -->
<custom-button></custom-button>
<!-- Shows: Click me (fallback) -->
```

#### Good Example: Multiple Named Slots

```html
<template id="profile-card">
  <div class="profile">
    <div class="avatar">
      <slot name="avatar">👤</slot>
    </div>
    <div class="info">
      <h3><slot name="name">Name</slot></h3>
      <p><slot name="bio">Bio</slot></p>
    </div>
    <div class="actions">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<!-- Usage -->
<profile-card>
  <img slot="avatar" src="avatar.jpg" alt="User" />
  <span slot="name">John Doe</span>
  <span slot="bio">Web Developer</span>
  <button slot="actions">Follow</button>
  <button slot="actions">Message</button>
</profile-card>
```

### Accessibility

**Indirect accessibility impact**: Slotted content must be accessible

**Best practices**:

- Ensure slot fallback content is accessible
- Test with screen readers
- Maintain semantic structure through slots

### SEO Impact

**Minimal SEO value**: Light DOM content (slotted) may be indexed

### Browser Support

✅ Modern browsers: Chrome 53+, Edge 79+, Firefox 63+, Safari 10+  
⚠️ Not in IE11 (requires polyfill)

---

## Web Components Basics

### Custom Elements

```html
<script>
  class MyElement extends HTMLElement {
    constructor() {
      super();
      // Setup
    }

    connectedCallback() {
      // Element added to DOM
      this.innerHTML = `<p>Hello from custom element!</p>`;
    }

    disconnectedCallback() {
      // Element removed from DOM
    }

    attributeChangedCallback(name, oldValue, newValue) {
      // Attribute changed
    }

    static get observedAttributes() {
      return ['data-value'];
    }
  }

  customElements.define('my-element', MyElement);
</script>

<!-- Usage -->
<my-element data-value="123"></my-element>
```

### Shadow DOM

```html
<script>
  class ShadowElement extends HTMLElement {
    constructor() {
      super();

      // Attach shadow DOM
      const shadow = this.attachShadow({ mode: 'open' });

      // Add content
      shadow.innerHTML = `
        <style>
          :host {
            display: block;
            padding: 1rem;
            border: 2px solid blue;
          }
        </style>
        <slot></slot>
      `;
    }
  }

  customElements.define('shadow-element', ShadowElement);
</script>
```

### Complete Web Component Example

```html
<template id="tooltip-template">
  <style>
    :host {
      position: relative;
      display: inline-block;
    }

    .tooltip-trigger {
      cursor: help;
      text-decoration: underline dotted;
    }

    .tooltip-content {
      position: absolute;
      bottom: 100%;
      left: 50%;
      transform: translateX(-50%);
      background: #333;
      color: white;
      padding: 0.5rem;
      border-radius: 4px;
      white-space: nowrap;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s;
    }

    :host([open]) .tooltip-content {
      opacity: 1;
    }
  </style>

  <span class="tooltip-trigger">
    <slot name="trigger">ℹ️</slot>
  </span>
  <div class="tooltip-content">
    <slot name="content">Tooltip content</slot>
  </div>
</template>

<script>
  class TooltipElement extends HTMLElement {
    constructor() {
      super();

      const template = document.getElementById('tooltip-template');
      const shadow = this.attachShadow({ mode: 'open' });
      shadow.appendChild(template.content.cloneNode(true));

      this.trigger = shadow.querySelector('.tooltip-trigger');

      this.trigger.addEventListener('mouseenter', () => this.open());
      this.trigger.addEventListener('mouseleave', () => this.close());
    }

    open() {
      this.setAttribute('open', '');
    }

    close() {
      this.removeAttribute('open');
    }
  }

  customElements.define('tool-tip', TooltipElement);
</script>

<!-- Usage -->
<tool-tip>
  <span slot="trigger">Hover me</span>
  <span slot="content">This is helpful information!</span>
</tool-tip>
```

---

## Scripting Best Practices

### 1. Progressive Enhancement

```html
<!-- Core functionality without JavaScript -->
<form action="/search" method="get">
  <label for="query">Search:</label>
  <input type="search" id="query" name="q" required />
  <button type="submit">Search</button>
</form>

<!-- Enhanced with JavaScript -->
<script>
  const form = document.querySelector('form');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Fetch results without page reload
    const formData = new FormData(form);
    const results = await fetch('/api/search?' + new URLSearchParams(formData));

    // Display results dynamically
    displayResults(await results.json());
  });
</script>
```

### 2. Unobtrusive JavaScript

```html
<!-- ❌ BAD: Inline event handlers -->
<button onclick="handleClick()">Click</button>

<!-- ✅ GOOD: Separate JavaScript -->
<button id="myButton">Click</button>

<script>
  document.getElementById('myButton').addEventListener('click', handleClick);
</script>
```

### 3. Module Pattern

```html
<script type="module">
  // app.js
  import { init } from './modules/app.js';

  // Initialize when DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
</script>
```

### 4. Performance

```html
<!-- Critical inline -->
<script>
  // Small, critical code inline
  const theme = localStorage.getItem('theme');
  document.documentElement.dataset.theme = theme;
</script>

<!-- Defer non-critical -->
<script src="/scripts/app.js" defer></script>

<!-- Async for independent features -->
<script src="/scripts/analytics.js" async></script>
```

### 5. Error Handling

```html
<script>
  window.addEventListener('error', (e) => {
    console.error('JavaScript error:', e.error);
    // Report to error tracking service
  });

  window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
  });
</script>
```

---

## Script Loading Checklist

- [ ] Critical scripts inline or early in `<head>`
- [ ] Non-critical scripts with `defer` or `async`
- [ ] Use `type="module"` for ES6 modules
- [ ] Include SRI hashes for CDN scripts
- [ ] Set appropriate CSP headers/nonces
- [ ] Provide `<noscript>` fallback for critical features
- [ ] Test with JavaScript disabled
- [ ] Minimize and bundle scripts for production
- [ ] Use lazy loading for below-fold features
- [ ] Monitor JavaScript errors in production

---

## Further Reading

- WHATWG HTML Standard - Scripting: https://html.spec.whatwg.org/multipage/scripting.html
- MDN Web Docs - `<script>`: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script
- MDN Web Docs - Web Components: https://developer.mozilla.org/en-US/docs/Web/API/Web_components
- MDN Web Docs - `<template>`: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/template
- Web Components Best Practices: https://web.dev/articles/custom-elements-best-practices
