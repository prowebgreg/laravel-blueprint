# Implementation Plan: System Foundation (Phase 1)

**Branch**: `002-system-foundation` | **Date**: 2025-12-05 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/002-system-foundation/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Establish foundational infrastructure for The Blueprint CMS: Laravel Sail with PostgreSQL 17, Redis 7, and Mailpit for local development; AWS S3/CloudFront integration for cloud storage; Filament V3 admin panel with authentication; and Laravel Horizon for queue monitoring. This phase enables all subsequent feature development by providing database, caching, file storage, authentication, and background job processing capabilities.

## Technical Context

**Language/Version**: PHP 8.3.x with `strict_types=1`
**Primary Dependencies**: Laravel 12.x, Filament 3.x, Laravel Horizon, Laravel Sail
**Storage**: PostgreSQL 17 (database), Redis 7 (cache/queue), AWS S3 (files)
**Testing**: Pest PHP with Laravel and Architecture plugins (70% coverage target)
**Target Platform**: Docker (Laravel Sail) for development, Linux server for production
**Project Type**: Web application (Laravel monolith with Filament admin)
**Performance Goals**: 95+ Lighthouse scores, LCP < 2.5s, CLS < 0.1 (for future public pages)
**Constraints**: 10MB max file upload, 2-hour admin session timeout, 3 job retry attempts, 128MB worker memory
**Allowed File Types**: jpg, jpeg, png, gif, webp, svg, pdf, mp4, webm
**Scale/Scope**: Single admin user (seeded), queue monitoring, environment-based storage switching

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Pre-Design Gate (Phase 0)

| Principle | Requirement | Status | Notes |
|-----------|-------------|--------|-------|
| I. Code-Defined Structure | Schema in PHP, not DB | ✅ PASS | User model exists; no dynamic schema |
| II. Performance First | SSR via Blade, 95+ Lighthouse | ✅ N/A | No public pages in this phase |
| III. Asset Sovereignty | S3/CloudFront for media | ✅ PASS | S3 integration with CloudFront CDN planned |
| IV. Testing Excellence | Pest PHP, 70% coverage | ✅ PLANNED | Pest not yet installed; will add |
| V. Clarity Over Cleverness | strict_types, PSR-12, PHPStan L6 | ✅ PLANNED | Pint installed; need PHPStan |

### Technical Constraints Validation

| Constraint | Required | Planned | Status |
|------------|----------|---------|--------|
| PHP Version | 8.3.x | 8.3.28 | ✅ PASS |
| Laravel Version | 12.x | 12.41.1 | ✅ PASS |
| PostgreSQL | 17.x | 17.x (Sail) | ✅ PLANNED |
| Redis | 7.x | 7.x (Sail) | ✅ PLANNED |
| Filament | 3.x | 3.x | ✅ PLANNED |
| Node | 20.x LTS | TBD | ⚠️ TO VERIFY |

### Architecture Patterns Compliance

| Pattern | Constitution Requirement | Implementation |
|---------|------------------------|----------------|
| Actions | Single-responsibility operations | Not needed this phase |
| Services | Orchestrate multiple Actions | Not needed this phase |
| Form Requests | Validate all mutations | Filament handles forms |
| Direct Eloquent | No repository pattern | ✅ Using Eloquent directly |

### Security Compliance

| Requirement | Implementation | Status |
|-------------|----------------|--------|
| Filament Auth | Session-based admin auth | ✅ PLANNED |
| Password Reset | Email-based reset | ✅ PLANNED |
| Session Timeout | 2-hour inactivity | ✅ PLANNED |
| Horizon Access | Admin-only gate | ✅ PLANNED |

**Pre-Design Gate Result**: ✅ **PASS** - All constitution requirements satisfied or planned

## Project Structure

### Documentation (this feature)

```text
specs/002-system-foundation/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
# Laravel monolith structure (per Constitution Code Organization)
app/
├── Actions/              # Single-responsibility operations (future phases)
├── Http/
│   ├── Controllers/      # Minimal - Filament handles admin
│   ├── Requests/         # Form Requests (future phases)
│   └── Middleware/
├── Models/
│   └── User.php          # Admin user model (existing)
├── Providers/
│   ├── AppServiceProvider.php
│   ├── HorizonServiceProvider.php    # Queue monitoring auth gate
│   └── Filament/
│       └── AdminPanelProvider.php    # Filament panel config
└── Jobs/                 # Queue jobs (future phases)

config/
├── filesystems.php       # S3 disk configuration
├── horizon.php           # Horizon configuration
└── filament.php          # Filament configuration

database/
├── migrations/           # PostgreSQL migrations
├── seeders/
│   └── AdminUserSeeder.php  # Dev-only admin seeder
└── factories/

docker/
└── 8.3/
    └── Dockerfile        # Custom Sail Dockerfile with image binaries

tests/
├── Feature/              # HTTP/integration tests
├── Unit/                 # Unit tests
└── Pest.php              # Pest configuration

docker-compose.yml        # Sail services configuration
```

**Structure Decision**: Laravel monolith with Filament admin panel. Following Constitution's Code Organization pattern with Actions, Services, and Form Requests directories ready for future phases. Custom Sail Dockerfile for image processing binaries.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

**No complexity violations.** This phase implements standard Laravel patterns:
- Laravel Sail for Docker orchestration (standard)
- Filament for admin panel (recommended by Constitution)
- Horizon for queue monitoring (standard Laravel package)
- S3/CloudFront for file storage (Constitution requirement)

---

## Post-Design Constitution Check (Phase 1)

*Re-evaluation after design artifacts complete*

### Core Principles Validation

| Principle | Requirement | Design Decision | Status |
|-----------|-------------|-----------------|--------|
| I. Code-Defined Structure | Schema in PHP, not DB | User model in PHP; migrations define schema | ✅ PASS |
| II. Performance First | SSR via Blade, 95+ Lighthouse | N/A this phase (no public pages) | ✅ N/A |
| III. Asset Sovereignty | S3/CloudFront for media | Scoped disks (s3-temp, s3-permanent) with CloudFront URL | ✅ PASS |
| IV. Testing Excellence | Pest PHP, 70% coverage | Pest planned; architecture tests for future phases | ✅ PLANNED |
| V. Clarity Over Cleverness | strict_types, PSR-12, PHPStan L6 | All PHP files will declare strict_types; Pint installed | ✅ PASS |

### Technical Constraints Final Check

| Constraint | Required | Designed | Status |
|------------|----------|----------|--------|
| PHP Version | 8.3.x | 8.3.x (Sail) | ✅ PASS |
| Laravel Version | 12.x | 12.41.1 | ✅ PASS |
| PostgreSQL | 17.x | `postgres:17` (docker-compose) | ✅ PASS |
| Redis | 7.x | `redis:7-alpine` (docker-compose) | ✅ PASS |
| Filament | 3.x | `filament/filament:^3.0` | ✅ PASS |
| Node | 20.x LTS | To verify during implementation | ⚠️ VERIFY |

### Architecture Patterns Final Check

| Pattern | Requirement | Design | Status |
|---------|-------------|--------|--------|
| Actions | `app/Actions/` | Directory ready; no Actions this phase | ✅ PASS |
| Services | `app/Services/` | Not needed this phase | ✅ PASS |
| Form Requests | Validate all mutations | Filament handles admin forms | ✅ PASS |
| Direct Eloquent | No repository pattern | Using Eloquent directly | ✅ PASS |
| DTOs | API boundary contracts | Not needed this phase | ✅ N/A |

### Security Requirements Final Check

| Requirement | Design Decision | Status |
|-------------|-----------------|--------|
| Filament Auth | Built-in login with `->login()` | ✅ PASS |
| Password Reset | Built-in with `->passwordReset()` | ✅ PASS |
| Session Timeout | SESSION_LIFETIME=120 (2 hours) | ✅ PASS |
| Horizon Gate | `canAccessPanel()` integration | ✅ PASS |
| CSRF Protection | Laravel default | ✅ PASS |
| Password Hashing | bcrypt (Laravel default) | ✅ PASS |

### Storage Configuration Final Check

| Requirement | Design Decision | Status |
|-------------|-----------------|--------|
| S3 Bucket | `laravel-blueprint-assets` | ✅ PASS |
| Region | `us-west-1` | ✅ PASS |
| Temp Folder | `s3-temp` scoped disk → `temp/` | ✅ PASS |
| Permanent Folder | `s3-permanent` scoped disk → `permanent/` | ✅ PASS |
| CloudFront URL | `AWS_URL` env variable | ✅ PASS |
| Local Fallback | `local-temp`, `local-permanent` disks (min 1GB space) | ✅ PASS |
| Max File Size | 10MB validation + PHP config | ✅ PASS |
| Allowed File Types | jpg, jpeg, png, gif, webp, svg, pdf, mp4, webm | ✅ PASS |
| IAM Permissions | s3:PutObject, s3:GetObject, s3:DeleteObject, s3:ListBucket | ✅ PASS |
| Error Logging | S3 errors logged to storage/logs/laravel.log | ✅ PASS |
| Concurrent Uploads | Supported with unique temp paths | ✅ PASS |

### Queue Configuration Final Check

| Requirement | Design Decision | Status |
|-------------|-----------------|--------|
| Redis Driver | `QUEUE_CONNECTION=redis` | ✅ PASS |
| Horizon Dashboard | `/horizon` path | ✅ PASS |
| Admin-Only Access | Gate with `canAccessPanel()` | ✅ PASS |
| 3 Retry Attempts | `tries => 3` in horizon.php | ✅ PASS |
| Memory Limit | 128MB per worker | ✅ PASS |
| Worker Count | 3 local, 10 production | ✅ PASS |
| Failed Job Retention | Remain in failed list for manual review | ✅ PASS |
| Throughput Metrics | Jobs/min, avg runtime, hourly/daily counts | ✅ PASS |

**Post-Design Gate Result**: ✅ **PASS** - All constitution requirements validated in design

---

## Generated Artifacts

| Artifact | Path | Status |
|----------|------|--------|
| Implementation Plan | `specs/002-system-foundation/plan.md` | ✅ Complete |
| Research Document | `specs/002-system-foundation/research.md` | ✅ Complete |
| Data Model | `specs/002-system-foundation/data-model.md` | ✅ Complete |
| Admin Auth Contract | `specs/002-system-foundation/contracts/admin-auth.md` | ✅ Complete |
| Horizon Contract | `specs/002-system-foundation/contracts/horizon-dashboard.md` | ✅ Complete |
| Storage Contract | `specs/002-system-foundation/contracts/storage-operations.md` | ✅ Complete |
| Quickstart Guide | `specs/002-system-foundation/quickstart.md` | ✅ Complete |
| Agent Context | `CLAUDE.md` | ✅ Updated |

---

## Next Steps

1. Run `/speckit.tasks` to generate implementation tasks
2. Execute tasks following the quickstart guide
3. Verify all success criteria from spec.md are met
