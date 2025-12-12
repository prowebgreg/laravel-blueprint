# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**The Blueprint CMS** - A high-performance, strictly-typed, developer-first Content Management System built to replace WordPress for professional bespoke website development. Schema and structure are code-defined, not database-configured.

**Core Stack:** Laravel 12.x, Filament V3, PostgreSQL 17, Redis, AWS S3/CloudFront, Vite, Tailwind CSS (admin only), Custom SCSS (public frontend), Alpine.js

## Critical Development Rules

**Use Laravel Sail for development.** The app runs at `http://localhost` (port 80). Start with: `./vendor/bin/sail up -d`

**All PHP files MUST declare `strict_types=1`** at the top of every file.

## Commands

```bash
# Start development environment (Docker containers)
./vendor/bin/sail up -d

# Stop development environment
./vendor/bin/sail down

# Run tests
./vendor/bin/sail test

# Run single test file
./vendor/bin/sail test tests/Feature/MediaUploadTest.php

# Run tests with filter
./vendor/bin/sail test --filter=testName

# Run artisan commands
./vendor/bin/sail artisan <command>

# Code formatting (Laravel Pint)
./vendor/bin/sail pint

# Build frontend assets
./vendor/bin/sail npm run build

# Development assets with HMR
./vendor/bin/sail npm run dev

# Access container shell
./vendor/bin/sail shell

# Start Horizon queue workers
./vendor/bin/sail artisan horizon

# Purge soft-deleted content (30+ days old)
./vendor/bin/sail artisan content:purge-deleted
./vendor/bin/sail artisan content:purge-deleted --dry-run  # Preview only
```

## Architecture Patterns

| Pattern | Purpose | Location |
|---------|---------|----------|
| Direct Eloquent | Data access (no repository abstraction) | `app/Models/` |
| Action Classes | Single-responsibility operations | `app/Actions/` |
| Service Classes | Orchestrate multiple Actions | `app/Services/` |
| Form Requests | Validate all mutations | `app/Http/Requests/` |
| DTOs | API boundary contracts | `app/DTOs/` |

## Code Organization (Current Structure)

```
app/
├── Actions/Media/     # Media pipeline actions (7 actions)
├── Blocks/            # Content block definitions (HeroBlock, CtaBlock)
├── Console/Commands/  # Artisan commands (PurgeDeletedContentCommand)
├── DTOs/              # Data Transfer Objects (Descriptive+Data)
├── Enums/             # Type enums (ContentStatus, MediaType, MediaState, MediaFolder, OgType)
├── Http/
│   ├── Controllers/   # Resource+Controller
│   ├── Requests/      # Resource+Operation+Request
│   └── Middleware/
├── Jobs/Media/        # Media queue jobs (ProcessMediaVariantsJob, CleanupFailedMediaJob, SyncOrphanedFilesJob)
├── Models/            # Page, Service, BlogPost, Faq, Testimonial, MediaAsset, MediaVariant, Setting
├── Services/Media/    # Media services (MediaUploadService, MediaDeletionService, MediaFallbackService, MediaUsageService)
└── Traits/            # HasSeo, HasSlug, HasContentBlocks, HasRelatedContent, HasMedia
```

## Naming Conventions

- **Models**: Singular PascalCase (`Page`, `Service`, `BlogPost`, `Faq`)
- **Controllers**: `{Resource}Controller`
- **Actions**: `{Verb}Action` (`OptimizeImageAction`)
- **Services**: `{Noun}Service` (`MediaService`)
- **Form Requests**: `{Resource}{Operation}Request` (`StorePageRequest`)
- **DTOs**: `{Descriptive}Data` (`CreateServiceData`)
- **Traits**: `Has{Feature}` or `Is{State}` (`HasSeo`, `IsPublishable`)
- **Jobs**: `{Verb}Job` (`ProcessMediaJob`)

## Performance Requirements

- **Lighthouse Score**: 95+ across all categories (Performance, Accessibility, Best Practices, SEO)
- **Core Web Vitals**: LCP < 2.5s, FID < 100ms, CLS < 0.1
- **Query Limits**: Warning at 20 queries/request, exception at 30 (dev only)
- **Mandatory**: Explicit `with()` for eager loading, no lazy loading in Blade

## Key Principles

1. **Code-Defined Structure**: Schema lives in PHP classes, not database. Admin panel reflects code-defined structure.
2. **Performance First**: SSR via Blade templates. No client-side hydration frameworks. JS limited to Alpine.js.
3. **Asset Sovereignty**: All media processed and offloaded to S3/CloudFront. App server serves only dynamic content.
4. **Hybrid Headless**: Native Blade frontend with full API for external automation tools (Notion, n8n).
5. **Clarity Over Cleverness**: Explicit over implicit. PSR-12 via Laravel Pint. PHPStan Level 6 minimum.

## Content Architecture

### Content Types
- **Static Pages** (`Page`): Standard architecture pages (Home, About, Contact) at `/{slug}`
- **Custom Pages** (`Service`, `BlogPost`): Repeatable public content at `/services/{slug}`, `/blog/{slug}`
- **Content Resources** (`Faq`, `Testimonial`, `TeamMember`): Hidden content used to populate page sections (no public URL)

### Field Types
- **Fixed Fields**: Shared across all pages (identity, SEO-related: meta title, description, canonical, noindex, breadcrumbs)
- **Custom Fields**: Unique to a specific page or page type
- **Reusable Fields**: Grouped into reusable section/component types (CTA, FAQs section) shareable across pages
- **Structured Data Fields**: Populate JSON-LD templates, reusing existing fields where possible

### Relationship Engine
- Generic many-to-many relationships between any page-like model and content resource
- Configurable pairs (e.g., `Service` ↔ `Faq`, `Service` ↔ `ServiceArea`)
- Ordered display: relationships preserve manually defined order for frontend rendering

## Styling Rules

- **Admin/Filament UI**: Tailwind CSS exclusively
- **Public Frontend**: Custom SCSS + CSS variables (no Tailwind utility classes)
- **Per-Project**: Each new site creates fresh SCSS structure for public frontend while reusing shared admin styling

## Testing

- Use **Pest PHP** (not PHPUnit directly)
- Overall coverage target: 70% minimum
- Critical paths (media pipeline, API, SEO, Actions): 90%+ coverage
- Unit tests mock external dependencies (S3, Redis)
- Feature tests test HTTP endpoints end-to-end with database

## Quality Gates

| Tool | Standard | Enforcement |
|------|----------|-------------|
| Laravel Pint | `laravel` preset | Pre-commit + CI |
| PHPStan + Larastan | Level 6 | CI blocking |
| `composer audit` | No vulnerabilities | CI blocking |
| `npm audit --audit-level=high` | No high vulnerabilities | CI blocking |

## Spec-Kit Workflow

This project uses spec-kit for feature development. Key commands:
- `/speckit.specify` - Create/update feature specification
- `/speckit.plan` - Generate implementation plan
- `/speckit.tasks` - Generate actionable tasks
- `/speckit.implement` - Execute implementation plan
- `/speckit.clarify` - Ask clarification questions for underspecified areas

Constitution and templates are in `.specify/` directory.

## Media Pipeline

- All uploads go to S3 `/temp` folder → processed via queue → moved to `/permanent` folder
- Convert JPG/PNG to WebP, run `spatie/image-optimizer`, generate responsive widths (480, 640, 720, 960, 1168, 1440, 1920px)
- SVG and Video bypass resizing and WebP conversion
- In Blade, use `<figure>` tag or Spatie helper to ensure WebP and responsive `srcset`
- All media served via CloudFront URLs (never S3 direct or local storage)

## API Layer

- Sanctum token authentication for external tools
- `POST /api/v1/pages/{type}` - create/update any page-like model
- `POST /api/v1/content/{type}` - create/update Content Resources
- Payloads mirror declared schema; include `relationships` section for attaching related records with order
- Strict validation against model schema

## Current Development Phase

**Phase 1 (Complete)**: Architecture & Environment - Laravel Sail, PostgreSQL, Redis, Filament V3

**Phase 2 (Complete)**: Core Data Models & Schema
- Content Models: `Page`, `Service`, `BlogPost`, `Faq`, `Testimonial`
- Traits: `HasSeo`, `HasSlug`, `HasContentBlocks`, `HasRelatedContent`, `HasMedia`
- Enums: `ContentStatus`, `OgType`
- Content Blocks: `HeroBlock`, `CtaBlock` (with `BlockInterface` contract)
- Relationship Engine via `content_relations` polymorphic pivot table
- `PurgeDeletedContentCommand` for soft-delete cleanup

**Phase 3 (Complete)**: Media Engine
- Models: `MediaAsset` (UUID), `MediaVariant` (UUID)
- Enums: `MediaType`, `MediaState`, `MediaFolder`
- Upload Pipeline: `MediaUploadService` orchestrates validation → sanitization → S3 upload
- Actions: `ValidateUploadAction`, `SanitizeFilenameAction`, `SanitizeSvgAction`, `UploadToS3Action`, `GenerateVariantsAction`, `ExtractImageMetadataAction`, `DeleteFromS3Action`
- Services: `MediaUploadService`, `MediaDeletionService`, `MediaFallbackService`, `MediaUsageService`
- Jobs: `ProcessMediaVariantsJob`, `CleanupFailedMediaJob`, `SyncOrphanedFilesJob`
- Horizon queue configuration with dedicated `media` supervisor
- Responsive variants: 480, 640, 720, 960, 1168, 1440, 1920px widths (WebP)
- SVG sanitization via `enshrined/svg-sanitize`

**Phase 4 (Next)**: Filament Admin Panel - CRUD resources, media picker, relationship management

## Implementation Notes

### PostgreSQL 17 UUID Handling
PostgreSQL 17's PDO driver incorrectly infers UUID type for hyphenated string parameters. Use explicit `::text` casts in raw queries:
```php
// WRONG: PDO infers UUID type, fails with "invalid input syntax for type uuid"
->where('slug', $slug)

// CORRECT: Explicit text cast
->whereRaw('slug::text = ?::text', [$slug])
```

### Media State Machine
```
Uploading → Processing → Ready (success)
                      → Failed (failure, retryable)
```
- Images: Start `Processing`, transition to `Ready` after variant generation
- SVG/Video: Start `Ready` immediately (no variants needed)
- Use `MediaState::isAccessible()` to check if media can be served

### Content Relations Pivot Table
The `content_relations` table supports both content-to-content and content-to-media relationships:
- `source_type`/`source_id`: Model initiating relationship
- `target_type`/`target_id`: Model being related (supports UUID for MediaAsset)
- `relation_type`: Type identifier (e.g., `page:home:hero:image`)
- `order`: Display ordering (0-indexed)

### Block Interface Contract
New blocks must implement `App\Blocks\Contracts\BlockInterface`:
```php
public static function type(): string;        // Unique identifier
public static function schema(): array;       // Field definitions
public static function validate(array $data, Closure $fail, int $index): void;
```

## Developer Guidelines (PRD Rules)

1. **Filament Best Practice**: Use `schema()` within Resource files. Do not rely on auto-discovery.
2. **No Logic in Views**: Blade files should only display data. Logic belongs in ViewModels or Components.
3. **Content Schema Discipline**: Define all fields explicitly as Fixed, Custom, Reusable, or Structured Data Fields in code; avoid ad-hoc JSON blobs or new "meta" tables.
4. **Relationship Engine Only**: When linking content, always use the generic Relationship Engine; do not introduce custom pivot tables unless PRD is extended.
5. **Structured Data Implementation**: Generate all JSON-LD via Structured Data Engine templates in code; never hard-code schema JSON in Blade views.
6. **Media Pipeline Discipline**: All media must go through the Media Engine (S3 `/temp` → `/permanent`, conversions); never bypass or serve from local storage.
7. **Example Data**: Every new feature must ship with minimal seed/example data for end-to-end verification.
8. **Laravel Boost MCP First**: Follow `laravel/boost` package patterns as primary source; extend PRD only where necessary.

## Response Style Preferences

### After Task Completion
- Provide brief summary as a checklist with checkmarks
- No lengthy explanations of process or implementation details
- Focus on what was accomplished, not how

### When to Provide Detail
- Clarifying questions - be direct and specific
- Planning tasks - include only necessary info for decision-making
- Keep outputs concise and scannable

### Examples

✅ Good:
```
Done:
✅ Created UserService class
✅ Added validation rules
✅ Updated routes
Ready to test.
```

❌ Avoid: "I've successfully completed the modifications you requested. Here's what I did: First, I analyzed the existing code structure, then I implemented the changes across multiple files including..."

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.28
- filament/filament (FILAMENT) - v3
- laravel/framework (LARAVEL) - v12
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== sail rules ===

## Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands** with `vendor/bin/sail`. Examples:
- Run Artisan Commands: `vendor/bin/sail artisan migrate`
- Install Composer packages: `vendor/bin/sail composer install`
- Execute node commands: `vendor/bin/sail npm run dev`
- Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `vendor/bin/sail artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `vendor/bin/sail artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/sail bin pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test`, simply run `vendor/bin/sail bin pint` to fix any formatting issues.


=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `vendor/bin/sail artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `vendor/bin/sail artisan test`.
- To run all tests in a file: `vendor/bin/sail artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `vendor/bin/sail artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>
</laravel-boost-guidelines>

## Active Technologies
- PHP 8.3.x with `strict_types=1` + Laravel 12.x, Filament V3, Tailwind CSS v3, Vite (004-admin-panel-scaffold)
- PostgreSQL 17 (existing), Redis (existing) (004-admin-panel-scaffold)
- PHP 8.3.x with `strict_types=1` + Laravel 12.x, Filament v3.x, Livewire v3.x, Tailwind CSS v3.x (004-admin-panel-scaffold)
- PostgreSQL 17 (JSONB for content blocks), Redis 7 (queues/cache) (004-admin-panel-scaffold)

**Backend:** PHP 8.3.x (`strict_types=1` in all files), Laravel 12.x, Filament V3

**Database:** PostgreSQL 17 with JSONB for content blocks, Redis for caching/queues

**Media Processing:** spatie/image ^3.0, spatie/laravel-image-optimizer ^1.7, enshrined/svg-sanitize ^0.16

**Storage:** AWS S3 with CloudFront CDN (temp → permanent folder pipeline)

**Queue:** Laravel Horizon with dedicated `media` supervisor

## Recent Changes
- 004-admin-panel-scaffold: Added PHP 8.3.x with `strict_types=1` + Laravel 12.x, Filament V3, Tailwind CSS v3, Vite
