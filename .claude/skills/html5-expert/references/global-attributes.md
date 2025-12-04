# Global Attributes

Comprehensive reference for HTML global attributes that can be used on **any** HTML element.

## Overview

Global attributes are attributes that can be applied to **all HTML elements**, though they may not always have an effect on every element. They provide:

- **Identity and classification** (id, class)
- **Styling hooks** (style, class)
- **Interactivity** (event handlers, tabindex, contenteditable)
- **Accessibility** (ARIA attributes, lang, title)
- **Data storage** (data-\* attributes)
- **Behavior control** (hidden, draggable, spellcheck)
- **Internationalization** (lang, dir, translate)

**Core Principle**: Global attributes enhance elements with additional functionality, metadata, and accessibility features.

---

## Standard Global Attributes

### `id`

**Purpose**: Provides a **unique identifier** for the element within the document.

**Value**: String (must be unique in document, no spaces)

**Use cases**:

- Link anchors (`<a href="#section">`)
- JavaScript selection (`document.getElementById()`)
- CSS targeting (`#elementId`)
- Form label association (`<label for="inputId">`)
- Fragment identifiers in URLs
- ARIA relationships (`aria-labelledby`, `aria-describedby`)

**Rules**:

- Must be unique within the document
- Must contain at least one character
- Must not contain whitespace
- Case-sensitive
- Should start with letter (not required, but best practice)

**Examples**:

```html
<!-- Good: Unique, descriptive ID -->
<section id="about-section">
  <h2>About Us</h2>
</section>

<!-- Link to ID -->
<a href="#about-section">Jump to About</a>

<!-- JavaScript usage -->
<button id="submit-button">Submit</button>
<script>
  document.getElementById('submit-button').addEventListener('click', handleSubmit);
</script>

<!-- Form association -->
<label for="email-input">Email:</label>
<input type="email" id="email-input" name="email" />

<!-- CSS targeting -->
<style>
  #hero-banner {
    background: linear-gradient(to right, blue, purple);
  }
</style>
<div id="hero-banner">Hero Content</div>

<!-- Bad: Duplicate IDs -->
<!-- ❌ WRONG: IDs must be unique -->
<div id="container">First</div>
<div id="container">Second</div>
<!-- Invalid! -->

<!-- Bad: Spaces in ID -->
<!-- ❌ WRONG: No spaces allowed -->
<div id="my section">Content</div>

<!-- ✅ CORRECT: Use hyphens or camelCase -->
<div id="my-section">Content</div>
<div id="mySection">Content</div>
```

**Best practices**:

- Use descriptive, semantic IDs
- Use kebab-case or camelCase (consistent within project)
- Avoid generic IDs like "div1", "container"
- Don't use IDs for styling (prefer classes)
- Reserve IDs for JavaScript hooks and anchors

---

### `class`

**Purpose**: Assigns one or more **CSS class names** to the element for styling and JavaScript selection.

**Value**: Space-separated list of class names

**Use cases**:

- CSS styling
- JavaScript selection (`.querySelector('.className')`)
- Grouping elements by category
- Component-based styling
- State management (`.active`, `.hidden`)

**Rules**:

- Multiple classes separated by spaces
- Case-sensitive
- Can be repeated across elements
- Should not start with number (CSS limitation)

**Examples**:

```html
<!-- Single class -->
<div class="container">Content</div>

<!-- Multiple classes -->
<button class="btn btn-primary btn-lg">Click Me</button>

<!-- Component pattern -->
<article class="card card--featured">
  <header class="card__header">
    <h2 class="card__title">Title</h2>
  </header>
  <div class="card__body">
    <p class="card__text">Content</p>
  </div>
</article>

<!-- State classes -->
<button class="btn btn-primary is-loading">Processing...</button>

<!-- Utility classes -->
<div class="flex items-center justify-between p-4 bg-gray-100">
  Flexbox container with utilities
</div>

<!-- JavaScript manipulation -->
<div class="modal hidden" id="myModal">Modal content</div>

<script>
  const modal = document.getElementById('myModal');

  // Toggle class
  modal.classList.toggle('hidden');

  // Add class
  modal.classList.add('active');

  // Remove class
  modal.classList.remove('hidden');

  // Check class
  if (modal.classList.contains('active')) {
    console.log('Modal is active');
  }
</script>
```

**Naming conventions**:

```html
<!-- BEM (Block Element Modifier) -->
<div class="card">
  <div class="card__header">
    <h2 class="card__title card__title--large">Title</h2>
  </div>
</div>

<!-- SMACSS (State Management) -->
<button class="btn is-active is-loading">Button</button>

<!-- Utility-first (Tailwind style) -->
<div class="flex flex-col gap-4 p-6 rounded-lg shadow-md">Content</div>

<!-- Component-based -->
<nav class="navigation navigation-primary navigation-mobile">Links</nav>
```

**Best practices**:

- Use semantic, descriptive class names
- Establish naming convention and stick to it
- Use classes for styling, IDs for JavaScript hooks
- Avoid presentational names (`.red-text` → `.error-message`)
- Use lowercase with hyphens (`.my-class`, not `.MyClass`)

---

### `style`

**Purpose**: Applies **inline CSS styles** directly to the element.

**Value**: CSS declarations (property: value pairs separated by semicolons)

**Use cases**:

- Dynamic styling via JavaScript
- Quick prototyping
- One-off styles (use sparingly)
- Overriding external styles (last resort)

**Rules**:

- CSS syntax: `property: value; property: value;`
- Highest specificity (overrides external styles)
- Cannot use pseudo-elements or pseudo-classes
- Not cached by browser

**Examples**:

```html
<!-- Basic inline styles -->
<p style="color: red; font-size: 18px;">Red text, 18px</p>

<!-- Multiple properties -->
<div
  style="
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(to right, #667eea, #764ba2);
"
>
  Centered content
</div>

<!-- JavaScript manipulation -->
<div id="box">Box</div>

<script>
  const box = document.getElementById('box');

  // Set style
  box.style.backgroundColor = 'blue';
  box.style.width = '200px';

  // Multiple styles
  Object.assign(box.style, {
    padding: '20px',
    borderRadius: '8px',
    boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
  });

  // Get computed style
  const styles = window.getComputedStyle(box);
  console.log(styles.backgroundColor);
</script>

<!-- Bad: Overuse of inline styles -->
<!-- ❌ WRONG: Too many inline styles -->
<section style="padding: 20px; margin: 0; background: #f0f0f0;">
  <h2 style="color: #333; font-size: 24px; margin-bottom: 10px;">Title</h2>
  <p style="color: #666; line-height: 1.6; font-size: 16px;">Text</p>
</section>

<!-- ✅ CORRECT: Use CSS classes -->
<style>
  .content-section {
    padding: 20px;
    margin: 0;
    background: #f0f0f0;
  }
  .content-section h2 {
    color: #333;
    font-size: 24px;
    margin-bottom: 10px;
  }
  .content-section p {
    color: #666;
    line-height: 1.6;
    font-size: 16px;
  }
</style>
<section class="content-section">
  <h2>Title</h2>
  <p>Text</p>
</section>
```

**Best practices**:

- **Avoid inline styles** when possible (use CSS classes)
- Use for JavaScript-generated dynamic styles
- Use for email HTML (where external CSS limited)
- Never use for layout (CSS classes more maintainable)
- CSP may block inline styles (use `style-src 'unsafe-inline'` or nonces)

---

### `title`

**Purpose**: Provides **advisory information** about the element, typically shown as a tooltip on hover.

**Value**: Text string

**Use cases**:

- Tooltips on hover
- Additional context for links
- Full text for abbreviations
- Alternative information for iframes
- Accessible names (when ARIA not available)

**Rules**:

- Displayed as browser tooltip (usually on hover)
- Not accessible on touch devices (no hover)
- Not read by screen readers by default (use `aria-label` instead)
- Supports line breaks (browser-dependent)

**Examples**:

```html
<!-- Link with additional context -->
<a href="/downloads/report.pdf" title="Download the Q3 2025 Financial Report (PDF, 2.4MB)">
  Download Report
</a>

<!-- Abbreviation -->
<abbr title="HyperText Markup Language">HTML</abbr>

<!-- Icon button -->
<button title="Close dialog" aria-label="Close dialog">×</button>

<!-- Iframe description -->
<iframe src="map.html" title="Interactive campus map"></iframe>

<!-- Image with context (prefer alt) -->
<img src="chart.png" alt="Sales chart" title="Hover to see monthly breakdown" />

<!-- Disabled element explanation -->
<button disabled title="This feature requires a premium account">Premium Feature</button>

<!-- Truncated text -->
<div
  class="truncate"
  title="This is the full text that is truncated in the UI due to space constraints"
>
  This is the full text that is...
</div>
```

**Accessibility considerations**:

```html
<!-- ❌ BAD: Relying on title for accessibility -->
<button title="Save document">💾</button>

<!-- ✅ GOOD: Use aria-label for screen readers, title for tooltip -->
<button aria-label="Save document" title="Save document">💾</button>

<!-- ✅ BETTER: Use visible text -->
<button>💾 Save</button>
```

**Best practices**:

- Don't rely on `title` for critical information (not accessible on mobile)
- Use `aria-label` or `aria-labelledby` for accessibility
- Keep tooltips concise (1-2 sentences)
- Don't duplicate visible text in title
- Consider CSS tooltips for better control

---

### `lang`

**Purpose**: Specifies the **language** of the element's content.

**Value**: Language code (BCP 47 format, e.g., `en`, `en-US`, `es-MX`)

**Use cases**:

- Screen reader pronunciation
- Search engine language detection
- Font selection (CJK languages)
- Hyphenation and text wrapping
- Quotation mark styling

**Rules**:

- Inherits from parent if not specified
- Should be on `<html>` for document language
- Can override parent language for specific sections
- Format: language-REGION (e.g., `en-US`, `zh-CN`)

**Common language codes**:

- `en` - English (generic)
- `en-US` - English (United States)
- `en-GB` - English (United Kingdom)
- `es` - Spanish
- `fr` - French
- `de` - German
- `zh` - Chinese
- `zh-CN` - Chinese (Simplified, China)
- `zh-TW` - Chinese (Traditional, Taiwan)
- `ja` - Japanese
- `ar` - Arabic
- `ru` - Russian
- `pt-BR` - Portuguese (Brazil)

**Examples**:

```html
<!-- Document language (on <html>) -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>English Page</title>
  </head>
  <body>
    <p>This content is in English.</p>
  </body>
</html>

<!-- Mixed languages -->
<p lang="en">The French word for "hello" is <span lang="fr">bonjour</span>.</p>

<!-- Foreign phrase -->
<p>
  The restaurant serves authentic <span lang="es">tapas</span> and <span lang="es">paella</span>.
</p>

<!-- Multilingual content -->
<article lang="en">
  <h1>Welcome</h1>
  <p>English content here.</p>

  <section lang="es">
    <h2>Bienvenido</h2>
    <p>Contenido en español.</p>
  </section>

  <section lang="fr">
    <h2>Bienvenue</h2>
    <p>Contenu en français.</p>
  </section>
</article>

<!-- Regional variants -->
<p lang="en-US">I ordered a sweater and some fries.</p>
<p lang="en-GB">I ordered a jumper and some chips.</p>
```

**SEO and accessibility**:

```html
<!-- ✅ GOOD: Helps search engines and screen readers -->
<html lang="en-US">
  <head>
    <title>My Website</title>
    <!-- Alternate language versions -->
    <link rel="alternate" hreflang="es" href="https://example.com/es/" />
    <link rel="alternate" hreflang="fr" href="https://example.com/fr/" />
  </head>
  <body>
    <p>Content in American English</p>
  </body>
</html>
```

**Best practices**:

- Always set `lang` on `<html>` element
- Use most specific code when relevant (`en-US` vs `en`)
- Mark foreign words/phrases with appropriate `lang`
- Use for proper screen reader pronunciation
- Helps with SEO and search engine targeting

---

### `dir`

**Purpose**: Specifies the **text directionality** of the element's content.

**Value**: `ltr` (left-to-right), `rtl` (right-to-left), or `auto`

**Use cases**:

- Right-to-left languages (Arabic, Hebrew)
- Bidirectional text (mixing LTR and RTL)
- Form inputs with directional content
- Proper text alignment

**Values**:

- `ltr` - Left-to-right (English, most languages)
- `rtl` - Right-to-left (Arabic, Hebrew, Persian, Urdu)
- `auto` - Browser determines based on content

**Examples**:

```html
<!-- Document direction -->
<html lang="ar" dir="rtl">
  <head>
    <title>صفحة عربية</title>
  </head>
  <body>
    <p>هذا نص عربي</p>
  </body>
</html>

<!-- Mixed directionality -->
<p dir="ltr">English text with <span dir="rtl" lang="ar">نص عربي</span> embedded.</p>

<!-- Automatic detection -->
<input type="text" dir="auto" placeholder="Enter name" />

<!-- Bidirectional text -->
<article dir="ltr">
  <h1>About Arabic Language</h1>
  <p>The word for "peace" in Arabic is <span dir="rtl" lang="ar">سلام</span>.</p>
</article>

<!-- Table with RTL content -->
<table dir="rtl" lang="ar">
  <thead>
    <tr>
      <th>الاسم</th>
      <th>العمر</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>أحمد</td>
      <td>25</td>
    </tr>
  </tbody>
</table>
```

**CSS and directionality**:

```html
<style>
  /* Logical properties for internationalization */
  .box {
    margin-inline-start: 20px; /* Start side (left for LTR, right for RTL) */
    margin-inline-end: 10px; /* End side (right for LTR, left for RTL) */
    border-inline-start: 2px solid blue;
  }

  /* Direction-aware styles */
  [dir='rtl'] .icon {
    transform: scaleX(-1); /* Mirror icon for RTL */
  }
</style>

<div dir="rtl" class="box">محتوى عربي</div>
```

**Best practices**:

- Set `dir` on `<html>` for document direction
- Use `dir="auto"` for user-generated content
- Use CSS logical properties for internationalization
- Test layout with RTL languages
- Consider icon mirroring for RTL (arrows, chevrons)

---

### `translate`

**Purpose**: Specifies whether the element's content should be **translated** or not.

**Value**: `yes` (translate) or `no` (don't translate)

**Use cases**:

- Brand names (shouldn't be translated)
- Code examples
- Proper nouns
- Technical terms
- Content that must remain in original language

**Rules**:

- Inherits from parent if not specified
- Affects machine translation tools
- Respected by Google Translate and similar services

**Examples**:

```html
<!-- Brand name shouldn't be translated -->
<p>Welcome to <span translate="no">Acme Corp</span>, your trusted partner.</p>

<!-- Code examples -->
<p>Use the <code translate="no">Array.map()</code> function to transform arrays.</p>

<!-- Mixed content -->
<article>
  <h1>About <span translate="no">GitHub</span></h1>
  <p>
    <span translate="no">GitHub</span> is a platform for developers. The company name should not be
    translated.
  </p>
</article>

<!-- Technical documentation -->
<div translate="no">
  <h2>API Endpoint</h2>
  <code>POST /api/v1/users</code>
</div>

<!-- Product names in multilingual content -->
<p lang="es">
  Nuestro producto <span translate="no">SuperWidget Pro</span> está disponible en España.
</p>
```

**Best practices**:

- Use for brand names, product names
- Apply to code blocks and technical terms
- Mark proper nouns that shouldn't be translated
- Consider user experience (some names translate naturally)
- Test with Google Translate and other tools

---

### `hidden`

**Purpose**: Indicates that the element is **not yet, or no longer, relevant** and should be hidden.

**Value**: Boolean attribute (presence = hidden)

**Use cases**:

- Temporarily hide content
- Progressive disclosure
- Tabbed interfaces (inactive tabs)
- Conditional content
- JavaScript-controlled visibility

**Rules**:

- Content not rendered by browser
- Not read by screen readers
- Different from CSS `display: none` (semantic vs presentational)
- Can be overridden with CSS (not recommended)

**Examples**:

```html
<!-- Simple hidden element -->
<div hidden>This content is hidden</div>

<!-- Progressive disclosure -->
<details>
  <summary>Show Details</summary>
  <div id="details-content">Detailed information here</div>
</details>

<!-- Tabbed interface -->
<div class="tabs">
  <button data-tab="tab1">Tab 1</button>
  <button data-tab="tab2">Tab 2</button>
</div>

<div id="tab1" class="tab-content">Tab 1 content</div>

<div id="tab2" class="tab-content" hidden>Tab 2 content</div>

<script>
  const buttons = document.querySelectorAll('[data-tab]');
  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      // Hide all tabs
      document.querySelectorAll('.tab-content').forEach((tab) => {
        tab.hidden = true;
      });

      // Show selected tab
      const tabId = button.dataset.tab;
      document.getElementById(tabId).hidden = false;
    });
  });
</script>

<!-- Conditional content -->
<div id="error-message" hidden>An error occurred. Please try again.</div>

<script>
  function showError() {
    document.getElementById('error-message').hidden = false;
  }

  function hideError() {
    document.getElementById('error-message').hidden = true;
  }
</script>
```

**Hidden vs CSS display:none**:

```html
<!-- ✅ GOOD: Semantic hiding with hidden attribute -->
<div hidden>Hidden content</div>

<!-- ✅ GOOD: CSS for styling/layout -->
<div style="display: none;">Hidden for layout</div>

<!-- ⚠️ CAUTION: Can override hidden with CSS (not recommended) -->
<style>
  [hidden] {
    display: block !important; /* Overrides hidden attribute */
  }
</style>
```

**Best practices**:

- Use `hidden` for semantic content state
- Use CSS for presentational hiding
- Don't override `hidden` with CSS
- JavaScript can toggle: `element.hidden = true/false`
- Prefer `hidden` over `style="display: none"` when semantically hidden

---

### `tabindex`

**Purpose**: Controls **keyboard focus order** and focusability of elements.

**Value**: Integer (negative, 0, or positive)

**Values**:

- `-1` - Programmatically focusable, not in tab order
- `0` - Focusable and in natural tab order
- `1+` - Focusable, custom tab order (discouraged)

**Use cases**:

- Make non-interactive elements focusable
- Remove elements from tab order
- Custom component keyboard navigation
- Skip links and focus management

**Rules**:

- Native interactive elements (button, a, input) already focusable
- Positive values change tab order (confusing for users)
- Tab order: tabindex="1+" (ascending), then tabindex="0" (document order)

**Examples**:

```html
<!-- Make div focusable (for custom widget) -->
<div tabindex="0" role="button" onclick="handleClick()">Custom Button</div>

<!-- Programmatically focusable (JavaScript only, not Tab key) -->
<div id="modal-content" tabindex="-1">Modal content that receives focus when opened</div>

<script>
  function openModal() {
    const modal = document.getElementById('modal-content');
    modal.focus(); // Works because tabindex="-1"
  }
</script>

<!-- Skip link (accessibility) -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<nav>
  <!-- Navigation links -->
</nav>

<main id="main-content" tabindex="-1">
  <!-- Main content -->
</main>

<!-- Remove from tab order -->
<button tabindex="-1">Not focusable via Tab</button>

<!-- Bad: Custom tab order -->
<!-- ❌ WRONG: Confusing tab order -->
<input type="text" tabindex="3" />
<input type="text" tabindex="1" />
<input type="text" tabindex="2" />

<!-- ✅ CORRECT: Natural document order -->
<input type="text" />
<input type="text" />
<input type="text" />
```

**Focus management patterns**:

```html
<!-- Modal dialog focus management -->
<div class="modal" hidden>
  <div class="modal-content" tabindex="-1" role="dialog" aria-labelledby="modal-title">
    <h2 id="modal-title">Modal Title</h2>
    <button class="close-btn">Close</button>
    <p>Modal content</p>
  </div>
</div>

<script>
  let previousFocus;

  function openModal() {
    const modal = document.querySelector('.modal');
    const modalContent = document.querySelector('.modal-content');

    // Store current focus
    previousFocus = document.activeElement;

    // Show modal
    modal.hidden = false;

    // Focus modal
    modalContent.focus();

    // Trap focus inside modal
    trapFocus(modalContent);
  }

  function closeModal() {
    const modal = document.querySelector('.modal');
    modal.hidden = true;

    // Return focus to trigger
    previousFocus.focus();
  }
</script>
```

**Best practices**:

- Use `tabindex="0"` to add elements to tab order
- Use `tabindex="-1"` for programmatic focus only
- **Avoid positive tabindex values** (confusing navigation)
- Only add tabindex to interactive elements
- Test keyboard navigation thoroughly

---

### `contenteditable`

**Purpose**: Makes the element's content **editable** by the user.

**Value**: `true` (editable), `false` (not editable), or empty string (same as true)

**Use cases**:

- Rich text editors
- Inline editing
- Note-taking apps
- Collaborative editing
- WYSIWYG editors

**Rules**:

- User can type, paste, format content
- Inherits editability from parent if not specified
- Requires JavaScript to capture changes
- Different from form inputs (free-form editing)

**Examples**:

```html
<!-- Basic contenteditable -->
<div contenteditable="true">This text can be edited. Try clicking and typing!</div>

<!-- Styled editable area -->
<div
  contenteditable="true"
  style="
    min-height: 200px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
  "
  placeholder="Enter your notes here..."
></div>

<!-- Non-editable section within editable area -->
<div contenteditable="true">
  You can edit this text, but <span contenteditable="false">not this part</span>, and then continue
  editing here.
</div>

<!-- Rich text editor foundation -->
<div class="editor">
  <div class="toolbar">
    <button onclick="document.execCommand('bold')"><strong>B</strong></button>
    <button onclick="document.execCommand('italic')"><em>I</em></button>
    <button onclick="document.execCommand('underline')"><u>U</u></button>
  </div>
  <div
    id="editor-content"
    contenteditable="true"
    style="min-height: 300px; padding: 10px; border: 1px solid #ddd;"
  >
    Start typing here...
  </div>
</div>

<!-- Capture changes -->
<div id="editable" contenteditable="true">Editable content</div>

<script>
  const editable = document.getElementById('editable');

  // Listen for input
  editable.addEventListener('input', (e) => {
    console.log('Content changed:', e.target.innerHTML);
  });

  // Listen for paste
  editable.addEventListener('paste', (e) => {
    e.preventDefault();
    const text = e.clipboardData.getData('text/plain');
    document.execCommand('insertText', false, text);
  });

  // Save content
  function saveContent() {
    const content = editable.innerHTML;
    localStorage.setItem('savedContent', content);
  }

  // Load content
  function loadContent() {
    const content = localStorage.getItem('savedContent');
    if (content) {
      editable.innerHTML = content;
    }
  }
</script>
```

**Advanced example - Inline editing**:

```html
<style>
  [contenteditable]:focus {
    outline: 2px solid blue;
    background: #f9f9f9;
  }

  .edit-mode {
    border: 1px dashed #ccc;
    padding: 4px;
  }
</style>

<h1 contenteditable="true" class="edit-mode">Click to edit this heading</h1>

<p contenteditable="true" class="edit-mode">Click to edit this paragraph text.</p>

<script>
  // Make elements editable on click
  document.querySelectorAll('.edit-mode').forEach((el) => {
    el.addEventListener('blur', () => {
      console.log('Saved:', el.textContent);
      // Send to server or save locally
    });
  });
</script>
```

**Best practices**:

- Always handle `input` event to capture changes
- Sanitize user input (XSS risk)
- Use `execCommand` for formatting (deprecated but still works)
- Consider using libraries (Quill, TinyMCE, ProseMirror)
- Provide clear visual feedback for editable areas
- Test with screen readers (accessibility concerns)

---

### `draggable`

**Purpose**: Specifies whether the element is **draggable** using the Drag and Drop API.

**Value**: `true` (draggable), `false` (not draggable), or `auto` (default browser behavior)

**Use cases**:

- Drag-and-drop interfaces
- Reorderable lists
- File uploads (drag files to page)
- Kanban boards
- Custom UI interactions

**Rules**:

- Requires JavaScript to handle drag events
- Images and links are draggable by default
- Use `draggable="false"` to prevent dragging

**Examples**:

```html
<!-- Basic draggable element -->
<div
  draggable="true"
  style="width: 100px; height: 100px; background: blue; color: white; padding: 10px;"
>
  Drag me
</div>

<!-- Draggable list items -->
<ul id="draggable-list">
  <li draggable="true" data-id="1">Item 1</li>
  <li draggable="true" data-id="2">Item 2</li>
  <li draggable="true" data-id="3">Item 3</li>
</ul>

<script>
  let draggedElement = null;

  document.querySelectorAll('#draggable-list li').forEach((item) => {
    // Start drag
    item.addEventListener('dragstart', (e) => {
      draggedElement = e.target;
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/html', e.target.innerHTML);
      e.target.style.opacity = '0.5';
    });

    // End drag
    item.addEventListener('dragend', (e) => {
      e.target.style.opacity = '1';
    });

    // Allow drop
    item.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
    });

    // Drop
    item.addEventListener('drop', (e) => {
      e.preventDefault();
      if (draggedElement !== e.target) {
        // Swap elements
        const list = e.target.parentNode;
        const draggedIndex = Array.from(list.children).indexOf(draggedElement);
        const targetIndex = Array.from(list.children).indexOf(e.target);

        if (draggedIndex < targetIndex) {
          e.target.after(draggedElement);
        } else {
          e.target.before(draggedElement);
        }
      }
    });
  });
</script>

<!-- Prevent image dragging -->
<img src="logo.png" alt="Logo" draggable="false" />

<!-- Drag and drop file upload -->
<div
  id="drop-zone"
  style="
    width: 300px;
    height: 200px;
    border: 2px dashed #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
  "
>
  Drop files here
</div>

<script>
  const dropZone = document.getElementById('drop-zone');

  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = 'blue';
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.style.borderColor = '#ccc';
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#ccc';

    const files = e.dataTransfer.files;
    console.log('Files dropped:', files);

    Array.from(files).forEach((file) => {
      console.log('File:', file.name);
      // Upload file
    });
  });
</script>
```

**Best practices**:

- Provide visual feedback during drag
- Use appropriate cursor styles
- Handle all drag events (dragstart, dragover, drop, dragend)
- Test keyboard accessibility (drag-and-drop not keyboard accessible)
- Provide alternative for keyboard users
- Use semantic HTML with drag functionality as enhancement

---

### `spellcheck`

**Purpose**: Controls browser **spell-checking** for editable content.

**Value**: `true` (enable), `false` (disable), or empty string (default)

**Use cases**:

- Disable spellcheck for code editors
- Enable for text inputs
- Control for contenteditable areas
- Language-sensitive inputs

**Rules**:

- Only works on editable elements (input, textarea, contenteditable)
- Inherits from parent if not specified
- Browser-dependent appearance

**Examples**:

```html
<!-- Enable spellcheck (usually default) -->
<textarea spellcheck="true" placeholder="Write your essay here..."></textarea>

<!-- Disable spellcheck for code -->
<textarea spellcheck="false" placeholder="Enter code here...">
function hello() {
  console.log("varName"); // No red squigglies
}
</textarea>

<!-- Disable for username -->
<input type="text" spellcheck="false" placeholder="Username" />

<!-- Enable for email body -->
<div contenteditable="true" spellcheck="true" style="min-height: 200px; border: 1px solid #ccc;">
  Compose your email here...
</div>

<!-- Context-dependent -->
<form>
  <label for="code-input">Code:</label>
  <input type="text" id="code-input" spellcheck="false" />

  <label for="comment-input">Comment:</label>
  <textarea id="comment-input" spellcheck="true"></textarea>
</form>
```

**Best practices**:

- Disable for code, usernames, technical fields
- Enable for prose, comments, messages
- Test in different browsers (behavior varies)
- Consider user preferences (some users prefer always on/off)
- Use appropriate `lang` attribute for correct language spellcheck

---

## Data Attributes (`data-*`)

**Purpose**: Stores **custom data** private to the page or application.

**Format**: `data-{name}` where name is lowercase, no capitals

**Use cases**:

- Store metadata for JavaScript
- Configuration values
- State management
- Testing selectors
- Component data

**Rules**:

- Name must be lowercase after `data-`
- Can contain hyphens (converted to camelCase in dataset)
- Values are always strings
- Not intended for publicly available data

**Examples**:

```html
<!-- Basic data attributes -->
<article
  data-article-id="12345"
  data-author="Jane Doe"
  data-published="2025-11-04"
  data-category="technology"
>
  Article content
</article>

<!-- JavaScript access -->
<button
  id="delete-btn"
  data-item-id="42"
  data-item-type="product"
  data-confirm-message="Are you sure you want to delete this product?"
>
  Delete
</button>

<script>
  const btn = document.getElementById('delete-btn');

  // Access via dataset (camelCase)
  console.log(btn.dataset.itemId); // "42"
  console.log(btn.dataset.itemType); // "product"
  console.log(btn.dataset.confirmMessage); // "Are you sure..."

  // Set data attribute
  btn.dataset.status = 'pending'; // Sets data-status="pending"

  // Remove data attribute
  delete btn.dataset.itemId;

  btn.addEventListener('click', () => {
    const id = btn.dataset.itemId;
    const message = btn.dataset.confirmMessage;

    if (confirm(message)) {
      deleteItem(id);
    }
  });
</script>

<!-- CSS selection -->
<style>
  [data-status='active'] {
    background-color: green;
  }

  [data-status='inactive'] {
    background-color: gray;
  }

  [data-priority='high'] {
    border-left: 4px solid red;
  }
</style>

<div data-status="active" data-priority="high">High priority active item</div>

<!-- Complex data (JSON) -->
<div id="widget" data-config='{"theme":"dark","size":"large","animation":true}'>Widget</div>

<script>
  const widget = document.getElementById('widget');
  const config = JSON.parse(widget.dataset.config);
  console.log(config.theme); // "dark"
</script>

<!-- Component configuration -->
<div
  class="carousel"
  data-autoplay="true"
  data-interval="3000"
  data-loop="true"
  data-animation="slide"
>
  <!-- Carousel items -->
</div>

<!-- Testing selectors -->
<button data-testid="submit-button">Submit</button>
<input data-testid="email-input" type="email" />

<!-- State management -->
<div class="accordion-item" data-expanded="false">
  <button class="accordion-header" data-toggle="accordion">Section Title</button>
  <div class="accordion-content">Content here</div>
</div>
```

**Best practices**:

- Use data attributes for JavaScript data, not styling
- Keep names descriptive and kebab-case
- Don't store sensitive information
- Don't use for SEO data (use microdata instead)
- Consider performance (dataset access creates overhead)
- Use for component configuration and state

---

## ARIA Attributes

**Purpose**: Enhance **accessibility** by providing semantic information to assistive technologies.

**Note**: ARIA attributes covered comprehensively in `references/accessibility.md`. See that file for complete details.

**Common ARIA attributes**:

- `aria-label` - Accessible name for element
- `aria-labelledby` - References element(s) that label this one
- `aria-describedby` - References element(s) that describe this one
- `aria-hidden` - Hide from assistive technologies
- `aria-live` - Announce dynamic changes
- `aria-expanded` - Indicates expanded state
- `aria-pressed` - Toggle button state
- `aria-checked` - Checkbox/radio state
- `aria-selected` - Selection state
- `aria-disabled` - Disabled state
- `aria-invalid` - Form validation state
- `aria-required` - Required field
- `role` - Semantic role (button, dialog, navigation, etc.)

**Quick examples**:

```html
<!-- Accessible name -->
<button aria-label="Close dialog">×</button>

<!-- Label association -->
<div id="dialog-title">Confirmation</div>
<div role="dialog" aria-labelledby="dialog-title">Content</div>

<!-- Description -->
<button aria-describedby="help-text">Submit</button>
<div id="help-text">Clicking will save your changes</div>

<!-- Live regions -->
<div aria-live="polite" aria-atomic="true">3 new messages</div>

<!-- Expanded state -->
<button aria-expanded="false" aria-controls="menu">Menu</button>
<div id="menu" hidden>Menu items</div>

<!-- Form validation -->
<input type="email" aria-invalid="true" aria-describedby="email-error" required />
<div id="email-error" role="alert">Please enter a valid email address</div>
```

**Best practices**:

- Use semantic HTML first, ARIA second
- Don't override semantic roles unnecessarily
- Test with screen readers (NVDA, JAWS, VoiceOver)
- See `references/accessibility.md` for complete guide

---

## Event Handler Attributes

**Purpose**: Define **inline JavaScript** to execute when events occur.

**Note**: Inline event handlers considered bad practice - use addEventListener instead.

**Common event handlers**:

- `onclick` - Element clicked
- `onload` - Resource loaded
- `onerror` - Error occurred
- `onsubmit` - Form submitted
- `onchange` - Form value changed
- `oninput` - Input value changed
- `onfocus` - Element focused
- `onblur` - Element lost focus
- `onmouseover` - Mouse enters element
- `onmouseout` - Mouse leaves element
- `onkeydown` - Key pressed down
- `onkeyup` - Key released

**Examples (old style - not recommended)**:

```html
<!-- ❌ BAD: Inline event handlers -->
<button onclick="alert('Clicked!')">Click Me</button>

<form onsubmit="return validateForm()">
  <input type="text" onchange="console.log('Changed')" />
  <button type="submit">Submit</button>
</form>

<img src="image.jpg" onerror="this.src='fallback.jpg'" alt="Image" />

<!-- ✅ GOOD: Use addEventListener -->
<button id="myButton">Click Me</button>

<script>
  document.getElementById('myButton').addEventListener('click', () => {
    alert('Clicked!');
  });
</script>
```

**Why avoid inline handlers**:

1. **Security**: CSP blocks inline event handlers
2. **Maintainability**: JavaScript should be separate
3. **Reusability**: Can't reuse inline handlers
4. **Testing**: Harder to test inline code
5. **Separation of concerns**: Keep HTML and JS separate

**Best practices**:

- Use `addEventListener()` instead of inline handlers
- Enable CSP to block inline handlers: `script-src 'self'`
- Keep JavaScript in external files or `<script>` tags
- Exception: Small projects/prototypes/demos only

---

## Global Attributes Summary Table

| Attribute         | Purpose           | Example Values             |
| ----------------- | ----------------- | -------------------------- |
| `id`              | Unique identifier | `"main-header"`            |
| `class`           | CSS class names   | `"btn btn-primary"`        |
| `style`           | Inline CSS        | `"color: red;"`            |
| `title`           | Tooltip text      | `"Click to close"`         |
| `lang`            | Language code     | `"en"`, `"es"`, `"fr"`     |
| `dir`             | Text direction    | `"ltr"`, `"rtl"`, `"auto"` |
| `translate`       | Translation flag  | `"yes"`, `"no"`            |
| `hidden`          | Hide element      | (boolean)                  |
| `tabindex`        | Focus order       | `-1`, `0`, `1+`            |
| `contenteditable` | Editable content  | `"true"`, `"false"`        |
| `draggable`       | Drag-and-drop     | `"true"`, `"false"`        |
| `spellcheck`      | Spell checking    | `"true"`, `"false"`        |
| `data-*`          | Custom data       | `data-id="123"`            |
| `aria-*`          | Accessibility     | `aria-label="Close"`       |
| `on*`             | Event handlers    | `onclick="fn()"` (avoid)   |

---

## Global Attributes Best Practices

### 1. Identity and Classification

- Use `id` for unique elements and JavaScript hooks
- Use `class` for styling and grouping
- Establish consistent naming conventions

### 2. Accessibility

- Always include `lang` on `<html>`
- Use `aria-*` attributes appropriately (see accessibility.md)
- Provide `title` for additional context (but not for critical info)
- Use `tabindex` carefully (mostly -1 and 0)

### 3. Data Storage

- Use `data-*` for application data
- Keep data attribute names descriptive
- Don't store sensitive information
- Consider JSON for complex data

### 4. Internationalization

- Set `lang` for proper screen reader pronunciation
- Use `dir` for RTL languages
- Mark untranslatable content with `translate="no"`

### 5. Interactivity

- Use `hidden` for semantic hiding
- Use `contenteditable` sparingly (security risk)
- Avoid inline event handlers (use addEventListener)
- Test keyboard navigation with `tabindex`

### 6. Performance

- Minimize inline styles (use CSS classes)
- Avoid excessive data attributes (DOM overhead)
- Use CSS for visual changes, not JavaScript when possible

### 7. Security

- Sanitize user input in `contenteditable`
- Be cautious with `innerHTML` and data attributes
- Use CSP to block inline event handlers
- Validate and escape data attribute values

---

## Testing Checklist

- [ ] IDs are unique throughout document
- [ ] Class names follow naming convention
- [ ] `lang` attribute set on `<html>`
- [ ] `dir` set appropriately for RTL languages
- [ ] `hidden` used for semantic hiding
- [ ] `tabindex` tested with keyboard navigation
- [ ] `aria-*` attributes validated (see accessibility.md)
- [ ] No inline event handlers (use addEventListener)
- [ ] `data-*` attributes follow naming conventions
- [ ] `title` provides useful additional context
- [ ] `contenteditable` input sanitized
- [ ] Draggable elements have keyboard alternatives

---

## Further Reading

- WHATWG HTML Standard - Global Attributes: https://html.spec.whatwg.org/multipage/dom.html#global-attributes
- MDN Web Docs - Global Attributes: https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes
- W3C ARIA Specification: https://www.w3.org/TR/wai-aria/
- BCP 47 Language Tags: https://www.rfc-editor.org/info/bcp47
