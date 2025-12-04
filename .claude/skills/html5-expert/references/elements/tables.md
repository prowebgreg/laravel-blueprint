# Table Elements

Comprehensive reference for HTML table elements that structure tabular data with proper semantics and accessibility.

## Overview

HTML tables are for **tabular data only** - data that naturally fits into rows and columns with meaningful relationships. Tables provide:

- **Structured data representation**: Clear row/column relationships
- **Accessibility**: Screen readers navigate and announce table structure
- **Semantic meaning**: Distinguishes headers from data cells
- **Responsive design**: Can be enhanced with CSS for mobile views

**Core Principle**: Use tables for data, not layout. For layout, use CSS Grid or Flexbox.

---

## `<table>`

**Category**: Flow Content  
**Content Model**: Optional `<caption>`, optional `<colgroup>`, optional `<thead>`, optional `<tbody>` (or zero or more `<tr>`), optional `<tfoot>`  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `table`

### Purpose & Semantic Meaning

Represents **tabular data** - information presented in a two-dimensional grid of rows and columns. The `<table>` element is the container for all table structure.

**Key characteristic**: Data organized in rows and columns with meaningful relationships.

### When to Use

Use `<table>` for:

- Data tables (spreadsheet-like data)
- Comparison charts
- Schedules and calendars
- Financial data
- Statistical information
- Product comparison tables
- Any data that needs row/column structure

### When NOT to Use

❌ Don't use for:

- Page layout (use CSS Grid or Flexbox)
- Visual alignment of non-tabular content
- Navigation menus
- Form layout
- Image galleries

**Rule of thumb**: If you need row and column headers to understand the data, use a table. Otherwise, use other layout methods.

### Attributes

**Element-Specific**: None (legacy attributes like `border`, `cellpadding`, `cellspacing` are obsolete - use CSS)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

Common global attributes:

- `id` - Unique identifier
- `class` - Styling and JavaScript hooks
- `role` - Override to `presentation` or `none` if table is used for layout (discouraged)

### Content Model Rules

**Required structure order**:

1. Optional `<caption>` (must be first if present)
2. Optional `<colgroup>` elements
3. Optional `<thead>` (table header rows)
4. Either:
   - One or more `<tbody>` elements (table body rows)
   - One or more `<tr>` elements directly in `<table>` (if no `<tbody>`)
5. Optional `<tfoot>` (table footer rows)

**Can contain**:

- `<caption>` (0 or 1, must be first child)
- `<colgroup>` (0 or more)
- `<thead>` (0 or 1)
- `<tbody>` (0 or more)
- `<tfoot>` (0 or 1)
- `<tr>` (0 or more, if no `<tbody>`)

**Cannot contain**:

- Direct text content
- Flow content except table structure elements

### Examples

#### Good Example: Simple Data Table

```html
<table>
  <caption>
    Monthly Sales Data
  </caption>
  <thead>
    <tr>
      <th scope="col">Month</th>
      <th scope="col">Sales</th>
      <th scope="col">Revenue</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">January</th>
      <td>142</td>
      <td>$14,200</td>
    </tr>
    <tr>
      <th scope="row">February</th>
      <td>158</td>
      <td>$15,800</td>
    </tr>
  </tbody>
</table>
```

#### Good Example: Complex Table with Header Groups

```html
<table>
  <caption>
    Quarterly Revenue by Region
  </caption>
  <thead>
    <tr>
      <th rowspan="2" scope="col">Region</th>
      <th colspan="3" scope="colgroup">2025</th>
    </tr>
    <tr>
      <th scope="col">Q1</th>
      <th scope="col">Q2</th>
      <th scope="col">Q3</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">North America</th>
      <td>$2.4M</td>
      <td>$2.8M</td>
      <td>$3.1M</td>
    </tr>
    <tr>
      <th scope="row">Europe</th>
      <td>$1.9M</td>
      <td>$2.1M</td>
      <td>$2.3M</td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <th scope="row">Total</th>
      <td>$4.3M</td>
      <td>$4.9M</td>
      <td>$5.4M</td>
    </tr>
  </tfoot>
</table>
```

#### Bad Example: Table for Layout

```html
<!-- ❌ WRONG: Table used for page layout -->
<table>
  <tr>
    <td>
      <nav>Site navigation</nav>
    </td>
    <td>
      <main>Main content</main>
    </td>
  </tr>
</table>

<!-- ✅ CORRECT: Use CSS Grid or Flexbox for layout -->
<div style="display: grid; grid-template-columns: 200px 1fr;">
  <nav>Site navigation</nav>
  <main>Main content</main>
</div>
```

### Accessibility

**Implicit role**: `table`  
**Screen reader behavior**: Announces "table with X rows and Y columns", navigates cell by cell

**Best practices**:

- **Always include `<caption>`** for table description
- Use `<th>` for all headers with proper `scope` attribute
- Use `<thead>`, `<tbody>`, `<tfoot>` for structure
- Add `summary` via `<caption>` or `aria-describedby`
- Avoid merged cells (`colspan`/`rowspan`) when possible

**Required for accessibility**:

```html
<table>
  <!-- Caption describes table purpose -->
  <caption>
    Student Grades - Fall 2025
  </caption>
  <thead>
    <tr>
      <!-- scope="col" indicates column header -->
      <th scope="col">Student Name</th>
      <th scope="col">Grade</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <!-- scope="row" indicates row header -->
      <th scope="row">Alice Johnson</th>
      <td>A</td>
    </tr>
  </tbody>
</table>
```

**Complex tables** (with merged cells) may need:

- `headers` attribute referencing `id` of related header cells
- `aria-describedby` for additional context
- Consider simplifying structure if possible

### SEO Impact

**Moderate SEO value**: Tables help structure data for search engines

**Benefits**:

- `<caption>` provides context for search crawlers
- Well-structured tables may appear in rich results
- Header cells (`<th>`) signal importance
- Tabular data can be extracted for knowledge graphs

**Best practices for SEO**:

- Always use `<caption>` to describe table content
- Use semantic `<thead>`, `<tbody>`, `<tfoot>`
- Keep tables simple and avoid excessive nesting
- Ensure mobile responsiveness (doesn't break on small screens)

### Browser Support

✅ Universal support in all browsers  
✅ Legacy attributes gracefully degraded (use CSS instead)

---

## `<caption>`

**Category**: None (table child only)  
**Content Model**: Flow content, but no descendant `<table>` elements  
**Permitted Parents**: `<table>` (must be first child)  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `caption`

### Purpose & Semantic Meaning

Provides a **title or description** for the table. Acts as a heading that identifies what the table represents.

**Key characteristic**: Describes the table's purpose or content summary.

### When to Use

Use `<caption>` for:

- Every data table (accessibility requirement)
- Describing what the table contains
- Providing context for table data
- SEO-friendly table titles

### When NOT to Use

❌ Don't use for:

- Styling purposes only (use CSS)
- Long explanatory text (use paragraph before/after table or `aria-describedby`)
- Content that isn't about the table itself

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Flow content (text, links, emphasis)
- No `<table>` elements

**Must be**:

- First child of `<table>` element
- Only one `<caption>` per table

### Examples

#### Good Example: Descriptive Caption

```html
<table>
  <caption>
    Q3 2025 Sales Performance by Region
  </caption>
  <thead>
    <tr>
      <th scope="col">Region</th>
      <th scope="col">Sales</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Northeast</th>
      <td>$1.2M</td>
    </tr>
  </tbody>
</table>
```

#### Good Example: Caption with Additional Context

```html
<table>
  <caption>
    Employee Schedule - Week of November 4, 2025
    <span class="subtitle">(All times in EST)</span>
  </caption>
  <!-- table content -->
</table>
```

#### Bad Example: Missing Caption

```html
<!-- ❌ WRONG: No caption - inaccessible -->
<table>
  <thead>
    <tr>
      <th>Product</th>
      <th>Price</th>
    </tr>
  </thead>
</table>

<!-- ✅ CORRECT: Always include caption -->
<table>
  <caption>
    Product Pricing
  </caption>
  <thead>
    <tr>
      <th scope="col">Product</th>
      <th scope="col">Price</th>
    </tr>
  </thead>
</table>
```

### Accessibility

**Implicit role**: `caption`  
**Screen reader behavior**: Announced before table content, provides context

**Best practices**:

- **Always include** `<caption>` for every data table
- Keep caption concise (1-2 sentences)
- For longer descriptions, use `aria-describedby` pointing to paragraph
- Caption is visible by default (use CSS to style, don't hide)

### SEO Impact

**High SEO value**: Captions help search engines understand table content

**Benefits**:

- Acts as a heading for table data
- Included in search result snippets
- Provides context for data extraction

### Browser Support

✅ Universal support in all browsers

---

## `<thead>`

**Category**: None (table section)  
**Content Model**: Zero or more `<tr>` elements  
**Permitted Parents**: `<table>` (after `<caption>` and `<colgroup>`, before `<tbody>` and `<tfoot>`)  
**Tag Omission**: Start tag omissible if first thing is `<tr>`. End tag omissible if immediately followed by `<tbody>` or `<tfoot>`  
**Implicit ARIA Role**: `rowgroup`

### Purpose & Semantic Meaning

Groups **header rows** that contain column labels or row group labels. Defines the heading section of the table.

**Key characteristic**: Contains header information for table columns.

### When to Use

Use `<thead>` for:

- Column headers in data tables
- Multi-row headers
- Grouping header rows logically
- Tables that need print-friendly headers (repeated on each page)

### When NOT to Use

❌ Don't use for:

- Row headers (use `<th scope="row">` in `<tbody>`)
- Footer information (use `<tfoot>`)
- Data rows (use `<tbody>`)

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- One or more `<tr>` elements

**Must contain**:

- At least one `<tr>` if present

**Position**:

- After `<caption>` and `<colgroup>` (if present)
- Before `<tbody>` and `<tfoot>` (if present)

### Examples

#### Good Example: Simple Header

```html
<table>
  <caption>
    Product Inventory
  </caption>
  <thead>
    <tr>
      <th scope="col">Product ID</th>
      <th scope="col">Name</th>
      <th scope="col">Stock</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>001</td>
      <td>Widget</td>
      <td>42</td>
    </tr>
  </tbody>
</table>
```

#### Good Example: Multi-row Header

```html
<table>
  <caption>
    Quarterly Comparison
  </caption>
  <thead>
    <tr>
      <th rowspan="2" scope="col">Product</th>
      <th colspan="2" scope="colgroup">Q1</th>
      <th colspan="2" scope="colgroup">Q2</th>
    </tr>
    <tr>
      <th scope="col">Units</th>
      <th scope="col">Revenue</th>
      <th scope="col">Units</th>
      <th scope="col">Revenue</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Widget A</th>
      <td>100</td>
      <td>$5,000</td>
      <td>120</td>
      <td>$6,000</td>
    </tr>
  </tbody>
</table>
```

### Accessibility

**Implicit role**: `rowgroup`  
**Screen reader behavior**: Identifies header section, announces relationship to data cells

**Best practices**:

- Always use `<th>` elements inside `<thead>` (not `<td>`)
- Add `scope="col"` to `<th>` elements for clarity
- Use `scope="colgroup"` for headers spanning multiple columns

### SEO Impact

**Moderate SEO value**: Helps structure table for search engines

**Benefits**:

- Signals header vs data distinction
- Improves table data extraction

### Browser Support

✅ Universal support in all browsers  
✅ Optional tag omission supported

---

## `<tbody>`

**Category**: None (table section)  
**Content Model**: Zero or more `<tr>` elements  
**Permitted Parents**: `<table>` (after `<thead>`, before or after `<tfoot>`)  
**Tag Omission**: Start tag omissible if first thing is `<tr>`. End tag omissible in many cases  
**Implicit ARIA Role**: `rowgroup`

### Purpose & Semantic Meaning

Groups the **body rows** that contain the main data of the table. Separates data from headers and footers.

**Key characteristic**: Contains the primary data rows.

### When to Use

Use `<tbody>` for:

- All data rows (non-header, non-footer)
- Grouping related rows logically
- Multiple data sections in one table
- Better structure and styling control

### When NOT to Use

❌ Don't use for:

- Header rows (use `<thead>`)
- Footer rows (use `<tfoot>`)
- Tables with only 1-2 rows (optional, but recommended)

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Zero or more `<tr>` elements

**Multiple tbody**:

- A table can have multiple `<tbody>` elements
- Useful for grouping related rows

**Position**:

- After `<thead>` (if present)
- Before or after `<tfoot>` (if present)

### Examples

#### Good Example: Single Body Section

```html
<table>
  <caption>
    Employee List
  </caption>
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Department</th>
      <th scope="col">Email</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Alice Johnson</th>
      <td>Engineering</td>
      <td>alice@example.com</td>
    </tr>
    <tr>
      <th scope="row">Bob Smith</th>
      <td>Marketing</td>
      <td>bob@example.com</td>
    </tr>
  </tbody>
</table>
```

#### Good Example: Multiple Body Sections

```html
<table>
  <caption>
    Sales by Region
  </caption>
  <thead>
    <tr>
      <th scope="col">Store</th>
      <th scope="col">Sales</th>
    </tr>
  </thead>

  <tbody>
    <tr>
      <th colspan="2" scope="colgroup">North Region</th>
    </tr>
    <tr>
      <th scope="row">Store A</th>
      <td>$50,000</td>
    </tr>
    <tr>
      <th scope="row">Store B</th>
      <td>$62,000</td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <th colspan="2" scope="colgroup">South Region</th>
    </tr>
    <tr>
      <th scope="row">Store C</th>
      <td>$48,000</td>
    </tr>
    <tr>
      <th scope="row">Store D</th>
      <td>$55,000</td>
    </tr>
  </tbody>
</table>
```

### Accessibility

**Implicit role**: `rowgroup`  
**Screen reader behavior**: Groups data rows logically

**Best practices**:

- Always wrap data rows in `<tbody>` even if optional
- Use multiple `<tbody>` elements for logical grouping
- Use `<th scope="row">` for row headers

### SEO Impact

**Low SEO value**: Structural element for organization

**Benefits**:

- Improves table structure clarity
- Helps with data extraction

### Browser Support

✅ Universal support in all browsers  
✅ Optional tag omission supported

---

## `<tfoot>`

**Category**: None (table section)  
**Content Model**: Zero or more `<tr>` elements  
**Permitted Parents**: `<table>` (after `<thead>`, before or after `<tbody>`)  
**Tag Omission**: Start tag omissible if first thing is `<tr>`. End tag omissible if not followed by more table content  
**Implicit ARIA Role**: `rowgroup`

### Purpose & Semantic Meaning

Groups **footer rows** that contain summaries, totals, or other concluding information for the table.

**Key characteristic**: Contains summary or footer information.

### When to Use

Use `<tfoot>` for:

- Total or sum rows
- Averages and calculations
- Footer notes or legends
- Repeated information (like headers) for long tables

### When NOT to Use

❌ Don't use for:

- Regular data rows (use `<tbody>`)
- Header rows (use `<thead>`)
- General notes about table (use `<caption>` or paragraph)

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Zero or more `<tr>` elements

**Position** (in source):

- After `<thead>` (if present)
- Before or after `<tbody>` (appears at bottom visually regardless of source order)

**Note**: `<tfoot>` can appear before `<tbody>` in HTML source but will render at the bottom of the table.

### Examples

#### Good Example: Total Row

```html
<table>
  <caption>
    Monthly Expenses
  </caption>
  <thead>
    <tr>
      <th scope="col">Category</th>
      <th scope="col">Amount</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Rent</th>
      <td>$1,200</td>
    </tr>
    <tr>
      <th scope="row">Utilities</th>
      <td>$300</td>
    </tr>
    <tr>
      <th scope="row">Groceries</th>
      <td>$450</td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <th scope="row">Total</th>
      <td><strong>$1,950</strong></td>
    </tr>
  </tfoot>
</table>
```

#### Good Example: Average and Count

```html
<table>
  <caption>
    Test Scores
  </caption>
  <thead>
    <tr>
      <th scope="col">Student</th>
      <th scope="col">Score</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Alice</th>
      <td>92</td>
    </tr>
    <tr>
      <th scope="row">Bob</th>
      <td>87</td>
    </tr>
    <tr>
      <th scope="row">Carol</th>
      <td>95</td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <th scope="row">Class Average</th>
      <td>91.3</td>
    </tr>
    <tr>
      <th scope="row">Total Students</th>
      <td>3</td>
    </tr>
  </tfoot>
</table>
```

### Accessibility

**Implicit role**: `rowgroup`  
**Screen reader behavior**: Announces footer section, provides context for summary data

**Best practices**:

- Use `<th scope="row">` for footer labels
- Clearly distinguish footer data from body data (bold, borders)
- Include aria-label if footer purpose isn't obvious

### SEO Impact

**Low SEO value**: Structural element for summaries

**Benefits**:

- Signals summary/total information
- Helps with data comprehension

### Browser Support

✅ Universal support in all browsers  
✅ Renders at bottom regardless of source position

---

## `<tr>`

**Category**: None (table row)  
**Content Model**: Zero or more `<td>` or `<th>` elements  
**Permitted Parents**: `<thead>`, `<tbody>`, `<tfoot>`, or directly in `<table>`  
**Tag Omission**: End tag can be omitted if immediately followed by another `<tr>` or if parent element has no more content  
**Implicit ARIA Role**: `row`

### Purpose & Semantic Meaning

Represents a **table row** containing cells (`<td>` or `<th>`). Groups cells horizontally.

**Key characteristic**: Defines a single row in the table.

### When to Use

Use `<tr>` for:

- Every row in the table (required)
- Grouping cells that belong together horizontally
- Both header rows and data rows

### When NOT to Use

❌ Don't use for:

- Vertical grouping (handled by table structure)
- Layout purposes outside tables

### Attributes

**Element-Specific**: None

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Zero or more `<td>` elements (data cells)
- Zero or more `<th>` elements (header cells)
- Mix of `<td>` and `<th>` allowed

**Cannot contain**:

- Direct text
- Other elements besides `<td>` and `<th>`

### Examples

#### Good Example: Data Row

```html
<tr>
  <th scope="row">Product Name</th>
  <td>Widget Pro</td>
  <td>$29.99</td>
  <td>In Stock</td>
</tr>
```

#### Good Example: Header Row

```html
<tr>
  <th scope="col">Name</th>
  <th scope="col">Price</th>
  <th scope="col">Quantity</th>
</tr>
```

### Accessibility

**Implicit role**: `row`  
**Screen reader behavior**: Announces row position, navigates cell by cell

**Best practices**:

- Ensure each row has consistent number of cells (or use `colspan`)
- Use `<th>` for row headers, `<td>` for data

### SEO Impact

**Minimal SEO value**: Structural element

### Browser Support

✅ Universal support in all browsers  
✅ End tag omission supported

---

## `<th>`

**Category**: None (table header cell)  
**Content Model**: Flow content, but no header, footer, sectioning content, or heading content descendants  
**Permitted Parents**: `<tr>`  
**Tag Omission**: End tag can be omitted in some cases  
**Implicit ARIA Role**: `columnheader` or `rowheader` (depends on `scope`)

### Purpose & Semantic Meaning

Represents a **header cell** in a table. Used for column headers, row headers, or group headers. Provides labels for data cells.

**Key characteristic**: Labels columns, rows, or cell groups.

### When to Use

Use `<th>` for:

- Column headers (in `<thead>`)
- Row headers (first cell in each row)
- Group headers (spanning multiple rows/columns)
- Any cell that labels other cells

### When NOT to Use

❌ Don't use for:

- Regular data cells (use `<td>`)
- Purely visual emphasis (use `<td>` with CSS)

**Rule of thumb**: If the cell answers "what is this data?", use `<th>`. If it answers "what's the value?", use `<td>`.

### Attributes

**Element-Specific**:

- `scope` - Specifies which cells this header applies to
  - `col` - Column header (default for `<thead>`)
  - `row` - Row header
  - `colgroup` - Header for column group
  - `rowgroup` - Header for row group
- `colspan` - Number of columns this cell spans
- `rowspan` - Number of rows this cell spans
- `headers` - Space-separated list of header cell IDs (for complex tables)
- `abbr` - Abbreviated version of header text (for screen readers)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Flow content (text, links, emphasis)
- No sectioning content (article, section, nav, aside)
- No heading content (h1-h6)
- No header or footer elements

### Examples

#### Good Example: Column Headers

```html
<thead>
  <tr>
    <th scope="col">Product</th>
    <th scope="col">Price</th>
    <th scope="col">Stock</th>
  </tr>
</thead>
```

#### Good Example: Row Headers

```html
<tbody>
  <tr>
    <th scope="row">Monday</th>
    <td>9:00 AM - 5:00 PM</td>
  </tr>
  <tr>
    <th scope="row">Tuesday</th>
    <td>9:00 AM - 5:00 PM</td>
  </tr>
</tbody>
```

#### Good Example: Spanning Headers

```html
<thead>
  <tr>
    <th rowspan="2" scope="col">Product</th>
    <th colspan="3" scope="colgroup">Quarters</th>
  </tr>
  <tr>
    <th scope="col">Q1</th>
    <th scope="col">Q2</th>
    <th scope="col">Q3</th>
  </tr>
</thead>
```

#### Good Example: Abbreviated Header

```html
<th scope="col" abbr="Tax">Value Added Tax (19%)</th>
```

#### Bad Example: Missing scope

```html
<!-- ❌ WRONG: No scope attribute -->
<th>Name</th>

<!-- ✅ CORRECT: Always include scope -->
<th scope="col">Name</th>
```

### Accessibility

**Implicit role**: `columnheader` (with `scope="col"`) or `rowheader` (with `scope="row"`)  
**Screen reader behavior**: Announces as header, associates with data cells

**Best practices**:

- **Always use `scope` attribute** for clarity
- Use `scope="col"` for column headers
- Use `scope="row"` for row headers
- Use `abbr` for long header text
- For complex tables, use `id` + `headers` attribute

**Complex table example**:

```html
<table>
  <tr>
    <th id="name" scope="col">Name</th>
    <th id="morning" scope="col">Morning</th>
    <th id="afternoon" scope="col">Afternoon</th>
  </tr>
  <tr>
    <th id="mon" scope="row">Monday</th>
    <td headers="name mon morning">Alice</td>
    <td headers="name mon afternoon">Bob</td>
  </tr>
</table>
```

### SEO Impact

**High SEO value**: Headers signal important labels

**Benefits**:

- Identifies key terms and categories
- Helps structure table data extraction
- Improves content understanding

**Best practices**:

- Use descriptive header text
- Include keywords in headers when appropriate
- Always use `<th>` for headers, not styled `<td>`

### Browser Support

✅ Universal support in all browsers  
✅ `scope` attribute universally supported

---

## `<td>`

**Category**: None (table data cell)  
**Content Model**: Flow content  
**Permitted Parents**: `<tr>`  
**Tag Omission**: End tag can be omitted in some cases  
**Implicit ARIA Role**: `cell`

### Purpose & Semantic Meaning

Represents a **data cell** in a table. Contains the actual data values, not headers or labels.

**Key characteristic**: Holds data values within the table structure.

### When to Use

Use `<td>` for:

- All data values in table
- Non-header cells
- Content that is described by row/column headers

### When NOT to Use

❌ Don't use for:

- Header cells (use `<th>`)
- Row/column labels (use `<th scope="row">` or `<th scope="col">`)

### Attributes

**Element-Specific**:

- `colspan` - Number of columns this cell spans (default: 1)
- `rowspan` - Number of rows this cell spans (default: 1)
- `headers` - Space-separated list of header cell IDs this cell relates to (for complex tables)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Can contain**:

- Flow content (text, images, links, lists, etc.)
- Other HTML elements as needed

**Cannot contain**:

- No specific restrictions

### Examples

#### Good Example: Simple Data Cell

```html
<tr>
  <th scope="row">Alice</th>
  <td>Developer</td>
  <td>alice@example.com</td>
  <td>555-1234</td>
</tr>
```

#### Good Example: Spanning Cells

```html
<tr>
  <td colspan="2">This cell spans 2 columns</td>
  <td>Regular cell</td>
</tr>
<tr>
  <td rowspan="2">This cell spans 2 rows</td>
  <td>Row 1, Col 2</td>
  <td>Row 1, Col 3</td>
</tr>
<tr>
  <td>Row 2, Col 2</td>
  <td>Row 2, Col 3</td>
</tr>
```

#### Good Example: Complex Cell Content

```html
<td>
  <img src="product.jpg" alt="Product image" />
  <strong>Product Name</strong>
  <p>Product description...</p>
</td>
```

#### Good Example: Empty Data Cell

```html
<td>N/A</td>
<!-- Or leave empty if appropriate -->
<td></td>
```

#### Bad Example: Using for Headers

```html
<!-- ❌ WRONG: Using <td> for headers -->
<tr>
  <td><strong>Name</strong></td>
  <td><strong>Age</strong></td>
</tr>

<!-- ✅ CORRECT: Use <th> for headers -->
<tr>
  <th scope="col">Name</th>
  <th scope="col">Age</th>
</tr>
```

### Accessibility

**Implicit role**: `cell`  
**Screen reader behavior**: Announces as data cell, includes associated header information

**Best practices**:

- Ensure every `<td>` has associated `<th>` headers
- Use `headers` attribute for complex table relationships
- Don't leave cells completely empty (use "N/A" or similar)
- Avoid excessive spanning (makes navigation difficult)

**Complex table with headers attribute**:

```html
<table>
  <tr>
    <th id="name">Name</th>
    <th id="city">City</th>
  </tr>
  <tr>
    <td headers="name">Alice</td>
    <td headers="city">New York</td>
  </tr>
</table>
```

### SEO Impact

**Low SEO value**: Data cells contain values, not labels

**Benefits**:

- Contains actual data values
- Should be concise and meaningful
- Structured for data extraction

### Browser Support

✅ Universal support in all browsers  
✅ `colspan` and `rowspan` universally supported

---

## `<colgroup>`

**Category**: None (table column group)  
**Content Model**: If `span` attribute present: empty. If no `span`: zero or more `<col>` elements  
**Permitted Parents**: `<table>` (after `<caption>`, before `<thead>`, `<tbody>`, `<tfoot>`, `<tr>`)  
**Tag Omission**: Start tag omissible if first child is `<col>` and no `<colgroup>` precedes it. End tag omissible if not followed by space/comment  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Groups one or more columns together for styling or semantic purposes. Allows applying attributes to entire columns at once.

**Key characteristic**: Groups columns without affecting table structure.

### When to Use

Use `<colgroup>` for:

- Applying styles to entire columns
- Grouping related columns semantically
- Defining column widths with `<col>`
- Table column organization

### When NOT to Use

❌ Don't use for:

- Row grouping (use `<thead>`, `<tbody>`, `<tfoot>`)
- Individual cell styling (use classes on `<td>` or `<th>`)
- Layout purposes (most column styling better done with CSS)

**Note**: `<colgroup>` usage has declined with modern CSS. Consider CSS Grid or Flexbox for complex layouts.

### Attributes

**Element-Specific**:

- `span` - Number of consecutive columns this group spans (if no `<col>` children)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Content Model Rules

**Two usage patterns**:

1. **With span attribute** (self-contained):

```html
<colgroup span="2"></colgroup>
```

2. **With col children**:

```html
<colgroup>
  <col />
  <col />
</colgroup>
```

**Can contain**:

- Zero or more `<col>` elements (if no `span` attribute)
- Empty (if `span` attribute present)

**Position**:

- After `<caption>` (if present)
- Before `<thead>`, `<tbody>`, `<tfoot>`, `<tr>`

### Examples

#### Good Example: Styling Column Groups

```html
<table>
  <caption>
    Financial Report
  </caption>
  <colgroup span="1" class="name-column"></colgroup>
  <colgroup span="2" class="data-columns"></colgroup>
  <colgroup span="1" class="total-column"></colgroup>
  <thead>
    <tr>
      <th scope="col">Item</th>
      <th scope="col">Q1</th>
      <th scope="col">Q2</th>
      <th scope="col">Total</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Revenue</th>
      <td>$100K</td>
      <td>$120K</td>
      <td>$220K</td>
    </tr>
  </tbody>
</table>
```

#### Good Example: Multiple Column Definitions

```html
<table>
  <colgroup>
    <col style="width: 30%;" />
    <col style="width: 50%;" />
    <col style="width: 20%;" />
  </colgroup>
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Description</th>
      <th scope="col">Price</th>
    </tr>
  </thead>
</table>
```

### Accessibility

**Implicit role**: None  
**Screen reader behavior**: Not announced, purely structural

**Best practices**:

- Use for styling, not semantics
- Does not affect screen reader navigation
- Prefer CSS for column styling when possible

### SEO Impact

**No SEO value**: Purely structural for styling

### Browser Support

✅ Universal support in all browsers  
⚠️ Limited CSS properties work on `<colgroup>` (border, background, width, visibility)

---

## `<col>`

**Category**: None (table column)  
**Content Model**: Empty (void element)  
**Permitted Parents**: `<colgroup>` (without `span` attribute)  
**Tag Omission**: No end tag (void element)  
**Implicit ARIA Role**: None

### Purpose & Semantic Meaning

Represents a **single column** within a table for styling purposes. Always used inside `<colgroup>`.

**Key characteristic**: Defines properties for a single column without affecting content.

### When to Use

Use `<col>` for:

- Defining individual column properties
- Setting column widths
- Applying styles to specific columns
- When you need different properties for each column

### When NOT to Use

❌ Don't use for:

- Semantic column grouping (handled by table structure)
- Adding content (void element, no content allowed)
- When `<colgroup span="">` is sufficient

### Attributes

**Element-Specific**:

- `span` - Number of consecutive columns this `<col>` represents (default: 1)

**Global Attributes**: All [global attributes](../global-attributes.md) apply

**Note**: Limited CSS properties apply to `<col>` (border, background, width, visibility).

### Content Model Rules

**Void element**: Cannot have content or end tag

**Must be child of**: `<colgroup>` (without `span` attribute)

### Examples

#### Good Example: Column Width Definition

```html
<table>
  <colgroup>
    <col style="width: 200px;" />
    <col style="width: 300px;" />
    <col style="width: 150px;" />
  </colgroup>
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Description</th>
      <th scope="col">Price</th>
    </tr>
  </thead>
</table>
```

#### Good Example: Spanning Columns

```html
<colgroup>
  <col span="2" class="name-cols" />
  <col class="data-col" />
</colgroup>
```

#### Good Example: Background Color

```html
<style>
  .highlight-col {
    background-color: #f0f0f0;
  }
</style>

<table>
  <colgroup>
    <col />
    <col class="highlight-col" />
    <col />
  </colgroup>
</table>
```

### Accessibility

**Implicit role**: None  
**Screen reader behavior**: Not announced, purely for styling

### SEO Impact

**No SEO value**: Styling element only

### Browser Support

✅ Universal support in all browsers  
⚠️ Limited CSS properties supported

---

## Responsive Table Patterns

Tables can be challenging on mobile devices. Here are accessible patterns:

### Pattern 1: Horizontal Scrolling

```html
<div style="overflow-x: auto;">
  <table>
    <!-- table content -->
  </table>
</div>
```

**CSS**:

```css
.table-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch; /* smooth scrolling on iOS */
}
```

### Pattern 2: Responsive Cards (CSS Only)

```html
<table class="responsive-table">
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Email</th>
      <th scope="col">Phone</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td data-label="Name">Alice Johnson</td>
      <td data-label="Email">alice@example.com</td>
      <td data-label="Phone">555-1234</td>
    </tr>
  </tbody>
</table>
```

**CSS**:

```css
@media screen and (max-width: 600px) {
  .responsive-table thead {
    display: none;
  }

  .responsive-table tr {
    display: block;
    margin-bottom: 1rem;
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
```

### Pattern 3: Priority Columns

Show only essential columns on mobile, hide less important ones:

```html
<table>
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col" class="optional">Email</th>
      <th scope="col">Phone</th>
      <th scope="col" class="optional">Department</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Alice</td>
      <td class="optional">alice@example.com</td>
      <td>555-1234</td>
      <td class="optional">Engineering</td>
    </tr>
  </tbody>
</table>
```

**CSS**:

```css
@media screen and (max-width: 600px) {
  .optional {
    display: none;
  }
}
```

---

## Table Accessibility Checklist

Use this checklist for accessible tables:

### Required Elements

- [ ] `<caption>` present (describes table purpose)
- [ ] `<th>` used for all headers (not `<td>`)
- [ ] `scope` attribute on all `<th>` elements
- [ ] `<thead>`, `<tbody>`, `<tfoot>` for structure

### Semantic Structure

- [ ] Logical reading order (left-to-right, top-to-bottom)
- [ ] Consistent number of columns in each row
- [ ] Row headers use `scope="row"`
- [ ] Column headers use `scope="col"`

### Complex Tables

- [ ] Use `id` + `headers` attribute for multi-level headers
- [ ] Consider simplifying table if possible
- [ ] Test with screen reader

### Styling

- [ ] Visual separation between header and data (borders/background)
- [ ] Sufficient color contrast (WCAG AA: 4.5:1)
- [ ] Focus indicators for interactive tables
- [ ] Responsive design for mobile

### Testing

- [ ] Test keyboard navigation (Tab through cells)
- [ ] Test with screen reader (NVDA, JAWS, VoiceOver)
- [ ] Verify table announced with correct dimensions
- [ ] Verify headers associated with data cells

---

## Common Table Mistakes

### Mistake 1: Using Tables for Layout

```html
<!-- ❌ WRONG: Table for page layout -->
<table>
  <tr>
    <td><nav>Menu</nav></td>
    <td><main>Content</main></td>
  </tr>
</table>

<!-- ✅ CORRECT: CSS Grid for layout -->
<div style="display: grid; grid-template-columns: 200px 1fr;">
  <nav>Menu</nav>
  <main>Content</main>
</div>
```

### Mistake 2: Missing Caption

```html
<!-- ❌ WRONG: No caption -->
<table>
  <thead>
    <tr>
      <th>Name</th>
    </tr>
  </thead>
</table>

<!-- ✅ CORRECT: Always include caption -->
<table>
  <caption>
    Employee Names
  </caption>
  <thead>
    <tr>
      <th scope="col">Name</th>
    </tr>
  </thead>
</table>
```

### Mistake 3: Using `<td>` for Headers

```html
<!-- ❌ WRONG: <td> with bold for headers -->
<tr>
  <td><strong>Name</strong></td>
  <td><strong>Age</strong></td>
</tr>

<!-- ✅ CORRECT: Use <th> with scope -->
<tr>
  <th scope="col">Name</th>
  <th scope="col">Age</th>
</tr>
```

### Mistake 4: Missing `scope` Attribute

```html
<!-- ❌ WRONG: No scope -->
<th>Product</th>

<!-- ✅ CORRECT: Include scope -->
<th scope="col">Product</th>
```

### Mistake 5: Inconsistent Column Count

```html
<!-- ❌ WRONG: Rows have different column counts -->
<table>
  <tr>
    <td>A</td>
    <td>B</td>
    <td>C</td>
  </tr>
  <tr>
    <td>D</td>
    <td>E</td>
    <!-- Missing cell -->
  </tr>
</table>

<!-- ✅ CORRECT: Consistent columns or use colspan -->
<table>
  <tr>
    <td>A</td>
    <td>B</td>
    <td>C</td>
  </tr>
  <tr>
    <td>D</td>
    <td>E</td>
    <td>F</td>
  </tr>
</table>
```

---

## When NOT to Use Tables

Tables are for **tabular data only**. Don't use tables for:

### ❌ Page Layout

Use CSS Grid or Flexbox instead.

### ❌ Visual Alignment

Use CSS for alignment (flexbox, grid, text-align).

### ❌ Form Layout

Use proper form elements with CSS layout.

### ❌ List of Items

Use `<ul>`, `<ol>`, or `<dl>` for lists.

### ❌ Image Galleries

Use CSS Grid or Flexbox with `<figure>` elements.

### ❌ Navigation Menus

Use `<nav>` with `<ul>` and `<li>`.

---

## Table Best Practices Summary

1. **Always use `<caption>`** - Describes table purpose
2. **Always use `<th>` for headers** - With `scope` attribute
3. **Structure with `<thead>`, `<tbody>`, `<tfoot>`** - Even when optional
4. **Keep tables simple** - Avoid excessive spanning
5. **Mobile-first design** - Plan responsive strategy
6. **Test accessibility** - Use screen reader testing
7. **Semantic over layout** - Tables for data, CSS for layout
8. **Provide context** - Use descriptive captions and headers
9. **Consistent structure** - Same columns in every row
10. **Validate markup** - Ensure WHATWG compliance

---

## Further Reading

- WHATWG HTML Standard - Tables: https://html.spec.whatwg.org/multipage/tables.html
- W3C WAI - Accessible Tables Tutorial: https://www.w3.org/WAI/tutorials/tables/
- MDN Web Docs - HTML Table: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/table
