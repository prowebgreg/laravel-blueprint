# Text Content Elements

Comprehensive reference for HTML5 text content and grouping elements including headings, paragraphs, lists, quotes, code, and inline semantics.

## Overview

Text content elements structure and organize textual information on web pages. They provide semantic meaning, improve accessibility, and enhance SEO.

**Core Principles**:

- **Semantic Structure**: Use elements based on content meaning, not visual appearance
- **Heading Hierarchy**: Maintain logical heading order (h1 → h2 → h3)
- **Accessibility**: Proper text semantics enable screen reader navigation
- **SEO**: Search engines use text structure to understand content

---

## Headings: `<h1>` through `<h6>`

**Category**: Flow Content, Heading Content  
**Content Model**: Phrasing content  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `heading` with `aria-level` (1-6)

### Purpose & Semantic Meaning

Headings represent section titles and establish document hierarchy. Six levels from `<h1>` (most important) to `<h6>` (least important).

**Key characteristic**: Headings create document outline and navigation structure.

### Heading Hierarchy Rules

**Critical SEO & Accessibility Rule**: Use ONE `<h1>` per page describing the main topic.

**Hierarchy guidelines**:

1. Start with `<h1>` for page title/main topic
2. Use `<h2>` for major sections
3. Use `<h3>` for subsections under `<h2>`
4. Use `<h4>-<h6>` for deeper subsections
5. **Never skip levels** going down (h1 → h2 → h3, not h1 → h3)
6. **CAN skip levels** going up (h4 → h2 is OK when closing sections)

### Attributes

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Examples

#### Good Example: Proper Hierarchy

```html
<article>
  <h1>Complete Guide to HTML5</h1>
  <!-- Page title -->

  <section>
    <h2>Introduction</h2>
    <!-- Major section -->
    <p>Content about introduction...</p>

    <h3>What is HTML5?</h3>
    <!-- Subsection -->
    <p>HTML5 is...</p>

    <h3>Why Use HTML5?</h3>
    <!-- Another subsection -->
    <p>Benefits include...</p>
  </section>

  <section>
    <h2>Semantic Elements</h2>
    <!-- Next major section -->

    <h3>Structural Elements</h3>
    <p>Elements like article, section...</p>

    <h4>The Article Element</h4>
    <!-- Sub-subsection -->
    <p>Article represents...</p>
  </section>
</article>
```

#### Bad Example: Skipped Levels & Multiple H1s

```html
<!-- ❌ WRONG: Multiple H1 tags -->
<h1>Site Name</h1>
<h1>Page Title</h1>
<h1>Another Important Thing</h1>

<!-- ❌ WRONG: Skipped level (h1 → h3) -->
<h1>Main Title</h1>
<h3>Subsection</h3>
<!-- Missing h2 -->

<!-- ❌ WRONG: Using heading for styling -->
<h2>Some small text</h2>
<!-- Should use <p> with CSS -->

<!-- ✅ CORRECT: One H1, proper hierarchy -->
<h1>Page Title</h1>
<h2>Major Section</h2>
<h3>Subsection</h3>
<h2>Another Major Section</h2>
```

### Heading Levels Explained

#### `<h1>` - Main Page Title

**Purpose**: Describes the entire page's topic  
**Usage**: One per page  
**SEO Impact**: Highest SEO weight  
**Examples**: "Complete HTML5 Guide", "Product Name", "Article Title"

```html
<h1>Understanding Web Accessibility</h1>
```

#### `<h2>` - Major Sections

**Purpose**: Primary sections/chapters of content  
**Usage**: Multiple allowed  
**SEO Impact**: High SEO weight  
**Examples**: Section headers like "Introduction", "Features", "Pricing"

```html
<h2>Getting Started</h2>
<p>Begin your journey...</p>

<h2>Advanced Techniques</h2>
<p>Once you master the basics...</p>
```

#### `<h3>` - Subsections

**Purpose**: Subdivisions within h2 sections  
**Usage**: Multiple allowed  
**SEO Impact**: Moderate SEO weight  
**Examples**: Topics within chapters

```html
<h2>HTML Forms</h2>
<h3>Input Types</h3>
<p>HTML5 provides many input types...</p>

<h3>Form Validation</h3>
<p>Native validation is built into HTML5...</p>
```

#### `<h4>` through `<h6>` - Deep Subsections

**Purpose**: Further subdivisions (rarely needed)  
**Usage**: Use only for very long, complex content  
**SEO Impact**: Lower SEO weight  
**Note**: Most content needs only h1-h3

```html
<h2>JavaScript Frameworks</h2>
<h3>React</h3>
<h4>React Hooks</h4>
<h5>useState Hook</h5>
<h6>Common useState Patterns</h6>
```

### When to Use Each Level

| Level | Use Case       | Frequency     | Example                         |
| ----- | -------------- | ------------- | ------------------------------- |
| h1    | Page title     | Once per page | "Product Documentation"         |
| h2    | Major sections | 2-10 per page | "Installation", "Configuration" |
| h3    | Subsections    | As needed     | "Windows Setup", "Mac Setup"    |
| h4-h6 | Deep nesting   | Rare          | Technical documentation         |

### Accessibility

**Screen reader behavior**: Users navigate by headings to scan page structure

**Best practices**:

- One `<h1>` per page
- Never skip levels descending
- Don't use headings for styling (use CSS)
- Include descriptive text, not just "Introduction" or "Section 1"
- Screen readers announce heading level ("heading level 2")

**ARIA**:

```html
<!-- Implicit aria-level based on heading tag -->
<h2>Section Title</h2>
<!-- Equivalent to: -->
<h2 role="heading" aria-level="2">Section Title</h2>
```

### SEO Impact

**Critical for SEO**: Headings are one of the most important on-page SEO factors

**SEO Best Practices**:

1. **One H1 per page** with primary keyword
2. **Include target keywords naturally** in h2 and h3 tags
3. **Descriptive headings** - Not "Click Here" or "More Info"
4. **Logical structure** - Helps Google understand content hierarchy
5. **Front-load keywords** - Put important words at beginning of headings
6. **Match search intent** - Use words people actually search

**Example SEO-optimized headings**:

```html
<h1>Complete HTML5 Tutorial for Beginners [2025]</h1>
<!-- Primary keyword in H1 -->
<h2>What is HTML5? (Definition and Overview)</h2>
<!-- Question-based H2 -->
<h2>HTML5 vs HTML4: Key Differences Explained</h2>
<!-- Comparison keyword -->
<h3>New Semantic Elements in HTML5</h3>
<!-- Supporting keyword -->
```

### Common Mistakes

❌ **Multiple H1 tags** - Confuses SEO and accessibility  
❌ **Using headings for styling** - Use `<p>` + CSS instead  
❌ **Skipping heading levels** - Breaks document outline  
❌ **Empty headings** - No semantic value  
❌ **Non-descriptive headings** - "Section 1", "Content", "More"  
❌ **All keywords, no meaning** - "HTML5 Tutorial HTML Course Learn HTML"

---

## `<p>` - Paragraph

**Category**: Flow Content  
**Content Model**: Phrasing content  
**Tag Omission**: End tag can be omitted in certain cases (HTML parsing rules)  
**Implicit ARIA Role**: `paragraph`

### Purpose

Represents a paragraph of text - the most basic unit of text content.

### Attributes

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### When to Use

Use `<p>` for:

- Body text
- Paragraphs of content
- Any standalone text block

### When NOT to Use

❌ Don't use for:

- Line breaks (use `<br>` for address formatting only)
- Spacing (use CSS margins)
- Container divs (use semantic elements or `<div>`)

### Examples

```html
<!-- ✅ Good: Standard paragraph -->
<p>
  HTML5 is the latest version of HTML, providing powerful semantic elements and APIs for modern web
  development.
</p>

<p>
  It includes features like native video playback, improved form controls, and better accessibility
  support.
</p>

<!-- ❌ Bad: Empty paragraphs for spacing -->
<p>First paragraph</p>
<p></p>
<p></p>
<p>Second paragraph</p>

<!-- ✅ Correct: Use CSS for spacing -->
<p>First paragraph</p>
<p style="margin-top: 2em;">Second paragraph</p>
```

### Accessibility

**Screen reader behavior**: Paragraphs are announced and provide natural pauses

**Best practices**:

- Keep paragraphs focused (one idea per paragraph)
- Use clear, concise language
- Break up long text blocks

### SEO Impact

**Low direct SEO value** - Body text matters more than the `<p>` tag itself

**Best practices**:

- First paragraph is important (intro text)
- Include keywords naturally
- Write for users, not just search engines

---

## `<hr>` - Thematic Break

**Category**: Flow Content  
**Content Model**: Empty (void element)  
**Tag Omission**: No end tag  
**Implicit ARIA Role**: `separator`

### Purpose

Represents a thematic break between paragraphs - a shift in topic, scene, or section.

**Not just a horizontal line** - Has semantic meaning

### Attributes

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### When to Use

Use `<hr>` for:

- Topic changes within a section
- Scene breaks in stories
- Transition between themes

### When NOT to Use

❌ Don't use for:

- Visual decoration (use CSS borders)
- Separating sections (use semantic elements instead)

### Examples

```html
<article>
  <p>Chapter 1 describes the hero's journey...</p>

  <hr />
  <!-- Thematic break: scene change -->

  <p>The next morning, everything had changed...</p>
</article>

<!-- In legal documents -->
<section>
  <h2>Terms of Service</h2>
  <p>By using this service, you agree...</p>

  <hr />
  <!-- Topic shift -->

  <h2>Privacy Policy</h2>
  <p>We collect the following data...</p>
</section>
```

### Styling

Default: Horizontal line across page width

```css
hr {
  border: none;
  border-top: 2px solid #ccc;
  margin: 2rem 0;
}
```

### Accessibility

**ARIA role**: `separator`  
**Screen reader behavior**: Announced as "separator"

---

## `<br>` - Line Break

**Category**: Flow Content, Phrasing Content  
**Content Model**: Empty (void element)  
**Tag Omission**: No end tag  
**Implicit ARIA Role**: None (presentational)

### Purpose

Represents a line break within text - forces text to next line.

### Attributes

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### When to Use

**Use `<br>` ONLY for**:

- Postal addresses
- Poems (line breaks are meaningful)
- Song lyrics

### When NOT to Use

❌ **Don't use for**:

- Spacing between elements (use CSS margins)
- Creating new paragraphs (use `<p>`)
- Responsive layout (use CSS)

### Examples

```html
<!-- ✅ Good: Postal address -->
<address>
  John Doe<br />
  123 Main Street<br />
  Suite 100<br />
  City, State 12345
</address>

<!-- ✅ Good: Poem -->
<p>
  Roses are red,<br />
  Violets are blue,<br />
  HTML is semantic,<br />
  And so should you.
</p>

<!-- ❌ Bad: Spacing -->
<p>First paragraph</p>
<br /><br /><br />
<p>Second paragraph</p>

<!-- ✅ Correct: CSS margins -->
<p>First paragraph</p>
<p style="margin-top: 2em;">Second paragraph</p>

<!-- ❌ Bad: Line breaks instead of paragraphs -->
This is some text.<br /><br />
This is more text.<br /><br />

<!-- ✅ Correct: Use paragraphs -->
<p>This is some text.</p>
<p>This is more text.</p>
```

### Accessibility

**Avoid overuse**: Screen readers announce each break, which can be disruptive

---

## `<blockquote>` - Block Quotation

**Category**: Flow Content, Sectioning Root  
**Content Model**: Flow content  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: None (semantic only)

### Purpose

Represents content quoted from another source - typically a longer quotation displayed as a block.

### Attributes

**Element-Specific**:

- `cite` - URL of quote source (not displayed, but machine-readable)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### When to Use

Use `<blockquote>` for:

- Extended quotations (multiple lines or paragraphs)
- Testimonials
- Quoted text from external sources

### Content Guidelines

**Can contain**:

- Paragraphs (`<p>`)
- Headings
- Lists
- Other block elements

**Attribution**: Use `<footer>` or `<cite>` for source

### Examples

#### Basic Blockquote

```html
<blockquote cite="https://www.w3.org/standards/webdesign/accessibility">
  <p>
    The power of the Web is in its universality. Access by everyone regardless of disability is an
    essential aspect.
  </p>
  <footer>— <cite>Tim Berners-Lee, W3C Director</cite></footer>
</blockquote>
```

#### Multi-Paragraph Quote

```html
<blockquote>
  <p>First paragraph of the quote...</p>
  <p>Second paragraph continues the thought...</p>
  <footer>
    <cite>
      <a href="https://example.com/article">Article Title</a>
    </cite>
  </footer>
</blockquote>
```

#### Testimonial

```html
<blockquote>
  <p>"This product changed my life! I can't imagine working without it now."</p>
  <footer>— Jane Smith, <cite>CEO of TechCorp</cite></footer>
</blockquote>
```

### vs `<q>` (Inline Quote)

| Element        | Use Case                 | Display                      |
| -------------- | ------------------------ | ---------------------------- |
| `<blockquote>` | Long, block-level quotes | Block element, indented      |
| `<q>`          | Short, inline quotes     | Inline, with quotation marks |

### Styling

Default: Indented block with margins

```css
blockquote {
  margin: 1.5rem 0;
  padding-left: 1.5rem;
  border-left: 4px solid #ccc;
  font-style: italic;
  color: #666;
}
```

### Accessibility

**Best practices**:

- Include attribution with `<footer>` or `<cite>`
- Use `cite` attribute for source URL
- Don't nest blockquotes unless actually nested quotes

### SEO Impact

**Low SEO value** - Quoted content isn't your original content

**Best practices**:

- Always attribute quotes to avoid duplicate content issues
- Use `cite` attribute for source URL
- Don't overuse quotes (write original content)

---

## `<q>` - Inline Quotation

**Category**: Flow Content, Phrasing Content  
**Content Model**: Phrasing content  
**Implicit ARIA Role**: None (semantic only)

### Purpose

Represents a short inline quotation - automatically adds quotation marks.

### Attributes

- `cite` - URL of quote source

### Examples

```html
<p>
  Tim Berners-Lee said,
  <q cite="https://www.w3.org/standards/">The power of the Web is in its universality</q>,
  emphasizing accessibility.
</p>

<!-- Browser automatically adds quotation marks -->
<!-- Renders as: Tim Berners-Lee said, "The power of the Web is in its universality", emphasizing accessibility. -->
```

### Quotation Marks

**Automatic**: Browser adds language-appropriate quotation marks

- English: "quote"
- French: « quote »
- German: „quote"

**Don't manually add quotes** - Browser handles it

---

## `<pre>` - Preformatted Text

**Category**: Flow Content  
**Content Model**: Phrasing content  
**Implicit ARIA Role**: None (semantic only)

### Purpose

Represents preformatted text where whitespace (spaces, line breaks, tabs) is preserved exactly as written.

### Attributes

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### When to Use

Use `<pre>` for:

- Code blocks
- ASCII art
- Text where formatting matters (tabs, multiple spaces)
- Terminal/console output

### Examples

#### Code Block

```html
<pre><code>function hello() {
  console.log("Hello, World!");
  return true;
}</code></pre>
```

#### ASCII Art

```html
<pre>
    ^
   / \
  /   \
 /_____\
   | |
</pre>
```

#### Terminal Output

```html
<pre>
$ npm install
added 120 packages
done in 4.2s
</pre>
```

### Content Rules

**Preserves**:

- Multiple spaces
- Line breaks
- Tabs

**Contains**: Phrasing content only (no block elements)

### Accessibility

**Best practices**:

- Wrap code in `<code>` inside `<pre>` for semantics
- For long code blocks, consider scrollable container
- Add language identifier for syntax highlighting

### Styling

Default: Monospace font, preserves whitespace

```css
pre {
  background: #f4f4f4;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 1rem;
  overflow-x: auto;
  font-family: 'Courier New', monospace;
}
```

---

## Code-Related Elements

### `<code>` - Inline Code

**Purpose**: Represents a fragment of computer code

**Use for**:

- Function names: `<code>getElementById()</code>`
- Variable names: `<code>userName</code>`
- File names: `<code>index.html</code>`
- Short code snippets

**Example**:

```html
<p>Use the <code>console.log()</code> function to output values.</p>
```

### `<pre><code>` - Code Blocks

**Purpose**: Multi-line code with preserved formatting

**Best practice**: Always wrap code inside `<pre>` with `<code>`

**Example**:

```html
<pre><code class="language-javascript">const greeting = "Hello, World!";
console.log(greeting);</code></pre>
```

### `<samp>` - Sample Output

**Purpose**: Represents sample output from a program

**Example**:

```html
<p>The program outputs: <samp>Process completed successfully</samp></p>
```

### `<kbd>` - Keyboard Input

**Purpose**: Represents user keyboard input

**Example**:

```html
<p>Press <kbd>Ctrl</kbd> + <kbd>C</kbd> to copy.</p>
<p>Type <kbd>npm install</kbd> in the terminal.</p>
```

### `<var>` - Variable

**Purpose**: Represents a mathematical variable or programming variable

**Example**:

```html
<p>The area of a rectangle is <var>width</var> × <var>height</var>.</p>
```

---

## Lists

### `<ul>` - Unordered List

**Category**: Flow Content  
**Content Model**: Zero or more `<li>` elements  
**Implicit ARIA Role**: `list`

#### Purpose

Represents an unordered list of items where order doesn't matter.

#### Attributes

**Global Attributes**: All [global attributes](../global-attributes.md) apply

**Deprecated** (don't use): `type` - Use CSS instead

#### When to Use

Use `<ul>` for:

- Lists where order is irrelevant
- Features, benefits, options
- Navigation menus
- Tag clouds

#### Examples

```html
<!-- Basic unordered list -->
<ul>
  <li>HTML</li>
  <li>CSS</li>
  <li>JavaScript</li>
</ul>

<!-- Nested list -->
<ul>
  <li>
    Frontend
    <ul>
      <li>React</li>
      <li>Vue</li>
      <li>Angular</li>
    </ul>
  </li>
  <li>
    Backend
    <ul>
      <li>Node.js</li>
      <li>Python</li>
      <li>Ruby</li>
    </ul>
  </li>
</ul>

<!-- List with rich content -->
<ul>
  <li>
    <strong>HTML5</strong>
    <p>Semantic markup language for web content</p>
  </li>
  <li>
    <strong>CSS3</strong>
    <p>Styling and layout for web pages</p>
  </li>
</ul>
```

#### Styling

```css
/* Remove default bullets */
ul {
  list-style-type: none;
  padding: 0;
}

/* Custom bullets */
ul {
  list-style-type: disc; /* disc, circle, square */
}
```

---

### `<ol>` - Ordered List

**Category**: Flow Content  
**Content Model**: Zero or more `<li>` elements  
**Implicit ARIA Role**: `list`

#### Purpose

Represents an ordered list of items where sequence matters.

#### Attributes

**Element-Specific**:

- `reversed` - Reverse numbering (Boolean)
- `start` - Starting number (integer)
- `type` - Numbering style (1, A, a, I, i)

#### When to Use

Use `<ol>` for:

- Step-by-step instructions
- Rankings, top 10 lists
- Ordered procedures
- Table of contents

#### Examples

```html
<!-- Basic ordered list -->
<ol>
  <li>Mix dry ingredients</li>
  <li>Add wet ingredients</li>
  <li>Stir until combined</li>
  <li>Bake at 350°F for 25 minutes</li>
</ol>

<!-- Custom start number -->
<ol start="5">
  <li>Step 5</li>
  <li>Step 6</li>
  <li>Step 7</li>
</ol>

<!-- Reversed numbering -->
<ol reversed>
  <li>Bronze Medal</li>
  <li>Silver Medal</li>
  <li>Gold Medal</li>
</ol>

<!-- Letter numbering -->
<ol type="A">
  <li>Option A</li>
  <li>Option B</li>
  <li>Option C</li>
</ol>

<!-- Roman numerals -->
<ol type="I">
  <li>Introduction</li>
  <li>Methods</li>
  <li>Results</li>
</ol>
```

#### Type Attribute Values

| Value | Numbering Style              |
| ----- | ---------------------------- |
| `1`   | Numbers (1, 2, 3) - default  |
| `A`   | Uppercase letters (A, B, C)  |
| `a`   | Lowercase letters (a, b, c)  |
| `I`   | Uppercase Roman (I, II, III) |
| `i`   | Lowercase Roman (i, ii, iii) |

---

### `<li>` - List Item

**Category**: None (only allowed in lists)  
**Content Model**: Flow content  
**Permitted Parents**: `<ul>`, `<ol>`, `<menu>`  
**Tag Omission**: End tag can be omitted in certain cases  
**Implicit ARIA Role**: `listitem`

#### Purpose

Represents an item in a list.

#### Attributes

**For `<ol>` only**:

- `value` - Ordinal value of item (integer)

#### Examples

```html
<!-- Standard list item -->
<ul>
  <li>Item 1</li>
  <li>Item 2</li>
</ul>

<!-- Custom value in ordered list -->
<ol>
  <li value="1">First item</li>
  <li value="5">Skip to fifth</li>
  <li value="10">Skip to tenth</li>
</ol>

<!-- Rich content in list item -->
<ul>
  <li>
    <h3>Feature Title</h3>
    <p>Description of the feature...</p>
    <a href="/learn-more">Learn More</a>
  </li>
</ul>
```

---

### `<dl>`, `<dt>`, `<dd>` - Description List

**Category**: Flow Content  
**Implicit ARIA Role**: None (semantic structure)

#### Purpose

Represents a description list (formerly "definition list") - groups terms with their descriptions.

#### Elements

- **`<dl>`**: Description list container
- **`<dt>`**: Description term (the term being described)
- **`<dd>`**: Description details (the description/definition)

#### When to Use

Use description lists for:

- Glossaries
- Metadata (key-value pairs)
- FAQs
- Product specifications

#### Examples

##### Glossary

```html
<dl>
  <dt>HTML</dt>
  <dd>HyperText Markup Language - the standard markup language for web pages.</dd>

  <dt>CSS</dt>
  <dd>Cascading Style Sheets - used for styling HTML documents.</dd>

  <dt>JavaScript</dt>
  <dd>Programming language for web interactivity and dynamic content.</dd>
</dl>
```

##### Multiple Terms, One Description

```html
<dl>
  <dt>HTML</dt>
  <dt>HTML5</dt>
  <dt>HyperText Markup Language</dt>
  <dd>The standard markup language for creating web pages.</dd>
</dl>
```

##### One Term, Multiple Descriptions

```html
<dl>
  <dt>JavaScript</dt>
  <dd>A programming language for web development.</dd>
  <dd>Created by Brendan Eich in 1995.</dd>
  <dd>Runs in web browsers and on servers (Node.js).</dd>
</dl>
```

##### Product Specifications

```html
<dl>
  <dt>Brand</dt>
  <dd>Acme Corporation</dd>

  <dt>Model</dt>
  <dd>Pro-2000</dd>

  <dt>Weight</dt>
  <dd>2.5 kg</dd>

  <dt>Dimensions</dt>
  <dd>30cm × 20cm × 10cm</dd>
</dl>
```

##### FAQ Format

```html
<dl>
  <dt>What is HTML?</dt>
  <dd>
    HTML stands for HyperText Markup Language. It's the standard language for creating web pages.
  </dd>

  <dt>How do I learn HTML?</dt>
  <dd>
    You can learn HTML through online tutorials, courses, and practice. Start with basic tags and
    gradually learn more advanced concepts.
  </dd>
</dl>
```

#### Styling

```css
dl {
  display: grid;
  grid-template-columns: max-content auto;
  gap: 1rem;
}

dt {
  font-weight: bold;
  grid-column: 1;
}

dd {
  grid-column: 2;
  margin: 0;
}
```

---

## Inline Text Semantics

### Text Emphasis & Importance

#### `<strong>` - Strong Importance

**Purpose**: Strong importance, seriousness, or urgency

**Display**: Bold (by default)

**Use for**:

- Warnings
- Important instructions
- Critical information

```html
<p><strong>Warning:</strong> This action cannot be undone.</p>
<p><strong>Important:</strong> Submit your form by Friday.</p>
```

#### `<em>` - Emphasis

**Purpose**: Stress emphasis on text

**Display**: Italic (by default)

**Use for**:

- Emphasized words in speech
- Alternative voice or mood
- Stressed words

```html
<p>I <em>really</em> need you to understand this.</p>
<p>Make sure you save your work <em>before</em> closing.</p>
```

#### `<b>` - Bring Attention To

**Purpose**: Draw attention without conveying extra importance

**Display**: Bold

**Use for**:

- Keywords in document
- Product names
- Lead-in text (not semantically emphasized)

```html
<p><b>HTML5</b> provides new semantic elements for better document structure.</p>
```

#### `<i>` - Alternate Voice

**Purpose**: Text in alternate voice, mood, or quality

**Display**: Italic

**Use for**:

- Technical terms
- Foreign phrases
- Thoughts
- Taxonomic names

```html
<p>The term <i>semantic HTML</i> refers to markup that conveys meaning.</p>
<p>She thought to herself, <i>This is going to be great</i>.</p>
```

#### `<mark>` - Highlighted Text

**Purpose**: Marked or highlighted for reference

**Display**: Yellow background (default)

**Use for**:

- Search result highlighting
- Referenced text
- Important passage

```html
<p>Search results for "HTML": <mark>HTML</mark> is a markup language.</p>
```

---

### Text Modifications

#### `<s>` - Strikethrough

**Purpose**: No longer accurate or relevant

**Display**: Strikethrough line

**Use for**:

- Old prices
- Outdated information
- Obsolete content

```html
<p>Price: <s>$99</s> $79 (20% off!)</p>
```

#### `<u>` - Underline (Unarticulated Annotation)

**Purpose**: Unarticulated annotation (use sparingly)

**Display**: Underline

**Use for**:

- Misspelling indicators (spell-check)
- Chinese proper name marks

```html
<p>This sentance has a <u>mispeling</u>.</p>
```

**Note**: Avoid for general underlining (confuses with links). Use CSS instead.

#### `<del>` and `<ins>` - Deletions and Insertions

**Purpose**: Track document changes

**Attributes**:

- `cite` - URL explaining change
- `datetime` - When change was made

```html
<p>Price: <del>$99</del> <ins>$79</ins></p>

<p>
  <del datetime="2025-01-15">The event is on March 10th.</del>
  <ins datetime="2025-01-20">The event has been rescheduled to March 15th.</ins>
</p>
```

---

### Subscript & Superscript

#### `<sub>` - Subscript

**Purpose**: Subscript text (below baseline)

**Use for**:

- Chemical formulas
- Mathematical subscripts

```html
<p>Water formula: H<sub>2</sub>O</p>
<p>Variable: x<sub>i</sub></p>
```

#### `<sup>` - Superscript

**Purpose**: Superscript text (above baseline)

**Use for**:

- Exponents
- Ordinal numbers
- Footnote references

```html
<p>Einstein's equation: E=mc<sup>2</sup></p>
<p>4<sup>th</sup> of July</p>
<p>Citation<sup>[1]</sup></p>
```

---

### Additional Inline Semantics

#### `<small>` - Fine Print

**Purpose**: Side comments and fine print

**Use for**:

- Copyright notices
- Legal disclaimers
- Small print

```html
<p>Copyright © 2025 Company Name. <small>All rights reserved.</small></p>
```

#### `<cite>` - Citation

**Purpose**: Title of a creative work

**Use for**:

- Book titles
- Article titles
- Song/movie titles
- Artwork names

```html
<p>My favorite book is <cite>The Great Gatsby</cite>.</p>
```

#### `<dfn>` - Definition

**Purpose**: Term being defined

```html
<p><dfn>HTML</dfn> stands for HyperText Markup Language.</p>
```

#### `<abbr>` - Abbreviation

**Purpose**: Abbreviation or acronym

**Attributes**:

- `title` - Full expansion (hover tooltip)

```html
<p>The <abbr title="World Wide Web Consortium">W3C</abbr> develops web standards.</p>
<p><abbr title="HyperText Markup Language">HTML</abbr> is a markup language.</p>
```

#### `<time>` - Date/Time

**Purpose**: Machine-readable date/time

**Attributes**:

- `datetime` - Machine-readable format

```html
<p>Published on <time datetime="2025-11-03">November 3, 2025</time>.</p>
<p>Event starts at <time datetime="2025-12-25T09:00">9:00 AM on Christmas</time>.</p>
```

#### `<data>` - Machine-Readable Data

**Purpose**: Links content with machine-readable value

**Attributes**:

- `value` - Machine-readable value

```html
<p>Product: <data value="SKU-12345">Premium Widget</data></p>
```

#### `<span>` - Generic Inline Container

**Purpose**: Generic inline container (no semantic meaning)

**Use for**:

- Styling hooks when no semantic element fits
- JavaScript targeting

```html
<p>The <span class="highlight">important part</span> of this sentence.</p>
```

**Note**: Only use `<span>` when no semantic alternative exists

---

## Accessibility Best Practices

### Headings

✅ One `<h1>` per page  
✅ Logical hierarchy (don't skip levels)  
✅ Descriptive heading text  
✅ Use headings for structure, not styling

### Lists

✅ Use semantic lists (`<ul>`, `<ol>`, `<dl>`)  
✅ Don't use `<br>` to create fake lists  
✅ Nest lists properly

### Text Semantics

✅ Use `<strong>` and `<em>` for meaning, not styling  
✅ Don't use `<b>` or `<i>` alone (prefer strong/em)  
✅ Use `<code>` for code, not just monospace styling

---

## SEO Best Practices

### Heading Optimization

1. **One H1 with primary keyword**
2. **H2 tags for major topics** with related keywords
3. **H3 tags for supporting points**
4. **Descriptive, not generic** ("HTML5 Features" not "Features")
5. **Front-load keywords** (important words first)
6. **Match search intent**

### Content Structure

1. **Short paragraphs** (2-4 sentences)
2. **Scannable content** (headings, lists, bold)
3. **First paragraph matters** (intro contains keywords)
4. **Use lists** for easier reading
5. **Semantic markup** signals content type to search engines

---

## Common Mistakes to Avoid

❌ Multiple H1 tags  
❌ Skipping heading levels (h1 → h3)  
❌ Using headings for styling  
❌ Empty headings or paragraphs  
❌ Using `<br>` for spacing  
❌ Using `<div>` when semantic element exists  
❌ Non-semantic lists (using `<br>` or `-`)  
❌ Overusing `<b>` and `<i>` instead of `<strong>` and `<em>`

---

## Further Reading

- **WHATWG Sections**: [https://html.spec.whatwg.org/multipage/sections.html](https://html.spec.whatwg.org/multipage/sections.html)
- **WHATWG Text-level Semantics**: [https://html.spec.whatwg.org/multipage/text-level-semantics.html](https://html.spec.whatwg.org/multipage/text-level-semantics.html)
- **WCAG Headings**: [https://www.w3.org/WAI/tutorials/page-structure/headings/](https://www.w3.org/WAI/tutorials/page-structure/headings/)
- **MDN Headings**: [https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Heading_Elements](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Heading_Elements)

---

**Last Updated**: Based on WHATWG HTML Living Standard (2025)
