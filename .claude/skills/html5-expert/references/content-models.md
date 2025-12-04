# Content Models and Nesting Rules

Comprehensive reference for HTML content categories, element nesting rules, and structural relationships defined by the WHATWG HTML specification.

## Overview

Every HTML element belongs to one or more **content categories** that define:

- **What it can contain** (content model)
- **Where it can be used** (permitted parents)
- **How it behaves** (parsing and rendering)
- **What restrictions apply** (nesting limitations)

Understanding content models is essential for:

- **Valid HTML structure** - Ensuring proper document structure
- **Avoiding parsing errors** - Preventing unexpected browser behavior
- **Semantic correctness** - Using elements appropriately
- **Accessibility** - Maintaining proper document outline

**Core Principle**: HTML structure is not arbitrary - specific rules govern which elements can contain or be contained by others.

---

## Content Categories

HTML elements are organized into overlapping categories. An element can belong to multiple categories.

### 1. Metadata Content

**Purpose**: Describes document properties, relationships, and behavior.

**Location**: Primarily in `<head>` section

**Elements**:

- `<base>`
- `<link>`
- `<meta>`
- `<noscript>` (in `<head>`)
- `<script>`
- `<style>`
- `<template>`
- `<title>`

**Rules**:

- Usually not visible in page content
- `<title>` is required in `<head>`
- `<base>` must come before any element with URLs

**Example**:

```html
<head>
  <!-- Metadata content -->
  <meta charset="UTF-8" />
  <title>Page Title</title>
  <link rel="stylesheet" href="styles.css" />
  <script src="app.js" defer></script>
</head>
```

---

### 2. Flow Content

**Purpose**: Most elements used in body of document.

**Characteristics**: General-purpose content category - represents most visible elements

**Elements**: Most body content elements including:

- Text elements: `<p>`, `<h1>`-`<h6>`, `<ul>`, `<ol>`, `<dl>`, `<div>`, `<pre>`, `<blockquote>`
- Semantic: `<article>`, `<section>`, `<nav>`, `<aside>`, `<header>`, `<footer>`, `<main>`
- Interactive: `<a>`, `<button>`, `<label>`, `<form>`, `<input>`, `<select>`, `<textarea>`
- Media: `<img>`, `<video>`, `<audio>`, `<canvas>`, `<svg>`
- Tables: `<table>`
- Embedded: `<iframe>`, `<embed>`, `<object>`
- Scripting: `<script>`, `<template>`, `<noscript>` (in body)

**Rules**:

- Can be used wherever flow content is expected
- Most elements that appear in `<body>` are flow content

**Example**:

```html
<body>
  <!-- All flow content -->
  <header>
    <h1>Title</h1>
    <nav>Navigation</nav>
  </header>

  <main>
    <article>
      <p>Paragraph text</p>
      <img src="image.jpg" alt="Image" />
    </article>
  </main>

  <footer>
    <p>Footer text</p>
  </footer>
</body>
```

---

### 3. Sectioning Content

**Purpose**: Defines scope of `<header>`, `<footer>`, and creates sections in document outline.

**Characteristics**: Creates new sections in document structure

**Elements**:

- `<article>`
- `<aside>`
- `<nav>`
- `<section>`

**Rules**:

- Each creates a new section in document outline
- Should generally contain a heading (`<h1>`-`<h6>`)
- Contributes to document structure and hierarchy

**Example**:

```html
<body>
  <!-- Each sectioning element creates a new section -->
  <nav>
    <h2>Navigation</h2>
    <ul>
      ...
    </ul>
  </nav>

  <main>
    <article>
      <h1>Article Title</h1>
      <section>
        <h2>Section 1</h2>
        <p>Content...</p>
      </section>
      <section>
        <h2>Section 2</h2>
        <p>Content...</p>
      </section>
    </article>

    <aside>
      <h2>Related Links</h2>
      <ul>
        ...
      </ul>
    </aside>
  </main>
</body>
```

---

### 4. Heading Content

**Purpose**: Defines section headings.

**Elements**:

- `<h1>`, `<h2>`, `<h3>`, `<h4>`, `<h5>`, `<h6>`
- `<hgroup>` (groups headings)

**Rules**:

- Defines heading hierarchy
- Each sectioning element should have a heading
- Headings create document outline
- Don't skip levels (h1 → h3 ❌, h1 → h2 → h3 ✅)

**Example**:

```html
<article>
  <h1>Main Title</h1>

  <section>
    <h2>Section Title</h2>
    <p>Content...</p>

    <h3>Subsection</h3>
    <p>More content...</p>
  </section>
</article>

<!-- Document outline:
1. Main Title
   1.1 Section Title
       1.1.1 Subsection
-->
```

---

### 5. Phrasing Content

**Purpose**: Text and inline-level elements within paragraphs.

**Characteristics**: Inline elements that make up text content

**Elements**:

- Text: `<span>`, `<br>`, `<wbr>`
- Emphasis: `<em>`, `<strong>`, `<small>`, `<mark>`, `<ins>`, `<del>`, `<s>`, `<u>`
- Code: `<code>`, `<kbd>`, `<samp>`, `<var>`
- Links: `<a>` (without interactive descendants)
- Quotes: `<q>`, `<cite>`, `<abbr>`, `<dfn>`
- Time: `<time>`, `<data>`
- Ruby: `<ruby>`, `<rt>`, `<rp>`, `<rb>`, `<rtc>`
- Bidirectional: `<bdi>`, `<bdo>`
- Line breaks: `<br>`, `<wbr>`
- Images: `<img>`
- Interactive: `<button>`, `<input>`, `<label>`, `<select>`, `<textarea>`
- Media: `<audio>`, `<video>`, `<picture>`
- Embedded: `<iframe>`, `<embed>`, `<object>`

**Rules**:

- Can be used wherever phrasing content is expected
- Cannot contain flow content (with some exceptions)
- Makes up the text within paragraphs and headings

**Example**:

```html
<p>
  This is a paragraph with <strong>bold text</strong>,
  <em>italic text</em>, a <a href="#">link</a>,
  and <code>inline code</code>.
</p>

<!-- ❌ WRONG: Block-level element in phrasing content -->
<p>
  Some text
  <div>Block content</div> <!-- Invalid! -->
</p>

<!-- ✅ CORRECT: Only phrasing content in paragraph -->
<p>
  Some text
  <span>Inline content</span>
</p>
```

---

### 6. Embedded Content

**Purpose**: Imports or embeds external resources.

**Elements**:

- `<audio>`
- `<canvas>`
- `<embed>`
- `<iframe>`
- `<img>`
- `<math>` (MathML)
- `<object>`
- `<picture>`
- `<svg>`
- `<video>`

**Rules**:

- Represents external resources
- Usually self-contained
- May have fallback content

**Example**:

```html
<!-- Images -->
<img src="photo.jpg" alt="Photo" />

<!-- Video -->
<video src="video.mp4" controls></video>

<!-- Iframe -->
<iframe src="map.html" title="Map"></iframe>

<!-- Canvas -->
<canvas id="myCanvas" width="400" height="300"></canvas>

<!-- SVG -->
<svg width="100" height="100">
  <circle cx="50" cy="50" r="40" fill="red" />
</svg>
```

---

### 7. Interactive Content

**Purpose**: Specifically designed for user interaction.

**Elements**:

- `<a>` (with `href` attribute)
- `<audio>` (with `controls`)
- `<button>`
- `<details>`
- `<embed>`
- `<iframe>`
- `<img>` (with `usemap`)
- `<input>` (not `type="hidden"`)
- `<label>`
- `<select>`
- `<textarea>`
- `<video>` (with `controls`)

**Rules**:

- Designed for user interaction
- Cannot be nested inside other interactive content (with exceptions)
- Must be keyboard accessible

**Example**:

```html
<!-- Valid interactive elements -->
<button>Click Me</button>
<a href="/page">Link</a>
<input type="text" />
<select>
  <option>Choose</option>
</select>

<!-- ❌ WRONG: Nested interactive elements -->
<button>
  Click <a href="#">this link</a>
  <!-- Invalid! -->
</button>

<a href="/page">
  Go to page
  <button>Click</button>
  <!-- Invalid! -->
</a>

<!-- ✅ CORRECT: Separate interactive elements -->
<div>
  <button>Click Me</button>
  <a href="#">Or this link</a>
</div>
```

---

### 8. Palpable Content

**Purpose**: Content that is tangible and substantive.

**Characteristics**: Content that is not empty or hidden

**Rules**:

- Has at least one non-hidden element
- Has text that is not just whitespace
- `<audio>` or `<video>` with `controls` attribute

**Not palpable**:

- `<script>`
- `<style>`
- `<meta>`
- `<link>`
- Hidden elements

**Example**:

```html
<!-- Palpable (has content) -->
<p>Text content</p>
<div><span>Text</span></div>
<img src="photo.jpg" alt="Photo" />

<!-- Not palpable (empty or hidden) -->
<p></p>
<div></div>
<script>
  console.log('hidden');
</script>
<div hidden>Hidden content</div>
```

---

### 9. Script-Supporting Elements

**Purpose**: Don't contribute to document content directly but support scripts.

**Elements**:

- `<script>`
- `<template>`

**Rules**:

- Can appear in most contexts
- Don't render visible content
- Support dynamic behavior

**Example**:

```html
<!-- Script-supporting elements -->
<script>
  console.log('JavaScript code');
</script>

<template id="item-template">
  <li>Template content (not rendered)</li>
</template>
```

---

## Content Model Definitions

### Transparent Content Model

**Definition**: Element's content model is same as its parent's content model.

**Elements with transparent content**:

- `<a>` (without href, or without interactive descendants)
- `<ins>`
- `<del>`
- `<map>`
- `<object>`
- `<video>`
- `<audio>`
- `<canvas>` (fallback content)

**Example**:

```html
<!-- <a> has transparent content model -->

<!-- Valid in flow content -->
<div>
  <a href="#">
    <p>Paragraph inside link</p>
    <img src="photo.jpg" alt="Photo" />
  </a>
</div>

<!-- Valid in phrasing content -->
<p>
  Text with <a href="#"><strong>bold link</strong></a>
</p>

<!-- Invalid: depends on parent -->
<!-- ❌ WRONG: Can't have block content in phrasing context -->
<p>
  <a href="#">
    <div>Block content</div>
    <!-- Invalid here! -->
  </a>
</p>
```

---

## Void Elements (Self-Closing)

**Definition**: Elements that **cannot have any content** - no closing tag allowed.

**All void elements**:

- `<area>`
- `<base>`
- `<br>`
- `<col>`
- `<embed>`
- `<hr>`
- `<img>`
- `<input>`
- `<link>`
- `<meta>`
- `<param>` (deprecated)
- `<source>`
- `<track>`
- `<wbr>`

**Rules**:

- No closing tag: `<br>` not `<br></br>`
- Self-closing slash optional in HTML5: `<br>` or `<br />`
- Cannot contain any content

**Examples**:

```html
<!-- ✅ CORRECT: Void elements without closing tags -->
<img src="photo.jpg" alt="Photo">
<br>
<hr>
<input type="text" name="username">
<link rel="stylesheet" href="styles.css">
<meta charset="UTF-8">

<!-- ✅ ALSO CORRECT: Optional self-closing slash -->
<img src="photo.jpg" alt="Photo" />
<br />
<input type="text" name="username" />

<!-- ❌ WRONG: Closing tag on void element -->
<img src="photo.jpg" alt="Photo"></img>
<br></br>
<input type="text"></input>

<!-- ❌ WRONG: Content in void element -->
<img>Content here</img>
<input>Text here</input>
```

---

## Optional Tags

**Definition**: Some tags can be **omitted** under specific conditions (rarely used in practice).

### Elements with Optional Start Tags

**`<html>`**: Can be omitted if first thing is not a comment
**`<head>`**: Can be omitted if first thing inside is an element
**`<body>`**: Can be omitted if first thing is not a space or comment
**`<tbody>`**: Can be omitted if first thing is `<tr>`
**`<colgroup>`**: Can be omitted if first child is `<col>` and not preceded by another `<colgroup>`

### Elements with Optional End Tags

**`<html>`**: Can be omitted if not immediately followed by comment
**`<head>`**: Can be omitted if not immediately followed by space or comment
**`<body>`**: Can be omitted if not immediately followed by space or comment
**`<li>`**: Can be omitted if immediately followed by another `<li>` or if no more content
**`<dt>`, `<dd>`**: Can be omitted if immediately followed by another `<dt>` or `<dd>`
**`<p>`**: Can be omitted if immediately followed by certain elements or if no more content
**`<tr>`, `<td>`, `<th>`**: Can be omitted in certain conditions
**`<thead>`, `<tbody>`, `<tfoot>`**: Can be omitted in certain conditions

**Recommendation**: **Always include tags** for clarity and maintainability.

**Example**:

```html
<!-- Valid but discouraged: Optional tags omitted -->
<!DOCTYPE html>
<html lang="en">
  <title>Page</title>
  <p>Paragraph 1</p>
  <p>Paragraph 2</p>
  <ul>
    <li>Item 1</li>
    <li>Item 2</li>
  </ul>

  <!-- ✅ RECOMMENDED: All tags explicit -->
  <!DOCTYPE html>
  <html lang="en">
    <head>
      <title>Page</title>
    </head>
    <body>
      <p>Paragraph 1</p>
      <p>Paragraph 2</p>
      <ul>
        <li>Item 1</li>
        <li>Item 2</li>
      </ul>
    </body>
  </html>
</html>
```

---

## Nesting Rules and Restrictions

### Rule 1: Interactive Content Cannot Contain Interactive Content

**Restriction**: Interactive elements cannot be nested inside other interactive elements.

**Why**: Prevents ambiguous user interactions and accessibility issues.

**Examples**:

```html
<!-- ❌ WRONG: Button inside link -->
<a href="/page">
  Go to page
  <button>Click</button>
</a>

<!-- ❌ WRONG: Link inside button -->
<button>
  <a href="/page">Go to page</a>
</button>

<!-- ❌ WRONG: Nested links -->
<a href="/outer">
  Outer link
  <a href="/inner">Inner link</a>
</a>

<!-- ✅ CORRECT: Separate interactive elements -->
<div>
  <a href="/page">Go to page</a>
  <button onclick="doSomething()">Or click this</button>
</div>

<!-- ✅ CORRECT: Link with nested non-interactive content -->
<a href="/product">
  <img src="product.jpg" alt="Product" />
  <h3>Product Name</h3>
  <p>Description</p>
</a>
```

**Exceptions**:

- `<label>` can contain interactive elements (one labelable element)
- `<audio>` and `<video>` with `controls` - depends on context

---

### Rule 2: `<p>` Cannot Contain Block-Level Elements

**Restriction**: Paragraph elements can only contain phrasing content.

**Examples**:

```html
<!-- ❌ WRONG: Block elements in paragraph -->
<p>
  Some text
  <div>Block content</div>
  <ul><li>List</li></ul>
  <h2>Heading</h2>
</p>

<!-- ✅ CORRECT: Only phrasing content in paragraph -->
<p>
  Some text with <strong>bold</strong>, <em>italic</em>,
  <a href="#">links</a>, and <code>code</code>.
</p>

<!-- ✅ CORRECT: Block elements outside paragraph -->
<p>Some text</p>
<div>Block content</div>
<ul><li>List</li></ul>
<h2>Heading</h2>
```

**Note**: If you put block element in `<p>`, the browser automatically closes the `<p>`:

```html
<!-- Browser interprets this: -->
<p>Text <div>Block</div></p>

<!-- As this: -->
<p>Text </p><div>Block</div><p></p>
```

---

### Rule 3: Heading Elements Cannot Contain Sectioning Content

**Restriction**: `<h1>`-`<h6>` cannot contain sectioning elements.

**Examples**:

```html
<!-- ❌ WRONG: Sectioning content in heading -->
<h1>
  <section>Title in section</section>
</h1>

<!-- ❌ WRONG: Article in heading -->
<h2>
  <article>Title in article</article>
</h2>

<!-- ✅ CORRECT: Heading contains only phrasing content -->
<h1>Page Title</h1>

<h2>Section Title with <span>span</span></h2>

<!-- ✅ CORRECT: Sectioning content contains heading -->
<section>
  <h2>Section Title</h2>
  <p>Content...</p>
</section>
```

---

### Rule 4: `<form>` Cannot Contain Another `<form>`

**Restriction**: Forms cannot be nested.

**Examples**:

```html
<!-- ❌ WRONG: Nested forms -->
<form action="/outer">
  <input type="text" name="outer" />

  <form action="/inner">
    <input type="text" name="inner" />
  </form>
</form>

<!-- ✅ CORRECT: Separate forms -->
<form action="/form1">
  <input type="text" name="field1" />
  <button type="submit">Submit Form 1</button>
</form>

<form action="/form2">
  <input type="text" name="field2" />
  <button type="submit">Submit Form 2</button>
</form>

<!-- ✅ CORRECT: Single form with multiple sections -->
<form action="/submit">
  <fieldset>
    <legend>Section 1</legend>
    <input type="text" name="field1" />
  </fieldset>

  <fieldset>
    <legend>Section 2</legend>
    <input type="text" name="field2" />
  </fieldset>

  <button type="submit">Submit</button>
</form>
```

---

### Rule 5: `<label>` Can Contain at Most One Labelable Element

**Restriction**: Label can only have one form control inside.

**Labelable elements**: `<input>` (not hidden), `<textarea>`, `<select>`, `<button>`, `<meter>`, `<progress>`, `<output>`

**Examples**:

```html
<!-- ❌ WRONG: Multiple inputs in label -->
<label>
  Name: <input type="text" name="first" />
  <input type="text" name="last" />
</label>

<!-- ✅ CORRECT: One input per label -->
<label> First Name: <input type="text" name="first" /> </label>
<label> Last Name: <input type="text" name="last" /> </label>

<!-- ✅ CORRECT: Label wrapping single input -->
<label> Email: <input type="email" name="email" /> </label>

<!-- ✅ CORRECT: Label using for attribute -->
<label for="username">Username:</label>
<input type="text" id="username" name="username" />
```

---

### Rule 6: `<table>` Content Must Follow Specific Order

**Restriction**: Table children must appear in specific order.

**Required order**:

1. Optional `<caption>` (first)
2. Optional `<colgroup>` elements
3. Optional `<thead>`
4. Either `<tbody>` elements or `<tr>` elements directly
5. Optional `<tfoot>`

**Examples**:

```html
<!-- ❌ WRONG: Incorrect order -->
<table>
  <tbody>
    ...
  </tbody>
  <caption>
    Title
  </caption>
  <!-- Caption must be first! -->
  <thead>
    ...
  </thead>
</table>

<!-- ✅ CORRECT: Proper order -->
<table>
  <caption>
    Table Title
  </caption>
  <colgroup>
    <col />
    <col />
  </colgroup>
  <thead>
    <tr>
      <th>Header 1</th>
      <th>Header 2</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Data 1</td>
      <td>Data 2</td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <td>Footer 1</td>
      <td>Footer 2</td>
    </tr>
  </tfoot>
</table>
```

---

### Rule 7: `<dl>` Must Contain `<dt>` and `<dd>` Groups

**Restriction**: Description list must contain term-description pairs.

**Examples**:

```html
<!-- ❌ WRONG: Random elements in dl -->
<dl>
  <p>Paragraph</p>
  <dt>Term</dt>
  <div>Div element</div>
</dl>

<!-- ✅ CORRECT: Only dt and dd elements -->
<dl>
  <dt>Term 1</dt>
  <dd>Description 1</dd>

  <dt>Term 2</dt>
  <dd>Description 2a</dd>
  <dd>Description 2b</dd>
</dl>

<!-- ✅ CORRECT: Wrapped in div (HTML5.2+) -->
<dl>
  <div>
    <dt>Term</dt>
    <dd>Description</dd>
  </div>
</dl>
```

---

### Rule 8: `<ul>` and `<ol>` Must Only Contain `<li>`

**Restriction**: List elements can only contain list items (or script-supporting elements).

**Examples**:

```html
<!-- ❌ WRONG: Non-li elements in list -->
<ul>
  <p>Paragraph</p>
  <li>Item 1</li>
  <div>Div element</div>
  <li>Item 2</li>
</ul>

<!-- ✅ CORRECT: Only li elements -->
<ul>
  <li>Item 1</li>
  <li>Item 2</li>
  <li>Item 3</li>
</ul>

<!-- ✅ CORRECT: Complex content inside li -->
<ul>
  <li>
    <h3>Item Title</h3>
    <p>Item description with multiple paragraphs.</p>
    <p>More content.</p>
  </li>
  <li>
    <article>
      <h4>Article in list item</h4>
      <p>Content...</p>
    </article>
  </li>
</ul>
```

---

### Rule 9: `<select>` Content Restrictions

**Restriction**: Select can only contain `<option>`, `<optgroup>`, and script-supporting elements.

**Examples**:

```html
<!-- ❌ WRONG: Invalid elements in select -->
<select>
  <p>Choose option</p>
  <option value="1">Option 1</option>
</select>

<!-- ✅ CORRECT: Only option and optgroup -->
<select>
  <option value="">Choose option</option>
  <option value="1">Option 1</option>
  <option value="2">Option 2</option>
</select>

<!-- ✅ CORRECT: With optgroup -->
<select>
  <optgroup label="Group 1">
    <option value="1a">Option 1A</option>
    <option value="1b">Option 1B</option>
  </optgroup>
  <optgroup label="Group 2">
    <option value="2a">Option 2A</option>
    <option value="2b">Option 2B</option>
  </optgroup>
</select>
```

---

### Rule 10: `<head>` Must Contain Exactly One `<title>`

**Restriction**: Every document must have one (and only one) title element in head.

**Examples**:

```html
<!-- ❌ WRONG: No title -->
<head>
  <meta charset="UTF-8" />
</head>

<!-- ❌ WRONG: Multiple titles -->
<head>
  <title>First Title</title>
  <title>Second Title</title>
</head>

<!-- ✅ CORRECT: Exactly one title -->
<head>
  <meta charset="UTF-8" />
  <title>Page Title</title>
  <meta name="description" content="Description" />
</head>
```

---

## Document Structure Requirements

### Valid HTML5 Document Structure

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required: exactly one title -->
    <title>Page Title</title>

    <!-- Recommended: charset first -->
    <meta charset="UTF-8" />

    <!-- Recommended: viewport for responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Optional: other metadata -->
    <meta name="description" content="Page description" />
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <!-- Page content -->
    <header>
      <h1>Main Heading</h1>
    </header>

    <main>
      <article>
        <h2>Article Heading</h2>
        <p>Content...</p>
      </article>
    </main>

    <footer>
      <p>Footer content</p>
    </footer>
  </body>
</html>
```

### Sectioning Structure

```html
<body>
  <!-- Optional page header -->
  <header>
    <h1>Site Title</h1>
    <nav>
      <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/about">About</a></li>
      </ul>
    </nav>
  </header>

  <!-- Main content (one per page) -->
  <main>
    <!-- Articles are self-contained -->
    <article>
      <header>
        <h2>Article Title</h2>
        <p>By Author, <time datetime="2025-11-04">November 4, 2025</time></p>
      </header>

      <!-- Sections within article -->
      <section>
        <h3>Section 1</h3>
        <p>Content...</p>
      </section>

      <section>
        <h3>Section 2</h3>
        <p>Content...</p>
      </section>

      <footer>
        <p>Tags: HTML, CSS</p>
      </footer>
    </article>

    <!-- Sidebar content -->
    <aside>
      <h2>Related Links</h2>
      <ul>
        <li><a href="#">Link 1</a></li>
        <li><a href="#">Link 2</a></li>
      </ul>
    </aside>
  </main>

  <!-- Page footer -->
  <footer>
    <p>&copy; 2025 Company Name</p>
  </footer>
</body>
```

---

## Common Content Model Mistakes

### Mistake 1: Block Elements in `<span>`

```html
<!-- ❌ WRONG: Block element in span -->
<span>
  <div>Block content</div>
</span>

<!-- ✅ CORRECT: Use div for wrapper -->
<div>
  <span>Inline content</span>
</div>
```

### Mistake 2: `<p>` Containing `<div>`

```html
<!-- ❌ WRONG: Div in paragraph (browser will auto-close p) -->
<p>
  Text before
  <div>Block content</div>
  Text after
</p>

<!-- ✅ CORRECT: Separate elements -->
<p>Text before</p>
<div>Block content</div>
<p>Text after</p>
```

### Mistake 3: Nested Interactive Elements

```html
<!-- ❌ WRONG: Button in link -->
<a href="/page">
  <button>Click me</button>
</a>

<!-- ✅ CORRECT: Make link look like button -->
<a href="/page" class="button-link">Click me</a>

<!-- ✅ CORRECT: Or use button with JavaScript -->
<button onclick="location.href='/page'">Click me</button>
```

### Mistake 4: Direct Text in `<ul>` or `<ol>`

```html
<!-- ❌ WRONG: Text directly in list -->
<ul>
  This is a list:
  <li>Item 1</li>
  <li>Item 2</li>
</ul>

<!-- ✅ CORRECT: Text outside or in li -->
<p>This is a list:</p>
<ul>
  <li>Item 1</li>
  <li>Item 2</li>
</ul>
```

### Mistake 5: `<h1>`-`<h6>` in `<address>`

```html
<!-- ❌ WRONG: Heading in address -->
<address>
  <h2>Contact Information</h2>
  Email: contact@example.com
</address>

<!-- ✅ CORRECT: Heading outside address -->
<h2>Contact Information</h2>
<address>Email: contact@example.com</address>
```

### Mistake 6: Multiple `<main>` Elements

```html
<!-- ❌ WRONG: Multiple main elements -->
<body>
  <main>Content 1</main>
  <main>Content 2</main>
</body>

<!-- ✅ CORRECT: Single main element -->
<body>
  <main>
    <section>Content 1</section>
    <section>Content 2</section>
  </main>
</body>
```

### Mistake 7: `<article>` in `<address>`

```html
<!-- ❌ WRONG: Article in address -->
<address>
  <article>Content</article>
</address>

<!-- ✅ CORRECT: Address for contact info only -->
<address>
  Written by <a href="mailto:author@example.com">Author Name</a>.<br />
  Visit us at: 123 Main St, City
</address>
```

---

## Content Model Quick Reference

### Can `<a>` contain...

| Element    | Allowed? | Notes                                  |
| ---------- | -------- | -------------------------------------- |
| `<div>`    | ✅ Yes   | If link has no interactive descendants |
| `<p>`      | ✅ Yes   | Transparent content model              |
| `<button>` | ❌ No    | Interactive in interactive             |
| `<a>`      | ❌ No    | Cannot nest links                      |
| `<input>`  | ❌ No    | Interactive in interactive             |
| `<img>`    | ✅ Yes   | Common pattern                         |
| `<span>`   | ✅ Yes   | Phrasing content                       |

### Can `<p>` contain...

| Element    | Allowed? | Notes                  |
| ---------- | -------- | ---------------------- |
| `<span>`   | ✅ Yes   | Phrasing content       |
| `<strong>` | ✅ Yes   | Phrasing content       |
| `<a>`      | ✅ Yes   | Phrasing content       |
| `<img>`    | ✅ Yes   | Phrasing content       |
| `<div>`    | ❌ No    | Block-level element    |
| `<p>`      | ❌ No    | Cannot nest paragraphs |
| `<ul>`     | ❌ No    | Block-level element    |
| `<h1>`     | ❌ No    | Block-level element    |

### Can `<div>` contain...

| Element     | Allowed? | Notes                        |
| ----------- | -------- | ---------------------------- |
| `<p>`       | ✅ Yes   | Flow content                 |
| `<div>`     | ✅ Yes   | Flow content                 |
| `<section>` | ✅ Yes   | Flow content                 |
| `<h1>`      | ✅ Yes   | Flow content                 |
| `<ul>`      | ✅ Yes   | Flow content                 |
| `<button>`  | ✅ Yes   | Flow content                 |
| Anything    | ✅ Yes   | Div accepts all flow content |

### Can `<li>` contain...

| Element     | Allowed? | Notes                       |
| ----------- | -------- | --------------------------- |
| `<p>`       | ✅ Yes   | Flow content                |
| `<div>`     | ✅ Yes   | Flow content                |
| `<ul>`      | ✅ Yes   | Nested lists                |
| `<article>` | ✅ Yes   | Flow content                |
| Text        | ✅ Yes   | Phrasing content            |
| Anything    | ✅ Yes   | Li accepts all flow content |

---

## Validation Checklist

### Document Structure

- [ ] `<!DOCTYPE html>` present
- [ ] One `<html>` element with `lang` attribute
- [ ] One `<head>` element
- [ ] Exactly one `<title>` in `<head>`
- [ ] One `<body>` element
- [ ] At most one `<main>` element

### Content Categories

- [ ] Metadata content only in `<head>`
- [ ] No block elements in `<p>`
- [ ] No interactive elements in interactive elements
- [ ] Sectioning elements contain headings
- [ ] Proper heading hierarchy (no skipped levels)

### Element-Specific

- [ ] Void elements have no closing tags or content
- [ ] `<ul>`/`<ol>` only contain `<li>`
- [ ] `<dl>` only contains `<dt>`/`<dd>`
- [ ] Table structure follows proper order
- [ ] Forms not nested
- [ ] Labels contain at most one labelable element

### Accessibility

- [ ] Interactive elements keyboard accessible
- [ ] Proper nesting for document outline
- [ ] Semantic elements used appropriately

---

## Tools for Validation

### HTML Validators

- **W3C Markup Validation Service**: https://validator.w3.org/
- **Nu Html Checker**: https://validator.w3.org/nu/
- **Browser DevTools**: Built-in HTML validation

### Accessibility Checkers

- **axe DevTools**: Browser extension
- **WAVE**: Web accessibility evaluation tool
- **Lighthouse**: Chrome DevTools audit

### Linters

- **HTMLHint**: HTML linting tool
- **ESLint (with HTML plugin)**: JavaScript linter with HTML support

---

## Further Reading

- WHATWG HTML Standard - Content Models: https://html.spec.whatwg.org/multipage/dom.html#content-models
- WHATWG HTML Standard - Syntax: https://html.spec.whatwg.org/multipage/syntax.html
- MDN Web Docs - Content Categories: https://developer.mozilla.org/en-US/docs/Web/HTML/Content_categories
- W3C HTML Validator: https://validator.w3.org/

---

## Summary

**Key Takeaways**:

1. **Content categories define structure** - Flow, phrasing, sectioning, heading, embedded, interactive
2. **Void elements cannot have content** - 14 self-closing elements (img, br, hr, input, etc.)
3. **Interactive elements cannot nest** - No buttons in links, no links in buttons
4. **Paragraphs are phrasing-only** - No block elements in `<p>`
5. **Lists have strict children** - ul/ol only contain li, dl only contains dt/dd
6. **Tables have required order** - caption → colgroup → thead → tbody → tfoot
7. **One title required** - Every document needs exactly one `<title>` in `<head>`
8. **One main per page** - At most one `<main>` element
9. **Validate your HTML** - Use W3C validator to catch nesting errors
10. **When in doubt, check the spec** - WHATWG HTML Standard is authoritative

**Golden Rule**: Understanding content models prevents parsing errors, ensures semantic correctness, and improves accessibility.
