# Interactive Elements

Comprehensive reference for HTML interactive elements that provide user interaction patterns without requiring custom JavaScript.

## Overview

Interactive elements provide built-in functionality for common UI patterns:

- **Disclosure widgets** (`<details>`, `<summary>`)
- **Modal dialogs** (`<dialog>`)
- **Context menus** (`<menu>` - experimental)

These elements improve:

- **Accessibility**: Built-in keyboard navigation and ARIA semantics
- **Performance**: Native browser implementation
- **Consistency**: Standard behavior across sites
- **Development speed**: Less custom JavaScript required

**Core Principle**: Use native interactive elements for standard UI patterns before building custom solutions.

---

## `<details>`

**Category**: Flow Content, Interactive Content  
**Content Model**: One `<summary>` (optional, first child) followed by flow content  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `group`

### Purpose & Semantic Meaning

Represents a **disclosure widget** from which additional information or content can be shown or hidden. Creates an expandable/collapsible section.

**Key characteristic**: Content is initially hidden and can be toggled visible by user interaction.

### When to Use

Use `<details>` for:

- Accordion sections
- FAQ items
- Expandable content panels
- "Read more" sections
- Progressive disclosure of information
- Collapsible help text
- Optional configuration panels

### When NOT to Use

❌ Don't use for:

- Primary navigation (use `<nav>`)
- Tab panels (use `role="tabpanel"` pattern)
- Modal dialogs (use `<dialog>`)
- Important content that must be visible (default hidden)
- Content critical for SEO (may not be indexed)

### Attributes

**Element-Specific**:

- `open` - Boolean attribute indicating the details are visible
- `name` - Groups related `<details>` elements for accordion behavior (allows only one open at a time)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Zero or one `<summary>` element (if present, must be first child)
- Flow content

**If no `<summary>`**:

- Browser provides default summary (usually "Details")

**When open**:

- All content becomes visible
- `open` attribute is present

### Examples

#### Good Example: FAQ Item

```html
<details>
  <summary>What is HTML5?</summary>
  <p>
    HTML5 is the latest version of the HyperText Markup Language, providing new semantic elements,
    multimedia support, and improved APIs for building modern web applications.
  </p>
</details>
```

#### Good Example: Multiple FAQ Items

```html
<section>
  <h2>Frequently Asked Questions</h2>

  <details>
    <summary>How do I create an account?</summary>
    <p>Click the "Sign Up" button in the top right corner and fill out the registration form.</p>
  </details>

  <details>
    <summary>What payment methods do you accept?</summary>
    <p>We accept all major credit cards, PayPal, and bank transfers.</p>
  </details>

  <details open>
    <summary>Can I cancel my subscription?</summary>
    <p>Yes, you can cancel your subscription at any time from your account settings page.</p>
  </details>
</section>
```

#### Good Example: Exclusive Accordion (HTML 5.2+)

```html
<!-- Only one can be open at a time -->
<details name="accordion">
  <summary>Section 1</summary>
  <p>Content for section 1...</p>
</details>

<details name="accordion">
  <summary>Section 2</summary>
  <p>Content for section 2...</p>
</details>

<details name="accordion" open>
  <summary>Section 3</summary>
  <p>Content for section 3 (initially open)...</p>
</details>
```

#### Good Example: Complex Content

```html
<details>
  <summary>View Product Specifications</summary>
  <table>
    <caption>
      Technical Specifications
    </caption>
    <tbody>
      <tr>
        <th scope="row">Dimensions</th>
        <td>10" x 8" x 2"</td>
      </tr>
      <tr>
        <th scope="row">Weight</th>
        <td>2.5 lbs</td>
      </tr>
      <tr>
        <th scope="row">Material</th>
        <td>Aluminum</td>
      </tr>
    </tbody>
  </table>
</details>
```

#### Bad Example: Missing Summary

```html
<!-- ❌ WRONG: No summary element -->
<details>
  <p>This content has no summary, browser will show generic "Details"</p>
</details>

<!-- ✅ CORRECT: Always include summary -->
<details>
  <summary>More Information</summary>
  <p>Detailed content here...</p>
</details>
```

#### Bad Example: Critical Content Hidden

```html
<!-- ❌ WRONG: Important pricing info hidden by default -->
<details>
  <summary>Pricing</summary>
  <p>Our basic plan is $29/month</p>
</details>

<!-- ✅ CORRECT: Show critical info by default, use open attribute if needed -->
<section>
  <h2>Pricing</h2>
  <p>Our basic plan is $29/month</p>
  <details open>
    <summary>See detailed pricing breakdown</summary>
    <!-- Additional pricing details -->
  </details>
</section>
```

### Accessibility

**Implicit role**: `group`  
**Screen reader behavior**: Announces as expandable/collapsible control, indicates expanded/collapsed state

**Built-in accessibility**:

- Keyboard navigable (Tab to focus, Enter/Space to toggle)
- Announces expanded/collapsed state automatically
- `<summary>` acts as button with implicit `role="button"`

**Best practices**:

- Always include `<summary>` for descriptive label
- Don't hide critical content in collapsed state
- Keep summary text concise and descriptive
- Consider adding icon to indicate expandable state (via CSS)

**Styling example for better UX**:

```html
<style>
  summary {
    cursor: pointer;
    font-weight: bold;
    padding: 0.5rem;
  }

  summary:hover {
    background-color: #f0f0f0;
  }

  summary:focus {
    outline: 2px solid #0066cc;
    outline-offset: 2px;
  }

  /* Custom arrow indicator */
  summary::before {
    content: '▶ ';
    display: inline-block;
    transition: transform 0.2s;
  }

  details[open] > summary::before {
    transform: rotate(90deg);
  }
</style>
```

### SEO Impact

**Low to Moderate SEO value**: Content may or may not be indexed

**Considerations**:

- Search engines may index hidden content, but give it less weight
- Important keywords should be visible by default
- FAQ structured data (JSON-LD) helps even when using `<details>`
- Consider `open` attribute for SEO-critical content

**Best practices for SEO**:

- Use for supplementary, not primary content
- Include keywords in `<summary>` text
- Mark up FAQ pages with structured data
- Test indexing with Google Search Console

**FAQ Structured Data Example**:

```html
<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "What is HTML5?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "HTML5 is the latest version of HyperText Markup Language..."
        }
      }
    ]
  }
</script>

<details>
  <summary>What is HTML5?</summary>
  <p>HTML5 is the latest version of HyperText Markup Language...</p>
</details>
```

### Browser Support

✅ Widely supported in modern browsers (Chrome 12+, Firefox 49+, Safari 6+, Edge 79+)  
⚠️ Not supported in IE11 (use polyfill or progressive enhancement)  
✅ `name` attribute for exclusive accordions supported in Chrome 120+, Firefox 124+

**Polyfill for older browsers**:

```html
<script>
  // Basic polyfill for <details> in unsupported browsers
  if (!('open' in document.createElement('details'))) {
    // Polyfill implementation or load external polyfill
    // Example: <https://github.com/javan/details-element-polyfill>
  }
</script>
```

---

## `<summary>`

**Category**: None (details child only)  
**Content Model**: Phrasing content or one heading element  
**Permitted Parents**: `<details>` (must be first child if present)  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `button`

### Purpose & Semantic Meaning

Provides a **visible label or caption** for the `<details>` disclosure widget. Acts as the clickable button that toggles visibility.

**Key characteristic**: Always visible, serves as the toggle control.

### When to Use

Use `<summary>` for:

- Label for disclosure widget
- Clickable toggle control text
- Heading for expandable section
- Brief description of hidden content

### When NOT to Use

❌ Don't use for:

- Generic headings (use `<h1>`-`<h6>`)
- Buttons outside `<details>` (use `<button>`)
- Non-interactive labels

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Phrasing content (text, links, emphasis, etc.)
- One heading element (h1-h6) - but heading should be parent to `<details>`, not child of `<summary>`

**Cannot contain**:

- Interactive elements (nested `<button>`, `<a>` with href, etc.) - causes accessibility issues

**Must be**:

- First child of `<details>` (if present)
- Only one `<summary>` per `<details>`

### Examples

#### Good Example: Simple Text Summary

```html
<details>
  <summary>Show more information</summary>
  <p>Additional details here...</p>
</details>
```

#### Good Example: Summary with Emphasis

```html
<details>
  <summary><strong>Important:</strong> Read before continuing</summary>
  <p>Critical information that users need to know...</p>
</details>
```

#### Good Example: Icon in Summary

```html
<details>
  <summary>
    <svg width="16" height="16" aria-hidden="true">
      <use href="#info-icon"></use>
    </svg>
    Product Details
  </summary>
  <div>
    <!-- Product specifications -->
  </div>
</details>
```

#### Bad Example: Interactive Elements Inside

```html
<!-- ❌ WRONG: Nested button causes accessibility issues -->
<details>
  <summary>
    Options
    <button>Delete</button>
  </summary>
  <p>Content...</p>
</details>

<!-- ✅ CORRECT: Interactive elements outside summary or after details content -->
<details>
  <summary>Options</summary>
  <p>Content...</p>
  <button>Delete</button>
</details>
```

#### Bad Example: Heading Inside Summary

```html
<!-- ❌ WRONG: Heading inside summary -->
<details>
  <summary><h3>Section Title</h3></summary>
  <p>Content...</p>
</details>

<!-- ✅ CORRECT: Heading wraps details -->
<section>
  <h3>Section Title</h3>
  <details>
    <summary>Show details</summary>
    <p>Content...</p>
  </details>
</section>
```

### Accessibility

**Implicit role**: `button`  
**Screen reader behavior**: Announces as button, indicates expandable control

**Built-in accessibility**:

- Keyboard accessible (Tab to focus, Enter/Space to activate)
- Automatically announces expanded/collapsed state
- Acts as toggle button without requiring `role="button"`

**Best practices**:

- Keep summary text concise (1-10 words ideal)
- Make summary descriptive of content
- Don't nest interactive elements
- Ensure sufficient color contrast
- Add focus indicator styling

### SEO Impact

**Moderate SEO value**: Summary text is always visible

**Benefits**:

- Summary text indexed and visible to crawlers
- Acts as heading-like element for content
- Include keywords in summary when relevant

### Browser Support

✅ Same support as `<details>` element  
✅ Implicit button behavior universally supported

---

## `<dialog>`

**Category**: Flow Content  
**Content Model**: Flow content  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `dialog`

### Purpose & Semantic Meaning

Represents a **modal or non-modal dialog box** or popup. Provides native browser support for overlays, lightboxes, and alert dialogs.

**Key characteristic**: Content displayed in front of other page content, with optional backdrop.

### When to Use

Use `<dialog>` for:

- Modal dialogs (blocks interaction with page)
- Alert boxes and confirmations
- Lightboxes for images or media
- Popup forms or prompts
- Cookie consent notices
- Non-modal popups (tooltips, menus)

### When NOT to Use

❌ Don't use for:

- Dropdown menus (use `<select>` or ARIA menu pattern)
- Tooltips (use `title` attribute or ARIA tooltip)
- Notifications/toasts (different pattern)
- Persistent sidebars (use `<aside>`)

### Attributes

**Element-Specific**:

- `open` - Boolean attribute indicating dialog is visible (set programmatically)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Flow content (any HTML content)
- Typically includes form, heading, paragraph, buttons

**JavaScript API**:

- `dialog.show()` - Opens as non-modal
- `dialog.showModal()` - Opens as modal (with backdrop, traps focus)
- `dialog.close()` - Closes dialog

### Examples

#### Good Example: Modal Dialog

```html
<dialog id="myDialog">
  <form method="dialog">
    <h2>Confirm Action</h2>
    <p>Are you sure you want to delete this item?</p>
    <div>
      <button value="cancel">Cancel</button>
      <button value="confirm" autofocus>Confirm</button>
    </div>
  </form>
</dialog>

<button onclick="document.getElementById('myDialog').showModal()">Open Dialog</button>

<script>
  const dialog = document.getElementById('myDialog');

  dialog.addEventListener('close', () => {
    console.log('Dialog closed with value:', dialog.returnValue);
  });
</script>
```

#### Good Example: Non-Modal Dialog

```html
<dialog id="infoDialog">
  <h2>Information</h2>
  <p>This is a non-modal dialog. You can still interact with the page.</p>
  <button onclick="this.closest('dialog').close()">Close</button>
</dialog>

<button onclick="document.getElementById('infoDialog').show()">Show Info</button>
```

#### Good Example: Form in Dialog

```html
<dialog id="loginDialog">
  <form method="dialog">
    <h2>Login</h2>

    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required />

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required />

    <div>
      <button type="submit" value="cancel">Cancel</button>
      <button type="submit" value="login">Login</button>
    </div>
  </form>
</dialog>
```

#### Good Example: Dialog with Backdrop Styling

```html
<style>
  dialog::backdrop {
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(3px);
  }

  dialog {
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    padding: 2rem;
    max-width: 500px;
  }

  dialog[open] {
    animation: slideIn 0.3s ease-out;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<dialog id="styledDialog">
  <h2>Welcome!</h2>
  <p>This is a beautifully styled dialog.</p>
  <button onclick="this.closest('dialog').close()">Close</button>
</dialog>
```

#### Bad Example: No Close Mechanism

```html
<!-- ❌ WRONG: No way to close dialog -->
<dialog id="badDialog" open>
  <p>This dialog can't be closed!</p>
</dialog>

<!-- ✅ CORRECT: Always provide close button -->
<dialog id="goodDialog" open>
  <p>This dialog can be closed.</p>
  <button onclick="this.closest('dialog').close()">Close</button>
</dialog>
```

#### Bad Example: Open Attribute in HTML

```html
<!-- ❌ WRONG: Using open attribute in HTML -->
<dialog open>
  <p>Dialog visible on page load</p>
</dialog>

<!-- ✅ CORRECT: Open programmatically with JavaScript -->
<dialog id="myDialog">
  <p>Dialog content</p>
</dialog>

<script>
  // Open when needed
  document.getElementById('myDialog').showModal();
</script>
```

### Accessibility

**Implicit role**: `dialog`  
**Screen reader behavior**: Announces as dialog, indicates modal state

**Built-in accessibility**:

- **Focus trap**: Modal dialogs trap focus within dialog
- **Escape key**: Pressing Esc closes modal dialog
- **Backdrop click**: Can close on backdrop click (add event listener)
- **Focus management**: First focusable element receives focus

**Best practices**:

- Always include heading (h2 or aria-labelledby)
- Provide clear close button
- Set focus to appropriate element when opened
- Return focus to trigger element when closed
- Use `autofocus` on primary action button
- Add `aria-labelledby` or `aria-label` for screen readers

**Enhanced accessibility example**:

```html
<dialog id="accessibleDialog" aria-labelledby="dialogTitle">
  <h2 id="dialogTitle">Dialog Title</h2>
  <p>Dialog content...</p>

  <div role="group" aria-label="Dialog actions">
    <button onclick="this.closest('dialog').close()">Cancel</button>
    <button autofocus onclick="handleConfirm()">Confirm</button>
  </div>
</dialog>

<script>
  const dialog = document.getElementById('accessibleDialog');
  const trigger = document.getElementById('openButton');

  function openDialog() {
    dialog.showModal();
  }

  dialog.addEventListener('close', () => {
    // Return focus to trigger element
    trigger.focus();
  });

  // Close on backdrop click
  dialog.addEventListener('click', (e) => {
    if (e.target === dialog) {
      dialog.close();
    }
  });

  // Close on Escape key (built-in, but can customize)
  dialog.addEventListener('cancel', (e) => {
    console.log('Dialog cancelled');
  });
</script>
```

### SEO Impact

**No SEO value**: Dialog content typically hidden until interaction

**Considerations**:

- Content inside `<dialog>` not indexed when closed
- Use for UI enhancements, not primary content
- Critical information should be in main page content

### Browser Support

✅ Modern browser support (Chrome 37+, Firefox 98+, Safari 15.4+, Edge 79+)  
⚠️ Not supported in older browsers (requires polyfill)  
⚠️ Some features vary by browser (backdrop click behavior)

**Polyfill**:

```html
<script src="https://cdn.jsdelivr.net/npm/dialog-polyfill@0.5.6/dist/dialog-polyfill.min.js"></script>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/dialog-polyfill@0.5.6/dist/dialog-polyfill.css"
/>

<script>
  const dialog = document.querySelector('dialog');
  dialogPolyfill.registerDialog(dialog);
</script>
```

---

## `<menu>`

**Category**: Flow Content  
**Content Model**: Zero or more `<li>`, `<script>`, `<template>` elements  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `list`

### Purpose & Semantic Meaning

Represents an **unordered list of commands or options**. Originally intended for context menus and toolbars, now essentially synonymous with `<ul>`.

**Key characteristic**: Semantic alternative to `<ul>` for command lists.

**Status**: The `type` attribute (toolbar, context) was removed. `<menu>` is now treated like `<ul>`.

### When to Use

Use `<menu>` for:

- Toolbar button lists
- Context menu items (when building custom menus)
- Command palettes
- Action lists

**Note**: For most cases, `<ul>` is more widely supported and understood. Use `<menu>` only when semantically it represents commands/actions rather than general list items.

### When NOT to Use

❌ Don't use for:

- Site navigation (use `<nav>` with `<ul>`)
- General lists (use `<ul>` or `<ol>`)
- Native browser context menus (not currently implementable)

### Attributes

**Element-Specific**: None (historical `type` and `label` attributes removed)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Zero or more `<li>` elements
- `<script>` elements
- `<template>` elements

**Structure**: Same as `<ul>`

### Examples

#### Good Example: Toolbar Menu

```html
<menu>
  <li><button type="button">New File</button></li>
  <li><button type="button">Open</button></li>
  <li><button type="button">Save</button></li>
  <li><button type="button">Save As</button></li>
</menu>
```

#### Good Example: Context Menu Simulation

```html
<menu id="customMenu" hidden>
  <li><button onclick="copyText()">Copy</button></li>
  <li><button onclick="cutText()">Cut</button></li>
  <li><button onclick="pasteText()">Paste</button></li>
  <li><hr /></li>
  <li><button onclick="deleteText()">Delete</button></li>
</menu>

<div id="contextArea">Right-click here</div>

<script>
  const area = document.getElementById('contextArea');
  const menu = document.getElementById('customMenu');

  area.addEventListener('contextmenu', (e) => {
    e.preventDefault();
    menu.hidden = false;
    menu.style.position = 'absolute';
    menu.style.left = e.pageX + 'px';
    menu.style.top = e.pageY + 'px';
  });

  document.addEventListener('click', () => {
    menu.hidden = true;
  });
</script>
```

#### Better Alternative: Use `<ul>` for Clarity

```html
<!-- Most cases: just use <ul> -->
<ul role="menu">
  <li role="none">
    <button role="menuitem">Action 1</button>
  </li>
  <li role="none">
    <button role="menuitem">Action 2</button>
  </li>
</ul>
```

### Accessibility

**Implicit role**: `list`  
**Screen reader behavior**: Same as `<ul>` - announces as list

**Best practices**:

- Use ARIA menu roles for true menus (`role="menu"`, `role="menuitem"`)
- Ensure keyboard navigation (Arrow keys, Enter, Esc)
- Consider using `<ul>` with ARIA roles for better compatibility

### SEO Impact

**No SEO value**: Equivalent to `<ul>` for search engines

### Browser Support

✅ Universal support as synonym for `<ul>`  
⚠️ Historical `type` attribute no longer supported

**Recommendation**: Use `<ul>` unless you specifically need semantic distinction for command lists.

---

## Interactive Elements Best Practices

### 1. Use Native Elements First

Before building custom solutions:

- ✅ Use `<details>` for accordions (not `div` + JS)
- ✅ Use `<dialog>` for modals (not `div` + overlay)
- ✅ Use semantic HTML with ARIA when native elements don't fit

### 2. Keyboard Accessibility

All interactive elements must be keyboard accessible:

- **Tab**: Move focus between elements
- **Enter/Space**: Activate buttons and controls
- **Escape**: Close dialogs and dismiss widgets
- **Arrow keys**: Navigate within menus (custom implementation)

### 3. Focus Management

- Set focus to appropriate element when opening
- Trap focus in modal dialogs
- Return focus when closing
- Provide visible focus indicators

### 4. Progressive Enhancement

- Ensure content is accessible without JavaScript when possible
- Use `<details>` for accordion (works without JS)
- Provide fallback for `<dialog>` in older browsers

### 5. Testing Checklist

- [ ] Keyboard navigation works
- [ ] Screen reader announces elements correctly
- [ ] Focus management handles open/close
- [ ] Escape key closes dialogs
- [ ] Visual focus indicators present
- [ ] Color contrast sufficient (WCAG AA)
- [ ] Works without JavaScript (when possible)
- [ ] Responsive on mobile devices

---

## Common Patterns

### Pattern: FAQ with Details

```html
<section>
  <h2>Frequently Asked Questions</h2>

  <details>
    <summary>How do I reset my password?</summary>
    <ol>
      <li>Go to the login page</li>
      <li>Click "Forgot Password"</li>
      <li>Enter your email address</li>
      <li>Check your email for reset link</li>
    </ol>
  </details>

  <details>
    <summary>What payment methods do you accept?</summary>
    <p>We accept Visa, MasterCard, American Express, PayPal, and bank transfers.</p>
  </details>
</section>
```

### Pattern: Confirmation Dialog

```html
<dialog id="confirmDialog">
  <form method="dialog">
    <h2>Confirm Deletion</h2>
    <p>Are you sure you want to delete this item? This action cannot be undone.</p>
    <div class="dialog-buttons">
      <button value="cancel">Cancel</button>
      <button value="delete" class="danger">Delete</button>
    </div>
  </form>
</dialog>

<button onclick="showConfirmDialog()">Delete Item</button>

<script>
  function showConfirmDialog() {
    const dialog = document.getElementById('confirmDialog');
    dialog.showModal();

    dialog.addEventListener('close', function handler() {
      if (dialog.returnValue === 'delete') {
        // Perform deletion
        console.log('Item deleted');
      }
      dialog.removeEventListener('close', handler);
    });
  }
</script>
```

### Pattern: Image Lightbox

```html
<dialog id="lightbox">
  <form method="dialog">
    <img id="lightboxImage" alt="" />
    <button class="close-button" aria-label="Close">×</button>
  </form>
</dialog>

<div class="gallery">
  <img src="thumb1.jpg" alt="Photo 1" onclick="openLightbox('full1.jpg', 'Photo 1')" />
  <img src="thumb2.jpg" alt="Photo 2" onclick="openLightbox('full2.jpg', 'Photo 2')" />
</div>

<script>
  function openLightbox(src, alt) {
    const dialog = document.getElementById('lightbox');
    const img = document.getElementById('lightboxImage');
    img.src = src;
    img.alt = alt;
    dialog.showModal();
  }

  // Close on backdrop click
  document.getElementById('lightbox').addEventListener('click', (e) => {
    if (e.target.tagName === 'DIALOG') {
      e.target.close();
    }
  });
</script>
```

---

## Further Reading

- WHATWG HTML Standard - Interactive Elements: https://html.spec.whatwg.org/multipage/interactive-elements.html
- MDN Web Docs - `<details>`: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/details
- MDN Web Docs - `<dialog>`: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dialog
- W3C ARIA Authoring Practices - Dialog Pattern: https://www.w3.org/WAI/ARIA/apg/patterns/dialog-modal/
