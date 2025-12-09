# Implementation Plan: Core Content Models & Architecture

**Branch**: `001-core-content-models` | **Date**: 2025-12-08 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-core-content-models/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Phase 2 of Blueprint CMS establishes the core content architecture: data models (Page, Service, BlogPost, Faq, Testimonial), a generic polymorphic relationship engine, content block storage via JSONB, and SEO trait with field mirroring. All models support soft delete with configurable recovery period and automatic slug generation with reserved slug blocking.

## Technical Context

**Language/Version**: PHP 8.3.x with `strict_types=1` in all files
**Primary Dependencies**: Laravel 12.x, Filament V3, PostgreSQL 17
**Storage**: PostgreSQL with JSONB for content blocks, Redis for caching
**Testing**: Pest PHP with Laravel and Architecture plugins
**Target Platform**: Laravel Sail Docker environment, production on Coolify
**Project Type**: Web application (Laravel monolith)
**Performance Goals**: 95+ Lighthouse scores, <20 queries/request warning, <30 queries exception
**Constraints**: SSR via Blade only, no client-side hydration frameworks, direct Eloquent (no repository pattern)
**Scale/Scope**: Foundation for unlimited content types, supporting 5 models in Phase 2

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Requirement | Status | Notes |
|-----------|-------------|--------|-------|
| I. Code-Defined Structure | Schema in PHP classes, not database | PASS | Block types in `app/Blocks/`, traits define fields |
| II. Performance First | 95+ Lighthouse, SSR via Blade | N/A | Phase 2 is data layer only, no views |
| III. Asset Sovereignty | Media to S3/CloudFront | N/A | Phase 3 handles media integration |
| IV. Testing Excellence | 70% overall, 90% critical paths | REQUIRED | Content block validation, relationships = critical |
| V. Clarity Over Cleverness | strict_types, PSR-12, PHPStan L6 | REQUIRED | All new PHP files must comply |
| VI. Example-Driven Development | Factories and seeders | REQUIRED | Every model needs factory + seeder |

### Gate Violations: None

All requirements align with constitution. No justifications needed.

## Project Structure

### Documentation (this feature)

```text
specs/001-core-content-models/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
│   └── (N/A - no API endpoints in Phase 2)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Blocks/                    # Content block type definitions
│   ├── Contracts/
│   │   └── BlockInterface.php # Interface all blocks implement
│   ├── HeroBlock.php          # Hero section block
│   └── CtaBlock.php           # Call-to-action block
├── Enums/
│   ├── ContentStatus.php      # draft|published enum
│   └── OgType.php             # website|article enum
├── Models/
│   ├── Page.php               # Static pages with template
│   ├── Service.php            # Custom page type (services)
│   ├── BlogPost.php           # Custom page type (blog)
│   ├── Faq.php                # Content resource (FAQ)
│   └── Testimonial.php        # Content resource (testimonial)
├── Traits/
│   ├── HasSeo.php             # SEO fields with mirroring
│   ├── HasContentBlocks.php   # JSONB content block storage
│   ├── HasRelatedContent.php  # Polymorphic relationships
│   └── HasSlug.php            # Slug auto-generation
└── Console/
    └── Commands/
        └── PurgeDeletedContentCommand.php  # Scheduled purge job

database/
├── migrations/
│   ├── 2025_12_08_000001_create_pages_table.php
│   ├── 2025_12_08_000002_create_services_table.php
│   ├── 2025_12_08_000003_create_blog_posts_table.php
│   ├── 2025_12_08_000004_create_faqs_table.php
│   ├── 2025_12_08_000005_create_testimonials_table.php
│   └── 2025_12_08_000006_create_content_relations_table.php
├── factories/
│   ├── PageFactory.php
│   ├── ServiceFactory.php
│   ├── BlogPostFactory.php
│   ├── FaqFactory.php
│   └── TestimonialFactory.php
└── seeders/
    ├── PageSeeder.php
    ├── ServiceSeeder.php
    ├── BlogPostSeeder.php
    ├── FaqSeeder.php
    ├── TestimonialSeeder.php
    └── ContentRelationSeeder.php

config/
└── content.php                # Reserved slugs, recovery period config

tests/
├── Feature/
│   ├── Models/
│   │   ├── PageTest.php
│   │   ├── ServiceTest.php
│   │   ├── BlogPostTest.php
│   │   ├── FaqTest.php
│   │   └── TestimonialTest.php
│   ├── Traits/
│   │   ├── HasSeoTest.php
│   │   ├── HasContentBlocksTest.php
│   │   ├── HasRelatedContentTest.php
│   │   └── HasSlugTest.php
│   └── Commands/
│       └── PurgeDeletedContentCommandTest.php
└── Unit/
    └── Blocks/
        ├── HeroBlockTest.php
        └── CtaBlockTest.php
```

**Structure Decision**: Laravel monolith with standard directory layout. Blocks in `app/Blocks/` per PRD requirement for code-defined schema. Traits in `app/Traits/` per constitution naming conventions (`Has*` prefix).

## Complexity Tracking

> **No violations requiring justification**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| N/A | N/A | N/A |

---

## Constitution Check (Post-Design)

*Re-evaluated after Phase 1 design completion.*

| Principle | Requirement | Status | Verification |
|-----------|-------------|--------|--------------|
| I. Code-Defined Structure | Schema in PHP classes | PASS | Block types in `app/Blocks/`, field definitions in traits, no database-driven config |
| II. Performance First | Query discipline | PASS | HasRelatedContent trait uses eager loading, no N+1 patterns in design |
| III. Asset Sovereignty | Media handling | N/A | No media operations in Phase 2 scope |
| IV. Testing Excellence | 70%+ coverage | PLANNED | Test files defined for all models, traits, blocks, and commands |
| V. Clarity Over Cleverness | Code standards | PASS | All classes use strict_types, follow naming conventions, direct Eloquent |
| VI. Example-Driven Development | Seeders exist | PLANNED | 6 seeders defined for all entities plus relationships |

### Design Decisions Aligned with Constitution

1. **Relationship Engine**: Uses Laravel's built-in polymorphic relationships (morphToMany) - no custom pivot tables violating constitution
2. **Content Blocks**: Schema defined in PHP classes (BlockInterface), validated at write-time - code-defined structure
3. **SEO Mirroring**: Simple accessor pattern - explicit over implicit, no magic behavior
4. **Slug Generation**: Model boot method with config-based reserved list - transparent, testable
5. **Soft Delete**: Laravel's SoftDeletes trait + scheduled command - standard patterns, no custom implementations

### Ready for Task Generation

All constitution gates pass. Design artifacts complete:
- [x] research.md - Technology decisions documented
- [x] data-model.md - Entity schemas and relationships defined
- [x] contracts/README.md - Internal contracts documented (no API in Phase 2)
- [x] quickstart.md - Usage examples and verification steps

**Next Step**: Run `/speckit.tasks` to generate implementation tasks.
