Create a new specification (005-headless-api) for implementing the Headless API Layer.
The goal is to expose the EXISTING content models to an external frontend (Astro) via a structured JSON API.

**Architecture:**
- **Namespace:** `App\Http\Controllers\Api\V1`
- **Route Prefix:** `api/v1`
- **Format:** JSON (using Laravel JsonResources)

**Core Requirements:**

1.  **Media Handling (Leverage existing `MediaAsset`):**
    - Create `Api\V1\MediaResource`.
    - It must transform the `MediaAsset` model.
    - **Logic:**
      - Use `cloudfront_url_original` for the main URL.
      - Iterate over the `variants` relationship to build the `srcset` string.
      - Return a rich object: `original_url`, `alt_text`, `caption`, `width`, `height`, `srcset`, `variants` (array of variant URLs).

2.  **Domain Resources (Transform existing Models):**
    - **ServiceResource:** Wraps `App\Models\Service`.
      - Includes `content_blocks` (already cast by `HasContentBlocks` trait).
      - Includes `featured_image` (via `MediaResource`).
      - Includes `related_services` (via existing relationship).
    - **BlogPostResource:** Wraps `App\Models\BlogPost`.
      - Includes `author`, `tags`, `published_at`.
    - **PageResource:** Wraps `App\Models\Page`.

3.  **API Controllers (Read-Only):**
    - Create `ServiceController`, `PostController`, `PageController` in `Api\V1`.
    - **Method:** `show(string $slug)` is the priority.
    - **Method:** `index()` for listing.
    - **Constraint:** Must use `firstOrFail()` with `slug` column.

4.  **Routes:**
    - Register public routes in `routes/api.php`.
    - No authentication middleware for these public "Get Content" routes.

5.  **Testing:**
    - Create Feature tests ensuring the JSON structure matches what Astro expects (specifically the `srcset` format).

  
  
  
  
    Based on `specs/005-headless-api`, generate the implementation plan.

**Key Constraints:**
- Do NOT modify existing Models or Migrations.
- Do NOT create new tables.
- Focus strictly on the **API Layer** (Controllers + Resources).

**Step-by-Step Plan:**
1.  **Setup:** Create `App\Http\Resources\Api\V1` namespace.
2.  **Media:** Implement `MediaResource` first. This is the dependency for all other resources.
    - *Verification:* Unit test the `srcset` generation logic within the Resource.
3.  **Content Resources:** Implement `ServiceResource`, `BlogPostResource`, `PageResource`.
    - *Detail:* Ensure `content_blocks` are passed through as raw JSON/Array.
4.  **Controllers:** Create the Read-Only controllers.
    - *Detail:* Use `eagerLoad` ('variants', 'relatedContents') to solve N+1 issues.
5.  **Routes:** Register `api/v1` routes in `routes/api.php`.
6.  **Feature Tests:** Write `tests/Feature/Api/V1/ContentApiTest.php` to verify full JSON response structure.