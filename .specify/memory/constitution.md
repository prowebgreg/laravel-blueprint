<!--
================================================================================
SYNC IMPACT REPORT
================================================================================
Version Change: 0.0.0 (template) → 1.0.0 (initial ratification)
Bump Rationale: MAJOR - Initial constitution establishment with full governance

Modified Principles:
  - [PRINCIPLE_1_NAME] → I. Code-Defined Structure
  - [PRINCIPLE_2_NAME] → II. Performance First
  - [PRINCIPLE_3_NAME] → III. Asset Sovereignty
  - [PRINCIPLE_4_NAME] → IV. Testing Excellence
  - [PRINCIPLE_5_NAME] → V. Clarity Over Cleverness

Added Sections:
  - Code Quality Standards (PHP Standards, Architecture Patterns, Naming, Organization)
  - Performance Requirements (Lighthouse, Core Web Vitals, Query Discipline, Caching)
  - Security & Privacy (Authentication, Rate Limiting, Dependency Security, Data Protection)
  - Technical Constraints (Version Requirements, Extensions, Environment Structure)
  - Logging & Observability (Logging Strategy, Log Format)
  - Documentation Standards (Code Documentation, Project Documentation, API Documentation)
  - Deployment & Infrastructure (Local Development, Production, CI/CD)
  - Decision-Making Framework

Removed Sections:
  - All placeholder content removed

Templates Requiring Updates:
  - .specify/templates/plan-template.md: ✅ Compatible (Constitution Check section exists)
  - .specify/templates/spec-template.md: ✅ Compatible (Requirements section aligns)
  - .specify/templates/tasks-template.md: ✅ Compatible (Phase structure supports principles)

Follow-up TODOs: None
================================================================================
-->

# The Blueprint CMS Constitution

## Core Principles

### I. Code-Defined Structure

The schema (Post Types, Fields, Relations) MUST live in PHP classes under version control, not in database configurations. The Filament Admin Panel is a window into this schema, not the source of truth.

**Non-Negotiable Rules**:
- All content types, field definitions, and relationships MUST be defined in PHP classes
- Database migrations MUST be generated from code definitions, never edited manually for schema changes
- Configuration changes MUST go through version control, never direct database manipulation
- The admin panel MUST reflect code-defined structure; it MUST NOT create or modify schema

**Rationale**: This ensures stability, testability, and reproducibility across Blueprint instances. A new environment can be spun up from code alone without database dumps or manual configuration.

### II. Performance First

All public pages MUST achieve 95+ Google Lighthouse scores across Performance, Accessibility, Best Practices, and SEO. Server-Side Rendering via Blade is mandatory.

**Non-Negotiable Rules**:
- SSR via Blade templates for all public-facing pages; no client-side hydration frameworks
- JavaScript limited to Alpine.js for essential interactivity only
- Core Web Vitals targets: LCP < 2.5s, FID < 100ms, CLS < 0.1
- Query limits enforced: warning at 20 queries/request, exception at 30 in development
- Response caching MUST be implemented for all public content pages

**Rationale**: Fast page loads improve user experience, SEO rankings, and conversion rates. SSR eliminates hydration delays and ensures content is immediately visible.

### III. Asset Sovereignty

All media MUST be processed, optimized, and offloaded to AWS S3/CloudFront immediately upon upload. The application server MUST NOT serve static assets.

**Non-Negotiable Rules**:
- All uploaded media MUST be stored on S3, served via CloudFront
- Images MUST be optimized (jpegoptim, optipng) and converted to WebP
- Responsive image sets with proper `srcset` MUST be generated automatically
- The application server MUST serve only dynamic content
- Direct file uploads go to temporary S3 folder, moved to permanent only after processing

**Rationale**: Keeping the application server lightweight improves scalability and reduces hosting costs. CloudFront provides global CDN delivery for optimal performance worldwide.

### IV. Testing Excellence

All tests MUST be written in Pest PHP with Laravel and Architecture plugins. Test coverage MUST meet defined thresholds for critical and important paths.

**Non-Negotiable Rules**:
- Overall coverage target: 70% minimum
- Critical paths (media pipeline, API controllers, SEO, Actions) MUST have 90%+ coverage
- Important paths (Services, Models, Content blocks) MUST have 70%+ coverage
- Architecture plugin MUST enforce: Controllers delegate to Services/Actions, Actions have single public method
- Unit tests MUST mock external dependencies (S3, Redis)
- Feature tests MUST test HTTP endpoints end-to-end with database

**Rationale**: Comprehensive testing ensures reliability and enables confident refactoring. Architecture tests enforce consistent patterns across the codebase.

### V. Clarity Over Cleverness

Code MUST be understandable by a developer unfamiliar with the project within five minutes of reading it. Explicit is better than implicit; boring reliability beats exciting experimentation.

**Non-Negotiable Rules**:
- All PHP files MUST declare `strict_types=1`
- PSR-12 coding standards enforced via Laravel Pint; non-compliant code MUST NOT be merged
- PHPStan Level 6 minimum; new code MUST pass static analysis
- Direct Eloquent for data access; no repository pattern abstraction
- Action classes for complex operations; Service classes for orchestration only
- Form Requests for all mutations; no inline validation in controllers

**Rationale**: Maintainability over cleverness. When multiple developers work on a project over years, readable code prevents bugs and reduces onboarding time.

## Code Quality Standards

### PHP Standards & Tooling

| Tool | Configuration | Enforcement |
|------|---------------|-------------|
| PHP | 8.3.x with `strict_types=1` | All files |
| Laravel Pint | `laravel` preset | Pre-commit hook + CI |
| PHPStan + Larastan | Level 6 (roadmap to 8) | CI blocking |

**Baseline Management**: A `phpstan-baseline.neon` MAY temporarily suppress existing issues during initial build. The baseline MUST shrink over time, never grow.

### Architecture Patterns

| Pattern | Purpose | Location |
|---------|---------|----------|
| Direct Eloquent | Data access | Models |
| Action Classes | Single-responsibility operations | `app/Actions/` |
| Service Classes | Orchestrate multiple Actions | `app/Services/` |
| Form Requests | Validate all mutations | `app/Http/Requests/` |
| DTOs | API boundary contracts | `app/DTOs/` |

### Naming Conventions

- **Models**: Singular PascalCase (`Page`, `Service`, `BlogPost`, `Faq`)
- **Controllers**: Resource + `Controller` (`PageController`, `ServiceController`)
- **Actions**: Verb + `Action` (`OptimizeImageAction`, `ClearCacheAction`)
- **Services**: Noun + `Service` (`MediaService`, `SeoService`)
- **Form Requests**: Resource + Operation + `Request` (`StorePageRequest`, `UpdateServiceRequest`)
- **DTOs**: Descriptive + `Data` (`CreateServiceData`, `WebhookPayloadData`)
- **Traits**: `Has`/`Is` prefix (`HasSeo`, `IsPublishable`)
- **Jobs**: Verb + `Job` (`ProcessMediaJob`, `WarmCacheJob`)

### Code Organization

```
app/
├── Actions/           # Single-responsibility operations
│   ├── Media/
│   └── Content/
├── Blocks/            # Content block definitions
├── DTOs/              # Data Transfer Objects for API boundaries
├── Http/
│   ├── Controllers/
│   ├── Requests/      # Form Requests (all mutations)
│   └── Middleware/
├── Jobs/              # Queue jobs
├── Models/            # Eloquent models
├── Services/          # Orchestration services
└── Traits/            # Reusable model traits (HasSeo, etc.)
```

## Performance Requirements

### Lighthouse & Core Web Vitals

| Metric | Target | Enforcement |
|--------|--------|-------------|
| Lighthouse Score (all categories) | 95+ | CI via Lighthouse CI |
| LCP (Largest Contentful Paint) | < 2.5s | Monitoring |
| FID (First Input Delay) | < 100ms | Monitoring |
| CLS (Cumulative Layout Shift) | < 0.1 | Monitoring |

### Database Query Discipline

**Laravel Strict Mode** MUST be enabled in development:
```php
Model::shouldBeStrict(! app()->isProduction());
```

**Query Limits**:
- Warning logged at 20 queries per request
- Exception thrown at 30 queries per request (development only)
- Production logs warnings but does not throw

**Mandatory Patterns**:
- All controller queries MUST use explicit `with()` for eager loading
- No lazy loading in Blade templates
- Document expected query count in controller method docblocks

### Caching Strategy

| Layer | Tool | Scope | Invalidation |
|-------|------|-------|--------------|
| Response Cache | spatie/laravel-responsecache | Public pages | Model observers |
| Settings Cache | Laravel Cache | Global settings | Settings model update |
| Fragment Cache | Tagged Cache | Navigation, widgets | Tagged flush |

**Exclusions**: Admin panel (`/admin/*`) and API routes (`/api/*`) MUST NOT be response cached.

## Security & Privacy

### Authentication & Authorization

| Context | Method | Details |
|---------|--------|---------|
| Admin Panel | Filament Auth | Session-based, email/password, role-based permissions |
| API | Laravel Sanctum | Token-based, scoped abilities, manual revocation |

### API Rate Limiting

**Global baseline**: 60 requests per minute per token

| Operation | Limit |
|-----------|-------|
| GET (read) | 120/min |
| POST (create) | 30/min |
| PUT/PATCH (update) | 30/min |
| DELETE | 10/min |

**Response Headers**: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`

### Dependency Security

| Tool | Purpose | Enforcement |
|------|---------|-------------|
| `composer audit` | Known vulnerabilities | CI blocking |
| `npm audit --audit-level=high` | Frontend vulnerabilities | CI blocking |
| `roave/security-advisories` | Block insecure installs | Composer require |
| GitHub Dependabot | Automatic security PRs | Repository setting |

### Data Protection Rules

- All form inputs MUST be validated via Form Requests
- SQL injection prevention via Eloquent; raw queries MUST use bindings
- XSS prevention via Blade escaping; `{!! !!}` requires explicit justification
- CSRF protection on all forms
- Sensitive data (passwords, tokens, PII) MUST NOT be logged
- User-uploaded files MUST NOT be served from application server

## Technical Constraints

### Version Requirements (Strict Parity)

| Component | Version | Notes |
|-----------|---------|-------|
| PHP | 8.3.x | With strict_types |
| Laravel | 12.x | Latest stable |
| PostgreSQL | 17.x | JSONB for content |
| Redis | 7.x | Queues and caching |
| Node | 20.x LTS | Vite builds |
| Filament | 3.x | Admin panel |

### Required PHP Extensions

- `pdo_pgsql` (PostgreSQL driver)
- `redis` (Redis driver)
- `gd` or `imagick` (Image processing)
- `exif` (Image metadata)

### Required System Binaries

- `jpegoptim` (JPEG optimization)
- `optipng` (PNG optimization)
- `cwebp` (WebP conversion)

### Environment Structure

```
.env.example      # Template with all variables (committed)
.env              # Local development (git-ignored)
.env.staging      # Reference for staging values (committed, no secrets)
.env.production   # Reference for production structure (committed, no secrets)
```

Secrets MUST be stored in Coolify environment configuration, never in repository.

### Browser Support

- Chrome/Edge: Last 2 versions
- Firefox: Last 2 versions
- Safari: Last 2 versions
- Mobile: iOS Safari 15+, Chrome Android
- No IE11 support

## Logging & Observability

### Logging Strategy

**Native Laravel logging** with structured JSON format. No external error tracking service.

**Always Logged (all environments)**:
- Authentication attempts (success/failure) with IP and user agent
- API requests (endpoint, token identifier, response code, duration)
- Media processing failures with file details and error context
- Queue job failures with payload and exception
- Scheduled task completion and failures

**Info Level (all environments)**:
- Content publishing events (who, what, when)
- Settings changes (old value, new value, changed by)
- Cache invalidation events
- Redirect matches (for SEO debugging)

**Debug Level (local/staging only)**:
- Media processing steps (resize, optimize, upload progress)
- Query counts per request
- Cache hits and misses

**Never Logged**:
- Passwords or credentials
- Full request bodies containing PII
- Credit card or financial data
- Session tokens or API secrets

### Log Format

Structured JSON with consistent fields:
```json
{
  "timestamp": "2025-01-15T10:30:00Z",
  "level": "info",
  "channel": "media",
  "message": "Image optimization complete",
  "context": {
    "user_id": 1,
    "request_id": "abc-123",
    "file": "hero.jpg",
    "duration_ms": 450
  }
}
```

Daily rotating log files. Retention: 14 days local, 30 days production.

## Documentation Standards

### Code Documentation

**PHPDoc Required On**:
- All public methods in Services, Actions, and Controllers
- Complex private methods with non-obvious logic
- All DTO properties

**PHPDoc MUST Include**:
- `@param` with type and description
- `@return` with type
- `@throws` for all thrown exceptions

**Skip PHPDoc On**:
- Simple getters/setters
- Eloquent relationships (self-documenting)
- Methods where types are fully declared in signatures

### Project Documentation Structure

```
README.md                          # Quick start, requirements, setup
docs/
├── project/
│   ├── README.md                  # Documentation index
│   ├── architecture.md            # System design, data flow
│   ├── media-pipeline.md          # Media processing documentation
│   ├── api.md                     # API reference (or Scribe output)
│   ├── deployment.md              # Production deployment guide
│   └── decisions/                 # Architecture Decision Records
│       └── 000-template.md
```

### API Documentation

Generated via **Scribe** from route definitions and Form Requests. Outputs OpenAPI spec and interactive HTML documentation. Hosted at `/docs/api` in staging environment.

### Documentation Discipline

Every PR that adds or changes functionality MUST include corresponding documentation updates. Architecture decisions affecting future development MUST be recorded as ADRs in `docs/project/decisions/`.

## Deployment & Infrastructure

### Local Development

**Laravel Sail** (Docker Compose) with:
- PHP 8.3 + required extensions
- PostgreSQL 17
- Redis 7
- Mailpit (email testing)
- Image optimization binaries installed in container

Custom Sail Dockerfile MUST be maintained to ensure parity with production.

### Production Environment

**Coolify** on Hostinger VPS:
- Git push-to-deploy from `main` branch
- PostgreSQL and Redis as managed containers
- Queue workers running as separate containers
- Automatic SSL via Let's Encrypt
- Zero-downtime deployments

### CI/CD Pipeline

**On Every PR**:
1. `composer audit` — Security check
2. `npm audit` — Frontend security check
3. Laravel Pint — Code style
4. PHPStan Level 6 — Static analysis
5. Pest tests — Unit and feature tests
6. `composer check-platform-reqs` — Version verification

**On Merge to Main**:
1. All PR checks pass
2. Coolify webhook triggered
3. Build and deploy
4. Cache warming job runs
5. Smoke test verifies deployment

## Decision-Making Framework

When facing architectural or implementation decisions, apply these principles in order:

1. **Does it maintain 95+ Lighthouse scores?** Performance is non-negotiable.

2. **Does it keep the schema in code?** Database MUST NOT be source of truth for structure.

3. **Can a new developer understand it in 5 minutes?** Clarity over cleverness.

4. **Does it work with Coolify's deployment model?** No exotic infrastructure requirements.

5. **Does it have a clear invalidation/update path?** Especially for cached data.

6. **Is there a well-maintained package for this?** Prefer Spatie and Laravel ecosystem packages over custom implementations.

7. **Does it fail loudly in development?** Silent failures are debugging nightmares.

8. **Can it be tested?** If it cannot be tested, it MUST be refactored until it can.

When in doubt, choose the boring solution. This is a Blueprint meant to be reused—stability and predictability matter more than novelty.

## Governance

This constitution supersedes all other development practices for The Blueprint CMS. All implementation decisions MUST be validated against these principles.

**Amendment Process**:
1. Propose change via PR with rationale
2. Review against existing principles for conflicts
3. Update version number following semantic versioning:
   - MAJOR: Backward-incompatible governance/principle changes
   - MINOR: New principle/section added or materially expanded
   - PATCH: Clarifications, wording, non-semantic refinements
4. Document migration plan if existing code is affected
5. Update all dependent templates and artifacts

**Compliance Review**:
- All PRs MUST pass constitution checks before merge
- Architecture tests enforce structural rules automatically
- Code review MUST verify adherence to naming conventions and patterns
- Quarterly review of constitution relevance and completeness

**Runtime Guidance**: Use the spec-kit workflow via Claude Code for feature development. See `.specify/` directory for templates and commands.

**Version**: 1.0.0 | **Ratified**: 2025-12-05 | **Last Amended**: 2025-12-05
