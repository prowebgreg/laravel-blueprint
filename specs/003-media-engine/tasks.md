# Tasks: Media Engine & Asset Model

**Input**: Design documents from `/specs/003-media-engine/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, quickstart.md

**Tests**: Tests are included as this feature has critical paths requiring 100% coverage per plan.md.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description → @subagent`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- **→ @subagent**: Assigned specialist agent(s) for the task

## Path Conventions

- **Laravel app**: `app/`, `database/`, `config/`, `tests/` at repository root
- Commands use `./vendor/bin/sail` prefix per CLAUDE.md

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Package installation, configuration, and project structure

- [X] T001 Install required packages (spatie/image ^3.0, spatie/laravel-image-optimizer ^1.7, enshrined/svg-sanitize ^0.16) → @devops-engineer
- [X] T002 Create config/media.php with all settings from plan.md → @laravel-specialist
- [X] T003 [P] Publish and configure config/image-optimizer.php for WebP optimization → @laravel-specialist
- [X] T004 [P] Create app/Enums/MediaType.php enum with allowedMimeTypes() and fromMimeType() methods → @laravel-specialist
- [X] T005 [P] Create app/Enums/MediaFolder.php enum with forMediaType() and s3Prefix() methods → @laravel-specialist
- [X] T006 [P] Create app/Enums/MediaState.php enum with isAccessible(), isFailed(), isProcessing() methods → @laravel-specialist

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Database schema, core models, and factories that ALL user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T007 Create migration 2025_12_10_000001_create_media_assets_table.php with UUID primary key, JSONB columns (dimensions, focal_point, tags), all fields from data-model.md → @laravel-specialist, @postgres-pro
- [X] T008 Create migration 2025_12_10_000002_create_media_variants_table.php with UUID PK, foreign key to media_assets, unique constraint on (media_asset_id, width) → @laravel-specialist, @postgres-pro
- [X] T009 Create migration 2025_12_10_000003_create_settings_table.php with key as PK, value as text → @laravel-specialist
- [X] T010 Create migration 2025_12_10_000004_modify_content_relations_for_uuid.php to change source_id and target_id to string(36) → @laravel-specialist, @postgres-pro
- [X] T011 Run migrations and verify all tables created correctly → @laravel-specialist
- [X] T012 [P] Create app/Models/MediaAsset.php with UUID trait, JSONB casts, variants() relationship, state enum cast → @laravel-specialist
- [X] T013 [P] Create app/Models/MediaVariant.php with UUID trait, mediaAsset() relationship → @laravel-specialist
- [X] T014 [P] Create app/Models/Setting.php with key as primary key, static get()/set() helper methods → @laravel-specialist
- [X] T015 [P] Create database/factories/MediaAssetFactory.php with states: uploading(), processing(), failed(), video(), svg(), withMetadata(), withVariants() → @laravel-specialist
- [X] T016 [P] Create database/factories/MediaVariantFactory.php with default 16:9 aspect ratio → @laravel-specialist
- [X] T017 Create database/seeders/MediaSeeder.php with example assets per data-model.md including fallback image setting → @laravel-specialist
- [X] T018 Run seeder and verify example data created → @laravel-specialist

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Upload and Process Image (Priority: P1) 🎯 MVP

**Goal**: Content editor can upload JPEG/PNG/GIF/WebP images, system converts to WebP and generates responsive variants

**Independent Test**: Upload a test image, verify all 8 variants generated with correct dimensions and WebP format

### Tests for User Story 1

- [X] T019 [P] [US1] Create tests/Unit/Actions/Media/ValidateUploadActionTest.php - test all validation rules, magic bytes, dimensions 50-16000, file size 20MB limit → @test-automator, @laravel-specialist
- [X] T020 [P] [US1] Create tests/Unit/Actions/Media/SanitizeFilenameActionTest.php - test lowercase, hyphens, special chars removal, nanoid suffix → @test-automator, @laravel-specialist
- [X] T021 [P] [US1] Create tests/Unit/Actions/Media/ExtractImageMetadataActionTest.php - test dimension extraction from all image formats → @test-automator, @laravel-specialist
- [ ] T022 [P] [US1] Create tests/Unit/Actions/Media/GenerateVariantsActionTest.php - test 8 variants, skip upscaling, aspect ratio preservation, WebP conversion → @test-automator, @laravel-specialist
- [ ] T023 [P] [US1] Create tests/Unit/Actions/Media/UploadToS3ActionTest.php - test success, retry logic (3 attempts with 1s/2s/4s delays), rollback on failure → @test-automator, @laravel-specialist
- [ ] T024 [P] [US1] Create tests/Feature/MediaUploadTest.php - test complete upload flow, state transitions, variant generation → @test-automator, @laravel-specialist
- [ ] T025 [P] [US1] Create tests/Feature/MediaStateTransitionTest.php - test uploading→processing→ready and uploading→processing→failed paths → @test-automator, @laravel-specialist
- [ ] T026 [P] [US1] Create tests/Feature/Jobs/ProcessMediaVariantsJobTest.php - test async processing, S3 upload, variant record creation → @test-automator, @laravel-specialist

### Implementation for User Story 1

- [ ] T027 [P] [US1] Create app/Actions/Media/ValidateUploadAction.php - validate MIME types, magic bytes, dimensions 50-16000, file size ≤20MB → @laravel-specialist, @php-pro
- [ ] T028 [P] [US1] Create app/Actions/Media/SanitizeFilenameAction.php - lowercase, hyphens, remove special chars, append 8-char nanoid → @laravel-specialist, @php-pro
- [ ] T029 [P] [US1] Create app/Actions/Media/ExtractImageMetadataAction.php - extract width/height from image using GD → @laravel-specialist, @php-pro
- [ ] T030 [US1] Create app/Actions/Media/GenerateVariantsAction.php - generate WebP variants at 480/640/720/960/1168/1440/1920/original widths, skip larger than original → @laravel-specialist, @php-pro
- [ ] T031 [US1] Create app/Actions/Media/UploadToS3Action.php - upload to S3 with retry logic (3 attempts, exponential backoff), return S3 key and CloudFront URL → @laravel-specialist, @php-pro
- [ ] T032 [US1] Create app/Jobs/Media/ProcessMediaVariantsJob.php - async variant generation with 180s timeout, rollback on failure, state transitions → @laravel-specialist
- [ ] T033 [US1] Create app/Services/Media/MediaUploadService.php - orchestrate upload flow: validate, sanitize, extract metadata, create asset, dispatch job → @laravel-specialist
- [ ] T034 [US1] Verify all US1 tests pass with ./vendor/bin/sail artisan test --filter=MediaUpload → @test-automator

**Checkpoint**: Image upload with variant generation is fully functional

---

## Phase 4: User Story 2 - Upload SVG File (Priority: P1)

**Goal**: Content editor can upload SVG files, system sanitizes XSS vectors and stores as-is

**Independent Test**: Upload SVG with embedded script tags, verify scripts removed, valid structure preserved

### Tests for User Story 2

- [ ] T035 [P] [US2] Create tests/Unit/Actions/Media/SanitizeSvgActionTest.php - test script removal, event handler removal, external reference removal, valid SVG preservation → @test-automator, @laravel-specialist

### Implementation for User Story 2

- [ ] T036 [US2] Create app/Actions/Media/SanitizeSvgAction.php - use enshrined/svg-sanitize to remove scripts, event handlers, external references → @laravel-specialist, @php-pro
- [ ] T037 [US2] Update app/Services/Media/MediaUploadService.php - add SVG handling path: validate, sanitize, upload directly without variant generation → @laravel-specialist
- [ ] T038 [US2] Verify all US2 tests pass with ./vendor/bin/sail artisan test --filter=SanitizeSvg → @test-automator

**Checkpoint**: SVG upload with sanitization is fully functional

---

## Phase 5: User Story 3 - Upload Video File (Priority: P2)

**Goal**: Content editor can upload MP4/WebM/MOV videos, system stores as-is without processing

**Independent Test**: Upload a video file, verify stored in original format and immediately available

### Implementation for User Story 3

- [ ] T039 [US3] Update app/Services/Media/MediaUploadService.php - add video handling path: validate, upload directly, set state to ready immediately → @laravel-specialist
- [ ] T040 [US3] Add feature test in tests/Feature/MediaUploadTest.php for video upload flow → @test-automator, @laravel-specialist

**Checkpoint**: Video upload is fully functional

---

## Phase 6: User Story 4 - Attach Media to Content Model (Priority: P1)

**Goal**: Developer can use HasMedia trait on any model to attach/detach media with type identifiers

**Independent Test**: Attach media to test model with type identifier, verify relationship created and retrievable

### Tests for User Story 4

- [ ] T041 [P] [US4] Create tests/Feature/HasMediaTraitTest.php - test attachMedia(), detachMedia(), getMedia(), getAllMedia() methods → @test-automator, @laravel-specialist

### Implementation for User Story 4

- [ ] T042 [US4] Create app/Traits/HasMedia.php - implement mediaAssets() morphToMany, attachMedia(), detachMedia(), getMedia(), getAllMedia() using content_relations table → @laravel-specialist, @php-pro
- [ ] T043 [US4] Add HasMedia trait to app/Models/Page.php → @laravel-specialist
- [ ] T044 [P] [US4] Add HasMedia trait to app/Models/Service.php → @laravel-specialist
- [ ] T045 [P] [US4] Add HasMedia trait to app/Models/BlogPost.php → @laravel-specialist
- [ ] T046 [P] [US4] Add HasMedia trait to app/Models/Faq.php → @laravel-specialist
- [ ] T047 [P] [US4] Add HasMedia trait to app/Models/Testimonial.php → @laravel-specialist
- [ ] T048 [US4] Verify all US4 tests pass with ./vendor/bin/sail artisan test --filter=HasMedia → @test-automator

**Checkpoint**: HasMedia trait enables media attachment on any content model

---

## Phase 7: User Story 5 - Display Media on Frontend with Fallback (Priority: P1)

**Goal**: Developer can get media URLs for display, system returns fallback when media missing or deleted

**Independent Test**: Request URL for attached media (returns CDN URL), request URL for missing media (returns fallback URL)

### Tests for User Story 5

- [ ] T049 [P] [US5] Create tests/Feature/MediaFallbackTest.php - test getMediaUrl() with attached media, missing media, deleted media, width selection → @test-automator, @laravel-specialist

### Implementation for User Story 5

- [ ] T050 [US5] Create app/Services/Media/MediaFallbackService.php - get fallback image ID from settings, return fallback URL with optional width → @laravel-specialist
- [ ] T051 [US5] Add getMediaUrl(type, width?) method to HasMedia trait - return CDN URL or fallback, select appropriate variant width → @laravel-specialist, @php-pro
- [ ] T052 [US5] Add getUrl(width?) method to MediaAsset model - return variant URL for width or next larger → @laravel-specialist
- [ ] T053 [US5] Verify all US5 tests pass with ./vendor/bin/sail artisan test --filter=MediaFallback → @test-automator

**Checkpoint**: Frontend can display media with automatic fallback for missing content

---

## Phase 8: User Story 6 - Delete Media with Usage Protection (Priority: P2)

**Goal**: System blocks deletion when media used by published public content, allows when only used by content resources

**Independent Test**: Attempt to delete media used by Page (blocked), attempt to delete media used only by Faq (allowed)

### Tests for User Story 6

- [ ] T054 [P] [US6] Create tests/Feature/MediaUsageTrackingTest.php - test findUsages(), isBlockedByPublicContent() for Page/Service/BlogPost vs Faq/Testimonial → @test-automator, @laravel-specialist
- [ ] T055 [P] [US6] Create tests/Feature/MediaDeletionTest.php - test soft delete, S3 cleanup, relationship removal, blocking logic → @test-automator, @laravel-specialist
- [ ] T056 [P] [US6] Create tests/Unit/Actions/Media/DeleteFromS3ActionTest.php - test single file and batch deletion → @test-automator, @laravel-specialist

### Implementation for User Story 6

- [ ] T057 [US6] Create app/Services/Media/MediaUsageService.php - findUsages() query content_relations, isBlockedByPublicContent() check model types → @laravel-specialist
- [ ] T058 [US6] Create app/Actions/Media/DeleteFromS3Action.php - delete single file and batch delete with error handling → @laravel-specialist, @php-pro
- [ ] T059 [US6] Create app/Services/Media/MediaDeletionService.php - check usage, block if public content, soft-delete asset, remove relationships, delete S3 files → @laravel-specialist
- [ ] T060 [US6] Block deletion of fallback image in MediaDeletionService → @laravel-specialist
- [ ] T061 [US6] Verify all US6 tests pass with ./vendor/bin/sail artisan test --filter=MediaDeletion → @test-automator

**Checkpoint**: Media deletion is protected for live content

---

## Phase 9: User Story 7 - View and Restore Deleted Media (Priority: P3)

**Goal**: Content editor can filter deleted media, view metadata within 30 days

**Independent Test**: Soft-delete media, verify it appears in deleted filter with metadata visible

### Implementation for User Story 7

- [ ] T062 [US7] Add scope onlyTrashed(), withTrashed() usage examples in MediaAsset model docblock → @laravel-specialist
- [ ] T063 [US7] Add withDeletedMedia() scope to MediaAsset for admin queries → @laravel-specialist

**Checkpoint**: Deleted media is viewable for 30-day recovery window

---

## Phase 10: User Story 8 - Automatic Cleanup and Maintenance (Priority: P3)

**Goal**: System automatically cleans failed uploads after 24h, purges soft-deleted after 30d, reports orphans weekly

**Independent Test**: Create failed asset older than 24h, run cleanup job, verify removed

### Tests for User Story 8

- [ ] T064 [P] [US8] Create tests/Feature/Jobs/CleanupFailedMediaJobTest.php - test 24-hour threshold cleanup → @test-automator, @laravel-specialist
- [ ] T065 [P] [US8] Create tests/Feature/Jobs/SyncOrphanedFilesJobTest.php - test orphan detection and reporting → @test-automator, @laravel-specialist

### Implementation for User Story 8

- [ ] T066 [US8] Create app/Jobs/Media/CleanupFailedMediaJob.php - delete failed assets older than 24 hours, cleanup S3 files → @laravel-specialist
- [ ] T067 [US8] Create app/Jobs/Media/SyncOrphanedFilesJob.php - scan S3 for orphaned files, mark missing DB records as failed, generate report → @laravel-specialist
- [ ] T068 [US8] Add purge logic for soft-deleted assets older than 30 days to CleanupFailedMediaJob → @laravel-specialist
- [ ] T069 [US8] Register scheduled jobs in routes/console.php: CleanupFailedMediaJob daily at 3:00, SyncOrphanedFilesJob weekly Sunday at 4:00 → @laravel-specialist
- [ ] T070 [US8] Verify all US8 tests pass with ./vendor/bin/sail artisan test --filter=Cleanup → @test-automator

**Checkpoint**: Automatic maintenance keeps storage clean

---

## Phase 11: Polish & Cross-Cutting Concerns

**Purpose**: Logging, optimization, edge case handling, final verification

- [ ] T071 Add slow operation logging (>30s) to ProcessMediaVariantsJob → @laravel-specialist
- [ ] T072 Add processing failure logging with asset ID and stack trace → @laravel-specialist
- [ ] T073 [P] Run ./vendor/bin/sail pint to format all new files → @laravel-specialist
- [ ] T074 [P] Run ./vendor/bin/sail artisan test to verify entire test suite passes → @test-automator
- [ ] T075 Verify Horizon media queue configuration exists → @laravel-specialist
- [ ] T076 Run quickstart.md validation steps to verify complete implementation → @qa-expert

### Edge Case Handling (from Checklist Cross-Check)

- [ ] T077 [P] Add empty file (0-byte) validation rejection in ValidateUploadAction → @laravel-specialist
- [ ] T078 [P] Add EXIF orientation auto-correction in GenerateVariantsAction using Spatie Image → @laravel-specialist
- [ ] T079 [P] Add animated WebP first-frame extraction handling (same as GIF) in GenerateVariantsAction → @laravel-specialist
- [ ] T080 [P] Add pessimistic locking for state transitions to prevent race conditions in ProcessMediaVariantsJob → @laravel-specialist
- [ ] T081 [P] Add test cases for edge cases: empty file, EXIF orientation, animated WebP, concurrent state updates → @test-automator

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup - BLOCKS all user stories
- **User Stories (Phase 3-10)**: All depend on Foundational phase completion
- **Polish (Phase 11)**: Depends on all user stories being complete

### User Story Dependencies

| Story | Depends On | Can Parallel With |
|-------|------------|-------------------|
| US1 (Image Upload) | Foundational | US2 (after Actions created) |
| US2 (SVG Upload) | T027-T031 from US1 | US3, US4 |
| US3 (Video Upload) | T033 MediaUploadService | US4, US5 |
| US4 (HasMedia Trait) | Foundational | US5, US6 |
| US5 (Fallback Display) | US4 (HasMedia) | US6 |
| US6 (Delete Protection) | US4 (HasMedia), US5 (Fallback) | US7, US8 |
| US7 (View Deleted) | US6 (soft delete) | US8 |
| US8 (Cleanup Jobs) | US6 (deletion service) | - |

### Parallel Opportunities per Phase

**Phase 1 Setup**: T003-T006 can run in parallel

**Phase 2 Foundational**: T012-T016 can run in parallel (after migrations)

**Phase 3 US1**: T019-T026 tests in parallel, T027-T029 Actions in parallel

**Phase 6 US4**: T044-T047 trait additions in parallel

---

## Parallel Example: Phase 2 Foundational

```bash
# After T011 migrations complete, launch models and factories in parallel:
Task: "Create app/Models/MediaAsset.php" → @laravel-specialist
Task: "Create app/Models/MediaVariant.php" → @laravel-specialist
Task: "Create app/Models/Setting.php" → @laravel-specialist
Task: "Create database/factories/MediaAssetFactory.php" → @laravel-specialist
Task: "Create database/factories/MediaVariantFactory.php" → @laravel-specialist
```

## Parallel Example: Phase 3 US1 Tests

```bash
# All US1 unit tests can run in parallel:
Task: "Create tests/Unit/Actions/Media/ValidateUploadActionTest.php" → @test-automator
Task: "Create tests/Unit/Actions/Media/SanitizeFilenameActionTest.php" → @test-automator
Task: "Create tests/Unit/Actions/Media/ExtractImageMetadataActionTest.php" → @test-automator
Task: "Create tests/Unit/Actions/Media/GenerateVariantsActionTest.php" → @test-automator
Task: "Create tests/Unit/Actions/Media/UploadToS3ActionTest.php" → @test-automator
```

---

## Implementation Strategy

### MVP First (User Stories 1, 2, 4, 5 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1 (Image Upload)
4. Complete Phase 4: User Story 2 (SVG Upload)
5. Complete Phase 6: User Story 4 (HasMedia Trait)
6. Complete Phase 7: User Story 5 (Fallback Display)
7. **STOP and VALIDATE**: Core media functionality is complete
8. Deploy/demo - editors can upload and attach media to content

### Incremental Delivery

1. MVP → Image/SVG upload + attachment working
2. Add US3 (Video) → Video content enabled
3. Add US6 (Delete Protection) → Production-safe deletion
4. Add US7-US8 (Cleanup) → Full maintenance automation

---

## Summary

| Metric | Count |
|--------|-------|
| **Total Tasks** | 81 |
| **Setup Tasks** | 6 |
| **Foundational Tasks** | 12 |
| **US1 Tasks** | 16 |
| **US2 Tasks** | 4 |
| **US3 Tasks** | 2 |
| **US4 Tasks** | 8 |
| **US5 Tasks** | 5 |
| **US6 Tasks** | 8 |
| **US7 Tasks** | 2 |
| **US8 Tasks** | 7 |
| **Polish Tasks** | 11 |

### Subagent Assignment Summary

| Subagent | Task Count | Primary Use |
|----------|------------|-------------|
| @laravel-specialist | 62 | Laravel models, services, actions, traits |
| @test-automator | 21 | Pest tests, verification |
| @php-pro | 10 | Complex PHP logic, strict typing |
| @postgres-pro | 4 | JSONB columns, migrations, UUID support |
| @devops-engineer | 1 | Package installation |
| @qa-expert | 1 | Quickstart validation |

---

## Notes

- All PHP files MUST have `declare(strict_types=1);`
- Use `./vendor/bin/sail` prefix for all commands
- Factory states (uploading, processing, failed, video, svg) enable comprehensive testing
- content_relations table modified to support UUID via string(36) columns
- MediaState enum provides type-safe state machine
