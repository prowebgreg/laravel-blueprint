# Research: System Foundation (Phase 1)

**Feature Branch**: `002-system-foundation`
**Date**: 2025-12-05
**Status**: Complete

## Overview

This document captures technical research and decisions for the System Foundation phase. All NEEDS CLARIFICATION items from the Technical Context have been resolved.

---

## Decision 1: Sail Dockerfile Customization for Image Binaries

### Decision
Publish and customize the default Laravel Sail Dockerfile to include image processing binaries (jpegoptim, optipng, cwebp).

### Rationale
- Official Laravel documentation recommends `sail:publish` for customization
- Provides full control over the Docker image
- Maintains compatibility with Sail's conventions
- Allows for complete customization while keeping Sail's structure

### Alternatives Considered
1. **Extend the Dockerfile** - Rejected: Less control, requires maintaining separate inheritance chain
2. **Install binaries at runtime** - Rejected: Slower startup, not persistent across rebuilds
3. **Use separate image optimization container** - Rejected: Over-engineering for this use case

### Implementation

**Step 1**: Publish Sail Dockerfiles
```bash
sail artisan sail:publish
```

**Step 2**: Modify `/docker/8.3/Dockerfile` - add after existing apt-get commands:
```dockerfile
# Install image optimization binaries
RUN apt-get update \
    && apt-get install -y jpegoptim optipng webp \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
```

**Step 3**: Update `docker-compose.yml` image name:
```yaml
image: blueprint-cms/app
```

**Step 4**: Rebuild containers:
```bash
sail build --no-cache
```

### Required Packages
| Binary | APT Package | Purpose |
|--------|-------------|---------|
| jpegoptim | `jpegoptim` | JPEG optimization |
| optipng | `optipng` | PNG optimization |
| cwebp | `webp` | WebP conversion |

---

## Decision 2: S3 Disk Configuration Strategy

### Decision
Use Laravel's **scoped driver** to create path-prefixed disks (`s3-temp`, `s3-permanent`) that share the same underlying S3 connection, with local filesystem equivalents for development.

### Rationale
- Cleaner application code: `Storage::disk('s3-temp')` vs `Storage::disk('s3')->put('temp/...')`
- Environment-based switching handled at disk level
- Single S3 connection configuration to maintain
- Each scoped disk can have different visibility settings

### Alternatives Considered
1. **Single disk with manual path prefixes** - Rejected: Error-prone, harder to switch environments
2. **Multiple independent S3 disks** - Rejected: Duplicated configuration, harder to maintain
3. **Custom filesystem adapter** - Rejected: Over-engineering, no advantage over scoped driver

### Implementation

**filesystems.php configuration:**
```php
'disks' => [
    // Base S3 disk
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-west-1'),
        'bucket' => env('AWS_BUCKET', 'laravel-blueprint-assets'),
        'url' => env('AWS_URL'), // CloudFront URL
        'visibility' => 'public',
    ],

    // Scoped disk for temporary processing files
    's3-temp' => [
        'driver' => 'scoped',
        'disk' => 's3',
        'prefix' => 'temp',
        'visibility' => 'private',
    ],

    // Scoped disk for permanent/finalized files
    's3-permanent' => [
        'driver' => 'scoped',
        'disk' => 's3',
        'prefix' => 'permanent',
        'visibility' => 'public',
    ],

    // Local equivalents for development
    'local-temp' => [
        'driver' => 'scoped',
        'disk' => 'local',
        'prefix' => 'temp',
    ],

    'local-permanent' => [
        'driver' => 'scoped',
        'disk' => 'local',
        'prefix' => 'permanent',
    ],
],
```

### Environment Variables Structure
```env
# Development
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=

# Production
FILESYSTEM_DISK=s3-permanent
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-west-1
AWS_BUCKET=laravel-blueprint-assets
AWS_URL=https://dxrnpyjkukgbc.cloudfront.net
```

### Required Packages
```bash
composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
composer require league/flysystem-path-prefixing "^3.0"
```

---

## Decision 3: Filament V3 Admin Panel Configuration

### Decision
Install **Filament V3** (not V4) for Laravel 12 with built-in authentication, password reset, and 2-hour session timeout.

### Rationale
- Filament V3 is mature and stable with Laravel 12 support
- Built-in login page, password reset, and profile management
- Session timeout configurable via Laravel's `SESSION_LIFETIME`
- Seamless integration with existing User model

### Alternatives Considered
1. **Filament V4** - Considered but V3 is more stable; V4 is newer (Aug 2025) and may have edge cases
2. **Custom auth scaffolding** - Rejected: Duplicates Filament's built-in functionality
3. **Laravel Breeze + Custom Admin** - Rejected: More work, less integrated

### Implementation

**Step 1**: Install Filament
```bash
composer require filament/filament:"^3.0"
php artisan filament:install --panels
```

**Step 2**: Configure AdminPanelProvider (`app/Providers/Filament/AdminPanelProvider.php`):
```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->passwordReset()
        ->authGuard('web')
        ->authPasswordBroker('users')
        ->colors(['primary' => '#4F46E5'])
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
        ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
        ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets');
}
```

**Step 3**: Configure 2-hour session timeout (`.env`):
```env
SESSION_LIFETIME=120
SESSION_DRIVER=database
```

**Step 4**: Update User model for Filament access control:
```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // All users can access admin panel
    }
}
```

### Admin User Seeding (dev only)
```php
// database/seeders/AdminUserSeeder.php
User::firstOrCreate(
    ['email' => 'info@proweb.ai'],
    [
        'name' => 'Admin',
        'password' => Hash::make('Levonik2007@'),
        'email_verified_at' => now(),
    ]
);
```

---

## Decision 4: Horizon Authorization Gate Implementation

### Decision
Implement Horizon authorization gate that integrates with Filament's `canAccessPanel()` method, ensuring only authenticated admin users can access `/horizon`.

### Rationale
- Single source of truth for admin authorization
- Consistent with Filament's access control pattern
- Gate checks both authentication and admin status
- Production-safe (denies access by default in non-local environments)

### Alternatives Considered
1. **Separate admin role check** - Rejected: Duplicates logic, harder to maintain
2. **Email domain whitelist** - Rejected: Too restrictive for this use case
3. **Environment-only restriction** - Rejected: Need admin access in staging/production

### Implementation

**HorizonServiceProvider.php:**
```php
protected function gate(): void
{
    Gate::define('viewHorizon', function (User $user) {
        // Use Filament's canAccessPanel for consistent authorization
        return $user->canAccessPanel(
            \Filament\Facades\Filament::getPanel('admin')
        );
    });
}
```

**Horizon Configuration (`config/horizon.php`):**
```php
'defaults' => [
    'supervisor-1' => [
        'connection' => 'redis',
        'queue' => ['default'],
        'balance' => 'auto',
        'maxProcesses' => 1,
        'tries' => 3, // 3 retry attempts
        'timeout' => 60,
    ],
],

'environments' => [
    'production' => [
        'supervisor-1' => [
            'maxProcesses' => 10,
            'tries' => 3,
            'timeout' => 300,
            'backoff' => [10, 30, 60], // Exponential backoff
        ],
    ],
    'local' => [
        'supervisor-1' => [
            'maxProcesses' => 3,
            'tries' => 3,
            'timeout' => 60,
        ],
    ],
],
```

---

## Decision 5: Laravel Sail Services Configuration

### Decision
Configure Laravel Sail with PostgreSQL 17, Redis 7, and Mailpit as default services with persistent volumes.

### Rationale
- PostgreSQL 17 provides latest features and JSONB support
- Redis 7 for caching, queues, and session storage (Constitution requirement)
- Mailpit for local email testing with web UI
- Named volumes ensure data persistence across restarts

### Alternatives Considered
1. **MySQL instead of PostgreSQL** - Rejected: Constitution specifies PostgreSQL 17
2. **Docker standalone** - Rejected: Sail provides better Laravel integration
3. **In-memory Redis** - Rejected: Need persistence for development data

### Implementation

**docker-compose.yml key services:**
```yaml
services:
    laravel.test:
        build:
            context: ./docker/8.3
            dockerfile: Dockerfile
        image: blueprint-cms/app
        ports:
            - '${APP_PORT:-80}:80'
            - '${VITE_PORT:-5173}:${VITE_PORT:-5173}'
        depends_on:
            - pgsql
            - redis
            - mailpit

    pgsql:
        image: 'postgres:17'
        ports:
            - '${FORWARD_DB_PORT:-5432}:5432'
        environment:
            POSTGRES_DB: '${DB_DATABASE}'
            POSTGRES_USER: '${DB_USERNAME}'
            POSTGRES_PASSWORD: '${DB_PASSWORD:-secret}'
        volumes:
            - 'sail-pgsql:/var/lib/postgresql/data'

    redis:
        image: 'redis:7-alpine'
        ports:
            - '${FORWARD_REDIS_PORT:-6379}:6379'
        volumes:
            - 'sail-redis:/data'

    mailpit:
        image: 'axllent/mailpit:latest'
        ports:
            - '${FORWARD_MAILPIT_PORT:-1025}:1025'
            - '${FORWARD_MAILPIT_DASHBOARD_PORT:-8025}:8025'

volumes:
    sail-pgsql:
        driver: local
    sail-redis:
        driver: local
```

**.env configuration:**
```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=blueprint_db
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### Service Ports Summary
| Service | Internal | External | Access URL |
|---------|----------|----------|------------|
| Application | 80 | 80 | http://localhost |
| PostgreSQL | 5432 | 5432 | localhost:5432 |
| Redis | 6379 | 6379 | localhost:6379 |
| Mailpit SMTP | 1025 | 1025 | - |
| Mailpit Web | 8025 | 8025 | http://localhost:8025 |

---

## Decision 6: File Upload Size Limit Configuration

### Decision
Configure 10MB file upload limit at PHP, web server, and Laravel validation layers.

### Rationale
- Multi-layer validation prevents oversized uploads at earliest point
- PHP configuration catches before application code runs
- Laravel validation provides user-friendly error messages
- Consistent limit across all layers

### Implementation

**PHP Configuration (Docker/php.ini):**
```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 128M
```

**Laravel Validation:**
```php
'file' => 'required|file|max:10240' // 10MB in KB
```

---

## Package Requirements Summary

| Package | Version | Purpose |
|---------|---------|---------|
| `filament/filament` | ^3.0 | Admin panel |
| `laravel/horizon` | ^5.0 | Queue monitoring |
| `league/flysystem-aws-s3-v3` | ^3.0 | S3 filesystem driver |
| `league/flysystem-path-prefixing` | ^3.0 | Scoped disk driver |
| `pestphp/pest` | ^3.0 | Testing framework |
| `pestphp/pest-plugin-laravel` | ^3.0 | Laravel test helpers |

---

## Environment Variables Summary

### Required for All Environments
```env
APP_NAME="Blueprint CMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=blueprint_db
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### Production-Only
```env
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-west-1
AWS_BUCKET=laravel-blueprint-assets
AWS_URL=https://dxrnpyjkukgbc.cloudfront.net
FILESYSTEM_DISK=s3-permanent
```

---

## Research Sources

- Laravel Sail Documentation (12.x)
- Filament V3 Documentation
- Laravel Horizon Documentation
- Laravel Filesystem Documentation
- PostgreSQL 17 Docker Hub
- Redis 7 Docker Hub
- AWS S3 Best Practices
