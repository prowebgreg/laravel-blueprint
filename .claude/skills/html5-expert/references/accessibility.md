# Accessibility Best Practices

Comprehensive guide to creating accessible HTML using semantic markup, ARIA attributes, and WCAG compliance standards.

## Overview

Web accessibility ensures that websites, tools, and technologies are designed and developed so that people with disabilities can use them. This includes:

- **Visual disabilities** - Blindness, low vision, color blindness
- **Auditory disabilities** - Deafness, hard of hearing
- **Motor disabilities** - Limited mobility, tremors, paralysis
- **Cognitive disabilities** - Learning disabilities, ADHD, memory issues
- **Neurological disabilities** - Epilepsy, seizure disorders
- **Speech disabilities** - Difficulty producing speech

**Core Principle**: Accessibility benefits everyone - keyboard users, mobile users, older adults, people with temporary disabilities, and users with slow internet connections.

**Legal Requirements**: Many countries require web accessibility (ADA in US, EAA in EU, etc.).

---

## The Four WCAG Principles (POUR)

### 1. Perceivable

Information and user interface components must be presentable to users in ways they can perceive.

**Guidelines**:

- Provide text alternatives for non-text content
- Provide captions and alternatives for multimedia
- Make content adaptable (different presentations)
- Make it easier to see and hear content

### 2. Operable

User interface components and navigation must be operable.

**Guidelines**:

- Make all functionality available from keyboard
- Give users enough time to read and use content
- Don't design content that causes seizures
- Help users navigate and find content

### 3. Understandable

Information and operation of user interface must be understandable.

**Guidelines**:

- Make text readable and understandable
- Make content appear and operate in predictable ways
- Help users avoid and correct mistakes

### 4. Robust

Content must be robust enough to be interpreted by a wide variety of user agents, including assistive technologies.

**Guidelines**:

- Maximize compatibility with current and future user agents
- Use valid HTML
- Provide names, roles, and values for UI components

---

## WCAG Conformance Levels

### Level A (Minimum)

**Essential** accessibility features that all websites should meet.

**Examples**:

- Text alternatives for images
- Keyboard accessibility
- Color is not the only means of conveying information
- Form labels

### Level AA (Recommended)

**Standard** level for most organizations and legal requirements.

**Examples**:

- Color contrast ratios (4.5:1 for normal text)
- Resize text up to 200%
- Multiple ways to navigate
- Consistent navigation
- Error identification and suggestions

### Level AAA (Enhanced)

**Highest** level of accessibility - often not fully achievable.

**Examples**:

- Higher color contrast (7:1 for normal text)
- Sign language for audio
- Extended audio descriptions
- No timing requirements

**Target**: Aim for **WCAG 2.1 Level AA** compliance as minimum standard.

---

## Semantic HTML First

### Why Semantic HTML Matters for Accessibility

**Benefits**:

- Built-in keyboard accessibility
- Screen reader compatibility
- No ARIA required (in most cases)
- Browser handles accessibility automatically
- Less code to maintain

**First Rule of ARIA**: Don't use ARIA if you can use semantic HTML instead.

### Semantic vs Non-Semantic Examples

#### Navigation

```html
<!-- ❌ BAD: Generic div without semantics -->
<div class="navigation">
  <ul>
    <li><a href="/">Home</a></li>
    <li><a href="/about">About</a></li>
  </ul>
</div>

<!-- ✅ GOOD: Semantic nav element -->
<nav aria-label="Primary navigation">
  <ul>
    <li><a href="/">Home</a></li>
    <li><a href="/about">About</a></li>
  </ul>
</nav>
```

#### Buttons

```html
<!-- ❌ BAD: Div styled as button -->
<div class="button" onclick="submit()">Submit</div>

<!-- ❌ BAD: Link used as button -->
<a href="#" onclick="submit(); return false;">Submit</a>

<!-- ✅ GOOD: Semantic button element -->
<button type="button" onclick="submit()">Submit</button>
```

#### Form Controls

```html
<!-- ❌ BAD: Div as checkbox -->
<div class="checkbox" onclick="toggleCheck()">
  <span class="checkmark"></span>
  Option 1
</div>

<!-- ✅ GOOD: Semantic checkbox -->
<label>
  <input type="checkbox" name="option1" />
  Option 1
</label>
```

---

## ARIA Basics

### What is ARIA?

**ARIA (Accessible Rich Internet Applications)** provides extra semantics and information to assistive technologies when HTML semantics are insufficient.

**Three main features**:

1. **Roles** - What an element is/does
2. **States** - Current condition of element
3. **Properties** - Characteristics and relationships

### Five Rules of ARIA

**Rule 1**: Don't use ARIA if you can use native HTML

```html
<!-- ❌ Don't do this -->
<div role="button">Click me</div>

<!-- ✅ Do this -->
<button>Click me</button>
```

**Rule 2**: Don't change native semantics (unless you really have to)

```html
<!-- ❌ Don't do this -->
<h2 role="button">Heading as button</h2>

<!-- ✅ Do this -->
<h2>Heading</h2>
<button>Separate button</button>
```

**Rule 3**: All interactive ARIA controls must be keyboard accessible

```html
<!-- ❌ Missing keyboard support -->
<div role="button" onclick="doSomething()">Click</div>

<!-- ✅ Keyboard accessible -->
<div role="button" tabindex="0" onclick="doSomething()" onkeypress="handleKey(event)">Click</div>

<!-- ✅ Better: Use semantic HTML -->
<button onclick="doSomething()">Click</button>
```

**Rule 4**: Don't hide focusable elements

```html
<!-- ❌ Hidden but focusable -->
<button style="display: none;">Hidden button</button>

<!-- ✅ Properly hidden -->
<button hidden>Hidden button</button>
<button aria-hidden="true" tabindex="-1">Decorative</button>
```

**Rule 5**: All interactive elements must have an accessible name

```html
<!-- ❌ No accessible name -->
<button><span class="icon-close"></span></button>

<!-- ✅ Has accessible name -->
<button aria-label="Close">
  <span class="icon-close" aria-hidden="true"></span>
</button>
```

---

## ARIA Roles

### Landmark Roles

**Purpose**: Define page regions for navigation

```html
<!-- Native HTML provides implicit roles -->
<header>
  <!-- role="banner" (top-level only) -->
  <nav>
    <!-- role="navigation" -->
    <main>
      <!-- role="main" -->
      <aside>
        <!-- role="complementary" -->
        <footer>
          <!-- role="contentinfo" (top-level only) -->
          <form>
            <!-- role="form" (if has accessible name) -->
            <section>
              <!-- role="region" (if has accessible name) -->

              <!-- Explicit roles when semantic HTML unavailable -->
              <div role="navigation">
                <ul>
                  ...
                </ul>
              </div>

              <div role="main">Main content</div>

              <div role="complementary">Sidebar content</div>
            </section>
          </form>
        </footer>
      </aside>
    </main>
  </nav>
</header>
```

**Best Practice**: Use semantic HTML; add `aria-label` to distinguish multiple landmarks

```html
<nav aria-label="Primary navigation">
  <ul>
    ...
  </ul>
</nav>

<nav aria-label="Footer navigation">
  <ul>
    ...
  </ul>
</nav>
```

### Widget Roles

**Purpose**: Define interactive components

```html
<!-- Common widget roles -->
<div role="button">Button</div>
<div role="checkbox" aria-checked="false">Option</div>
<div role="tab" aria-selected="true">Tab 1</div>
<div role="tabpanel">Tab content</div>
<div role="dialog" aria-labelledby="dialog-title">Dialog</div>
<div role="alertdialog">Alert dialog</div>
<div role="menu">Context menu</div>
<div role="menuitem">Menu option</div>
<div role="tooltip">Tooltip text</div>
<div role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
```

**Remember**: Always prefer semantic HTML when available!

### Document Structure Roles

```html
<div role="article">Article content</div>
<div role="list">
  <div role="listitem">Item 1</div>
  <div role="listitem">Item 2</div>
</div>
<div role="table">
  <div role="row">
    <div role="columnheader">Header</div>
  </div>
  <div role="row">
    <div role="cell">Data</div>
  </div>
</div>
```

---

## ARIA States and Properties

### Common ARIA States

**States** change with user interaction:

```html
<!-- Checked state -->
<div role="checkbox" aria-checked="true">Selected</div>
<div role="checkbox" aria-checked="false">Not selected</div>
<div role="checkbox" aria-checked="mixed">Partially selected</div>

<!-- Expanded state -->
<button aria-expanded="false" aria-controls="menu">Menu</button>
<div id="menu" hidden>Menu content</div>

<!-- Pressed state (toggle button) -->
<button aria-pressed="false">Not pressed</button>
<button aria-pressed="true">Pressed</button>

<!-- Selected state -->
<div role="tab" aria-selected="true">Active tab</div>
<div role="tab" aria-selected="false">Inactive tab</div>

<!-- Disabled state -->
<button aria-disabled="true">Disabled button</button>

<!-- Hidden state -->
<div aria-hidden="true">Hidden from screen readers</div>
```

### Common ARIA Properties

**Properties** define characteristics:

```html
<!-- Labels and descriptions -->
<button aria-label="Close dialog">×</button>
<button aria-labelledby="title">Save</button>
<span id="title">Save document</span>

<input type="text" aria-describedby="help-text" />
<div id="help-text">Enter your full name</div>

<!-- Required fields -->
<input type="text" required aria-required="true" />

<!-- Invalid state -->
<input type="email" aria-invalid="true" aria-describedby="error" />
<div id="error" role="alert">Invalid email address</div>

<!-- Live regions -->
<div aria-live="polite">Status updates appear here</div>
<div aria-live="assertive">Critical alerts</div>
<div role="status">Polite updates</div>
<div role="alert">Important announcements</div>

<!-- Relationships -->
<div aria-controls="panel1">Controller</div>
<div id="panel1">Controlled panel</div>

<button aria-owns="submenu">Menu</button>
<ul id="submenu">
  ...
</ul>

<!-- Value properties -->
<div
  role="slider"
  aria-valuemin="0"
  aria-valuemax="100"
  aria-valuenow="50"
  aria-valuetext="50 percent"
></div>
```

---

## Accessible Forms

### Form Labels

```html
<!-- ✅ GOOD: Explicit label with for/id -->
<label for="username">Username:</label>
<input type="text" id="username" name="username" required />

<!-- ✅ GOOD: Implicit label (wrapping) -->
<label>
  Email:
  <input type="email" name="email" required />
</label>

<!-- ✅ GOOD: aria-label when visual label not desired -->
<input type="search" aria-label="Search products" placeholder="Search..." />

<!-- ✅ GOOD: aria-labelledby for complex labels -->
<div id="username-label">
  Username
  <span class="required">*</span>
  <span class="help">(lowercase only)</span>
</div>
<input type="text" aria-labelledby="username-label" />

<!-- ❌ BAD: No label -->
<input type="text" placeholder="Username" />

<!-- ❌ BAD: Label without association -->
<label>Username</label>
<input type="text" name="username" />
```

### Required Fields

```html
<!-- ✅ GOOD: Required attribute + visual indicator -->
<label for="email">
  Email <span aria-hidden="true">*</span>
  <span class="sr-only">(required)</span>
</label>
<input type="email" id="email" name="email" required />

<!-- Alternative with aria-required -->
<label for="phone">Phone Number</label>
<input type="tel" id="phone" name="phone" aria-required="true" />

<!-- Legend for required fields -->
<p><span aria-hidden="true">*</span> = Required field</p>
```

### Form Validation

```html
<!-- ✅ GOOD: Validation with aria-invalid and aria-describedby -->
<label for="email">Email:</label>
<input
  type="email"
  id="email"
  name="email"
  aria-invalid="true"
  aria-describedby="email-error"
  required
/>
<div id="email-error" role="alert">Please enter a valid email address</div>

<!-- Success message -->
<div role="status" aria-live="polite">Form submitted successfully</div>

<!-- Error summary -->
<div role="alert" aria-live="assertive">
  <h2>Form contains 3 errors:</h2>
  <ul>
    <li><a href="#email">Email is required</a></li>
    <li><a href="#password">Password must be 8+ characters</a></li>
    <li><a href="#terms">Terms must be accepted</a></li>
  </ul>
</div>
```

### Fieldsets and Legends

```html
<!-- ✅ GOOD: Group related fields -->
<fieldset>
  <legend>Shipping Address</legend>

  <label for="street">Street:</label>
  <input type="text" id="street" name="street" />

  <label for="city">City:</label>
  <input type="text" id="city" name="city" />

  <label for="zip">ZIP Code:</label>
  <input type="text" id="zip" name="zip" />
</fieldset>

<!-- Radio buttons -->
<fieldset>
  <legend>Select shipping method:</legend>

  <label>
    <input type="radio" name="shipping" value="standard" />
    Standard (3-5 days)
  </label>

  <label>
    <input type="radio" name="shipping" value="express" />
    Express (1-2 days)
  </label>
</fieldset>
```

### Form Instructions

```html
<!-- ✅ GOOD: Instructions associated with field -->
<label for="password">Password:</label>
<input type="password" id="password" name="password" aria-describedby="password-requirements" />
<div id="password-requirements">
  Must be at least 8 characters with 1 uppercase letter and 1 number
</div>

<!-- Multiple descriptions -->
<label for="username">Username:</label>
<input type="text" id="username" aria-describedby="username-format username-availability" />
<div id="username-format">Lowercase letters and numbers only</div>
<div id="username-availability" role="status">Checking availability...</div>
```

---

## Keyboard Accessibility

### Focus Management

```html
<!-- ✅ GOOD: Visible focus indicator -->
<style>
  button:focus {
    outline: 2px solid blue;
    outline-offset: 2px;
  }

  /* Never remove focus styles without replacement */
  /* ❌ BAD: *:focus { outline: none; } */
</style>

<!-- ✅ GOOD: Skip link for keyboard users -->
<a href="#main-content" class="skip-link"> Skip to main content </a>
<nav>...</nav>
<main id="main-content" tabindex="-1">Main content</main>

<style>
  .skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #000;
    color: #fff;
    padding: 8px;
    text-decoration: none;
  }

  .skip-link:focus {
    top: 0;
  }
</style>
```

### Tab Order

```html
<!-- ✅ GOOD: Natural tab order -->
<button>First</button>
<button>Second</button>
<button>Third</button>

<!-- ✅ GOOD: Make element focusable programmatically -->
<div id="content" tabindex="-1">Content that can receive focus via JavaScript</div>

<!-- ✅ GOOD: Add non-interactive element to tab order -->
<div role="button" tabindex="0" onclick="handleClick()">Custom button (keyboard accessible)</div>

<!-- ❌ BAD: Positive tabindex (confusing order) -->
<button tabindex="3">Third</button>
<button tabindex="1">First</button>
<button tabindex="2">Second</button>
```

### Keyboard Event Handling

```html
<div role="button" tabindex="0" onclick="handleClick()" onkeydown="handleKeyDown(event)">
  Custom Button
</div>

<script>
  function handleKeyDown(event) {
    // Enter or Space activates button
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      handleClick();
    }
  }
</script>

<!-- ✅ BETTER: Use semantic button (keyboard handling built-in) -->
<button onclick="handleClick()">Semantic Button</button>
```

---

## Accessible Images

### Alt Text Best Practices

```html
<!-- ✅ GOOD: Descriptive alt text -->
<img src="golden-retriever.jpg" alt="Golden retriever puppy playing in grass" />

<!-- ✅ GOOD: Informative image -->
<img src="chart.png" alt="Bar chart showing 30% sales increase in Q3" />

<!-- ✅ GOOD: Decorative image (empty alt) -->
<img src="decorative-line.png" alt="" />

<!-- ✅ GOOD: Complex image with detailed description -->
<figure>
  <img src="infographic.png" alt="Benefits of exercise infographic" />
  <figcaption>
    <details>
      <summary>Full infographic description</summary>
      <p>The infographic shows five main benefits of exercise...</p>
    </details>
  </figcaption>
</figure>

<!-- ✅ GOOD: Functional image (button/link) -->
<a href="/search">
  <img src="search-icon.svg" alt="Search" />
</a>

<button>
  <img src="close-icon.svg" alt="Close dialog" />
</button>

<!-- ❌ BAD: No alt attribute -->
<img src="photo.jpg" />

<!-- ❌ BAD: Redundant "image of" -->
<img src="dog.jpg" alt="Image of a dog" />

<!-- ❌ BAD: Filename as alt -->
<img src="IMG_1234.jpg" alt="IMG_1234.jpg" />
```

### Text in Images

```html
<!-- ❌ AVOID: Important text in image -->
<img src="announcement.png" alt="We're hiring! Apply now at careers page" />

<!-- ✅ BETTER: Text as HTML, image decorative -->
<div>
  <img src="decorative-banner.png" alt="" />
  <h2>We're Hiring!</h2>
  <p>Apply now at our <a href="/careers">careers page</a></p>
</div>
```

### SVG Accessibility

```html
<!-- ✅ GOOD: Inline SVG with title and desc -->
<svg role="img" aria-labelledby="svg-title svg-desc">
  <title id="svg-title">Company Logo</title>
  <desc id="svg-desc">Blue circle with white text</desc>
  <circle cx="50" cy="50" r="40" fill="blue" />
  <text x="50" y="55" text-anchor="middle" fill="white">Logo</text>
</svg>

<!-- ✅ GOOD: Decorative SVG -->
<svg aria-hidden="true" focusable="false">
  <circle cx="50" cy="50" r="40" />
</svg>

<!-- ✅ GOOD: SVG as img -->
<img src="logo.svg" alt="Company Logo" />
```

---

## Accessible Tables

### Data Tables

```html
<!-- ✅ GOOD: Complete accessible table -->
<table>
  <caption>
    Employee Contact Information
  </caption>
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Department</th>
      <th scope="col">Email</th>
      <th scope="col">Phone</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Alice Johnson</th>
      <td>Engineering</td>
      <td>alice@example.com</td>
      <td>555-0100</td>
    </tr>
    <tr>
      <th scope="row">Bob Smith</th>
      <td>Marketing</td>
      <td>bob@example.com</td>
      <td>555-0101</td>
    </tr>
  </tbody>
</table>

<!-- Required elements:
     - <caption> for table description
     - <th> for headers with scope attribute
     - scope="col" for column headers
     - scope="row" for row headers
-->
```

### Complex Tables

```html
<!-- ✅ GOOD: Complex table with id/headers -->
<table>
  <caption>
    Quarterly Sales by Region
  </caption>
  <thead>
    <tr>
      <th id="region" scope="col">Region</th>
      <th id="q1" scope="col">Q1</th>
      <th id="q2" scope="col">Q2</th>
      <th id="q3" scope="col">Q3</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th id="north" scope="row">North</th>
      <td headers="north q1">$100K</td>
      <td headers="north q2">$120K</td>
      <td headers="north q3">$135K</td>
    </tr>
    <tr>
      <th id="south" scope="row">South</th>
      <td headers="south q1">$90K</td>
      <td headers="south q2">$95K</td>
      <td headers="south q3">$110K</td>
    </tr>
  </tbody>
</table>
```

### Responsive Tables

```html
<!-- ✅ GOOD: Responsive table with data attributes -->
<table class="responsive-table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td data-label="Name">Alice Johnson</td>
      <td data-label="Email">alice@example.com</td>
      <td data-label="Phone">555-0100</td>
    </tr>
  </tbody>
</table>

<style>
  @media screen and (max-width: 600px) {
    .responsive-table thead {
      position: absolute;
      clip: rect(0 0 0 0);
    }

    .responsive-table td {
      display: block;
      text-align: right;
    }

    .responsive-table td::before {
      content: attr(data-label);
      float: left;
      font-weight: bold;
    }
  }
</style>
```

---

## Accessible Navigation

### Main Navigation

```html
<!-- ✅ GOOD: Accessible navigation with aria-label -->
<nav aria-label="Primary navigation">
  <ul>
    <li><a href="/" aria-current="page">Home</a></li>
    <li><a href="/products">Products</a></li>
    <li><a href="/about">About</a></li>
    <li><a href="/contact">Contact</a></li>
  </ul>
</nav>

<!-- aria-current="page" indicates current page -->
```

### Breadcrumb Navigation

```html
<!-- ✅ GOOD: Breadcrumb navigation -->
<nav aria-label="Breadcrumb">
  <ol>
    <li><a href="/">Home</a></li>
    <li><a href="/electronics">Electronics</a></li>
    <li><a href="/electronics/cameras">Cameras</a></li>
    <li><a href="/electronics/cameras/dslr" aria-current="page">DSLR</a></li>
  </ol>
</nav>
```

### Pagination

```html
<!-- ✅ GOOD: Accessible pagination -->
<nav aria-label="Pagination">
  <ul>
    <li>
      <a href="?page=1" aria-label="Go to first page">
        <span aria-hidden="true">«</span>
      </a>
    </li>
    <li>
      <a href="?page=4" aria-label="Go to previous page, page 4">
        <span aria-hidden="true">‹</span>
      </a>
    </li>
    <li><a href="?page=1">1</a></li>
    <li><a href="?page=2">2</a></li>
    <li><a href="?page=3">3</a></li>
    <li><a href="?page=4">4</a></li>
    <li>
      <span aria-current="page" aria-label="Page 5, current page">5</span>
    </li>
    <li><a href="?page=6">6</a></li>
    <li>
      <a href="?page=6" aria-label="Go to next page, page 6">
        <span aria-hidden="true">›</span>
      </a>
    </li>
    <li>
      <a href="?page=10" aria-label="Go to last page, page 10">
        <span aria-hidden="true">»</span>
      </a>
    </li>
  </ul>
</nav>
```

---

## Common Accessible Patterns

### Modal Dialog

```html
<!-- ✅ GOOD: Accessible modal dialog -->
<div id="dialog" role="dialog" aria-labelledby="dialog-title" aria-modal="true" hidden>
  <div class="dialog-content">
    <h2 id="dialog-title">Confirm Action</h2>
    <p>Are you sure you want to delete this item?</p>

    <div>
      <button id="confirm-button">Confirm</button>
      <button id="cancel-button">Cancel</button>
    </div>
  </div>
</div>

<script>
  let previousFocus;

  function openDialog() {
    const dialog = document.getElementById('dialog');

    // Store current focus
    previousFocus = document.activeElement;

    // Show dialog
    dialog.hidden = false;

    // Move focus to dialog
    document.getElementById('confirm-button').focus();

    // Trap focus in dialog
    trapFocus(dialog);
  }

  function closeDialog() {
    const dialog = document.getElementById('dialog');

    // Hide dialog
    dialog.hidden = true;

    // Return focus
    previousFocus.focus();
  }

  function trapFocus(element) {
    const focusableElements = element.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    element.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        if (e.shiftKey) {
          if (document.activeElement === firstFocusable) {
            e.preventDefault();
            lastFocusable.focus();
          }
        } else {
          if (document.activeElement === lastFocusable) {
            e.preventDefault();
            firstFocusable.focus();
          }
        }
      }

      if (e.key === 'Escape') {
        closeDialog();
      }
    });
  }
</script>
```

### Tabs

```html
<!-- ✅ GOOD: Accessible tabs -->
<div class="tabs">
  <div role="tablist" aria-label="Product Information">
    <button role="tab" aria-selected="true" aria-controls="tab1" id="tab1-button">
      Description
    </button>
    <button role="tab" aria-selected="false" aria-controls="tab2" id="tab2-button" tabindex="-1">
      Specifications
    </button>
    <button role="tab" aria-selected="false" aria-controls="tab3" id="tab3-button" tabindex="-1">
      Reviews
    </button>
  </div>

  <div role="tabpanel" id="tab1" aria-labelledby="tab1-button">
    <p>Product description content...</p>
  </div>

  <div role="tabpanel" id="tab2" aria-labelledby="tab2-button" hidden>
    <p>Technical specifications...</p>
  </div>

  <div role="tabpanel" id="tab3" aria-labelledby="tab3-button" hidden>
    <p>Customer reviews...</p>
  </div>
</div>

<script>
  // Keyboard navigation: Arrow keys to switch tabs, Home/End for first/last
  // Selected tab: tabindex="0", others: tabindex="-1"
  // Update aria-selected and show/hide panels
</script>
```

### Accordion

```html
<!-- ✅ GOOD: Accessible accordion -->
<div class="accordion">
  <h3>
    <button aria-expanded="false" aria-controls="section1" id="accordion1">Section 1</button>
  </h3>
  <div id="section1" role="region" aria-labelledby="accordion1" hidden>
    <p>Content for section 1...</p>
  </div>

  <h3>
    <button aria-expanded="false" aria-controls="section2" id="accordion2">Section 2</button>
  </h3>
  <div id="section2" role="region" aria-labelledby="accordion2" hidden>
    <p>Content for section 2...</p>
  </div>
</div>

<script>
  // Toggle aria-expanded and show/hide content
  // Optional: Close other sections when opening one
</script>
```

### Dropdown Menu

```html
<!-- ✅ GOOD: Accessible dropdown menu -->
<nav>
  <ul role="menubar">
    <li role="none">
      <button role="menuitem" aria-haspopup="true" aria-expanded="false" aria-controls="submenu1">
        Products
      </button>
      <ul role="menu" id="submenu1" hidden>
        <li role="none">
          <a role="menuitem" href="/products/laptops">Laptops</a>
        </li>
        <li role="none">
          <a role="menuitem" href="/products/desktops">Desktops</a>
        </li>
        <li role="none">
          <a role="menuitem" href="/products/tablets">Tablets</a>
        </li>
      </ul>
    </li>
  </ul>
</nav>

<script>
  // Keyboard: Enter/Space to open, Escape to close, Arrow keys to navigate
</script>
```

### Alert/Notification

```html
<!-- ✅ GOOD: Polite announcement (non-interrupting) -->
<div role="status" aria-live="polite" aria-atomic="true">
  <p>3 new messages</p>
</div>

<!-- ✅ GOOD: Assertive alert (interrupting) -->
<div role="alert" aria-live="assertive" aria-atomic="true">
  <p>Error: Your session has expired. Please log in again.</p>
</div>

<!-- JavaScript adds content, screen reader announces -->
<script>
  function showNotification(message) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    // Screen reader announces automatically due to aria-live
  }
</script>
```

---

## Color and Contrast

### WCAG Contrast Requirements

**Level AA** (Minimum):

- **Normal text**: 4.5:1 contrast ratio
- **Large text** (18pt+ or 14pt+ bold): 3:1 contrast ratio
- **UI components and graphics**: 3:1 contrast ratio

**Level AAA** (Enhanced):

- **Normal text**: 7:1 contrast ratio
- **Large text**: 4.5:1 contrast ratio

### Don't Rely on Color Alone

```html
<!-- ❌ BAD: Color only to convey information -->
<p style="color: red;">Error: Invalid email</p>
<p style="color: green;">Success: Form submitted</p>

<!-- ✅ GOOD: Color plus icon/text -->
<p class="error">
  <span class="icon" aria-hidden="true">⚠</span>
  Error: Invalid email
</p>

<p class="success">
  <span class="icon" aria-hidden="true">✓</span>
  Success: Form submitted
</p>

<!-- ❌ BAD: Color-coded links without underline -->
<p>Visit our <span style="color: blue;">products page</span> for more.</p>

<!-- ✅ GOOD: Links are underlined or clearly distinguished -->
<p>Visit our <a href="/products" style="text-decoration: underline;">products page</a> for more.</p>
```

### Testing Contrast

**Tools**:

- WebAIM Contrast Checker: https://webaim.org/resources/contrastchecker/
- Chrome DevTools: Built-in contrast checker
- Browser extensions: WCAG Color Contrast Checker

---

## Screen Reader Considerations

### Visually Hidden Content

```html
<!-- ✅ GOOD: Visually hidden but available to screen readers -->
<style>
  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  .sr-only-focusable:focus {
    position: static;
    width: auto;
    height: auto;
    overflow: visible;
    clip: auto;
    white-space: normal;
  }
</style>

<button>
  <span class="icon" aria-hidden="true">+</span>
  <span class="sr-only">Add to cart</span>
</button>
```

### Hide Decorative Content

```html
<!-- ✅ GOOD: Hide decorative elements from screen readers -->
<div>
  <span aria-hidden="true">⭐⭐⭐⭐⭐</span>
  <span class="sr-only">5 out of 5 stars</span>
</div>

<!-- Icons -->
<button>
  <i class="icon-save" aria-hidden="true"></i>
  Save
</button>

<!-- Decorative images -->
<img src="decorative-divider.png" alt="" />
```

### Reading Order

```html
<!-- ❌ BAD: Visual order doesn't match DOM order -->
<div style="display: flex; flex-direction: column-reverse;">
  <div>Appears second visually</div>
  <div>Appears first visually</div>
</div>
<!-- Screen reader reads in DOM order, not visual order -->

<!-- ✅ GOOD: DOM order matches visual order -->
<div style="display: flex; flex-direction: column;">
  <div>Appears first visually and in DOM</div>
  <div>Appears second visually and in DOM</div>
</div>
```

---

## Testing Accessibility

### Automated Testing Tools

**Browser Extensions**:

- **axe DevTools** - Comprehensive accessibility testing
- **WAVE** - Web accessibility evaluation tool
- **Lighthouse** - Chrome DevTools audit (includes accessibility)

**Command-line Tools**:

- **axe-core** - JavaScript accessibility testing
- **pa11y** - Automated accessibility testing

**Online Tools**:

- **WAVE Web Accessibility Evaluation Tool**: https://wave.webaim.org/
- **Google Lighthouse**: Built into Chrome DevTools
- **WebAIM Color Contrast Checker**: https://webaim.org/resources/contrastchecker/

### Manual Testing

**Keyboard Testing**:

1. Unplug mouse or don't use trackpad
2. Tab through entire page
3. Test all interactive elements with Enter/Space
4. Test dropdowns, modals, forms
5. Verify focus is always visible
6. Ensure logical tab order

**Screen Reader Testing**:

- **Windows**: NVDA (free), JAWS (commercial)
- **Mac**: VoiceOver (built-in)
- **Mobile**: TalkBack (Android), VoiceOver (iOS)

**Basic screen reader commands**:

- NVDA: Ctrl + Alt to toggle
- VoiceOver: Cmd + F5 to toggle
- Navigate: Arrow keys, Tab key
- Read all: Insert + Down (NVDA), Ctrl + A (VoiceOver)

### Testing Checklist

**Structure**:

- [ ] Valid HTML (no errors in validator)
- [ ] Proper heading hierarchy (H1 → H2 → H3)
- [ ] Landmarks used correctly (main, nav, header, footer)
- [ ] Page has meaningful title

**Keyboard**:

- [ ] All functionality accessible via keyboard
- [ ] Focus visible at all times
- [ ] Logical tab order
- [ ] No keyboard traps
- [ ] Skip links work

**Forms**:

- [ ] All inputs have labels
- [ ] Required fields indicated
- [ ] Error messages associated with fields
- [ ] Error messages descriptive
- [ ] Forms keyboard accessible

**Images**:

- [ ] All images have alt attributes
- [ ] Alt text descriptive (not filename)
- [ ] Decorative images have empty alt
- [ ] Complex images have detailed descriptions

**Color/Contrast**:

- [ ] Sufficient contrast (4.5:1 minimum)
- [ ] Information not conveyed by color alone
- [ ] Links distinguishable from text

**ARIA**:

- [ ] ARIA used only when necessary
- [ ] ARIA roles appropriate
- [ ] ARIA states update dynamically
- [ ] No ARIA on semantic HTML

**Multimedia**:

- [ ] Video has captions
- [ ] Audio has transcripts
- [ ] No autoplay or provide controls
- [ ] No flashing content (seizure risk)

---

## Accessibility Resources

### Guidelines and Standards

- **WCAG 2.1**: https://www.w3.org/WAI/WCAG21/quickref/
- **ARIA Authoring Practices Guide**: https://www.w3.org/WAI/ARIA/apg/
- **MDN Accessibility**: https://developer.mozilla.org/en-US/docs/Web/Accessibility

### Testing Tools

- **axe DevTools**: https://www.deque.com/axe/devtools/
- **WAVE**: https://wave.webaim.org/
- **Pa11y**: https://pa11y.org/
- **Lighthouse**: Chrome DevTools

### Learning Resources

- **WebAIM**: https://webaim.org/
- **A11y Project**: https://www.a11yproject.com/
- **Inclusive Components**: https://inclusive-components.design/

---

## Summary

**Key Accessibility Principles**:

1. **Use semantic HTML first** - Built-in accessibility, less code
2. **Add ARIA only when needed** - Don't override native semantics
3. **Keyboard accessibility required** - All functionality must work without mouse
4. **Provide text alternatives** - Alt text for images, captions for video
5. **Sufficient color contrast** - 4.5:1 for normal text (WCAG AA)
6. **Don't rely on color alone** - Use text, icons, or patterns too
7. **Label all form inputs** - Use `<label>` or aria-label
8. **Maintain focus visibility** - Users must see where focus is
9. **Test with real users** - Automated tools catch ~30-40% of issues
10. **Make accessibility a priority** - Build it in from the start

**Remember**: Accessibility is not optional - it's a requirement for inclusive, usable, and often legally compliant websites. Good accessibility benefits everyone, not just users with disabilities.
