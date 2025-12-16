## Public Pages Listing Tables – Implementation Summary

### 1. Purpose and scope

- **Goal**: Provide a **single, consistent admin table UI** for all public-facing content types (static pages, services, service areas, fleet vehicles, blog posts) under the **Public Pages** section in `/admin`.
- **Key behaviors**:
  - **Search + filters** (status, date range) shared across all page types.
  - **Sorting, pagination, row selection, and bulk actions** (publish, delete).
  - **Per-row actions** (edit, view live, publish, delete) via a dropdown.
  - **Safe deletion** with confirmation modal and optional relationship checks.
- **Core abstraction**: `UniversalDataTable<T extends BaseContentItem>` in `components/admin/universal-table/universal-data-table.tsx`.

---

### 2. Tech stack used

- **Frontend framework**: **Next.js App Router** (React 18, client components for list pages in `app/(admin)/admin/...`).
- **Language**: **TypeScript** (strong typing via generics and shared interfaces).
- **UI & styling**:
  - **React** function components and hooks (`useState`, `useEffect`, `useMemo`).
  - **Tailwind CSS** utility classes for layout and styling.
  - **shadcn/ui** components (Radix-based) wrapped under `components/ui/*`:
    - `Table`, `TableHeader`, `TableBody`, `TableRow`, `TableHead`, `TableCell`.
    - `Button`, `Input`, `Select`, `Popover`, `Calendar`, `Dialog`, etc.
  - **Icons** from `lucide-react` (e.g., `PlusCircle`, `RefreshCw`, `MoreHorizontal`, `FileCheck`, `Trash2`).
- **Table engine**: **TanStack React Table v8** (`@tanstack/react-table`) for core table logic:
  - Row model, sorting, filtering, pagination, selection state.
- **Backend & data**:
  - **Next.js API routes** under `app/api/admin/content/*` for CRUD + bulk actions.
  - **Supabase/Postgres** as the database (content tables + `display_order`, `deleted_at`, SEO fields, JSON `custom_fields`).
  - Auth enforced globally via middleware (`middleware.ts`) that validates Supabase admin session.
- **Notifications**: Custom `useToast` hook and toast components used for success/error messages.

---

### 3. Shared data model (`BaseContentItem`)

All public content tables share a common TypeScript shape, defined in `components/admin/universal-table/types.ts` as `BaseContentItem`:

- **Core fields**:
  - **`id: string`** – primary key (UUID in database).
  - **`slug: string`** – URL slug used for public routes.
  - **`status: 'draft' | 'published' | 'scheduled'`** – publishing workflow.
  - **Timestamps**: `created_at`, `updated_at`, `published_at`, `scheduled_for`.
  - **Soft delete**: `deleted_at: string | null` (rows with this set are filtered out in list pages).
- **SEO fields**:
  - `meta_title`, `meta_description`, `meta_robots`, `canonical_url`.
  - `og_data`, `schema_json` (JSON for social/structured data).
- **Flexible content**:
  - **`custom_fields: Record<string, any>`** – type-specific fields (e.g., `page_title`, `title`, `area_name`, `vehicle_name`).
  - Optional **`display_order?: number | null`** for future drag-and-drop ordering.

Each content type (Service, StaticPage, etc.) **extends** `BaseContentItem` and narrows `custom_fields` to its own properties.

---

### 4. Universal table configuration (`UniversalTableConfig`)

`UniversalDataTable` is configured via a generic config interface `UniversalTableConfig<T>`:

- **Required config props**:
  - **`data: T[]`** – already-loaded array of content items.
  - **`contentType: string`** – label used in UI copy (e.g. "Service", "Static Page").
  - **`tableName: string`** – DB table name (e.g. `services`, `blog_posts`).
  - **`titleField: string`** – dot-path to the title in `custom_fields` (`"custom_fields.title"`, `"custom_fields.page_title"`, `"custom_fields.area_name"`, etc.).
  - **`editPath: (id: string) => string`** – function returning admin edit URL.
  - **`viewPath: (slug: string) => string`** – function returning public view URL.
- **Optional behavior flags**:
  - **`enableBulkActions?: boolean`** – toggles checkbox selection + bulk toolbar (default `true`).
  - **`enableManualSorting?: boolean`** – reserved for drag-and-drop ordering (not currently enabled in UI).
- **Callbacks**:
  - **`onRefresh?: () => void`** – called after mutations so the parent page can re-fetch data.
  - **`onDelete: (id: string) => Promise<void>`** – required; single-item delete handler.
  - **`onPublish?: (id: string) => Promise<void>`** – optional single-item publish handler.
  - **`onBulkDelete?: (ids: string[]) => Promise<void>`** – optional bulk delete handler.
  - **`onBulkPublish?: (ids: string[]) => Promise<void>`** – optional bulk publish handler.
- **Relationship-aware deletion**:
  - **`relationshipContentType?: RelationshipContentType`** – when set (e.g. `"service"`, `"static_page"`), deletion first calls `/api/admin/content/{relationshipContentType}/{id}/usage` and may display a warning modal if the item is referenced by other pages.

This config lets the same `UniversalDataTable` power all public-page list views with only table-specific wiring at the page level.

---

### 5. Table UI architecture

`UniversalDataTable` is a **client-side React component** that combines TanStack Table with shadcn UI primitives and a few helper components:

- **Layout** (high level):
  - **Filter bar** at top: `FilterToolbar` (search, status, date range, clear button).
  - **Bulk actions toolbar**: `BulkActionsToolbar` (only appears when rows are selected).
  - **Main table**: shadcn `Table` + TanStack header/body rendering.
  - **Pagination footer**: rows-per-page select + page index and navigation buttons.
  - **Modals**: `DeleteConfirmationModal` + `DeletionWarningModal` (if relationships exist).

- **Core internal state** (React hooks):
  - TanStack state: `sorting`, `columnFilters`, `columnVisibility`, `rowSelection`.
  - Filter state: `filters: TableFilters` (search text, status, date range).
  - Deletion state: `deleteModalOpen`, `itemToDelete`, `bulkDeleteMode`.
  - Relationship warning state: `deletionWarning`, `isCheckingRelationships`, `isConfirmingWarningDelete`.

- **Column definitions**:
  - Derived via `createDefaultColumns<T>(...)` unless `customColumns` is supplied.
  - Default columns include:
    - Selection checkbox (if bulk actions enabled).
    - Title column (using `titleField` path and a human label derived from content type).
    - Slug column.
    - Last updated date (`updated_at`).
    - Status badge (draft/published/scheduled).
    - Meta title + meta description (truncated, often with tooltip).
    - Actions column with `TableActions` dropdown.

- **Actions dropdown (`TableActions`)**:
  - Shows per-row options:
    - **Edit** – navigates to `editPath(id)` via Next.js router.
    - **View Page** – opens `viewPath(slug)` in a new tab (only for `status === 'published'`).
    - **Publish** – calls `onPublish(id)` when item is in `draft` state.
    - **Delete** – triggers `onDelete(id)` via a callback into `UniversalDataTable` (opens confirmation flow).

---

### 6. Filters and search behavior

The filters in `UniversalDataTable` are **client-side** and implemented with a dedicated `FilterToolbar` component and memoized filtering logic:

- **Filter state shape (`TableFilters`)**:
  - **`search: string`** – free-text search box.
  - **`status: 'all' | 'draft' | 'published' | 'scheduled'`** – status dropdown.
  - **`dateFrom: Date | null`**, **`dateTo: Date | null`** – optional date range (filter on `updated_at`).

- **Search logic**:
  - `filters.search` is lowercased and matched against a concatenation of several text fields per item:
    - The resolved `titleField` from `custom_fields` (e.g. `page_title`, `title`, `area_name`, `vehicle_name`).
    - `slug`.
    - `meta_title`.
    - `meta_description`.
  - All available strings are joined into a single `searchableText` and `.includes(searchLower)` is used for a simple substring match.

- **Status filter**:
  - If **`status !== 'all'`**, the item must have `item.status === filters.status` to be included.
  - This matches the publishing workflow states (draft / published / scheduled).

- **Date range filter**:
  - Uses `updated_at` as the source date (converted to `Date`).
  - Applies one of:
    - Both `dateFrom` and `dateTo` – item date must be **within** the interval via `isWithinInterval`.
    - Only `dateFrom` – item date must be `>= dateFrom`.
    - Only `dateTo` – item date must be `<= dateTo`.

- **Filter toolbar UI (`FilterToolbar`)**:
  - **Search input** with optional placeholder override (e.g., "Search services...", "Search static pages...").
  - **Status select** (All, Draft, Published, Scheduled).
  - **Date range picker** implemented with a `Popover` and two `Calendar` components (From/To).
  - **Clear filters button** (X icon) that resets all filters to defaults.
  - `hasActiveFilters` is computed to decide whether the clear button should show.

The combination of `FilterToolbar` and `filteredData = useMemo(...)` ensures fast, client-side filtering across the smallish admin datasets.

---

### 7. Sorting, pagination, and selection

- **Sorting**:
  - Provided by **TanStack Table** via `getSortedRowModel()`.
  - Each sortable column has a header that can be clicked to toggle sort direction.
  - Sort state is stored in `sorting: SortingState` and passed into `useReactTable`.

- **Pagination**:
  - TanStack Table is configured with `getPaginationRowModel()`.
  - Page size selector uses a `Select` with values `[10, 25, 50, 100]` and calls `table.setPageSize(Number(value))`.
  - Footer displays the current item range (e.g. `1-10 of 57`) based on `pageIndex`, `pageSize`, and `filteredData.length`.
  - Navigation buttons (First, Previous, Next, Last) call `table.setPageIndex(0)`, `table.previousPage()`, `table.nextPage()`, and `table.setPageIndex(table.getPageCount() - 1)` respectively.

- **Row selection & bulk actions**:
  - Row selection state: `rowSelection: RowSelectionState` tracked in component state and wired to TanStack via `onRowSelectionChange`.
  - `getFilteredSelectedRowModel()` produces the selected rows after filters are applied.
  - **`selectedCount`** = `table.getFilteredSelectedRowModel().rows.length`.
  - When `selectedCount > 0`, `BulkActionsToolbar` is rendered with:
    - **Publish** button – calls `handleBulkPublish`, which maps selected rows to IDs and forwards them to `onBulkPublish`.
    - **Delete** button – opens delete modal in `bulkDeleteMode` and passes selected IDs to `onBulkDelete` upon confirmation.
    - **Clear** button – resets `rowSelection`.

---

### 8. Deletion flows and safety

There are two layers of delete safety:

- **Standard confirmation modal (`DeleteConfirmationModal`)**:
  - Opens when user triggers delete (single or bulk).
  - Requires typing the string **"delete"** to enable the **Delete** button.
  - Shows a different message for single vs multiple items (based on `itemCount`).
  - On confirm, calls `handleConfirmDelete`, which:
    - For bulk mode, calls `onBulkDelete(selectedIds)` then clears selection and refreshes.
    - For single mode, calls `onDelete(itemToDelete)` then refreshes.

- **Relationship-aware deletion (`DeletionWarningModal`)**:
  - When `relationshipContentType` is provided, the table **first calls**:
    - `GET /api/admin/content/{relationshipContentType}/{id}/usage`.
  - If the API returns `usage.count > 0`, it **does not open the standard delete modal immediately**; instead it opens a dedicated `DeletionWarningModal` that:
    - Lists the number of affected pages and their titles.
    - Requires explicit confirmation before proceeding.
  - Confirming in this modal calls `onDelete(deletionWarning.contentId)` and then `onRefresh`.

This pattern is designed to avoid accidentally deleting content that is still used by other pages (e.g. services linked from static pages, or vice versa).

---

### 9. Page-level integration per content type

Each Public Pages list view is a **thin wrapper** around `UniversalDataTable` that wires in data fetching, API calls, and content-specific config. All follow the same pattern:

- **Common structure** (example: `/admin/services` in `app/(admin)/admin/services/page.tsx`):
  - `useState` for `items` (typed as `Service[] extends BaseContentItem`) and `isLoading`.
  - `useToast` for showing success/error toasts.
  - `useEffect` to fetch items on mount (and after mutations via `onRefresh`).
  - `fetchX` function that calls `/api/admin/content/{table}` and filters out soft-deleted rows (`!item.deleted_at`).
  - Per-item `handleDelete(id)` and `handlePublish(id)` calling REST endpoints.
  - Bulk handlers `handleBulkDelete(ids)` and `handleBulkPublish(ids)` calling `/api/admin/content/bulk/delete` and `/api/admin/content/bulk/update-status`.
  - Header area with page title, description, **Refresh** button, and **New [Content]** button.
  - Conditional **skeleton** (`AdminListSkeleton`) when `isLoading && items.length === 0`.

- **Static pages (`/admin/pages`)**:
  - Table name: **`static_pages`**.
  - `titleField`: **`"custom_fields.page_title"`**.
  - `editPath`: `(id) => "/admin/pages/" + id`.
  - `viewPath`: `(slug) => "/" + slug`.
  - `relationshipContentType`: **`"static_page"`**.

- **Services (`/admin/services`)**:
  - Table name: **`services`**.
  - `titleField`: **`"custom_fields.title"`**.
  - `editPath`: `(id) => "/admin/services/" + id`.
  - `viewPath`: `(slug) => "/services/" + slug`.
  - `relationshipContentType`: **`"service"`**.

- **Service areas (`/admin/service-areas`)**:
  - Table name: **`service_areas`**.
  - `titleField`: **`"custom_fields.area_name"`**.
  - `editPath`: `(id) => "/admin/service-areas/" + id`.
  - `viewPath`: `(slug) => "/service-area/" + slug`.
  - `relationshipContentType`: **`"service_area"`**.

- **Fleet vehicles (`/admin/fleet`)**:
  - Table name: **`fleet_vehicles`**.
  - `titleField`: **`"custom_fields.vehicle_name"`**.
  - `editPath`: `(id) => "/admin/fleet/" + id`.
  - `viewPath`: `(slug) => "/our-fleet/" + slug`.
  - `relationshipContentType`: **`"fleet_vehicle"`**.

- **Blog posts (`/admin/blog`)**:
  - Table name: **`blog_posts`**.
  - `titleField`: **`"custom_fields.title"`**.
  - `editPath`: `(id) => "/admin/blog/" + id`.
  - `viewPath`: `(slug) => "/blog/" + slug`.
  - `relationshipContentType`: **`"blog_post"`**.

Despite different data models and URLs, all five use **the exact same table component and filter/search behavior** due to the universal abstraction.

---

### 10. API contract for list and bulk actions

To support the table, the backend exposes a small, consistent set of HTTP endpoints under `/api/admin/content`:

- **List items**:
  - `GET /api/admin/content/{table}` – returns JSON `{ data: BaseContentItem[], pagination?: {...} }`.
  - Each admin page calls this on mount (via `fetch('/api/admin/content/static_pages')`, etc.).

- **Single item actions**:
  - `DELETE /api/admin/content/{table}/{id}` – soft delete (sets `deleted_at`).
  - `PATCH /api/admin/content/{table}/{id}` – used for publishing single items, typically with body:
    - `{ "status": "published", "published_at": ISOString }`.

- **Bulk actions**:
  - `POST /api/admin/content/bulk/update-status` – bulk publish.
    - Request body: `{ "ids": ["uuid1", ...], "status": "published", "tableName": "services" }`.
  - `POST /api/admin/content/bulk/delete` – bulk delete.
    - Request body: `{ "ids": ["uuid1", ...], "tableName": "services" }`.
  - Responses include a `message`, `success`, and counts (e.g. `updated`, `deleted`).

These endpoints are **secured by auth middleware** and use a table name whitelist on the server side to prevent SQL injection or arbitrary table access.

---

### 11. What to replicate in a Laravel/PHP implementation

To recreate the **exact same table UX** in a Laravel-based project, you should mirror these aspects:

- **Data shape**:
  - Use a common **content model interface** (or PHP DTO) equivalent to `BaseContentItem`, including `slug`, `status`, timestamps, SEO fields, `custom_fields` (JSON), and `deleted_at`.

- **Endpoints**:
  - Implement REST-style routes:
    - `GET /admin/content/{table}` → returns an array of items (with the shared shape).
    - `DELETE /admin/content/{table}/{id}` → soft delete.
    - `PATCH /admin/content/{table}/{id}` → single publish.
    - `POST /admin/content/bulk/update-status` and `POST /admin/content/bulk/delete` → bulk operations.
  - Ensure responses match the expectations above so the table UI can stay almost identical.

- **Frontend behavior** (whether React via Inertia, Livewire, or Blade + Alpine):
  - Top **filter toolbar** with:
    - Free-text search over title/slug/meta.
    - Status dropdown (All/Draft/Published/Scheduled).
    - Date range picker on `updated_at`.
    - Clear filters button.
  - **Client-side or server-side filtering** that respects these filters.
  - **Paginated table** with:
    - Sortable columns.
    - Page size selector (10/25/50/100).
    - First/Prev/Next/Last controls and an item range display.
  - **Row selection and bulk actions**:
    - Checkboxes + bulk toolbar with Publish, Delete, Clear.
  - **Row actions dropdown**:
    - Edit, View, Publish (only for drafts), Delete (with confirmation flow).
  - **Delete safety**:
    - Modal requiring the user to type "delete" to confirm.
    - Optional relationship check endpoint and warning modal before final delete.

If you mirror this structure, data shape, and set of behaviors in Laravel, you’ll effectively reproduce the **same admin table UX** (search, filters, bulk actions, safe deletes) that this Next.js project uses for all public pages.