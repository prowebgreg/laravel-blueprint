# Tasks: Core Content Models & Architecture

**Input**: Design documents from `/specs/001-core-content-models/`
**Prerequisites**: plan.md, spec.md, data-model.md, research.md, quickstart.md, contracts/README.md

**Tests**: Tests are included as this is a critical foundation phase. Constitution requires 70% overall, 90%+ on content block validation and relationships.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description → @subagent`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Configuration and foundational files for content system

- [X] T001 Create content configuration file at config/content.php with reserved_slugs, recovery_days, and max_slug_suffix_attempts → @laravel-specialist
- [X] T002 [P] Create ContentStatus enum at app/Enums/ContentStatus.php (draft|published) → @laravel-specialist
- [X] T003 [P] Create OgType enum at app/Enums/OgType.php (website|article) → @laravel-specialist

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Traits and base components that ALL user stories depend on - MUST complete before any user story

**CRITICAL**: No user story work can begin until this phase is complete

### Block System (Code-Defined Schema)

- [X] T004 Create BlockInterface contract at app/Blocks/Contracts/BlockInterface.php → @laravel-specialist, @php-pro
- [X] T005 [P] Create HeroBlock class at app/Blocks/HeroBlock.php implementing BlockInterface → @laravel-specialist
- [X] T006 [P] Create CtaBlock class at app/Blocks/CtaBlock.php implementing BlockInterface → @laravel-specialist
- [X] T007 Create ValidContentBlocks validation rule at app/Rules/ValidContentBlocks.php → @laravel-specialist, @php-pro

### Core Traits

- [X] T008 Create HasSlug trait at app/Traits/HasSlug.php with auto-generation and reserved slug blocking → @laravel-specialist, @php-pro
- [X] T009 [P] Create HasSeo trait at app/Traits/HasSeo.php with field mirroring (OG/Twitter from meta) → @laravel-specialist
- [X] T010 [P] Create HasContentBlocks trait at app/Traits/HasContentBlocks.php with JSONB handling → @laravel-specialist, @postgres-pro
- [X] T011 Create HasRelatedContent trait at app/Traits/HasRelatedContent.php with polymorphic relationships → @laravel-specialist, @postgres-pro

### Trait Tests

- [X] T012 [P] Create HasSlugTest at tests/Feature/Traits/HasSlugTest.php → @test-automator, @laravel-specialist
- [X] T013 [P] Create HasSeoTest at tests/Feature/Traits/HasSeoTest.php → @test-automator, @laravel-specialist
- [X] T014 [P] Create HasContentBlocksTest at tests/Feature/Traits/HasContentBlocksTest.php (90%+ coverage required) → @test-automator, @laravel-specialist
- [X] T015 [P] Create HasRelatedContentTest at tests/Feature/Traits/HasRelatedContentTest.php (90%+ coverage required) → @test-automator, @laravel-specialist

**90%+ Coverage Test Scenarios for T014 (HasContentBlocks)**:
- Valid block array with known types (hero, cta)
- Invalid block type (undefined class) → validation error
- Missing `type` field → validation error
- Missing `data` field → validation passes (data optional)
- Wrong data type in block field (number instead of string) → validation error
- Empty blocks array [] → valid
- NULL content_blocks → valid
- Add block to existing array
- Remove block by index
- Reorder blocks
- Large number of blocks (stress test)

**90%+ Coverage Test Scenarios for T015 (HasRelatedContent)**:
- Attach single related model with order
- Attach multiple related models with order
- Detach related model
- Query related models returns correct order
- Bidirectional query (relatedFromX methods)
- Duplicate relationship attempt → unique constraint
- Sync relationships (add, remove, update order)
- Eager loading relationships
- Query with soft-deleted target → excluded
- Query with withTrashed() → includes soft-deleted
- Relationship to same model type (self-reference)
- Cross-type relationships (Service to Faq, Service to BlogPost)

### Block Tests

- [X] T016 [P] Create HeroBlockTest at tests/Unit/Blocks/HeroBlockTest.php → @test-automator
- [X] T017 [P] Create CtaBlockTest at tests/Unit/Blocks/CtaBlockTest.php → @test-automator

**Checkpoint**: Foundation ready - traits tested and validated, user story implementation can now begin

---

## Phase 3: User Story 1 - Create and Publish a Static Page (Priority: P1) MVP

**Goal**: Editors can create static pages (Home, About, Contact) with content blocks and SEO data, save as draft, and publish

**Independent Test**: Create a static page with content blocks, set SEO data, toggle between draft/published status

### Migration

- [X] T018 [US1] Create pages table migration at database/migrations/2025_12_08_000001_create_pages_table.php → @laravel-specialist, @postgres-pro

### Model

- [X] T019 [US1] Create Page model at app/Models/Page.php with all traits (HasSeo, HasSlug, HasContentBlocks, HasRelatedContent, SoftDeletes) → @laravel-specialist

### Factory & Seeder

- [X] T020 [P] [US1] Create PageFactory at database/factories/PageFactory.php → @laravel-specialist
- [X] T021 [US1] Create PageSeeder at database/seeders/PageSeeder.php with example data → @laravel-specialist

### Tests

- [X] T022 [US1] Create PageTest at tests/Feature/Models/PageTest.php covering CRUD, status transitions, content blocks, and SEO → @test-automator, @laravel-specialist

**Checkpoint**: User Story 1 complete - static pages fully functional with content blocks and SEO

---

## Phase 4: User Story 2 - Create Custom Page Type Records (Priority: P1)

**Goal**: Editors can create multiple Service and BlogPost records that share template structure but have unique content

**Independent Test**: Create multiple Service records, verify consistent structure, unique URLs at /services/{slug}

### Migrations

- [X] T023 [P] [US2] Create services table migration at database/migrations/2025_12_08_000002_create_services_table.php → @laravel-specialist, @postgres-pro
- [X] T024 [P] [US2] Create blog_posts table migration at database/migrations/2025_12_08_000003_create_blog_posts_table.php → @laravel-specialist, @postgres-pro

### Models

- [X] T025 [P] [US2] Create Service model at app/Models/Service.php with all traits → @laravel-specialist
- [X] T026 [P] [US2] Create BlogPost model at app/Models/BlogPost.php with all traits → @laravel-specialist

### Factories & Seeders

- [X] T027 [P] [US2] Create ServiceFactory at database/factories/ServiceFactory.php → @laravel-specialist
- [X] T028 [P] [US2] Create BlogPostFactory at database/factories/BlogPostFactory.php → @laravel-specialist
- [X] T029 [P] [US2] Create ServiceSeeder at database/seeders/ServiceSeeder.php with example data → @laravel-specialist
- [X] T030 [P] [US2] Create BlogPostSeeder at database/seeders/BlogPostSeeder.php with example data → @laravel-specialist

### Tests

- [X] T031 [P] [US2] Create ServiceTest at tests/Feature/Models/ServiceTest.php covering CRUD, content blocks, SEO → @test-automator, @laravel-specialist
- [X] T032 [P] [US2] Create BlogPostTest at tests/Feature/Models/BlogPostTest.php covering CRUD, content blocks, SEO → @test-automator, @laravel-specialist

**Checkpoint**: User Story 2 complete - custom page types (Service, BlogPost) fully functional

---

## Phase 5: User Story 3 - Create Content Resources (Priority: P2)

**Goal**: Editors can create FAQ and Testimonial records as supporting content without public URLs

**Independent Test**: Create FAQ and Testimonial records, verify no public URL exists, only published resources available for linking

### Migrations

- [X] T033 [P] [US3] Create faqs table migration at database/migrations/2025_12_08_000004_create_faqs_table.php → @laravel-specialist, @postgres-pro
- [X] T034 [P] [US3] Create testimonials table migration at database/migrations/2025_12_08_000005_create_testimonials_table.php → @laravel-specialist, @postgres-pro

### Models

- [X] T035 [P] [US3] Create Faq model at app/Models/Faq.php with HasRelatedContent and SoftDeletes → @laravel-specialist
- [X] T036 [P] [US3] Create Testimonial model at app/Models/Testimonial.php with HasRelatedContent and SoftDeletes → @laravel-specialist

### Factories & Seeders

- [X] T037 [P] [US3] Create FaqFactory at database/factories/FaqFactory.php → @laravel-specialist
- [X] T038 [P] [US3] Create TestimonialFactory at database/factories/TestimonialFactory.php → @laravel-specialist
- [X] T039 [P] [US3] Create FaqSeeder at database/seeders/FaqSeeder.php with example data → @laravel-specialist
- [X] T040 [P] [US3] Create TestimonialSeeder at database/seeders/TestimonialSeeder.php with example data → @laravel-specialist

### Tests

- [X] T041 [P] [US3] Create FaqTest at tests/Feature/Models/FaqTest.php covering CRUD and status filtering → @test-automator, @laravel-specialist
- [X] T042 [P] [US3] Create TestimonialTest at tests/Feature/Models/TestimonialTest.php covering CRUD, rating validation → @test-automator, @laravel-specialist

**Checkpoint**: User Story 3 complete - content resources (FAQ, Testimonial) fully functional

---

## Phase 6: User Story 4 - Link Related Content (Priority: P2)

**Goal**: Editors can connect content together via the polymorphic relationship engine with custom ordering

**Independent Test**: Link FAQs to a Service with specific order, verify order is preserved, test bidirectional queries

### Migration

- [ ] T043 [US4] Create content_relations pivot table migration at database/migrations/2025_12_08_000006_create_content_relations_table.php → @laravel-specialist, @postgres-pro

### Seeder

- [ ] T044 [US4] Create ContentRelationSeeder at database/seeders/ContentRelationSeeder.php linking Services to FAQs and Testimonials → @laravel-specialist

### Tests

- [ ] T045 [US4] Create ContentRelationTest at tests/Feature/Models/ContentRelationTest.php covering creation, ordering, bidirectional queries, and cascade behavior → @test-automator, @laravel-specialist

**Checkpoint**: User Story 4 complete - relationship engine fully functional with ordering

---

## Phase 7: User Story 5 - Manage SEO Data (Priority: P2)

**Goal**: SEO field mirroring works correctly - OG/Twitter fields default from meta until manually edited

**Independent Test**: Set meta fields, verify OG/Twitter mirror correctly, manually edit and verify independence

**Note**: SEO functionality is implemented in HasSeo trait (Phase 2). This phase validates the integration.

### Tests

- [ ] T046 [US5] Add SEO mirroring integration tests to PageTest at tests/Feature/Models/PageTest.php → @test-automator, @laravel-specialist
- [ ] T047 [US5] Add SEO mirroring integration tests to ServiceTest at tests/Feature/Models/ServiceTest.php → @test-automator, @laravel-specialist

**Checkpoint**: User Story 5 complete - SEO mirroring validated on all page-like models

---

## Phase 8: User Story 6 - Delete and Recover Content (Priority: P3)

**Goal**: Soft delete with configurable recovery period, relationships preserved until permanent purge

**Independent Test**: Delete a page, verify hidden from queries, restore within recovery period, verify purge after expiration

### Command

- [ ] T048 [US6] Create PurgeDeletedContentCommand at app/Console/Commands/PurgeDeletedContentCommand.php → @laravel-specialist, @php-pro

### Schedule Registration

- [ ] T049 [US6] Register purge command in scheduler (routes/console.php or bootstrap/app.php) → @laravel-specialist

### Tests

- [ ] T050 [US6] Create PurgeDeletedContentCommandTest at tests/Feature/Commands/PurgeDeletedContentCommandTest.php covering soft delete, restore, and purge with relationship cascade → @test-automator, @laravel-specialist

**Checkpoint**: User Story 6 complete - soft delete with recovery period fully functional

---

## Phase 9: User Story 7 - URL Slug Management (Priority: P3)

**Goal**: Automatic slug generation, duplicate handling with suffixes, reserved slug blocking

**Independent Test**: Create pages with duplicate names, verify suffix addition, test reserved slug rejection

**Note**: Slug functionality is implemented in HasSlug trait (Phase 2). This phase validates edge cases.

### Tests

- [ ] T051 [US7] Add slug edge case tests to HasSlugTest at tests/Feature/Traits/HasSlugTest.php (duplicate suffixes, reserved slugs, cross-model same slug) → @test-automator, @laravel-specialist

**Checkpoint**: User Story 7 complete - slug management validated with all edge cases

---

## Phase 10: Polish & Cross-Cutting Concerns

**Purpose**: Final validation and cleanup

- [ ] T052 [P] Run full test suite and ensure 70%+ overall coverage → @test-automator
- [ ] T053 [P] Run Laravel Pint formatter on all new files → @code-reviewer
- [ ] T054 [P] Verify PHPStan Level 6 compliance on all new files → @code-reviewer
- [ ] T055 Run quickstart.md validation steps (migrations, seeders, tinker commands) → @qa-expert, @laravel-specialist
- [ ] T056 Verify all seeders can run without errors → @qa-expert

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup - BLOCKS all user stories
- **User Stories (Phases 3-9)**: All depend on Foundational completion
  - US1 (P1): Can proceed first
  - US2 (P1): Can proceed after or parallel to US1
  - US3 (P2): Can proceed after US1/US2 or parallel
  - US4 (P2): Requires US1, US2, US3 models to exist (for relationship testing)
  - US5 (P2): Requires US1, US2 for integration testing
  - US6 (P3): Requires all models to exist
  - US7 (P3): Can proceed after traits are complete
- **Polish (Phase 10)**: Depends on all user stories complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational - No dependencies on other stories
- **User Story 2 (P1)**: Can start after Foundational - No dependencies on other stories
- **User Story 3 (P2)**: Can start after Foundational - No dependencies on other stories
- **User Story 4 (P2)**: Requires US1, US2, US3 models (needs entities to relate)
- **User Story 5 (P2)**: Requires US1, US2 models for integration tests
- **User Story 6 (P3)**: Requires all models for purge command
- **User Story 7 (P3)**: Tests trait functionality, can run after Foundational

### Within Each User Story

- Migrations before models
- Models before factories
- Factories before seeders
- All above before tests
- Story complete before moving to next priority

### Parallel Opportunities

- T002, T003 (enums) can run in parallel
- T005, T006 (blocks) can run in parallel
- T009, T010 (HasSeo, HasContentBlocks) can run in parallel after T008
- T012-T017 (trait and block tests) can run in parallel
- T023, T024 (US2 migrations) can run in parallel
- T025, T026 (US2 models) can run in parallel
- T027-T032 (US2 factories, seeders, tests) can run in parallel
- T033, T034 (US3 migrations) can run in parallel
- T035-T042 (US3 models, factories, seeders, tests) can run in parallel
- T052-T054 (polish tasks) can run in parallel

---

## Parallel Example: Phase 2 Foundational

```bash
# After T004 (BlockInterface) completes, launch blocks in parallel:
Task: "Create HeroBlock class at app/Blocks/HeroBlock.php"
Task: "Create CtaBlock class at app/Blocks/CtaBlock.php"

# After T008 (HasSlug) completes, launch other traits in parallel:
Task: "Create HasSeo trait at app/Traits/HasSeo.php"
Task: "Create HasContentBlocks trait at app/Traits/HasContentBlocks.php"

# After all traits complete, launch trait tests in parallel:
Task: "Create HasSlugTest at tests/Feature/Traits/HasSlugTest.php"
Task: "Create HasSeoTest at tests/Feature/Traits/HasSeoTest.php"
Task: "Create HasContentBlocksTest at tests/Feature/Traits/HasContentBlocksTest.php"
Task: "Create HasRelatedContentTest at tests/Feature/Traits/HasRelatedContentTest.php"
```

---

## Parallel Example: User Story 2 & 3

```bash
# After Foundational completes, US2 and US3 can start in parallel:

# US2 migrations in parallel:
Task: "Create services table migration"
Task: "Create blog_posts table migration"

# US3 migrations in parallel:
Task: "Create faqs table migration"
Task: "Create testimonials table migration"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL)
3. Complete Phase 3: User Story 1 (Static Pages)
4. **STOP and VALIDATE**: Test Page model independently
5. Deploy/demo if ready - basic page creation works

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. User Story 1 → Test independently → MVP (static pages work)
3. User Story 2 → Test independently → Custom page types work
4. User Story 3 → Test independently → Content resources work
5. User Story 4 → Test independently → Relationships work
6. User Story 5 → Test independently → SEO mirroring validated
7. User Story 6 → Test independently → Soft delete with purge works
8. User Story 7 → Test independently → Slug management validated
9. Polish → Full validation

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Stories 1, 5
   - Developer B: User Stories 2, 4
   - Developer C: User Stories 3, 6, 7
3. Stories complete and integrate independently

---

## Notes

- All PHP files MUST declare `strict_types=1`
- Use `./vendor/bin/sail` for all commands (Laravel Sail environment)
- PostgreSQL JSONB for content_blocks column
- Follow naming conventions: Models (singular PascalCase), Traits (Has* prefix), Enums (TitleCase values)
- Run `./vendor/bin/sail pint` after completing each task
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently

---

## Summary

| Metric | Count |
|--------|-------|
| **Total Tasks** | 56 |
| **Phase 1 (Setup)** | 3 |
| **Phase 2 (Foundational)** | 14 |
| **User Story 1** | 5 |
| **User Story 2** | 10 |
| **User Story 3** | 10 |
| **User Story 4** | 3 |
| **User Story 5** | 2 |
| **User Story 6** | 3 |
| **User Story 7** | 1 |
| **Phase 10 (Polish)** | 5 |

### Subagent Assignment Summary

| Subagent | Task Count |
|----------|------------|
| **@laravel-specialist** | 46 |
| **@test-automator** | 16 |
| **@postgres-pro** | 9 |
| **@php-pro** | 4 |
| **@code-reviewer** | 2 |
| **@qa-expert** | 2 |

### Parallel Opportunities

- Phase 1: 2 parallel tasks (T002, T003)
- Phase 2: 6 parallel groups identified
- User Stories: US1-US3 can run in parallel after Foundational
- US4-US7: Limited parallelization due to model dependencies
- Phase 10: 3 parallel tasks

### MVP Scope

Complete through User Story 1 (Phases 1-3, Tasks T001-T022) for a functional static page system with content blocks and SEO.
