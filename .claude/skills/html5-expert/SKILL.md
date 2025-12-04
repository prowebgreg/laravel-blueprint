---
name: html5-expert
description: Expert knowledge of WHATWG HTML Living Standard for generating semantically correct, SEO-optimized, and accessible HTML. Use for HTML generation, validation, element explanation, and standards compliance.
---

# HTML5 Expert Skill

Expert-level knowledge of the WHATWG HTML Living Standard for generating semantically correct, SEO-optimized, and accessible HTML markup.

## Core Principle

**Semantic HTML First**: Always choose the most semantically appropriate element for the content's meaning and purpose, not for visual styling. Semantics improve SEO, accessibility, and maintainability.

## Quick Reference

### Most Common Tasks

**Need element details?** → Load `references/elements/[category].md` for the specific element category
**Need attribute info?** → Load `references/global-attributes.md` for all global attributes
**Unsure about nesting?** → Load `references/content-models.md` for content model rules
**Need SEO guidance?** → Load `references/seo-best-practices.md` for semantic SEO patterns
**Need accessibility patterns?** → Load `references/accessibility.md` for ARIA integration

### Element Category Map

Load the appropriate reference file based on element type:

- **Semantic structure**: article, section, nav, main, header, footer, aside, address → `references/elements/semantic-structure.md`
- **Forms & inputs**: form, input (all types), select, textarea, button, label, fieldset → `references/elements/forms.md`
- **Text content**: p, h1-h6, blockquote, pre, code, lists (ul, ol, dl) → `references/elements/text-content.md`
- **Media**: img, picture, video, audio, canvas, svg, source → `references/elements/media.md`
- **Tables**: table, thead, tbody, tfoot, tr, th, td, caption → `references/elements/tables.md`
- **Interactive**: details, summary, dialog, menu → `references/elements/interactive.md`
- **Metadata**: head, meta, link, title, base (SEO critical) → `references/elements/metadata.md`
- **Embedded**: iframe, embed, object, portal → `references/elements/embedded.md`
- **Scripting**: script, noscript, template, slot, custom elements → `references/elements/scripting.md`

## Workflows

### Workflow 1: Generating HTML Structure

Follow this process when creating HTML markup:

1. **Understand content purpose and hierarchy**
   - What is the semantic meaning of this content?
   - What is the document outline structure?

2. **Select semantic elements**
   - Load appropriate `references/elements/[category].md` files
   - Choose the most semantically meaningful element
   - Avoid using `<div>` or `<span>` when semantic alternatives exist

3. **Apply proper attributes**
   - Load `references/global-attributes.md` for all available attributes
   - Include required attributes first
   - Add accessibility attributes (aria-\*, role) as needed
   - Include data-\* attributes for JavaScript hooks

4. **Verify content model compliance**
   - Load `references/content-models.md` to check nesting rules
   - Ensure parent-child relationships are valid
   - Verify void elements are used correctly

5. **Optimize for SEO**
   - Load `references/seo-best-practices.md` for semantic SEO guidance
   - Use proper heading hierarchy (h1-h6)
   - Include semantic metadata in `<head>`

6. **Ensure accessibility**
   - Load `references/accessibility.md` for ARIA patterns
   - Prefer semantic HTML over ARIA when possible
   - Add ARIA only when semantic HTML is insufficient

### Workflow 2: Validating HTML Markup

Follow this process when analyzing existing HTML:

1. **Structural validation**
   - Run `scripts/validate_html.py` for automated WHATWG compliance checking
   - Check for proper document structure (html → head + body)
   - Verify all tags are properly closed (except void elements)

2. **Semantic analysis**
   - Run `scripts/analyze_semantics.py` for semantic improvement suggestions
   - Identify non-semantic elements (div, span) that could be replaced
   - Check heading hierarchy for logical structure

3. **Content model verification**
   - Load `references/content-models.md`
   - Verify parent-child relationships follow WHATWG rules
   - Check for forbidden nesting patterns

4. **Accessibility audit**
   - Load `references/accessibility.md`
   - Verify ARIA usage is correct
   - Check for missing accessibility attributes

5. **SEO optimization review**
   - Load `references/seo-best-practices.md`
   - Verify meta tags and structured data
   - Check semantic element usage for SEO benefit

### Workflow 3: Explaining HTML Elements

When explaining an element or answering questions about HTML:

1. **Load the relevant reference file** based on element category (see Element Category Map above)

2. **Provide comprehensive information**:
   - Element purpose and semantic meaning
   - Content model (what can go inside)
   - Valid attributes (element-specific + global)
   - Nesting rules and restrictions
   - Accessibility implications
   - SEO impact
   - Practical examples (correct and incorrect usage)

3. **Cross-reference related elements** from the same or other categories

4. **Cite WHATWG specification** when discussing standards compliance

## Common Patterns

### Pattern 1: Semantic Document Structure

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Load references/elements/metadata.md for head content -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Page Title</title>
  </head>
  <body>
    <!-- Load references/elements/semantic-structure.md for semantic elements -->
    <header>
      <nav><!-- Site navigation --></nav>
    </header>

    <main>
      <article>
        <header>
          <h1>Article Title</h1>
        </header>
        <section>
          <!-- Content sections -->
        </section>
      </article>
    </main>

    <aside>
      <!-- Complementary content -->
    </aside>

    <footer>
      <!-- Site footer -->
    </footer>
  </body>
</html>
```

**Reference**: Load `references/elements/semantic-structure.md` for complete semantic element guidance.

### Pattern 2: Accessible Forms

```html
<!-- Load references/elements/forms.md for comprehensive form guidance -->
<form method="post" action="/submit">
  <fieldset>
    <legend>Personal Information</legend>

    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required />

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required />
  </fieldset>

  <button type="submit">Submit</button>
</form>
```

**Reference**: Load `references/elements/forms.md` and `references/accessibility.md` for accessible form patterns.

### Pattern 3: Responsive Images

```html
<!-- Load references/elements/media.md for image best practices -->
<picture>
  <source media="(min-width: 800px)" srcset="large.jpg" />
  <source media="(min-width: 400px)" srcset="medium.jpg" />
  <img src="small.jpg" alt="Descriptive text" loading="lazy" />
</picture>
```

**Reference**: Load `references/elements/media.md` for complete media element guidance.

### Pattern 4: SEO-Optimized Head

```html
<!-- Load references/elements/metadata.md + references/seo-best-practices.md -->
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Page Title - Site Name</title>
  <meta name="description" content="Page description for search results" />
  <link rel="canonical" href="https://example.com/page" />

  <!-- Open Graph -->
  <meta property="og:title" content="Page Title" />
  <meta property="og:description" content="Page description" />
  <meta property="og:image" content="https://example.com/image.jpg" />

  <!-- Structured Data -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Article",
      "headline": "Article Title"
    }
  </script>
</head>
```

**Reference**: Load `references/elements/metadata.md` and `references/seo-best-practices.md` for SEO optimization.

## Reference Navigation

### Element References

All element documentation organized by category with complete coverage:

- `references/elements/semantic-structure.md` - Semantic HTML5 structural elements
- `references/elements/forms.md` - Form elements and all input types
- `references/elements/text-content.md` - Text-level and grouping elements
- `references/elements/media.md` - Images, video, audio, canvas, SVG
- `references/elements/tables.md` - Table elements and accessibility
- `references/elements/interactive.md` - Interactive elements (details, dialog)
- `references/elements/metadata.md` - Document metadata and SEO
- `references/elements/embedded.md` - Embedded content (iframe, embed)
- `references/elements/scripting.md` - Script elements and web components

### Core References

Essential reference files for comprehensive HTML knowledge:

- `references/global-attributes.md` - All global attributes (id, class, data-_, aria-_, etc.)
- `references/content-models.md` - Element nesting rules and content categories
- `references/seo-best-practices.md` - Semantic SEO and structured data
- `references/accessibility.md` - ARIA integration and accessibility patterns

### Validation Scripts

Automated tools for HTML validation and analysis:

- `scripts/validate_html.py` - Validate HTML against WHATWG content model rules
- `scripts/analyze_semantics.py` - Analyze semantic structure and suggest improvements

## Usage in Claude Code

When generating HTML in Claude Code:

1. **Always prioritize semantic correctness** - Load relevant element references before generating
2. **Validate against content models** - Use `references/content-models.md` to verify nesting
3. **Include accessibility by default** - Follow patterns from `references/accessibility.md`
4. **Optimize for SEO** - Apply best practices from `references/seo-best-practices.md`
5. **Use validation scripts** - Run `scripts/validate_html.py` on generated HTML

## Key Principles

### Semantic HTML Priority Order

1. **Semantic HTML elements** - Use semantic elements when they match content meaning
2. **ARIA roles/attributes** - Add ARIA only when semantic HTML is insufficient
3. **Generic containers** - Use `<div>` and `<span>` only when no semantic alternative exists

### Content Model Compliance

Load `references/content-models.md` to verify:

- Parent-child relationships are valid
- Void elements (self-closing) are used correctly
- Optional tag omissions follow WHATWG rules
- Content categories match (flow, phrasing, sectioning, etc.)

### Accessibility-First Approach

Load `references/accessibility.md` to ensure:

- Semantic HTML provides implicit accessibility
- ARIA complements rather than replaces semantics
- All interactive elements are keyboard accessible
- Form inputs have associated labels

### SEO Through Semantics

Load `references/seo-best-practices.md` to optimize:

- Proper heading hierarchy (h1 → h6)
- Semantic elements signal content structure to search engines
- Metadata and structured data in document head
- Descriptive alt text for images

## WHATWG Specification Reference

This skill is based on the WHATWG HTML Living Standard: https://html.spec.whatwg.org

When citing standards or resolving ambiguity, the WHATWG specification is the authoritative source.

## Note on Coverage

This skill provides **complete encyclopedic coverage** of all HTML5 elements and attributes. For any HTML question or use case, load the appropriate reference file(s) from the Reference Navigation section above.
