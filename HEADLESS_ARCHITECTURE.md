# Headless CMS Architecture: Laravel + Astro Blueprint

## Overview

This document outlines the architectural decisions and strategy for using this project as a **Headless CMS Blueprint**. The goal is to create a reusable foundation where Laravel serves as the backend/admin panel and Astro acts as the high-performance frontend.

**Key Philosophy:**
*   **One Backend (Laravel):** Handles data, logic, admin UI (Filament), and API.
*   **One Frontend (Astro):** Handles presentation, routing, and performance.
*   **Decoupled:** The frontend connects to the backend exclusively via API, allowing for independent scaling and technology swaps.

---

## 1. Database Architecture

**Decision:** **Single, Self-Hosted PostgreSQL Database.**

We rejected the "Split Database" approach (Local Admin + Cloud Content) in favor of a unified architecture.

*   **Technology:** PostgreSQL (hosted via Docker for dev, VPS/Managed for production).
*   **Topology:** The database resides on the same network/server as the Laravel backend.
*   **Access:**
    *   **Laravel:** Direct connection (Read/Write).
    *   **Astro:** **NO Direct Connection.** Astro accesses data *only* via the Laravel API.
*   **Benefits:**
    *   Maintains data integrity and relationships (Foreign Keys).
    *   Simplifies backups and migrations.
    *   Zero "syncing" issues between Admin and Frontend.

---

## 2. API Strategy (The "Bridge")

Laravel acts as a pure API for the frontend. We avoid direct database queries from Astro to ensure security and centralize logic.

### Data Flow
1.  **Astro** requests data (e.g., `GET /api/services/web-design`).
2.  **Laravel** queries the database.
3.  **Laravel** transforms the data using **API Resources** (JsonResource).
4.  **Laravel** returns structured JSON.
5.  **Astro** renders the HTML.

### API Resources & Relationships
We use Laravel's `JsonResource` to handle data formatting and nested relationships.

**Example Response Structure:**
```json
{
  "data": {
    "id": 1,
    "title": "Web Design",
    "slug": "web-design",
    "content": "...",
    "featured_image": {
        "url": "https://cdn.example.com/img.jpg",
        "alt": "Hero Image",
        "srcset": "..." 
    },
    // Relationships are nested
    "related_services": [
      {
        "title": "SEO",
        "slug": "seo",
        "thumbnail": "..."
      }
    ]
  }
}
```
*   **Benefit:** Astro doesn't need complex logic. It just renders what it receives.
*   **Flexibility:** We can change the backend logic (e.g., how "Related Services" are calculated) without touching the frontend code.

### Media Handling (AWS/CloudFront)
Images are stored in AWS S3 and served via CloudFront. The API is responsible for generating the full image object, not just a raw URL.

*   **API Responsibility:** Calculate and return the full `srcset` string and variant URLs.
*   **Astro Responsibility:** Simply place the `srcset` string into the `<img>` tag.

---

## 3. Frontend Strategy (Astro)

Astro is configured based on the specific performance requirements of each project derived from this blueprint.

### Project Type A: Static Marketing Site (SSG)
*   **Use Case:** Blogs, Portfolios, Corporate sites.
*   **Mode:** `output: 'static'`
*   **Data Fetching:** Occurs at **Build Time** via `getStaticPaths()`.
*   **Updates:** Triggered via **Webhooks**. Saving a post in Laravel sends a signal to the host (Vercel/Netlify) to rebuild the site.
*   **Performance:** Maximum (Pre-rendered HTML).

### Project Type B: Dynamic/App-Like (SSR)
*   **Use Case:** E-commerce, User Dashboards, Search Directories.
*   **Mode:** `output: 'server'` (SSR).
*   **Data Fetching:** Occurs at **Request Time**.
*   **Updates:** Instant (No rebuild required).
*   **Performance:** Good (depends on API latency).

### Templating Logic
Astro uses **Dynamic Routes** to map to Laravel Models.
*   `src/pages/blog/[slug].astro` -> Consumes `BlogPost` API.
*   `src/pages/services/[slug].astro` -> Consumes `Service` API.
*   `src/pages/[slug].astro` -> Consumes generic `Page` API.

---

## 4. Development Workflow

1.  **Local Development:**
    *   Laravel running in Docker (Sail) on localhost.
    *   Astro running on localhost, pointing to `http://localhost/api`.
2.  **Deployment (Production):**
    *   **Laravel:** Deployed to a VPS (DigitalOcean/Hetzner) with Docker.
    *   **Astro:**
        *   *Option A (Static):* Deployed to a CDN (Vercel/Netlify).
        *   *Option B (SSR):* Deployed as a Node.js app or to an Edge network.

---

## 5. Next Implementation Steps

1.  **API Structure:** Create the `api.php` routes and Controller structure.
2.  **Resources:** Implement `ServiceResource`, `PostResource`, and the critical `MediaResource`.
3.  **Authentication (Future):** If the frontend needs user login, implement Sanctum or simple Token auth for the API.