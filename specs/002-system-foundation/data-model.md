# Data Model: System Foundation (Phase 1)

**Feature Branch**: `002-system-foundation`
**Date**: 2025-12-05
**Status**: Complete

## Overview

This document defines the data models for the System Foundation phase. These entities support authentication, queue monitoring, and file storage functionality.

---

## Entity: User

Represents an administrator with authentication credentials and panel access control.

### Schema

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | bigint | PK, auto-increment | Primary identifier |
| `name` | varchar(255) | required | User's display name |
| `email` | varchar(255) | required, unique | Email for authentication |
| `email_verified_at` | timestamp | nullable | Email verification timestamp |
| `password` | varchar(255) | required, hashed | Bcrypt hashed password |
| `remember_token` | varchar(100) | nullable | Session remember token |
| `created_at` | timestamp | auto | Record creation timestamp |
| `updated_at` | timestamp | auto | Record update timestamp |

### Relationships

| Relationship | Type | Target | Description |
|--------------|------|--------|-------------|
| sessions | hasMany | Session | User's active sessions |

### Validation Rules

| Field | Rules |
|-------|-------|
| `name` | required, string, max:255 |
| `email` | required, string, email, max:255, unique:users |
| `password` | required, string, min:8, confirmed |

### Interfaces

```php
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool;
}
```

### Seeding (Development Only)

```php
[
    'name' => 'Admin',
    'email' => 'info@proweb.ai',
    'password' => Hash::make('Levonik2007@'),
    'email_verified_at' => now(),
]
```

**Constraint**: This seed MUST only run in `local` and `development` environments.

---

## Entity: Session

Tracks authenticated user sessions with 2-hour timeout.

### Schema

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | varchar(255) | PK | Session identifier |
| `user_id` | bigint | nullable, FK(users.id) | Associated user (nullable for guests) |
| `ip_address` | varchar(45) | nullable | Client IP address |
| `user_agent` | text | nullable | Browser user agent string |
| `payload` | longtext | required | Serialized session data |
| `last_activity` | integer | required, indexed | Unix timestamp of last activity |

### Relationships

| Relationship | Type | Target | Description |
|--------------|------|--------|-------------|
| user | belongsTo | User | Session owner |

### Timeout Configuration

```php
// config/session.php
'lifetime' => env('SESSION_LIFETIME', 120), // 2 hours in minutes
'expire_on_close' => false,
```

### Indexes

| Index Name | Columns | Type |
|------------|---------|------|
| sessions_user_id_index | user_id | btree |
| sessions_last_activity_index | last_activity | btree |

---

## Entity: Job (Queue)

Represents a queued background task managed by Laravel Horizon.

### Schema (jobs table)

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | bigint | PK, auto-increment | Primary identifier |
| `queue` | varchar(255) | required, indexed | Queue name |
| `payload` | longtext | required | Serialized job data |
| `attempts` | tinyint unsigned | required | Current attempt count |
| `reserved_at` | int unsigned | nullable | Timestamp when job was reserved |
| `available_at` | int unsigned | required | Timestamp when job becomes available |
| `created_at` | int unsigned | required | Job creation timestamp |

### Schema (failed_jobs table)

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | bigint | PK, auto-increment | Primary identifier |
| `uuid` | varchar(255) | required, unique | Unique job identifier |
| `connection` | text | required | Queue connection name |
| `queue` | text | required | Queue name |
| `payload` | longtext | required | Serialized job data |
| `exception` | longtext | required | Exception message and trace |
| `failed_at` | timestamp | required, default:now | Failure timestamp |

### State Machine

```
[Pending] → [Processing] → [Completed]
              ↓
         [Failed] → (retry up to 3x) → [Permanently Failed]
```

### Configuration

```php
// config/horizon.php
'tries' => 3,           // Maximum retry attempts
'timeout' => 60,        // Job timeout in seconds (local)
'backoff' => [10, 30, 60], // Exponential backoff (production)
```

### Indexes

| Table | Index Name | Columns | Type |
|-------|------------|---------|------|
| jobs | jobs_queue_index | queue | btree |
| failed_jobs | failed_jobs_uuid_unique | uuid | unique |

---

## Entity: File (Conceptual)

Represents an uploaded media file stored in S3 or local filesystem.

**Note**: This is a conceptual entity for Phase 1. The full Media model with database persistence will be implemented in Phase 3 (Media Engine).

### Storage Structure

```
s3://laravel-blueprint-assets/
├── temp/           # Temporary processing folder (private)
│   └── {uuid}/     # Per-upload subfolder
│       └── original.{ext}
└── permanent/      # Finalized files (public via CloudFront)
    └── {year}/{month}/
        └── {uuid}.{ext}
```

### Properties (Runtime)

| Property | Type | Description |
|----------|------|-------------|
| `disk` | string | Storage disk name (s3-temp, s3-permanent, local-temp, local-permanent) |
| `path` | string | File path within disk |
| `url` | string | Public URL (CloudFront for S3, local URL for filesystem) |
| `size` | integer | File size in bytes |
| `mime_type` | string | MIME type (e.g., image/jpeg) |

### Validation Rules

| Rule | Value | Description |
|------|-------|-------------|
| max_size | 10MB | Maximum file upload size |
| allowed_types | image/jpeg, image/png, image/webp, image/gif, application/pdf | Allowed MIME types |

### URL Generation

```php
// Local development
Storage::disk('local-permanent')->url('path/file.jpg');
// → http://localhost/storage/permanent/path/file.jpg

// Production (S3 + CloudFront)
Storage::disk('s3-permanent')->url('path/file.jpg');
// → https://dxrnpyjkukgbc.cloudfront.net/permanent/path/file.jpg
```

---

## Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐
│    User     │       │   Session   │
├─────────────┤       ├─────────────┤
│ id          │───┬───│ id          │
│ name        │   │   │ user_id     │
│ email       │   │   │ ip_address  │
│ password    │   │   │ user_agent  │
│ ...         │   │   │ payload     │
└─────────────┘   │   │ last_activity│
                  │   └─────────────┘
                  │
                  │   ┌─────────────┐
                  │   │    Job      │
                  │   ├─────────────┤
                  └───│ id          │
                      │ queue       │
                      │ payload     │
                      │ attempts    │
                      │ ...         │
                      └─────────────┘
```

---

## Database Migrations

### Required Migrations

1. `0001_01_01_000000_create_users_table.php` - **Exists** (Laravel default)
2. `0001_01_01_000001_create_cache_table.php` - **Exists** (Laravel default)
3. `0001_01_01_000002_create_jobs_table.php` - **Exists** (Laravel default)
4. `xxxx_xx_xx_create_sessions_table.php` - **To Create** if using database sessions

### Session Migration (if needed)

```bash
php artisan make:session-table
php artisan migrate
```

---

## PostgreSQL-Specific Considerations

### JSONB Usage (Future Phases)

PostgreSQL JSONB will be used for:
- Content block data storage (Phase 2)
- SEO metadata (Phase 2)
- Flexible field storage

### Indexes for Performance

```sql
-- User email lookup
CREATE INDEX users_email_index ON users (email);

-- Session cleanup
CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

-- Queue processing
CREATE INDEX jobs_queue_reserved_at_index ON jobs (queue, reserved_at);
```

---

## Data Integrity Rules

### User
- Email must be unique across all users
- Password must be at least 8 characters
- Email verification optional but recommended for production

### Session
- Sessions expire after 2 hours of inactivity
- Orphaned sessions (user deleted) should be cleaned up

### Job
- Jobs retry up to 3 times before permanent failure
- Failed jobs preserved for manual review
- Job payload must not exceed 64KB

---

## Security Considerations

### Password Storage
- Passwords hashed with bcrypt (cost factor 12)
- Never log or expose raw passwords
- Password reset tokens expire after 60 minutes

### Session Security
- Session IDs regenerated on login
- Sessions invalidated on logout
- IP address tracked for audit

### Job Security
- Sensitive data in job payloads must be encrypted
- Failed job exceptions may expose stack traces (review before production)
