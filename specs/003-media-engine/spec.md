# Feature Specification: Media Engine & Asset Model

**Feature Branch**: `003-media-engine`
**Created**: 2025-12-10
**Status**: Draft
**Input**: User description: "Phase 3: Media Engine & Asset Model of Blueprint CMS"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Upload and Process Image (Priority: P1)

A content editor uploads an image file (JPEG, PNG, GIF, or WebP) through the admin interface. The system immediately accepts the upload and shows "processing" status. In the background, the system validates dimensions, converts to WebP format, generates responsive size variants, and uploads all files to cloud storage. When complete, the status changes to "ready" and the editor sees the image with all variants available.

**Why this priority**: Image upload is the foundational capability that all visual content depends on. Without working image uploads, no other media features can function. This story enables content editors to add visual content to any page or content block.

**Independent Test**: Can be fully tested by uploading a test image and verifying all variants are generated with correct dimensions. Delivers immediate value by enabling visual content creation.

**Acceptance Scenarios**:

1. **Given** a content editor is authenticated, **When** they upload a valid 5MB JPEG image, **Then** the system shows "processing" status immediately and the image becomes "ready" within 60 seconds with all 8 WebP variants generated.

2. **Given** a content editor uploads a PNG image, **When** processing completes, **Then** the original PNG is discarded and only WebP variants are stored.

3. **Given** a content editor uploads a GIF image, **When** processing completes, **Then** only the first frame is extracted and converted to WebP (animation discarded).

4. **Given** a content editor uploads an image exceeding 16000x16000 pixels, **When** validation runs, **Then** the upload is rejected with a clear error message about maximum dimensions.

5. **Given** a content editor uploads an image smaller than 50x50 pixels, **When** validation runs, **Then** the upload is rejected with a clear error message about minimum dimensions.

6. **Given** a content editor uploads a file exceeding 20MB, **When** validation runs, **Then** the upload is rejected with a clear error message about maximum file size.

---

### User Story 2 - Upload SVG File (Priority: P1)

A content editor uploads an SVG file for use as an icon, logo, or decorative element. The system sanitizes the file to remove any embedded scripts or dangerous content, then stores the sanitized version as-is without format conversion or variant generation.

**Why this priority**: SVGs are essential for logos, icons, and scalable graphics that must remain crisp at any size. This is equally critical to raster images for professional website development.

**Independent Test**: Can be fully tested by uploading an SVG file and verifying it is sanitized and immediately available. Delivers value by enabling vector graphics in content.

**Acceptance Scenarios**:

1. **Given** a content editor uploads a valid SVG file, **When** processing completes, **Then** the SVG becomes immediately available without variant generation.

2. **Given** an SVG contains embedded JavaScript, **When** sanitization runs, **Then** all scripts are removed while preserving valid SVG structure.

3. **Given** an SVG contains onclick or other event handlers, **When** sanitization runs, **Then** all event handlers are removed.

4. **Given** an SVG contains external references (xlink:href to external URLs), **When** sanitization runs, **Then** external references are removed or converted to inline content.

---

### User Story 3 - Upload Video File (Priority: P2)

A content editor uploads a video file (MP4, WebM, or MOV) for use in content. The system stores the video as-is without processing or transcoding. The video becomes available immediately after upload.

**Why this priority**: Video support is important but less frequently used than images. Storing videos without processing keeps the initial implementation simpler while still enabling video content.

**Independent Test**: Can be fully tested by uploading a test video and verifying it is stored and retrievable. Delivers value by enabling video content on pages.

**Acceptance Scenarios**:

1. **Given** a content editor uploads a valid MP4 video under 20MB, **When** upload completes, **Then** the video becomes immediately available without processing delay.

2. **Given** a content editor uploads a WebM video, **When** upload completes, **Then** the video is stored in its original format without conversion.

3. **Given** a content editor uploads a video exceeding 20MB, **When** validation runs, **Then** the upload is rejected with a clear error message.

---

### User Story 4 - Attach Media to Content Model (Priority: P1)

A developer adds media attachment capability to any content model (Page, Service, Testimonial, etc.) using a trait. When a content editor saves content with an image field, the system creates a relationship record linking that content to the selected media asset with a contextual type identifier.

**Why this priority**: Without the ability to attach media to content, uploaded images cannot be used on pages. This story bridges media storage with content display.

**Independent Test**: Can be tested by attaching media to a test model and verifying the relationship is created with correct type identifier. Delivers value by enabling media-rich content.

**Acceptance Scenarios**:

1. **Given** a model uses the HasMedia trait, **When** attachMedia is called with a media asset and type identifier, **Then** a relationship record is created linking the model to the media.

2. **Given** a Page model with attached hero image, **When** getMedia('page:home:hero:image') is called, **Then** the correct media asset is returned.

3. **Given** a model has media attached, **When** detachMedia is called for that type, **Then** the relationship record is removed but the media asset remains available.

4. **Given** a model with multiple media attachments, **When** getAllMedia is called, **Then** all attached media assets are returned with their type identifiers.

---

### User Story 5 - Display Media on Frontend with Fallback (Priority: P1)

A developer requests media for a content item by relationship type. The system returns the appropriate variant URL for display. If no media is attached or the attached media has been deleted, the system returns a configurable fallback image URL instead.

**Why this priority**: Frontend display is the ultimate purpose of media management. Fallback handling ensures pages never show broken image icons.

**Independent Test**: Can be tested by requesting media URLs for attached and unattached content, verifying correct URLs or fallbacks are returned.

**Acceptance Scenarios**:

1. **Given** a content item has media attached, **When** getMediaUrl is called with a width, **Then** the CDN URL for the appropriate variant is returned.

2. **Given** a content item has no media attached, **When** getMediaUrl is called, **Then** the fallback image URL is returned.

3. **Given** media was attached but later deleted, **When** getMediaUrl is called, **Then** the fallback image URL is returned.

4. **Given** a request for a specific variant width, **When** the exact width doesn't exist, **Then** the next larger variant is returned.

---

### User Story 6 - Delete Media with Usage Protection (Priority: P2)

A content editor attempts to delete a media asset. The system checks if the asset is used by any published public content (Pages, Services, BlogPosts). If in use, deletion is blocked with a list of where it's used. If not in use (or only used by internal resources), the system removes all relationship records, deletes all cloud storage files, and soft-deletes the database record.

**Why this priority**: Safe deletion prevents content editors from accidentally breaking live pages. This protection is essential for production reliability.

**Independent Test**: Can be tested by attempting to delete media used by public and internal content, verifying blocking and allowing behavior.

**Acceptance Scenarios**:

1. **Given** a media asset is used by a published Page, **When** delete is attempted, **Then** deletion is blocked with a list showing the Page using it.

2. **Given** a media asset is used by a published Service, **When** delete is attempted, **Then** deletion is blocked with a list showing the Service using it.

3. **Given** a media asset is only used by Faq content resources, **When** delete is attempted, **Then** deletion proceeds (internal content doesn't block).

4. **Given** a media asset is only used by Testimonial content resources, **When** delete is attempted, **Then** deletion proceeds.

5. **Given** a media asset is used by soft-deleted content, **When** delete is attempted, **Then** deletion proceeds (soft-deleted content doesn't block).

6. **Given** a media asset is not used by any content, **When** delete is attempted, **Then** all cloud storage files are deleted and the database record is soft-deleted.

---

### User Story 7 - View and Restore Deleted Media (Priority: P3)

A content editor can filter the media library to see recently deleted items. Within 30 days, they can view metadata of deleted media. Restoration requires re-uploading the file since cloud storage files are already removed.

**Why this priority**: Recovery capability provides a safety net but is less critical than core upload and display functionality.

**Independent Test**: Can be tested by soft-deleting media and verifying it appears in the deleted filter with metadata visible.

**Acceptance Scenarios**:

1. **Given** media was soft-deleted 10 days ago, **When** filtering for deleted media, **Then** the deleted asset appears in results with preserved metadata.

2. **Given** media was soft-deleted 31 days ago, **When** the purge job runs, **Then** the database record is permanently deleted.

3. **Given** a deleted media asset, **When** viewing its details, **Then** the original filename, dimensions, and deletion date are visible.

---

### User Story 8 - Automatic Cleanup and Maintenance (Priority: P3)

The system automatically cleans up failed uploads after 24 hours and permanently purges soft-deleted media after 30 days. A weekly scan reports orphaned files for admin review without auto-deleting.

**Why this priority**: Cleanup is important for system health but runs in background and is less visible to users.

**Independent Test**: Can be tested by creating failed uploads and soft-deleted records, then running cleanup jobs and verifying removal.

**Acceptance Scenarios**:

1. **Given** a media asset has been in "failed" state for 25 hours, **When** the cleanup job runs, **Then** the failed asset is permanently deleted.

2. **Given** a media asset was soft-deleted 31 days ago, **When** the purge job runs, **Then** the database record and any remaining cloud files are permanently deleted.

3. **Given** files exist in cloud storage but not in database, **When** the orphan scan runs, **Then** a report is generated listing orphaned files without auto-deleting them.

4. **Given** database records reference cloud files that don't exist, **When** the orphan scan runs, **Then** those records are marked as "failed" for admin review.

---

### Edge Cases

- What happens when cloud storage upload fails after some variants are uploaded? System rolls back all successfully uploaded variants and marks asset as "failed".
- What happens when image dimensions are exactly at the min/max boundary? Images at exactly 50x50 or 16000x16000 are accepted.
- What happens when processing fails mid-way? Asset is marked "failed" with descriptive error, all partial uploads are cleaned up.
- What happens when the fallback image is deleted? Fallback image deletion is blocked (it's referenced in system settings).
- What happens when requesting a variant width larger than the original? The original-size variant is returned.
- What happens when two users upload files with identical names? Each gets a unique 8-character suffix preventing collisions.
- What happens when the same file is uploaded twice (byte-identical)? Separate assets are created; no deduplication is performed.

## Requirements *(mandatory)*

### Functional Requirements

**Media Asset Management**

- **FR-001**: System MUST accept image uploads in JPEG, PNG, GIF, and WebP formats up to 20MB
- **FR-001a**: System MUST validate file magic bytes match declared MIME type before processing
- **FR-002**: System MUST validate image dimensions are between 50x50 and 16000x16000 pixels
- **FR-003**: System MUST convert all accepted image formats to WebP at 85% quality
- **FR-004**: System MUST generate size variants per image at widths: 480, 640, 720, 960, 1168, 1440, 1920, and original (only variants smaller than or equal to original width are generated)
- **FR-005**: System MUST preserve aspect ratio when generating variants (height calculated automatically)
- **FR-005a**: System MUST NOT upscale images; variants larger than the original width are skipped
- **FR-006**: System MUST extract first frame only from GIF uploads (animation discarded)
- **FR-007**: System MUST accept SVG uploads and sanitize them to remove scripts, event handlers, and external references
- **FR-008**: System MUST store sanitized SVGs as-is without format conversion or variant generation
- **FR-009**: System MUST accept video uploads in MP4, WebM, and MOV formats up to 20MB
- **FR-010**: System MUST store videos as-is without processing or transcoding
- **FR-011**: System MUST process images asynchronously without blocking the user interface
- **FR-012**: System MUST track processing state for each asset: uploading, processing, ready, failed
- **FR-013**: System MUST store error messages when processing fails

**Storage Organization**

- **FR-014**: System MUST organize cloud storage with prefix "media/" and subfolders: images/, videos/, svg/
- **FR-015**: System MUST generate filenames as: sanitized-name-nanoid8.webp (e.g., hero-image-x7k9m2p4-480.webp)
- **FR-016**: System MUST sanitize original filenames: lowercase, replace spaces with hyphens, remove special characters
- **FR-017**: System MUST append 8-character unique identifier to prevent filename collisions
- **FR-018**: System MUST preserve original filename in database for display purposes
- **FR-019**: System MUST store variants alongside originals (not in subfolders)

**Upload Reliability**

- **FR-020**: System MUST retry failed cloud storage uploads 3 times with increasing delays (1s, 2s, 4s)
- **FR-021**: System MUST roll back all successfully uploaded variants if any variant upload fails after retries
- **FR-022**: System MUST mark assets as "failed" with descriptive error message when processing fails
- **FR-023**: System MUST auto-cleanup failed uploads after 24 hours

**Media Metadata**

- **FR-024**: System MUST store for each asset: UUID, filename, original filename, media type, folder, file size, dimensions, MIME type, state, cloud storage key, CDN URL
- **FR-025**: System MUST support optional metadata: alt text, title, caption
- **FR-026**: System MUST support focal point coordinates (x, y values 0-1) for smart cropping
- **FR-027**: System MUST support tags array for flexible categorization
- **FR-028**: System MUST store for each variant: UUID, parent asset reference, width, height, format, file size, cloud storage key, CDN URL

**Content Relationship System**

- **FR-029**: System MUST use the existing content_relations table for media-to-content relationships
- **FR-030**: System MUST support relationship type identifiers following naming convention: fixed fields (og_image), static pages (page:home:hero:image), custom page types (service:hero:image), content resources (testimonial:avatar)
- **FR-031**: System MUST support multiple relationship types per content item
- **FR-032**: System MUST enable bidirectional queries: "what media does this content use?" and "what content uses this media?"
- **FR-033**: System MUST auto-sync media relationships when content is saved (add new, remove old)

**HasMedia Trait**

- **FR-034**: System MUST provide a HasMedia trait that any model can use to enable media attachment
- **FR-035**: HasMedia MUST provide attachMedia(MediaAsset, type) method
- **FR-036**: HasMedia MUST provide detachMedia(type) method
- **FR-037**: HasMedia MUST provide getMedia(type) method returning MediaAsset or null
- **FR-038**: HasMedia MUST provide getAllMedia() method returning collection of attached media
- **FR-039**: HasMedia MUST provide getMediaUrl(type, width?) method returning CDN URL or fallback

**Usage Tracking & Deletion Protection**

- **FR-040**: System MUST identify all content using a specific media asset
- **FR-041**: System MUST block deletion when media is used by published Page, Service, or BlogPost content
- **FR-042**: System MUST allow deletion when media is only used by Faq, Testimonial, or other internal content resources
- **FR-043**: System MUST allow deletion when media is only used by soft-deleted content
- **FR-044**: System MUST show list of content items blocking deletion when deletion is blocked
- **FR-045**: System MUST soft-delete media records with 30-day retention before permanent purge
- **FR-046**: System MUST remove all relationship records when media is deleted
- **FR-047**: System MUST delete all cloud storage files (original and variants) when media is deleted

**Fallback Image System**

- **FR-048**: System MUST support configuring a fallback media asset in system settings
- **FR-049**: System MUST return fallback URL when getMediaUrl is called for non-existent media
- **FR-050**: System MUST return fallback URL when attached media has been deleted
- **FR-051**: System MUST block deletion of the designated fallback image

**Cleanup & Maintenance**

- **FR-052**: System MUST automatically delete failed assets after 24 hours via scheduled job
- **FR-053**: System MUST permanently purge soft-deleted assets after 30 days via scheduled job
- **FR-054**: System MUST run weekly scan to report orphaned cloud storage files (files in storage but not in database)
- **FR-055**: System MUST mark database records as "failed" when referenced cloud files are missing

**Observability**

- **FR-056**: System MUST log processing failures with asset ID, error type, and stack trace
- **FR-057**: System MUST log slow operations exceeding 30 seconds with asset ID and duration

### Key Entities

- **MediaAsset**: Represents an uploaded media file with all metadata including UUID, filename, original filename, media type (image/video/svg), folder path, file size, dimensions, MIME type, processing state, error message, cloud storage key, CDN URL, optional alt text/title/caption, focal point coordinates, tags array, and soft-delete timestamp.

- **MediaVariant**: Represents a size variant of an image asset with UUID, reference to parent MediaAsset, width, height, format (webp), file size, cloud storage key, and CDN URL.

- **Setting**: Key-value store for system configuration including fallback image reference.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 20MB image uploads complete processing (all variants generated and uploaded) within 180 seconds
- **SC-002**: All 8 WebP variants are generated with correct dimensions (within 1px tolerance due to rounding)
- **SC-003**: Aspect ratio is preserved across all variants with no visible distortion
- **SC-004**: SVG sanitization removes malicious content (scripts, event handlers) while preserving valid visual structure
- **SC-005**: Videos store and retrieve correctly without corruption or quality loss
- **SC-006**: Usage tracking accurately identifies 100% of content items using a specific media asset
- **SC-007**: Media deletion is blocked with accurate usage list when used by any published public content
- **SC-008**: Media deletion proceeds when only used by internal content resources or soft-deleted content
- **SC-009**: Fallback image displays in place of missing or deleted media with no broken image indicators
- **SC-010**: State machine transitions correctly through uploading → processing → ready for successful uploads
- **SC-011**: State machine transitions correctly through uploading → processing → failed for unsuccessful uploads
- **SC-012**: Failed uploads are automatically removed within 25 hours of failure
- **SC-013**: Soft-deleted media is permanently purged within 31 days of deletion
- **SC-014**: Partial upload failures result in complete rollback with no orphaned variants in cloud storage

## Clarifications

### Session 2025-12-10

- Q: Should media operations emit events/logs for observability? → A: Log processing failures and slow operations (>30s) only
- Q: Should there be rate limiting on media uploads? → A: No rate limiting (rely on file size limits only)
- Q: Should variants larger than original be generated? → A: Skip variants larger than original (no upscaling)
- Q: Should byte-identical file uploads be deduplicated? → A: Create separate assets (no deduplication)
- Q: How should uploaded files be validated for authenticity? → A: Validate magic bytes match declared MIME type

## Assumptions

- Phase 1 infrastructure is complete: S3 bucket, CloudFront distribution, and Laravel Horizon queue system are configured and operational
- Phase 2 is complete: content_relations table exists with polymorphic relationship support for linking content models
- AWS credentials are properly configured via environment variables (AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET)
- Image processing binaries are installed in the Docker environment or available via PHP extensions (Intervention Image with GD or Imagick driver)
- Admin authentication system from Phase 1 is functional for protecting media management endpoints
- Queue workers are running and processing jobs reliably via Laravel Horizon

## Out of Scope

- Filament admin resources and pages (deferred to Phase 5: Admin Interface)
- Media library UI with table/grid views (deferred to Phase 5)
- Upload modal and image picker components (deferred to Phase 5)
- Tag management UI (deferred to Phase 5)
- Fallback image admin picker UI (deferred to Phase 4: Global Settings)
- PDF and document handling (future feature)
- Video processing or transcoding (future feature - videos stored as-is)
- Virus/malware scanning (infrastructure concern outside application scope)
- Image cropping UI (future feature - focal point is data-only in this phase)
- AVIF format support (future enhancement)
- External CDN cache invalidation (handled by CloudFront TTL settings)

## Dependencies

- **Phase 1**: Laravel Sail environment, S3/CloudFront configuration, Horizon queue system
- **Phase 2**: content_relations polymorphic relationship table structure
- **External**: AWS S3 for storage, CloudFront for CDN delivery, Intervention Image for processing
