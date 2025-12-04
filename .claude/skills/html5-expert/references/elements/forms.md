# Form Elements

Comprehensive reference for HTML5 form elements including all input types, form controls, validation, and accessibility patterns.

## Overview

HTML forms enable user input and data submission. HTML5 introduced powerful new input types, built-in validation, and improved accessibility features.

**Core Principles**:

- **Accessibility First**: Always associate labels with form controls
- **Native Validation**: Use built-in HTML5 validation before JavaScript
- **Semantic Input Types**: Choose the most appropriate input type for the data
- **Progressive Enhancement**: Forms work without JavaScript

---

## `<form>`

**Category**: Flow Content  
**Content Model**: Flow content (but no nested forms)  
**Permitted Parents**: Any element that accepts flow content  
**Tag Omission**: Neither tag is omissible  
**Implicit ARIA Role**: `form` (when labeled)

### Purpose & Semantic Meaning

Represents a section containing interactive controls for submitting information to a server.

### Attributes

**Element-Specific**:

- `action` - URL where form data is sent
- `method` - HTTP method (get, post, dialog)
- `enctype` - Encoding type for form data (default: application/x-www-form-urlencoded)
- `name` - Form name for JavaScript access
- `target` - Where to display response (\_self, \_blank, \_parent, \_top)
- `autocomplete` - Enable/disable autocomplete (on, off)
- `novalidate` - Disable native validation
- `accept-charset` - Character encoding for submission

**Global Attributes**: All [global attributes](../global-attributes.md) apply

### Method Attribute Values

| Value    | Use Case                       | Data Visibility        | Caching       |
| -------- | ------------------------------ | ---------------------- | ------------- |
| `get`    | Search forms, filters          | Visible in URL         | Cacheable     |
| `post`   | Login, data submission         | Hidden in request body | Not cacheable |
| `dialog` | Dialog forms (with `<dialog>`) | No submission          | N/A           |

### Enctype Attribute Values

| Value                               | Use Case                  |
| ----------------------------------- | ------------------------- |
| `application/x-www-form-urlencoded` | Default; text data        |
| `multipart/form-data`               | File uploads              |
| `text/plain`                        | Email forms (rarely used) |

### Examples

#### Basic Form

```html
<form action="/submit" method="post">
  <label for="username">Username:</label>
  <input type="text" id="username" name="username" required />

  <label for="password">Password:</label>
  <input type="password" id="password" name="password" required />

  <button type="submit">Sign In</button>
</form>
```

#### File Upload Form

```html
<form action="/upload" method="post" enctype="multipart/form-data">
  <label for="file">Choose file:</label>
  <input type="file" id="file" name="file" required />

  <button type="submit">Upload</button>
</form>
```

#### Search Form with GET

```html
<form action="/search" method="get">
  <label for="query">Search:</label>
  <input type="search" id="query" name="q" />

  <button type="submit">Search</button>
</form>
```

### Accessibility

**Best Practices**:

- Use `<label>` for every form control
- Group related fields with `<fieldset>` and `<legend>`
- Provide clear error messages
- Indicate required fields
- Use `autocomplete` attributes for autofill

### Form Submission Behavior

**Default behavior**: Sends data to server and reloads/redirects page  
**Prevention**: Use `event.preventDefault()` in JavaScript for AJAX submissions

---

## `<input>`

**Category**: Flow Content, Phrasing Content  
**Content Model**: Empty (void element)  
**Tag Omission**: No end tag  
**Implicit ARIA Role**: Varies by type

### Purpose & Semantic Meaning

Represents a typed data field with a form control to allow user input. The `type` attribute determines the control type and validation behavior.

### Common Attributes (All Input Types)

| Attribute      | Purpose                       | Applicable Types                                |
| -------------- | ----------------------------- | ----------------------------------------------- |
| `type`         | Control type                  | All                                             |
| `name`         | Submission key name           | All (except submit/reset/button)                |
| `id`           | Unique identifier for labels  | All                                             |
| `value`        | Initial/default value         | All                                             |
| `required`     | Makes field mandatory         | Most types                                      |
| `disabled`     | Disables control              | All                                             |
| `readonly`     | Prevents editing              | text, password, number, date, etc.              |
| `autofocus`    | Auto-focus on page load       | All                                             |
| `autocomplete` | Autofill behavior             | Most types                                      |
| `placeholder`  | Hint text                     | text, search, url, tel, email, password, number |
| `pattern`      | Regex validation              | text, search, url, tel, email, password         |
| `title`        | Tooltip (pattern description) | All                                             |

### Input Types Overview

**Text Input Types**: text, password, email, tel, url, search  
**Numeric Types**: number, range  
**Date/Time Types**: date, time, datetime-local, month, week  
**Selection Types**: checkbox, radio  
**File Type**: file  
**Color Type**: color  
**Action Types**: submit, reset, button, image  
**Special Type**: hidden

---

## Input Type: `text`

**Default type** - Single-line plain text input

### Attributes

**Type-Specific**:

- `maxlength` - Maximum character count
- `minlength` - Minimum character count
- `size` - Visible width (in characters)
- `pattern` - Regex validation
- `placeholder` - Hint text
- `list` - Associates with `<datalist>` for suggestions

### Example

```html
<label for="username">Username:</label>
<input
  type="text"
  id="username"
  name="username"
  minlength="3"
  maxlength="20"
  pattern="[a-zA-Z0-9_]+"
  required
  placeholder="alphanumeric_only"
/>
```

### Use Cases

- Usernames
- Names
- Generic text input
- Any single-line text that doesn't fit specialized types

---

## Input Type: `password`

**Single-line obscured text** - Characters displayed as bullets/asterisks

### Attributes

Same as `text` type

### Example

```html
<label for="password">Password:</label>
<input
  type="password"
  id="password"
  name="password"
  minlength="8"
  required
  autocomplete="current-password"
/>
```

### Security Notes

- Doesn't provide encryption (use HTTPS)
- Use `autocomplete="new-password"` for registration forms
- Use `autocomplete="current-password"` for login forms

---

## Input Type: `email`

**Email address input** - Built-in email validation

### Attributes

**Type-Specific**:

- `multiple` - Allow multiple comma-separated emails
- `maxlength`, `minlength`, `size`, `pattern`, `placeholder`, `list`

### Example

```html
<!-- Single email -->
<label for="email">Email:</label>
<input type="email" id="email" name="email" required autocomplete="email" />

<!-- Multiple emails -->
<label for="emails">Email addresses:</label>
<input
  type="email"
  id="emails"
  name="emails"
  multiple
  placeholder="email1@example.com, email2@example.com"
/>
```

### Validation

Validates format: `username@domain.tld`  
**Note**: Validation is permissive; use pattern for stricter validation

### Mobile Behavior

Displays email-optimized keyboard on mobile devices

---

## Input Type: `tel`

**Telephone number** - No built-in validation (formats vary globally)

### Attributes

Same as `text` type; use `pattern` for validation

### Example

```html
<!-- US phone number -->
<label for="phone">Phone (US):</label>
<input
  type="tel"
  id="phone"
  name="phone"
  pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
  placeholder="123-456-7890"
  autocomplete="tel"
/>
```

### Mobile Behavior

Displays numeric telephone keypad on mobile devices

---

## Input Type: `url`

**URL input** - Validates URL format

### Attributes

Same as `text` type

### Example

```html
<label for="website">Website:</label>
<input
  type="url"
  id="website"
  name="website"
  placeholder="https://example.com"
  autocomplete="url"
/>
```

### Validation

Validates format: `scheme://domain.tld/path`  
Requires protocol (http://, https://, etc.)

---

## Input Type: `search`

**Search field** - Semantically indicates search input

### Attributes

Same as `text` type

### Example

```html
<form action="/search" method="get">
  <label for="search">Search:</label>
  <input type="search" id="search" name="q" placeholder="Search articles..." />
  <button type="submit">Search</button>
</form>
```

### Visual Differences

- May display a clear button (×) when text is entered
- May have rounded corners on some platforms
- May show recent searches

---

## Input Type: `number`

**Numeric input** - Spinner control with validation

### Attributes

**Type-Specific**:

- `min` - Minimum value
- `max` - Maximum value
- `step` - Increment/decrement value (default: 1)
- `placeholder`

### Example

```html
<label for="quantity">Quantity (1-10):</label>
<input type="number" id="quantity" name="quantity" min="1" max="10" step="1" value="1" required />

<!-- Decimal numbers -->
<label for="price">Price:</label>
<input type="number" id="price" name="price" min="0" step="0.01" placeholder="0.00" />
```

### When NOT to Use

❌ **Don't use for**:

- Credit card numbers (no mathematical operations)
- Postal codes (may have leading zeros)
- Phone numbers (use `tel` instead)

**Rule**: Only use for actual numbers requiring arithmetic operations

---

## Input Type: `range`

**Slider control** - Visual slider for numeric range

### Attributes

**Type-Specific**:

- `min` - Minimum value (default: 0)
- `max` - Maximum value (default: 100)
- `step` - Step interval (default: 1)
- `value` - Initial value
- `list` - Associates with `<datalist>` for tick marks

### Example

```html
<label for="volume">Volume:</label>
<input type="range" id="volume" name="volume" min="0" max="100" step="5" value="50" />
<output for="volume">50</output>

<!-- With datalist tick marks -->
<label for="rating">Rating:</label>
<input type="range" id="rating" name="rating" min="0" max="5" step="1" list="rating-values" />
<datalist id="rating-values">
  <option value="0" label="0"></option>
  <option value="1" label="1"></option>
  <option value="2" label="2"></option>
  <option value="3" label="3"></option>
  <option value="4" label="4"></option>
  <option value="5" label="5"></option>
</datalist>
```

### Accessibility

- Always pair with a `<label>`
- Consider showing current value with `<output>`
- Keyboard accessible: Arrow keys adjust value

---

## Input Type: `date`

**Date picker** - Date selection control (YYYY-MM-DD format)

### Attributes

**Type-Specific**:

- `min` - Earliest date (YYYY-MM-DD)
- `max` - Latest date (YYYY-MM-DD)
- `step` - Day interval (default: 1)

### Example

```html
<!-- Basic date -->
<label for="birthday">Birthday:</label>
<input type="date" id="birthday" name="birthday" required />

<!-- Date range -->
<label for="start-date">Start Date:</label>
<input type="date" id="start-date" name="start-date" min="2025-01-01" max="2025-12-31" />
```

### Value Format

**Submission format**: `YYYY-MM-DD` (e.g., "2025-11-03")  
**Display format**: Localized by browser

### Browser Support

✅ Good support in modern browsers  
⚠️ Fallback: Shows text input on unsupported browsers

---

## Input Type: `time`

**Time picker** - Time selection control (HH:MM format)

### Attributes

**Type-Specific**:

- `min` - Earliest time (HH:MM or HH:MM:SS)
- `max` - Latest time
- `step` - Second interval (default: 60)

### Example

```html
<label for="appointment">Appointment time:</label>
<input type="time" id="appointment" name="appointment" min="09:00" max="17:00" required />
```

### Value Format

**Submission format**: `HH:MM` or `HH:MM:SS` (24-hour)  
**Display format**: Localized (may show 12-hour with AM/PM)

---

## Input Type: `datetime-local`

**Date and time picker** - Combined date and time without timezone

### Attributes

Same as `date` and `time` combined

### Example

```html
<label for="meeting">Meeting date and time:</label>
<input type="datetime-local" id="meeting" name="meeting" required />
```

### Value Format

**Submission format**: `YYYY-MM-DDTHH:MM` (ISO 8601 without timezone)  
Example: "2025-11-03T14:30"

**Note**: Does not include timezone; represents local time

---

## Input Type: `month`

**Month picker** - Month and year selection

### Attributes

- `min`, `max` - Range in YYYY-MM format

### Example

```html
<label for="expiry">Credit card expiry:</label>
<input type="month" id="expiry" name="expiry" min="2025-01" required />
```

### Value Format

**Submission format**: `YYYY-MM` (e.g., "2025-11")

---

## Input Type: `week`

**Week picker** - Week and year selection

### Attributes

- `min`, `max` - Range in YYYY-Www format

### Example

```html
<label for="week">Select week:</label> <input type="week" id="week" name="week" />
```

### Value Format

**Submission format**: `YYYY-Www` (e.g., "2025-W44")  
Week numbers follow ISO 8601

---

## Input Type: `color`

**Color picker** - Visual color selection control

### Attributes

- `value` - Initial color (must be 7-character hex: #RRGGBB)

### Example

```html
<label for="theme-color">Choose theme color:</label>
<input type="color" id="theme-color" name="theme-color" value="#4A90E2" />
```

### Value Format

**Submission format**: `#RRGGBB` (lowercase hex)  
**Default value**: `#000000` (black)

**Note**: Always returns hex color; no transparency (alpha) support

---

## Input Type: `checkbox`

**Checkbox** - Boolean toggle control

### Attributes

**Type-Specific**:

- `checked` - Initially checked state
- `value` - Value sent when checked (default: "on")

### Example

```html
<!-- Single checkbox -->
<input type="checkbox" id="subscribe" name="subscribe" value="yes" checked />
<label for="subscribe">Subscribe to newsletter</label>

<!-- Multiple checkboxes (same name) -->
<fieldset>
  <legend>Select toppings:</legend>

  <input type="checkbox" id="cheese" name="toppings" value="cheese" />
  <label for="cheese">Cheese</label>

  <input type="checkbox" id="pepperoni" name="toppings" value="pepperoni" checked />
  <label for="pepperoni">Pepperoni</label>

  <input type="checkbox" id="mushrooms" name="toppings" value="mushrooms" />
  <label for="mushrooms">Mushrooms</label>
</fieldset>
```

### Submission Behavior

**If checked**: `name=value` submitted  
**If unchecked**: Nothing submitted (name omitted entirely)

### Accessibility

- Place `<label>` after checkbox (visual convention)
- Use `<fieldset>` and `<legend>` for groups
- Use `aria-describedby` for additional instructions

---

## Input Type: `radio`

**Radio button** - Single selection from multiple options

### Attributes

**Type-Specific**:

- `checked` - Initially selected option
- `value` - Value sent when selected (required)

**Critical**: Radio buttons with same `name` form a group (only one selectable)

### Example

```html
<fieldset>
  <legend>Select shipping method:</legend>

  <input type="radio" id="standard" name="shipping" value="standard" checked />
  <label for="standard">Standard (5-7 days)</label>

  <input type="radio" id="express" name="shipping" value="express" />
  <label for="express">Express (2-3 days)</label>

  <input type="radio" id="overnight" name="shipping" value="overnight" />
  <label for="overnight">Overnight</label>
</fieldset>
```

### Submission Behavior

**Always submits**: `name=value` of checked radio button  
**Exactly one** radio button in a group must be checked for valid submission

### Accessibility

- Always use `<fieldset>` and `<legend>` for radio groups
- Ensure one radio is checked by default
- Place `<label>` after radio button

---

## Input Type: `file`

**File upload** - File selection control

### Attributes

**Type-Specific**:

- `accept` - File type filter (MIME types or extensions)
- `multiple` - Allow multiple file selection
- `capture` - Camera/microphone capture (mobile)

### Example

```html
<!-- Single file -->
<label for="resume">Upload resume (PDF only):</label>
<input type="file" id="resume" name="resume" accept=".pdf" required />

<!-- Multiple files -->
<label for="photos">Upload photos:</label>
<input type="file" id="photos" name="photos" accept="image/*" multiple />

<!-- Image capture (mobile) -->
<label for="photo">Take photo:</label>
<input type="file" id="photo" name="photo" accept="image/*" capture="environment" />
```

### Accept Attribute Values

| Value                  | Accepts             |
| ---------------------- | ------------------- |
| `image/*`              | Any image type      |
| `video/*`              | Any video type      |
| `audio/*`              | Any audio type      |
| `.pdf`                 | PDF files only      |
| `.jpg,.jpeg,.png`      | Specific extensions |
| `image/png,image/jpeg` | Specific MIME types |

### Form Encoding

**Required**: Use `enctype="multipart/form-data"` on `<form>`

---

## Input Type: `color`

**Color picker** - Hexadecimal color selection

(Documented above in date/time section - moved for logical grouping)

---

## Input Type: `submit`

**Submit button** - Submits the form

### Attributes

**Type-Specific**:

- `value` - Button text
- `formaction` - Override form action
- `formmethod` - Override form method
- `formenctype` - Override form enctype
- `formtarget` - Override form target
- `formnovalidate` - Disable validation for this button

### Example

```html
<input type="submit" value="Sign Up" />

<!-- Override form action -->
<form action="/save" method="post">
  <input type="text" name="data" />
  <input type="submit" value="Save" />
  <input type="submit" value="Save as Draft" formaction="/save-draft" />
</form>
```

### vs `<button type="submit">`

Both submit forms; `<button>` allows richer content (images, icons)

---

## Input Type: `reset`

**Reset button** - Resets form to initial values

### Attributes

- `value` - Button text (default: "Reset")

### Example

```html
<input type="reset" value="Clear Form" />
```

**Warning**: Rarely recommended; users often click accidentally

---

## Input Type: `button`

**Generic button** - No default behavior

### Attributes

- `value` - Button text

### Example

```html
<input type="button" value="Click Me" onclick="doSomething()" />
```

**Better alternative**: Use `<button type="button">` for richer content

---

## Input Type: `image`

**Image submit button** - Submits form with click coordinates

### Attributes

**Type-Specific**:

- `src` - Image URL (required)
- `alt` - Alternative text (required for accessibility)
- `width`, `height` - Image dimensions
- `formaction`, `formmethod`, etc. - Same as submit

### Example

```html
<input type="image" src="submit-button.png" alt="Submit form" width="100" height="40" />
```

### Submission Behavior

Submits: `name.x=xcoord&name.y=ycoord` (click coordinates)

**Accessibility concern**: Use `<button>` with `<img>` instead for better semantics

---

## Input Type: `hidden`

**Hidden field** - Not visible; stores data

### Attributes

- `name`, `value` - Data to submit

### Example

```html
<input type="hidden" name="user_id" value="12345" />
<input type="hidden" name="csrf_token" value="abc123xyz" />
```

### Use Cases

- CSRF tokens
- Record IDs
- Tracking data
- Stateful information

**Security note**: Not secure; users can view/modify in dev tools

---

## `<button>`

**Category**: Flow Content, Phrasing Content  
**Content Model**: Phrasing content (no interactive content)  
**Implicit ARIA Role**: `button`

### Purpose

Represents a clickable button with richer content than `<input type="button">`.

### Attributes

**Type-Specific**:

- `type` - Button behavior (submit, reset, button)
- `name`, `value` - Submitted data (for submit buttons)
- `form` - Associate with form by ID
- `formaction`, `formmethod`, `formenctype`, `formtarget`, `formnovalidate` - Override form attributes
- `disabled` - Disable button
- `popovertarget`, `popovertargetaction` - Control popovers

### Type Attribute Values

| Value    | Behavior            | Default |
| -------- | ------------------- | ------- |
| `submit` | Submits form        | ✅ Yes  |
| `reset`  | Resets form         |         |
| `button` | No default behavior |         |

### Examples

```html
<!-- Submit button with icon -->
<button type="submit">
  <img src="icon.svg" alt="" />
  Submit Form
</button>

<!-- Button with rich content -->
<button type="button" onclick="openDialog()">
  <strong>Learn More</strong>
  <span class="arrow">→</span>
</button>

<!-- Disabled button -->
<button type="submit" disabled>Submit (form incomplete)</button>
```

### vs `<input type="submit">`

| Feature      | `<button>` | `<input>`     |
| ------------ | ---------- | ------------- |
| Content      | Rich HTML  | Text only     |
| Default type | submit     | button/submit |
| Flexibility  | Higher     | Lower         |

**Recommendation**: Prefer `<button>` for better styling and accessibility

---

## `<label>`

**Category**: Flow Content, Phrasing Content  
**Content Model**: Phrasing content (no label descendants)  
**Implicit ARIA Role**: None (semantic labeling)

### Purpose

Associates text label with form control for accessibility and usability.

### Attributes

- `for` - ID of associated form control (explicit association)
- `form` - Associate label with form by ID

### Association Methods

**Explicit (recommended)**:

```html
<label for="email">Email:</label> <input type="email" id="email" name="email" />
```

**Implicit (wrapping)**:

```html
<label>
  Email:
  <input type="email" name="email" />
</label>
```

### Benefits

- **Clickable**: Clicking label focuses/activates control
- **Accessibility**: Screen readers announce label when control is focused
- **Touch targets**: Increases clickable area on mobile

### Examples

```html
<!-- Text input -->
<label for="username">Username:</label>
<input type="text" id="username" name="username" required />

<!-- Checkbox -->
<input type="checkbox" id="terms" name="terms" required />
<label for="terms">I agree to the terms and conditions</label>

<!-- Radio button -->
<input type="radio" id="option1" name="choice" value="1" />
<label for="option1">Option 1</label>
```

### Accessibility Best Practices

- **Every form control must have a label** (except submit/reset/button)
- Use `for` attribute (explicit association)
- Don't nest interactive elements inside labels
- Use `aria-label` or `aria-labelledby` for complex cases

---

## `<fieldset>` and `<legend>`

**Category**: Flow Content  
**Implicit ARIA Role**: `group` (fieldset)

### Purpose

**`<fieldset>`**: Groups related form controls  
**`<legend>`**: Provides caption/title for the fieldset

### Example

```html
<form>
  <fieldset>
    <legend>Personal Information</legend>

    <label for="fname">First name:</label>
    <input type="text" id="fname" name="fname" required />

    <label for="lname">Last name:</label>
    <input type="text" id="lname" name="lname" required />
  </fieldset>

  <fieldset>
    <legend>Shipping Method</legend>

    <input type="radio" id="standard" name="shipping" value="standard" />
    <label for="standard">Standard Shipping</label>

    <input type="radio" id="express" name="shipping" value="express" />
    <label for="express">Express Shipping</label>
  </fieldset>
</form>
```

### When to Use

**Always use for**:

- Radio button groups
- Checkbox groups (when related)
- Multi-part forms (sections)

### Attributes

**`<fieldset>`**:

- `disabled` - Disables all controls inside
- `form` - Associate with form by ID
- `name` - Fieldset name

### Accessibility

- **Critical for radio buttons and checkbox groups**
- Screen readers announce legend when entering fieldset
- Provides context for grouped controls

### Styling

Default browser styles include border; override with CSS:

```css
fieldset {
  border: none;
  padding: 0;
  margin: 0;
}
```

---

## `<select>`, `<option>`, and `<optgroup>`

**Dropdown selection control**

### `<select>`

**Category**: Flow Content, Phrasing Content  
**Implicit ARIA Role**: `combobox` or `listbox`

#### Attributes

- `name` - Submission key
- `multiple` - Allow multiple selections
- `size` - Number of visible options (default: 1 for dropdown, 4 for multiple)
- `required` - Make selection mandatory
- `disabled` - Disable control
- `autofocus` - Auto-focus on load

### `<option>`

Represents a single option in a select control.

#### Attributes

- `value` - Submitted value (defaults to text content)
- `selected` - Initially selected option
- `disabled` - Disable option
- `label` - Display text (overrides content)

### `<optgroup>`

Groups related options.

#### Attributes

- `label` - Group label (required)
- `disabled` - Disable entire group

### Examples

#### Basic Dropdown

```html
<label for="country">Country:</label>
<select id="country" name="country" required>
  <option value="">-- Select a country --</option>
  <option value="us">United States</option>
  <option value="ca">Canada</option>
  <option value="uk">United Kingdom</option>
  <option value="au">Australia</option>
</select>
```

#### Grouped Options

```html
<label for="car">Choose a car:</label>
<select id="car" name="car">
  <optgroup label="Swedish Cars">
    <option value="volvo">Volvo</option>
    <option value="saab">Saab</option>
  </optgroup>
  <optgroup label="German Cars">
    <option value="mercedes">Mercedes</option>
    <option value="audi">Audi</option>
  </optgroup>
</select>
```

#### Multiple Selection

```html
<label for="skills">Select skills (hold Ctrl/Cmd):</label>
<select id="skills" name="skills" multiple size="5">
  <option value="html">HTML</option>
  <option value="css">CSS</option>
  <option value="js">JavaScript</option>
  <option value="python">Python</option>
  <option value="react">React</option>
</select>
```

### Accessibility

- Always use `<label>` with `<select>`
- Include a default "-- Select --" option for required fields
- Use `<optgroup>` for clarity when >10 options
- Consider accessibility of multiple select (not keyboard-friendly)

---

## `<textarea>`

**Multi-line text input**

**Category**: Flow Content, Phrasing Content  
**Implicit ARIA Role**: `textbox`

### Attributes

**Type-Specific**:

- `rows` - Visible rows (height)
- `cols` - Visible columns (width)
- `maxlength` - Maximum character count
- `minlength` - Minimum character count
- `placeholder` - Hint text
- `readonly` - Prevent editing
- `required` - Make mandatory
- `wrap` - Text wrapping (soft, hard, off)

### Example

```html
<label for="comments">Comments:</label>
<textarea
  id="comments"
  name="comments"
  rows="5"
  cols="50"
  maxlength="500"
  placeholder="Enter your comments here..."
  required
></textarea>
```

### Content

```html
<!-- ✅ Correct: Text between tags -->
<textarea>Default text here</textarea>

<!-- ❌ Wrong: Don't use value attribute -->
<textarea value="text"></textarea>
```

### Accessibility

- Always use `<label>`
- Don't use placeholder as label replacement
- Indicate character limit visually
- Consider showing remaining characters with JavaScript

---

## `<datalist>`

**Autocomplete suggestions**

**Category**: Flow Content, Phrasing Content  
**Content Model**: `<option>` elements or phrasing content

### Purpose

Provides a list of predefined autocomplete suggestions for input fields.

### Attributes

- `id` - Referenced by input's `list` attribute

### Compatible Input Types

Works with: text, search, url, tel, email, date, month, week, time, datetime-local, number, range, color

### Example

```html
<label for="browser">Choose a browser:</label>
<input type="text" id="browser" name="browser" list="browsers" />

<datalist id="browsers">
  <option value="Chrome"></option>
  <option value="Firefox"></option>
  <option value="Safari"></option>
  <option value="Edge"></option>
  <option value="Opera"></option>
</datalist>
```

### With Display Labels

```html
<label for="city">City:</label>
<input type="text" id="city" name="city" list="cities" />

<datalist id="cities">
  <option value="NYC" label="New York City"></option>
  <option value="LA" label="Los Angeles"></option>
  <option value="CHI" label="Chicago"></option>
</datalist>
```

### Behavior

- **Suggestions**, not restrictions - Users can enter any value
- Dropdown appears as user types (filtered)
- Gracefully degrades (ignored in unsupported browsers)

---

## `<output>`

**Calculation result display**

**Category**: Flow Content, Phrasing Content  
**Content Model**: Phrasing content  
**Implicit ARIA Role**: `status`

### Purpose

Represents the result of a calculation or user action.

### Attributes

- `for` - Space-separated IDs of related elements
- `form` - Associate with form by ID
- `name` - Element name

### Example

```html
<form oninput="result.value = parseInt(a.value) + parseInt(b.value)">
  <label for="a">Number A:</label>
  <input type="number" id="a" name="a" value="0" />

  <label for="b">Number B:</label>
  <input type="number" id="b" name="b" value="0" />

  <p>Result: <output name="result" for="a b">0</output></p>
</form>
```

### Use Cases

- Calculator results
- Form field sums/totals
- Live validation feedback
- Dynamic price calculations

### Accessibility

**Implicit ARIA role**: `status` (announced to screen readers when value changes)

---

## `<progress>`

**Progress indicator**

**Category**: Flow Content, Phrasing Content  
**Content Model**: Phrasing content (no progress descendants)  
**Implicit ARIA Role**: `progressbar`

### Purpose

Represents progress of a task (determinate or indeterminate).

### Attributes

- `value` - Current progress value
- `max` - Maximum value (default: 1.0)

### Examples

```html
<!-- Determinate progress (known duration) -->
<label for="file-progress">File upload progress:</label>
<progress id="file-progress" value="60" max="100">60%</progress>

<!-- Indeterminate progress (unknown duration) -->
<progress>Loading...</progress>
```

### Determinate vs Indeterminate

**Determinate** (has `value` attribute): Shows specific progress  
**Indeterminate** (no `value` attribute): Shows activity without specific progress

### Accessibility

- Always include a label or visible text
- Content between tags is fallback for unsupported browsers
- Value changes are announced by screen readers

---

## `<meter>`

**Scalar measurement gauge**

**Category**: Flow Content, Phrasing Content  
**Content Model**: Phrasing content (no meter descendants)  
**Implicit ARIA Role**: None (semantic only)

### Purpose

Represents a scalar measurement within a known range (NOT for progress).

### Attributes

- `value` - Current value (required)
- `min` - Minimum value (default: 0)
- `max` - Maximum value (default: 1.0)
- `low` - Upper bound of low range
- `high` - Lower bound of high range
- `optimum` - Optimal value

### Examples

```html
<!-- Disk usage -->
<label for="disk">Disk usage:</label>
<meter id="disk" value="65" min="0" max="100" low="33" high="66" optimum="10">65%</meter>

<!-- Test score -->
<label for="score">Test score:</label>
<meter id="score" value="85" min="0" max="100" low="60" high="80" optimum="100">
  85 out of 100
</meter>
```

### Range Colors

Browsers automatically color the meter based on value position:

- **Good** (green): Value in optimal range
- **Warning** (yellow): Value in suboptimal range
- **Critical** (red): Value in critical range

### vs `<progress>`

| Use `<progress>` for | Use `<meter>` for    |
| -------------------- | -------------------- |
| Task completion      | Measurements         |
| File uploads         | Disk usage           |
| Loading indicators   | Test scores          |
| Temporal progress    | Temperature, ratings |

---

## Form Validation

### HTML5 Native Validation

HTML5 provides built-in validation without JavaScript.

### Validation Attributes

| Attribute   | Purpose               | Applicable To                                     |
| ----------- | --------------------- | ------------------------------------------------- |
| `required`  | Field must have value | Most inputs, select, textarea                     |
| `minlength` | Minimum text length   | text, email, password, tel, url, search, textarea |
| `maxlength` | Maximum text length   | Same as minlength                                 |
| `min`       | Minimum numeric value | number, date, time, range, etc.                   |
| `max`       | Maximum numeric value | Same as min                                       |
| `step`      | Value increment       | number, date, time, range, etc.                   |
| `pattern`   | Regex validation      | text, email, password, tel, url, search           |
| `type`      | Input type validation | All input types                                   |

### Example: Comprehensive Validation

```html
<form>
  <!-- Required field -->
  <label for="username">Username (required):</label>
  <input
    type="text"
    id="username"
    name="username"
    required
    minlength="3"
    maxlength="20"
    pattern="[a-zA-Z0-9_]+"
    title="Alphanumeric and underscore only, 3-20 characters"
  />

  <!-- Email validation -->
  <label for="email">Email:</label>
  <input type="email" id="email" name="email" required />

  <!-- Number range -->
  <label for="age">Age (18-100):</label>
  <input type="number" id="age" name="age" min="18" max="100" required />

  <!-- Pattern validation -->
  <label for="zip">ZIP Code (US):</label>
  <input type="text" id="zip" name="zip" pattern="[0-9]{5}" title="5-digit ZIP code" required />

  <button type="submit">Submit</button>
</form>
```

### CSS Validation Pseudo-Classes

```css
/* Valid inputs */
input:valid {
  border-color: green;
}

/* Invalid inputs */
input:invalid {
  border-color: red;
}

/* Required inputs */
input:required {
  border-left: 3px solid blue;
}

/* Optional inputs */
input:optional {
  border-left: 3px solid gray;
}
```

### Disabling Validation

```html
<!-- Disable validation for entire form -->
<form novalidate>
  <!-- Controls -->
</form>

<!-- Disable validation for specific submit button -->
<button type="submit" formnovalidate>Save Draft</button>
```

### Custom Validation Messages

```javascript
// JavaScript
const input = document.getElementById('username');
input.setCustomValidity('Username already taken');

// Clear custom message
input.setCustomValidity('');
```

---

## Autocomplete Attribute

**Purpose**: Controls browser autofill behavior

### Common Autocomplete Values

| Value              | Use Case                      |
| ------------------ | ----------------------------- |
| `on`               | Enable autocomplete (default) |
| `off`              | Disable autocomplete          |
| `name`             | Full name                     |
| `given-name`       | First name                    |
| `family-name`      | Last name                     |
| `email`            | Email address                 |
| `username`         | Username                      |
| `new-password`     | New password (registration)   |
| `current-password` | Current password (login)      |
| `tel`              | Phone number                  |
| `street-address`   | Street address                |
| `postal-code`      | ZIP/postal code               |
| `country`          | Country                       |
| `cc-number`        | Credit card number            |
| `cc-exp`           | Credit card expiration        |
| `cc-csc`           | Credit card security code     |

### Example

```html
<form>
  <label for="fname">First Name:</label>
  <input type="text" id="fname" name="fname" autocomplete="given-name" />

  <label for="lname">Last Name:</label>
  <input type="text" id="lname" name="lname" autocomplete="family-name" />

  <label for="email">Email:</label>
  <input type="email" id="email" name="email" autocomplete="email" />

  <label for="password">Password:</label>
  <input type="password" id="password" name="password" autocomplete="new-password" />
</form>
```

### Benefits

- Faster form completion
- Reduces user errors
- Improves accessibility
- Better mobile experience

---

## Accessible Forms Checklist

### Essential Practices

✅ **Every form control must have a label**

```html
<label for="email">Email:</label> <input type="email" id="email" name="email" />
```

✅ **Use fieldset for grouped controls**

```html
<fieldset>
  <legend>Shipping Method</legend>
  <!-- Radio buttons -->
</fieldset>
```

✅ **Indicate required fields**

```html
<label for="name">Name <span aria-label="required">*</span>:</label>
<input type="text" id="name" name="name" required />
```

✅ **Provide clear error messages**

```html
<label for="email">Email:</label>
<input type="email" id="email" name="email" aria-describedby="email-error" required />
<span id="email-error" class="error" role="alert"> Please enter a valid email address </span>
```

✅ **Use appropriate input types**

- Correct type = correct mobile keyboard
- Native validation

✅ **Keyboard accessibility**

- All controls reachable via Tab
- Logical tab order
- Enter key submits form

✅ **Associate error messages**

```html
<input type="text" id="username" aria-invalid="true" aria-describedby="username-error" />
<span id="username-error" role="alert"> Username must be at least 3 characters </span>
```

---

## Common Form Patterns

### Login Form

```html
<form action="/login" method="post">
  <h2>Sign In</h2>

  <label for="username">Username or Email:</label>
  <input type="text" id="username" name="username" autocomplete="username" required autofocus />

  <label for="password">Password:</label>
  <input type="password" id="password" name="password" autocomplete="current-password" required />

  <button type="submit">Sign In</button>

  <p><a href="/forgot-password">Forgot password?</a></p>
</form>
```

### Registration Form

```html
<form action="/register" method="post">
  <h2>Create Account</h2>

  <label for="email">Email:</label>
  <input type="email" id="email" name="email" autocomplete="email" required />

  <label for="password">Password:</label>
  <input
    type="password"
    id="password"
    name="password"
    autocomplete="new-password"
    minlength="8"
    required
  />

  <label for="confirm-password">Confirm Password:</label>
  <input
    type="password"
    id="confirm-password"
    name="confirm-password"
    autocomplete="new-password"
    minlength="8"
    required
  />

  <input type="checkbox" id="terms" name="terms" required />
  <label for="terms">I agree to the Terms of Service</label>

  <button type="submit">Create Account</button>
</form>
```

### Contact Form

```html
<form action="/contact" method="post">
  <h2>Contact Us</h2>

  <label for="name">Name:</label>
  <input type="text" id="name" name="name" autocomplete="name" required />

  <label for="email">Email:</label>
  <input type="email" id="email" name="email" autocomplete="email" required />

  <label for="subject">Subject:</label>
  <input type="text" id="subject" name="subject" required />

  <label for="message">Message:</label>
  <textarea id="message" name="message" rows="5" required></textarea>

  <button type="submit">Send Message</button>
</form>
```

### Search Form

```html
<form action="/search" method="get" role="search">
  <label for="search">Search:</label>
  <input type="search" id="search" name="q" placeholder="Search articles..." autocomplete="off" />

  <button type="submit">Search</button>
</form>
```

---

## SEO Considerations

### Form SEO Best Practices

1. **Use semantic HTML** - Proper form elements signal purpose
2. **Accessible forms = SEO-friendly** - Screen readers and bots parse similarly
3. **Don't hide forms from search engines** - Use proper form elements, not JavaScript-only forms
4. **Use action attribute** - Provides context to search engines
5. **Label inputs properly** - Improves understanding of form purpose

### Forms and Conversion Rate

Well-structured, accessible forms improve:

- User experience → Lower bounce rate
- Form completion → Higher conversion
- Trust signals → Better engagement metrics

---

## Browser Support

### Input Type Support

| Type                        | Chrome | Firefox | Safari | Edge |
| --------------------------- | ------ | ------- | ------ | ---- |
| text, password, email, etc. | ✅     | ✅      | ✅     | ✅   |
| number, range               | ✅     | ✅      | ✅     | ✅   |
| date, time                  | ✅     | ✅      | ✅     | ✅   |
| color                       | ✅     | ✅      | ✅     | ✅   |
| tel, url, search            | ✅     | ✅      | ✅     | ✅   |

**Fallback**: Unsupported types default to `type="text"`

### Validation Support

✅ All modern browsers support HTML5 validation  
⚠️ Always provide server-side validation (never trust client-side only)

---

## Security Best Practices

### Client-Side Security

1. **Never trust user input** - Validate on server
2. **Use HTTPS** - Especially for sensitive data (passwords, payment)
3. **CSRF protection** - Use tokens for state-changing operations
4. **Autocomplete for sensitive fields**:
   - `autocomplete="off"` for payment info (optional)
   - `autocomplete="new-password"` for password creation
5. **Input sanitization** - Prevent XSS attacks

### Example: Security-Enhanced Form

```html
<form action="/submit" method="post">
  <!-- CSRF token -->
  <input type="hidden" name="csrf_token" value="abc123xyz" />

  <!-- Payment info (no autocomplete) -->
  <label for="cc-number">Credit Card:</label>
  <input
    type="text"
    id="cc-number"
    name="cc-number"
    autocomplete="off"
    inputmode="numeric"
    pattern="[0-9]{13,16}"
    required
  />

  <button type="submit">Pay Now</button>
</form>
```

---

## Further Reading

- **WHATWG Forms**: [https://html.spec.whatwg.org/multipage/forms.html](https://html.spec.whatwg.org/multipage/forms.html)
- **MDN Forms Guide**: [https://developer.mozilla.org/en-US/docs/Learn/Forms](https://developer.mozilla.org/en-US/docs/Learn/Forms)
- **W3C ARIA Practices**: [https://www.w3.org/WAI/ARIA/apg/patterns/](https://www.w3.org/WAI/ARIA/apg/patterns/)
- **Autocomplete Spec**: [https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#autofill](https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#autofill)

---

**Last Updated**: Based on WHATWG HTML Living Standard (2025)
