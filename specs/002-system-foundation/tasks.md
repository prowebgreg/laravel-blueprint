# Tasks: System Foundation (Phase 1)

**Input**: Design documents from `/specs/002-system-foundation/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Not explicitly requested in spec - tests are omitted per template instructions.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description → @subagent`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3, US4)
- Include exact file paths in descriptions
- **→ @subagent**: Assigned specialist for the task

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization, dependency installation, and Docker configuration

- [X] T001 Install Laravel Sail and publish Docker configuration → @devops-engineer
- [X] T002 [P] Install Filament V3 via Composer in composer.json → @laravel-specialist
- [X] T003 [P] Install Laravel Horizon via Composer in composer.json → @laravel-specialist
- [X] T004 [P] Install Pest PHP testing framework via Composer in composer.json → @laravel-specialist
- [X] T005 [P] Install AWS S3 Flysystem packages via Composer in composer.json → @laravel-specialist
- [X] T006 Update .env.example with all required environment variables → @devops-engineer

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

### Docker & Sail Configuration

- [X] T007 Customize docker/8.3/Dockerfile with image processing binaries (jpegoptim ≥1.4, optipng ≥0.7, cwebp ≥1.2) → @devops-engineer
- [X] T008 Configure docker-compose.yml with PostgreSQL 17, Redis 7, and Mailpit services → @devops-engineer
- [X] T009 [P] Configure PHP settings for 10MB upload limit in docker/8.3/php.ini → @devops-engineer
- [X] T010 Update docker-compose.yml image name to blueprint-cms/app → @devops-engineer

### Core Laravel Configuration

- [X] T011 Configure config/database.php for PostgreSQL connection → @laravel-specialist
- [X] T012 [P] Configure config/cache.php for Redis cache store → @laravel-specialist
- [X] T013 [P] Configure config/queue.php for Redis queue connection → @laravel-specialist
- [X] T014 [P] Configure config/session.php for database driver with 2-hour lifetime → @laravel-specialist
- [X] T015 [P] Configure config/mail.php for Mailpit SMTP in development → @laravel-specialist
- [X] T016 Create database/migrations for sessions table if not exists → @laravel-specialist, @postgres-pro
- [X] T017 Update app/Models/User.php to implement FilamentUser interface with canAccessPanel() method → @laravel-specialist

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Developer Environment Setup (Priority: P1) 🎯 MVP

**Goal**: Enable developers to start the complete development environment with a single command

**Independent Test**: Run `./vendor/bin/sail up -d` and verify all services (PostgreSQL, Redis, Mailpit) start without errors and are accessible

### Implementation for User Story 1

- [X] T018 [US1] Create .env file from .env.example with local development defaults → @devops-engineer
- [X] T019 [US1] Configure docker-compose.yml volumes for PostgreSQL (sail-pgsql → /var/lib/postgresql/data) and Redis (sail-redis → /data) persistence → @devops-engineer
- [X] T020 [US1] Verify PostgreSQL service starts and accepts connections on port 5432 → @devops-engineer
- [X] T021 [P] [US1] Verify Redis service starts and accepts connections on port 6379 → @devops-engineer
- [X] T022 [P] [US1] Verify Mailpit service starts with web UI on port 8025 → @devops-engineer
- [X] T023 [US1] Run database migrations with `sail artisan migrate` → @laravel-specialist
- [X] T024 [US1] Verify image processing binaries are available in container (jpegoptim, optipng, cwebp) → @devops-engineer
- [X] T025 [US1] Verify application loads at http://localhost without errors → @devops-engineer

**Checkpoint**: User Story 1 complete - developers can run `sail up -d` to start full environment

---

## Phase 4: User Story 2 - Admin Access and Authentication (Priority: P1)

**Goal**: Enable administrators to securely access the CMS admin panel with authentication

**Independent Test**: Navigate to /admin, login with seeded credentials, verify dashboard access, test password reset flow via Mailpit

### Implementation for User Story 2

- [X] T026 [US2] Run Filament panel installation with `sail artisan filament:install --panels` → @laravel-specialist
- [X] T027 [US2] Configure app/Providers/Filament/AdminPanelProvider.php with login, passwordReset, and auth settings → @laravel-specialist
- [X] T028 [P] [US2] Create database/seeders/AdminUserSeeder.php with development admin credentials → @laravel-specialist
- [X] T029 [P] [US2] Update database/seeders/DatabaseSeeder.php to call AdminUserSeeder in local/dev environment only → @laravel-specialist
- [X] T030 [US2] Run seeder with `sail artisan db:seed` to create admin user → @laravel-specialist
- [X] T031 [US2] Verify unauthenticated access to /admin redirects to /admin/login → @laravel-specialist
- [X] T032 [US2] Verify login with info@proweb.ai / Levonik2007@ succeeds and shows dashboard → @laravel-specialist
- [X] T033 [US2] Verify password reset sends email to Mailpit and reset link works → @laravel-specialist
- [X] T034 [US2] Verify logout terminates session and redirects to login → @laravel-specialist

**Checkpoint**: User Story 2 complete - admins can login, logout, and reset passwords

---

## Phase 5: User Story 3 - Cloud Media Storage (Priority: P2)

**Goal**: Enable file uploads to S3 with CloudFront CDN delivery and local filesystem fallback

**Independent Test**: Upload a file via Tinker to s3-temp disk, move to s3-permanent disk, verify CDN URL works

### Implementation for User Story 3

- [X] T035 [US3] Configure config/filesystems.php with s3, s3-temp, s3-permanent disks → @laravel-specialist
- [X] T036 [P] [US3] Configure config/filesystems.php with local-temp, local-permanent scoped disks → @laravel-specialist
- [X] T037 [US3] Set AWS_URL environment variable for CloudFront CDN in .env.example → @devops-engineer
- [X] T038 [US3] Verify Storage::disk('s3-temp')->put() uploads file to temp/ prefix → @laravel-specialist
- [X] T039 [US3] Verify file can be moved from s3-temp to s3-permanent disk → @laravel-specialist
- [X] T040 [US3] Verify Storage::disk('s3-permanent')->url() returns CloudFront URL → @laravel-specialist
- [X] T041 [US3] Verify Storage::disk('s3-permanent')->delete() removes file from S3 → @laravel-specialist
- [X] T042 [US3] Verify local-temp and local-permanent disks work in development mode → @laravel-specialist
- [X] T058 [US3] Configure file type validation for allowed types (jpg, jpeg, png, gif, webp, svg, pdf, mp4, webm) → @laravel-specialist
- [X] T059 [US3] Verify file size rejection with message "File size exceeds maximum allowed size of 10MB" → @laravel-specialist
- [X] T060 [US3] Verify invalid file type rejection with message "File type not allowed. Accepted types: [list]" → @laravel-specialist

**Checkpoint**: User Story 3 complete - files upload to S3 and serve via CloudFront

---

## Phase 6: User Story 4 - Background Job Processing (Priority: P2)

**Goal**: Enable background job processing with Horizon monitoring dashboard

**Independent Test**: Dispatch a test job, verify it appears in Horizon dashboard at /horizon and processes successfully

### Implementation for User Story 4

- [X] T043 [US4] Publish Horizon configuration with `sail artisan horizon:install` → @laravel-specialist
- [X] T044 [US4] Configure config/horizon.php with supervisor settings (128MB memory, 3 retries, 3 workers local/10 prod) → @laravel-specialist
- [X] T045 [US4] Create app/Providers/HorizonServiceProvider.php with viewHorizon gate using canAccessPanel() → @laravel-specialist
- [ ] T046 [US4] Register HorizonServiceProvider in bootstrap/providers.php → @laravel-specialist
- [ ] T047 [US4] Create app/Jobs/TestJob.php as a simple test job for verification → @laravel-specialist
- [ ] T048 [US4] Verify unauthenticated access to /horizon returns 403 → @laravel-specialist
- [ ] T049 [US4] Verify authenticated admin can access /horizon dashboard → @laravel-specialist
- [ ] T050 [US4] Dispatch TestJob and verify it appears in Horizon as pending → @laravel-specialist
- [ ] T051 [US4] Start Horizon worker and verify TestJob processes successfully → @laravel-specialist
- [ ] T052 [US4] Verify failed job appears in Horizon and can be retried → @laravel-specialist

**Checkpoint**: User Story 4 complete - jobs process in background with Horizon monitoring

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final verification, cleanup, and documentation updates

- [ ] T053 [P] Run Laravel Pint for code style formatting → @code-reviewer
- [ ] T054 [P] Document all environment variables in .env.example with comments → @documentation-engineer
- [ ] T055 Verify all success criteria from spec.md (SC-001 through SC-021) → @qa-expert
- [ ] T056 [P] Remove TestJob.php if not needed for production → @laravel-specialist
- [ ] T057 Run quickstart.md validation - follow all steps from clean clone → @qa-expert

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-6)**: All depend on Foundational phase completion
  - US1 and US2 are both P1 priority - can proceed in parallel after Foundational
  - US3 and US4 are both P2 priority - can proceed in parallel after US1/US2
- **Polish (Phase 7)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Environment Setup - No dependencies on other stories
- **User Story 2 (P1)**: Admin Auth - Can start after Foundational, independent of US1
- **User Story 3 (P2)**: Storage - Can start after Foundational, independent of US1/US2
- **User Story 4 (P2)**: Queue - Can start after Foundational, requires US2 for Horizon auth gate

### Within Each User Story

- Models/config before services
- Services before verification tasks
- Core implementation before integration verification
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel (T002-T005)
- All Foundational tasks marked [P] can run in parallel (T009, T012-T015)
- US1 environment checks marked [P] can run in parallel (T021-T022)
- US2 seeder tasks marked [P] can run in parallel (T028-T029)
- US3 disk config tasks marked [P] can run in parallel (T035-T036)
- All Polish tasks marked [P] can run in parallel (T053-T054, T056)

---

## Parallel Example: Phase 2 Foundational

```bash
# Launch all parallel foundational tasks together:
Task: "T009 Configure PHP settings for 10MB upload limit in docker/8.3/php.ini"
Task: "T012 Configure config/cache.php for Redis cache store"
Task: "T013 Configure config/queue.php for Redis queue connection"
Task: "T014 Configure config/session.php for database driver with 2-hour lifetime"
Task: "T015 Configure config/mail.php for Mailpit SMTP in development"
```

---

## Implementation Strategy

### MVP First (User Stories 1 & 2 Only)

1. Complete Phase 1: Setup (T001-T006)
2. Complete Phase 2: Foundational (T007-T017)
3. Complete Phase 3: User Story 1 - Environment (T018-T025)
4. Complete Phase 4: User Story 2 - Admin Auth (T026-T034)
5. **STOP and VALIDATE**: Test US1 and US2 independently
6. Deploy/demo if ready - developers can work, admins can login

### Full Implementation

1. MVP (above) → Foundation ready, admin access working
2. Add User Story 3 - Storage (T035-T042, T058-T060) → Test independently → Media ready
3. Add User Story 4 - Queue (T043-T052) → Test independently → Background jobs ready
4. Phase 7: Polish (T053-T057) → Production ready

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1 (Environment)
   - Developer B: User Story 2 (Admin Auth)
3. After P1 stories complete:
   - Developer A: User Story 3 (Storage)
   - Developer B: User Story 4 (Queue)
4. All: Polish phase

---

## Subagent Assignment Summary

| Subagent | Task Count | Primary Responsibilities |
|----------|------------|-------------------------|
| @devops-engineer | 14 | Docker, Sail, environment config, service verification |
| @laravel-specialist | 38 | Filament, Horizon, models, config, seeders, storage, validation |
| @postgres-pro | 1 | Database migrations (with laravel-specialist) |
| @code-reviewer | 1 | Code style formatting |
| @documentation-engineer | 1 | Environment documentation |
| @qa-expert | 2 | Success criteria validation |

**Total Tasks**: 60 (was 57, added T058-T060 for file validation)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story is independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Port 3010 not needed for Sail - uses port 80 by default
- Development server via `sail up -d`, not `php artisan serve`
