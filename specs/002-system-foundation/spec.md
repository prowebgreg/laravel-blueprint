# Feature Specification: System Foundation (Phase 1)

**Feature Branch**: `002-system-foundation`
**Created**: 2025-12-05
**Status**: Draft
**Input**: Blueprint CMS infrastructure foundation - local development environment, cloud storage, admin panel, and background job processing

## Clarifications

### Session 2025-12-05

- Q: Should the seeded admin user with hardcoded password be created in all environments? → A: Seed only in local/development environment; require manual user creation for staging/production
- Q: What is the maximum retry attempts for failed jobs before permanent failure? → A: 3 retries (standard default)
- Q: What is the admin session timeout duration? → A: 2 hours (balanced security and usability)
- Q: How should the system handle S3 operation failures (network/unavailability)? → A: Display error message to admin and allow manual retry
- Q: What is the maximum file size limit for uploads? → A: 10MB

### Session 2025-12-05 (Requirements Quality Review)

- Q: What is the exact startup command? → A: `./vendor/bin/sail up -d` (single command, Docker Compose orchestration)
- Q: How is session timeout measured? → A: From last activity (inactivity timeout), not from login time
- Q: What manual retry mechanism for S3 failures? → A: Filament notification with "Retry" action button in admin UI
- Q: What job throughput metrics should Horizon display? → A: Jobs per minute, average runtime, jobs processed (last hour/day), failed job count
- Q: What are the allowed file types for uploads? → A: Images (jpg, jpeg, png, gif, webp, svg), Documents (pdf), Videos (mp4, webm) - configurable per use case
- Q: How are concurrent uploads handled? → A: System supports parallel uploads; each upload is independent with unique temporary path
- Q: What happens on CloudFront when files are deleted? → A: Files are deleted from S3; CloudFront cache expires naturally (no manual invalidation required for this phase)
- Q: What are Horizon worker memory limits? → A: 128MB per worker, 3 workers in local, 10 workers in production

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Developer Environment Setup (Priority: P1)

A developer clones the repository and needs to start working immediately. They run a single command to launch the complete development environment with database, cache, and email testing services already configured and running.

**Why this priority**: Without a working development environment, no feature development can proceed. This is the absolute foundation that all other work depends on.

**Independent Test**: Can be fully tested by running the startup command and verifying all services are accessible. Delivers immediate value by enabling developers to begin work.

**Acceptance Scenarios**:

1. **Given** a fresh clone of the repository, **When** the developer runs the environment startup command, **Then** all required services (database, cache, email) start without errors
2. **Given** a running development environment, **When** the developer restarts their machine and re-runs the startup command, **Then** all previously stored data (database records, cache entries) persists and is available
3. **Given** a running development environment, **When** the developer triggers a test email, **Then** the email appears in the local email testing interface

---

### User Story 2 - Admin Access and Authentication (Priority: P1)

An administrator needs to securely access the content management system. They navigate to the admin panel, authenticate with credentials, and access the dashboard. If they forget their password, they can reset it via email.

**Why this priority**: Administrative access is essential for content management. Without secure login capability, administrators cannot perform any management tasks.

**Independent Test**: Can be fully tested by accessing the admin URL, logging in with provided credentials, and verifying dashboard access. Delivers value by enabling administrative control.

**Acceptance Scenarios**:

1. **Given** an unauthenticated user, **When** they navigate to the admin panel URL, **Then** they are redirected to a login page
2. **Given** the login page, **When** an administrator enters valid credentials, **Then** they are authenticated and see the admin dashboard
3. **Given** the login page, **When** an administrator enters invalid credentials, **Then** they see an error message and remain on the login page
4. **Given** the login page, **When** an administrator clicks "forgot password" and enters their email, **Then** they receive a password reset email
5. **Given** the admin dashboard, **When** an authenticated administrator clicks logout, **Then** they are logged out and redirected to the login page

---

### User Story 3 - Cloud Media Storage (Priority: P2)

A content administrator uploads media files to the CMS. The files are stored securely in cloud storage and delivered to site visitors through a content delivery network for fast loading worldwide.

**Why this priority**: Cloud storage is required for production media handling, but local development can proceed with local filesystem. This enables scalable, performant media delivery.

**Independent Test**: Can be fully tested by uploading a file and verifying it's accessible via CDN URL. Delivers value by enabling scalable media management.

**Acceptance Scenarios**:

1. **Given** configured cloud storage credentials, **When** the system uploads a file to temporary storage, **Then** the file is stored in the designated temporary location
2. **Given** a file in temporary storage, **When** the system processes and finalizes the upload, **Then** the file is moved to permanent storage
3. **Given** a file in permanent storage, **When** a visitor requests the file, **Then** it is served via CDN URL for optimal performance
4. **Given** a file in storage, **When** the system deletes the file, **Then** it is removed from cloud storage
5. **Given** development environment configuration, **When** storage is configured for local mode, **Then** files are stored locally instead of in cloud storage
6. **Given** a file exceeding maximum size (per FR-030), **When** the administrator attempts to upload it, **Then** they see the size validation error
7. **Given** an unsupported file type (per FR-031), **When** the administrator attempts to upload it, **Then** they see the file type validation error
8. **Given** an upload in progress, **When** the connection drops mid-transfer, **Then** partial upload is discarded and user sees "Upload incomplete. Please try again."

---

### User Story 4 - Background Job Processing (Priority: P2)

A developer needs to process time-consuming tasks (like image optimization) without blocking user requests. Jobs are queued and processed in the background, with a monitoring dashboard showing job status and allowing retry of failed jobs.

**Why this priority**: Background processing is essential for user experience (non-blocking operations) but is not immediately required for basic admin functionality.

**Independent Test**: Can be fully tested by dispatching a test job and verifying it appears in the monitoring dashboard and processes successfully. Delivers value by enabling asynchronous task processing.

**Acceptance Scenarios**:

1. **Given** the queue system is running, **When** a job is dispatched, **Then** it appears in the monitoring dashboard as pending
2. **Given** a pending job, **When** the job processor runs, **Then** the job executes and appears as completed
3. **Given** a job that fails during processing, **When** viewing the monitoring dashboard, **Then** the failed job is visible with error details
4. **Given** a failed job in the dashboard, **When** an administrator clicks retry, **Then** the job is re-queued for processing
5. **Given** an unauthenticated user, **When** they try to access the monitoring dashboard, **Then** they are denied access

---

### Edge Cases

- What happens when the database service fails to start? → Environment reports: "Database connection failed: [error]. Ensure PostgreSQL is running."
- How does the system handle cloud storage credential expiration? → S3 operations fail with: "AWS credentials invalid or expired. Please update credentials." Logged with full error details
- What happens when S3 operations fail due to network issues? → Display: "Upload failed: Network error. Click Retry to try again." Allow manual retry via Filament notification
- What happens when a job exceeds 3 retry attempts? → Job remains in Horizon failed jobs list for manual review; admin can retry or delete
- How does the system handle concurrent admin login attempts from different devices? → Session-based auth allows multiple concurrent sessions per user
- What happens when email service is unavailable during password reset? → Display: "Unable to send reset email. Please try again later or contact support."
- What happens when Redis is unavailable for queue operations? → Queue dispatch fails with logged error; admin sees "Queue unavailable" notification
- What happens with invalid/malformed file uploads? → Reject with validation error: "Invalid file" or specific message (corrupt, wrong type, too large)
- What happens when upload connection drops mid-transfer? → Partial upload is discarded; user receives "Upload incomplete. Please try again."

## Requirements *(mandatory)*

### Functional Requirements

#### Local Development Environment (Spec 1.1)

- **FR-001**: System MUST provide startup via `./vendor/bin/sail up -d` command to start the complete development environment with all required services
- **FR-002**: System MUST include PostgreSQL 17 database service with persistent storage using database name `blueprint_db`
- **FR-003**: System MUST include Redis 7 service for caching and queue processing with persistent storage
- **FR-004**: System MUST include local email testing service (Mailpit) accessible via web interface at port 8025
- **FR-005**: System MUST persist all service data between environment restarts using dedicated Docker volumes:
  - PostgreSQL: `sail-pgsql` volume mounted to `/var/lib/postgresql/data`
  - Redis: `sail-redis` volume mounted to `/data`
- **FR-006**: System MUST include image processing utilities in the development container:
  - jpegoptim (latest stable, ≥1.4)
  - optipng (latest stable, ≥0.7)
  - cwebp (libwebp, latest stable, ≥1.2)
- **FR-007**: System MUST be accessible at `http://localhost` (port 80) when running

#### Cloud Storage (Spec 1.2)

- **FR-008**: System MUST connect to AWS S3 bucket `laravel-blueprint-assets` in region `us-west-1` using IAM credentials with the following permissions: `s3:PutObject`, `s3:GetObject`, `s3:DeleteObject`, `s3:ListBucket`
- **FR-009**: System MUST organize files using scoped disk configuration:
  - `s3-temp` disk → `temp/` prefix for processing
  - `s3-permanent` disk → `permanent/` prefix for finalized files
  - `local-temp` disk → `storage/app/temp/` for local development
  - `local-permanent` disk → `storage/app/permanent/` for local development
- **FR-010**: System MUST serve public media files via CloudFront CDN at `https://dxrnpyjkukgbc.cloudfront.net`. Deleted files remain cached until CloudFront TTL expires (no manual invalidation in this phase)
- **FR-011**: System MUST support switching between local filesystem and cloud storage via `FILESYSTEM_DISK` environment variable. Local storage requires minimum 1GB available disk space for development
- **FR-012**: System MUST support uploading, moving, and deleting files from cloud storage. Concurrent uploads are supported with unique temporary paths per upload
- **FR-013**: System MUST document all cloud storage environment variables in `.env.example`:
  - `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_URL`, `AWS_USE_PATH_STYLE_ENDPOINT`
- **FR-014**: System MUST display actionable error messages when S3 operations fail:
  - Error format: "Upload failed: [specific error]. Click Retry to try again."
  - Retry mechanism: Filament notification with "Retry" action button
  - Errors MUST be logged to `storage/logs/laravel.log` with full exception details
- **FR-030**: System MUST reject file uploads exceeding 10MB with error message: "File size exceeds maximum allowed size of 10MB"
- **FR-031**: System MUST accept only allowed file types: Images (jpg, jpeg, png, gif, webp, svg), Documents (pdf), Videos (mp4, webm). Invalid types return: "File type not allowed. Accepted types: [list]"

#### Admin Panel (Spec 1.3)

- **FR-015**: System MUST provide admin panel accessible at `/admin` URL path
- **FR-016**: System MUST require authentication for all admin panel access (applies to both Filament admin and Horizon dashboard)
- **FR-017**: System MUST redirect unauthenticated users to login page at `/admin/login`
- **FR-018**: System MUST support email/password authentication for administrators
- **FR-019**: System MUST support password reset via email. When email service is unavailable, display: "Unable to send reset email. Please try again later or contact support."
- **FR-020**: System MUST provide secure logout functionality
- **FR-021**: System MUST include a seeded admin user with email `info@proweb.ai` and password `Levonik2007@` in local/development environment only; staging/production environments require manual user creation
- **FR-022**: System MUST expire admin sessions after 2 hours of inactivity (measured from last request, not login time). Concurrent sessions from multiple devices are allowed

#### Queue System (Spec 1.4)

- **FR-023**: System MUST use Redis as the default queue driver. When Redis is unavailable, queue operations fail with logged error (no silent fallback)
- **FR-024**: System MUST provide queue monitoring dashboard at `/horizon` URL path
- **FR-025**: System MUST restrict queue monitoring dashboard to authenticated administrators only (same auth as Filament admin via `canAccessPanel()`)
- **FR-026**: Queue monitoring MUST display pending, completed, and failed jobs
- **FR-027**: Queue monitoring MUST allow retry of failed jobs via dashboard UI
- **FR-028**: Queue monitoring MUST show job throughput metrics:
  - Jobs processed per minute
  - Average job runtime
  - Jobs processed (last hour and last 24 hours)
  - Failed job count
- **FR-029**: System MUST be able to dispatch and process jobs successfully
- **FR-032**: Horizon supervisor configuration:
  - Memory limit: 128MB per worker
  - Workers: 3 in local/development, 10 in production
  - Retry attempts: 3 per job
  - Jobs exceeding retry limit remain in failed state for manual review (not auto-deleted)

### Key Entities

- **User**: Represents an administrator with authentication credentials (email, password) and session state (2-hour inactivity timeout)
- **Job**: Represents a queued background task with status (pending, processing, completed, failed), payload data, and retry count (maximum 3 attempts before permanent failure)
- **File**: (Transient concept, not a persisted model in Phase 1) Represents an uploaded media file with storage location (temporary/permanent), path, CDN URL, and size constraint (maximum 10MB). A dedicated `Media` model with database tracking will be introduced in Phase 3 (Media Engine)

## Success Criteria *(mandatory)*

### Measurable Outcomes

#### Environment (Spec 1.1)
- **SC-001**: Environment startup via `./vendor/bin/sail up -d` completes with exit code 0 within 2 minutes; all containers report "healthy" status
- **SC-002**: Database migrations via `sail artisan migrate` execute successfully on first run with exit code 0
- **SC-003**: Cache operations verified via `sail artisan tinker`: `Cache::put('test', 'value', 60)` and `Cache::get('test')` returns 'value'
- **SC-004**: Email testing interface accessible at `http://localhost:8025`; sending test email via `sail artisan tinker` with `Mail::raw()` appears in Mailpit inbox
- **SC-005**: Image processing binaries available: `sail shell -c "jpegoptim --version && optipng --version && cwebp -version"` returns version info
- **SC-006**: Application accessible at `http://localhost` returns HTTP 200 status

#### Cloud Storage (Spec 1.2)
- **SC-007**: Files can be uploaded to temporary storage: `Storage::disk('s3-temp')->put('test.txt', 'content')` returns true
- **SC-008**: Files can be moved from temporary to permanent: file exists at `s3-permanent` disk after move
- **SC-009**: CDN URLs correctly generated: `Storage::disk('s3-permanent')->url('file.jpg')` returns `https://dxrnpyjkukgbc.cloudfront.net/permanent/file.jpg`
- **SC-010**: Files can be deleted: `Storage::disk('s3-permanent')->delete('file.jpg')` returns true and file no longer exists
- **SC-011**: All storage variables documented in `.env.example`: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET, AWS_URL, AWS_USE_PATH_STYLE_ENDPOINT
- **SC-021**: File uploads complete within 30 seconds for files up to 10MB on standard broadband connection

#### Admin Panel (Spec 1.3)
- **SC-012**: Admin panel URL redirects unauthenticated users to login
- **SC-013**: Seeded admin credentials successfully authenticate
- **SC-014**: Authenticated admin sees dashboard after login
- **SC-015**: Password reset email is sent and received (via local email testing)
- **SC-016**: Logout terminates session and redirects to login

#### Queue System (Spec 1.4)
- **SC-017**: Queue monitoring dashboard is accessible to authenticated admins only
- **SC-018**: Dispatched test job appears in monitoring dashboard
- **SC-019**: Test job processes successfully and shows as completed
- **SC-020**: Failed jobs can be retried from the monitoring interface

## Assumptions

- Docker Desktop 4.x or later is installed and running on the development machine (required for Docker Compose V2)
- AWS S3 bucket `laravel-blueprint-assets` and CloudFront distribution `dxrnpyjkukgbc.cloudfront.net` are already configured and accessible
- IAM credentials with `s3:PutObject`, `s3:GetObject`, `s3:DeleteObject`, `s3:ListBucket` permissions are available
- Laravel 12 is already installed as a blank application
- Developers have basic familiarity with Docker-based development environments
- The following ports are available on the development machine:
  - Port 80: Application web server
  - Port 5432: PostgreSQL database
  - Port 6379: Redis cache/queue
  - Port 8025: Mailpit web UI
  - Port 1025: Mailpit SMTP

## Scope Boundaries

### In Scope
- Local development environment configuration
- Cloud storage integration and configuration
- Admin panel installation with authentication
- Queue system with monitoring dashboard
- Initial admin user seeding

### Out of Scope
- Content models (Page, Service, BlogPost) - Phase 2
- Media library and image processing logic - Phase 3
- Global settings and webhooks - Phase 4
- Public API endpoints - Phase 5
- Frontend templates and styling - Phase 6
- Response caching and performance optimization - Phase 7
- Production deployment configuration
- CI/CD pipeline setup
- Custom admin panel themes or branding

## Dependencies

### Prerequisites (External)
- Docker Desktop installed and running
- AWS account with S3 bucket and CloudFront configured
- IAM credentials for S3 access

### Downstream (Future Phases)
- Phase 2 (Content Models) depends on database availability
- Phase 3 (Media Engine) depends on S3 configuration and image processing utilities
- Phase 5 (API) depends on authentication system
- All future phases depend on queue system for background jobs
