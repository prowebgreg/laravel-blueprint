# Quickstart: System Foundation (Phase 1)

**Feature Branch**: `002-system-foundation`
**Date**: 2025-12-05

## Prerequisites

Before starting, ensure you have:

- [ ] Docker Desktop installed and running
- [ ] Git installed
- [ ] Node.js 20.x LTS or newer installed
- [ ] Composer 2.x installed
- [ ] Ports available: 80, 5432, 6379, 1025, 8025, 5173

---

## Quick Setup (5 minutes)

```bash
# 1. Clone and enter directory
git clone <repository-url> blueprint-cms
cd blueprint-cms

# 2. Copy environment file
cp .env.example .env

# 3. Install PHP dependencies (required before Sail can start)
composer install

# 4. Generate application key
php artisan key:generate

# 5. Start Sail (Docker)
./vendor/bin/sail up -d

# 6. Install NPM dependencies (inside container)
./vendor/bin/sail npm install

# 7. Run migrations and seed
./vendor/bin/sail artisan migrate --seed

# 8. Build frontend assets
./vendor/bin/sail npm run build

# 9. Link storage directory
./vendor/bin/sail artisan storage:link
```

---

## Access Points

| Service | URL | Credentials |
|---------|-----|-------------|
| Application | http://localhost | - |
| Admin Panel | http://localhost/admin | info@proweb.ai / Levonik2007@ |
| Horizon Dashboard | http://localhost/horizon | (requires admin login) |
| Mailpit | http://localhost:8025 | - |

---

## Sail Alias (Recommended)

Add to your `~/.zshrc` or `~/.bashrc`:

```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

Then restart your shell. Now you can use `sail` instead of `./vendor/bin/sail`.

---

## Common Commands

### Start/Stop Development Environment

```bash
# Start all containers (detached)
sail up -d

# Stop all containers (data persists)
sail stop

# Stop and remove containers (data persists in volumes)
sail down

# View container logs
sail logs -f

# View specific service logs
sail logs -f pgsql
sail logs -f redis
```

### Database Operations

```bash
# Run migrations
sail artisan migrate

# Run migrations with seeding
sail artisan migrate --seed

# Fresh migration (drops all tables)
sail artisan migrate:fresh --seed

# Database CLI (PostgreSQL)
sail exec pgsql psql -U sail -d blueprint_db

# Tinker (REPL)
sail artisan tinker
```

### Queue Operations

```bash
# Start Horizon (queue worker)
sail artisan horizon

# Process queue manually (single run)
sail artisan queue:work --once

# View failed jobs
sail artisan queue:failed

# Retry all failed jobs
sail artisan queue:retry all

# Clear all failed jobs
sail artisan queue:flush
```

### Cache Operations

```bash
# Clear all caches
sail artisan optimize:clear

# Clear specific caches
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear

# Rebuild caches
sail artisan optimize
```

### Testing

```bash
# Run all tests
sail artisan test

# Run with coverage
sail artisan test --coverage

# Run specific test file
sail artisan test tests/Feature/ExampleTest.php

# Run tests in parallel
sail artisan test --parallel
```

### Code Quality

```bash
# Fix code style (Pint)
sail pint

# Check code style without fixing
sail pint --test

# Static analysis (PHPStan) - when installed
sail phpstan analyse
```

---

## Development Workflow

### 1. Starting Your Day

```bash
# Start the environment
sail up -d

# Check all services are healthy
sail ps

# Start Horizon in background terminal
sail artisan horizon
```

### 2. Making Changes

```bash
# Watch frontend assets
sail npm run dev

# Check for PHP errors
sail pint --test

# Run tests
sail artisan test
```

### 3. Testing Email

1. Trigger an action that sends email (e.g., password reset)
2. Open http://localhost:8025
3. View caught emails in Mailpit

### 4. Ending Your Day

```bash
# Stop containers
sail stop
```

---

## Troubleshooting

### Port Already in Use

```bash
# Find what's using port 80
lsof -i :80

# Use different port
APP_PORT=8080 sail up -d
```

### Permission Denied on Storage

```bash
sail artisan storage:link
sail exec laravel.test chmod -R 775 storage bootstrap/cache
```

### Database Connection Refused

1. Ensure PostgreSQL container is running: `sail ps`
2. Wait for healthcheck: `sail logs pgsql`
3. Verify `.env` has `DB_HOST=pgsql`

### Redis Connection Failed

1. Ensure Redis container is running: `sail ps`
2. Verify `.env` has `REDIS_HOST=redis`
3. Test connection: `sail exec redis redis-cli ping`

### Composer Out of Memory

```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### Rebuild Containers

```bash
# After Dockerfile changes
sail build --no-cache
sail up -d
```

---

## Environment Variables Reference

### Essential Variables

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
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### S3/CloudFront (Production)

```env
FILESYSTEM_DISK=s3-permanent

AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-west-1
AWS_BUCKET=laravel-blueprint-assets
AWS_URL=https://dxrnpyjkukgbc.cloudfront.net
```

---

## Admin Panel Overview

### Login (http://localhost/admin)

- Email: `info@proweb.ai`
- Password: `Levonik2007@`

### Dashboard Features

- System status overview
- Quick actions
- Recent activity

### Password Reset

1. Click "Forgot password?" on login page
2. Enter email address
3. Check Mailpit (http://localhost:8025) for reset link
4. Click link and set new password

---

## Horizon Dashboard Overview

### Access (http://localhost/horizon)

Requires admin authentication. Login to admin panel first.

### Sections

- **Dashboard**: Queue health overview
- **Monitoring**: Tagged job tracking
- **Metrics**: Job performance stats
- **Recent Jobs**: Activity log
- **Failed Jobs**: Error tracking with retry

### Key Metrics

- Jobs per minute
- Active processes
- Queue wait times
- Failed job count

---

## File Storage

### Local Development

Files stored in `storage/app/`:
- `storage/app/temp/` - Temporary processing
- `storage/app/permanent/` - Finalized files

### Testing S3 Integration

```php
// In Tinker
Storage::disk('s3-temp')->put('test.txt', 'Hello World');
Storage::disk('s3-temp')->get('test.txt');
Storage::disk('s3-temp')->delete('test.txt');
```

---

## Next Steps

After completing setup:

1. **Verify Environment**: Run `sail artisan about` to see system info
2. **Run Tests**: `sail artisan test` to verify everything works
3. **Explore Admin**: Login and familiarize with Filament
4. **Check Horizon**: Ensure queue processing is working
5. **Test Email**: Trigger password reset to verify Mailpit

---

## Getting Help

- **Laravel Docs**: https://laravel.com/docs/12.x
- **Filament Docs**: https://filamentphp.com/docs
- **Horizon Docs**: https://laravel.com/docs/12.x/horizon
- **Sail Docs**: https://laravel.com/docs/12.x/sail
