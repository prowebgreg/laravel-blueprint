# Contract: Horizon Queue Dashboard

**Feature Branch**: `002-system-foundation`
**Date**: 2025-12-05
**Spec Reference**: FR-023 through FR-029

## Overview

This contract defines the Laravel Horizon queue monitoring dashboard at `/horizon`.

---

## Access Control

### Authorization Gate

```php
Gate::define('viewHorizon', function (User $user) {
    return $user->canAccessPanel(Filament::getPanel('admin'));
});
```

**Requirements**:
- User must be authenticated
- User must have Filament admin panel access
- Non-local environments enforce gate check

---

## Endpoints

### GET /horizon

**Description**: Horizon dashboard entry point

**Authentication**: Required (admin only)

**Response (Unauthorized)**:
- Status: `403 Forbidden`
- Body: `Unauthorized.`

**Response (Unauthenticated)**:
- Status: `302 Found`
- Redirect: `/admin/login`

**Response (Authorized)**:
- Status: `200 OK`
- Body: Horizon Vue.js dashboard HTML

---

### GET /horizon/api/stats

**Description**: Queue statistics summary

**Authentication**: Required (admin only)

**Response**:
```json
{
  "jobsPerMinute": 12.5,
  "processes": 3,
  "queueWithMaxRuntime": "default",
  "queueWithMaxThroughput": "default",
  "recentlyFailed": 2,
  "recentJobs": 150,
  "status": "running",
  "wait": {
    "redis:default": 0
  }
}
```

---

### GET /horizon/api/workload

**Description**: Current queue workload

**Authentication**: Required (admin only)

**Response**:
```json
[
  {
    "name": "default",
    "length": 5,
    "wait": 0,
    "processes": 3
  }
]
```

---

### GET /horizon/api/masters

**Description**: Horizon master supervisors

**Authentication**: Required (admin only)

**Response**:
```json
[
  {
    "name": "master-abc123",
    "pid": 1234,
    "status": "running",
    "supervisors": [...]
  }
]
```

---

### GET /horizon/api/monitoring

**Description**: Monitored tags and jobs

**Authentication**: Required (admin only)

**Response**:
```json
{
  "tags": ["user:1", "order:123"],
  "jobs": [...]
}
```

---

### GET /horizon/api/jobs/recent

**Description**: Recently processed jobs

**Authentication**: Required (admin only)

**Query Parameters**:
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| starting_at | integer | -1 | Pagination cursor |

**Response**:
```json
{
  "jobs": [
    {
      "id": "abc123",
      "name": "App\\Jobs\\ProcessMedia",
      "queue": "default",
      "payload": {...},
      "status": "completed",
      "completed_at": "2025-12-05T10:30:00Z"
    }
  ],
  "total": 150
}
```

---

### GET /horizon/api/jobs/failed

**Description**: Failed jobs list

**Authentication**: Required (admin only)

**Query Parameters**:
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| starting_at | integer | -1 | Pagination cursor |

**Response**:
```json
{
  "jobs": [
    {
      "id": "def456",
      "name": "App\\Jobs\\ProcessMedia",
      "queue": "default",
      "payload": {...},
      "exception": "Error message...",
      "failed_at": "2025-12-05T10:25:00Z",
      "retried_by": null
    }
  ],
  "total": 2
}
```

---

### GET /horizon/api/jobs/failed/{id}

**Description**: Failed job details

**Authentication**: Required (admin only)

**Path Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| id | string | Failed job UUID |

**Response (Found)**:
```json
{
  "id": "def456",
  "name": "App\\Jobs\\ProcessMedia",
  "queue": "default",
  "connection": "redis",
  "payload": {
    "displayName": "App\\Jobs\\ProcessMedia",
    "job": "Illuminate\\Queue\\CallQueuedHandler@call",
    "data": {...}
  },
  "exception": "Full exception trace...",
  "failed_at": "2025-12-05T10:25:00Z",
  "retried_by": null
}
```

**Response (Not Found)**:
- Status: `404 Not Found`

---

### POST /horizon/api/jobs/retry/{id}

**Description**: Retry a failed job

**Authentication**: Required (admin only)

**Path Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| id | string | Failed job UUID |

**Response (Success)**:
- Status: `200 OK`
- Action: Job re-queued for processing

**Response (Not Found)**:
- Status: `404 Not Found`

---

### DELETE /horizon/api/jobs/failed/{id}

**Description**: Delete a failed job

**Authentication**: Required (admin only)

**Path Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| id | string | Failed job UUID |

**Response (Success)**:
- Status: `200 OK`
- Action: Job permanently removed

---

### GET /horizon/api/metrics/jobs

**Description**: Job throughput metrics

**Authentication**: Required (admin only)

**Response**:
```json
[
  {
    "name": "App\\Jobs\\ProcessMedia",
    "throughput": 45,
    "runtime": 2.5,
    "snapshotted_at": "2025-12-05T10:00:00Z"
  }
]
```

---

### GET /horizon/api/metrics/queues

**Description**: Queue throughput metrics

**Authentication**: Required (admin only)

**Response**:
```json
[
  {
    "name": "default",
    "throughput": 120,
    "runtime": 1.8,
    "wait": 0,
    "snapshotted_at": "2025-12-05T10:00:00Z"
  }
]
```

---

## Queue Configuration

### Supervisor Settings

| Setting | Local | Production | Description |
|---------|-------|------------|-------------|
| maxProcesses | 3 | 10 | Maximum worker processes |
| tries | 3 | 3 | Retry attempts before failure |
| timeout | 60s | 300s | Job execution timeout |
| backoff | none | [10, 30, 60] | Retry delays (seconds) |
| balance | auto | auto | Auto-scaling strategy |

### Queue Connection

```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'redis'),

'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
        'block_for' => null,
        'after_commit' => false,
    ],
],
```

---

## Job Lifecycle

### States

| State | Description |
|-------|-------------|
| pending | Job queued, waiting for worker |
| reserved | Job picked up by worker |
| completed | Job finished successfully |
| failed | Job failed (may retry) |
| permanently_failed | Job failed after max retries |

### Retry Logic

```
Attempt 1 → Fail → Wait 10s
Attempt 2 → Fail → Wait 30s
Attempt 3 → Fail → Wait 60s
Attempt 4 → Permanently Failed
```

---

## Metrics Retention

| Metric Type | Retention |
|-------------|-----------|
| Recent jobs | 60 minutes |
| Pending jobs | 60 minutes |
| Completed jobs | 60 minutes |
| Recent failed | 10080 minutes (1 week) |
| Failed jobs | 10080 minutes (1 week) |
| Monitored tags | 10080 minutes (1 week) |
| Job snapshots | 24 hours |
| Queue snapshots | 24 hours |

---

## Dashboard Sections

| Section | Description | Key Metrics |
|---------|-------------|-------------|
| Overview | System health | Jobs/min, processes, status |
| Monitoring | Tagged jobs | Custom tag tracking |
| Metrics | Performance | Throughput, runtime, wait times |
| Recent Jobs | Activity log | Recently processed jobs |
| Failed Jobs | Error tracking | Failed jobs with exceptions |
| Batches | Batch jobs | Multi-job batch status |

---

## Security Requirements

1. **Gate Authorization**: All endpoints check `viewHorizon` gate
2. **No CSRF on API**: API endpoints use session auth, not CSRF tokens
3. **Same-Origin Only**: API requests must originate from Horizon dashboard
4. **No External Access**: Horizon should not be exposed to public internet in production
5. **Admin Only**: Only users with admin panel access can view queue data
