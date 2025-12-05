# Contract: Storage Operations

**Feature Branch**: `002-system-foundation`
**Date**: 2025-12-05
**Spec Reference**: FR-008 through FR-014, FR-030

## Overview

This contract defines file storage operations for S3/CloudFront in production and local filesystem in development.

---

## Storage Disks

### Configuration Summary

| Disk | Driver | Environment | Path Prefix | Visibility |
|------|--------|-------------|-------------|------------|
| `local` | local | all | `storage/app/private` | private |
| `local-temp` | scoped | development | `temp/` | private |
| `local-permanent` | scoped | development | `permanent/` | public |
| `s3` | s3 | production | (root) | public |
| `s3-temp` | scoped | production | `temp/` | private |
| `s3-permanent` | scoped | production | `permanent/` | public |

---

## Operations

### Upload File to Temporary Storage

**Purpose**: Store file for processing before finalization

**PHP Interface**:
```php
Storage::disk('s3-temp')->put($path, $contents);
Storage::disk('s3-temp')->putFile($path, $uploadedFile);
Storage::disk('s3-temp')->putFileAs($path, $uploadedFile, $filename);
```

**Parameters**:
| Parameter | Type | Constraints |
|-----------|------|-------------|
| path | string | Max 255 chars, no `..` traversal |
| contents | string\|resource | Max 10MB |
| uploadedFile | UploadedFile | Max 10MB |
| filename | string | Max 255 chars, valid filename |

**Response**:
- Success: Returns stored path (string)
- Failure: Throws exception

**Error Cases**:
| Error | Description | Action |
|-------|-------------|--------|
| FileTooLarge | File exceeds 10MB | Return validation error |
| S3Exception | AWS connection failed | Log error, display user message |
| DiskNotFound | Invalid disk configuration | Throw exception |

---

### Move File to Permanent Storage

**Purpose**: Move processed file from temp to permanent location

**PHP Interface**:
```php
// Copy then delete (cross-disk move)
$contents = Storage::disk('s3-temp')->get($tempPath);
Storage::disk('s3-permanent')->put($permanentPath, $contents);
Storage::disk('s3-temp')->delete($tempPath);

// Or using streams for large files
$stream = Storage::disk('s3-temp')->readStream($tempPath);
Storage::disk('s3-permanent')->writeStream($permanentPath, $stream);
Storage::disk('s3-temp')->delete($tempPath);
```

**Path Convention**:
- Temp: `{uuid}/original.{ext}`
- Permanent: `{year}/{month}/{uuid}.{ext}`

**Example**:
```php
// Temp path
$tempPath = '550e8400-e29b-41d4-a716-446655440000/original.jpg';

// Permanent path
$permanentPath = '2025/12/550e8400-e29b-41d4-a716-446655440000.jpg';
```

---

### Get Public URL

**Purpose**: Generate CDN URL for public file access

**PHP Interface**:
```php
$url = Storage::disk('s3-permanent')->url($path);
```

**Response (Production)**:
```
https://dxrnpyjkukgbc.cloudfront.net/permanent/2025/12/550e8400.jpg
```

**Response (Development)**:
```
http://localhost/storage/permanent/2025/12/550e8400.jpg
```

---

### Delete File

**Purpose**: Remove file from storage

**PHP Interface**:
```php
Storage::disk('s3-permanent')->delete($path);
Storage::disk('s3-permanent')->delete([$path1, $path2]); // Multiple
```

**Response**:
- Success: Returns `true`
- Not Found: Returns `true` (idempotent)
- Failure: Throws exception

---

### Check File Exists

**Purpose**: Verify file presence before operations

**PHP Interface**:
```php
$exists = Storage::disk('s3-permanent')->exists($path);
```

**Response**: `bool`

---

### Get File Size

**Purpose**: Retrieve file size for validation/display

**PHP Interface**:
```php
$size = Storage::disk('s3-permanent')->size($path); // bytes
```

**Response**: `int` (bytes)

---

### Get File MIME Type

**Purpose**: Determine file type for processing decisions

**PHP Interface**:
```php
$mimeType = Storage::disk('s3-permanent')->mimeType($path);
```

**Response**: `string` (e.g., `image/jpeg`)

---

## Validation Rules

### File Upload Validation

```php
use Illuminate\Validation\Rules\File;

// Form Request
public function rules(): array
{
    return [
        'file' => [
            'required',
            File::default()
                ->max(10 * 1024) // 10MB in KB
                ->types([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                    'application/pdf',
                ]),
        ],
    ];
}
```

### Allowed File Types

| MIME Type | Extension | Category |
|-----------|-----------|----------|
| image/jpeg | .jpg, .jpeg | Image |
| image/png | .png | Image |
| image/webp | .webp | Image |
| image/gif | .gif | Image |
| application/pdf | .pdf | Document |

### Size Limits

| Limit | Value | Applied At |
|-------|-------|------------|
| PHP upload_max_filesize | 10M | PHP layer |
| PHP post_max_size | 12M | PHP layer |
| Laravel validation | 10240 KB | Application |

---

## Environment Configuration

### Development (.env)

```env
FILESYSTEM_DISK=local

# AWS credentials optional for local dev
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-west-1
AWS_BUCKET=laravel-blueprint-assets
```

### Production (.env)

```env
FILESYSTEM_DISK=s3-permanent

AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
AWS_DEFAULT_REGION=us-west-1
AWS_BUCKET=laravel-blueprint-assets
AWS_URL=https://dxrnpyjkukgbc.cloudfront.net
```

---

## Error Handling

### S3 Operation Failures

**Requirement**: FR-014 - Display actionable error messages, allow manual retry

**Implementation**:
```php
try {
    Storage::disk('s3-permanent')->put($path, $contents);
} catch (S3Exception $e) {
    Log::error('S3 upload failed', [
        'path' => $path,
        'error' => $e->getMessage(),
    ]);

    throw new StorageException(
        'Unable to upload file. Please try again.',
        previous: $e
    );
}
```

### User-Facing Error Messages

| Scenario | Message |
|----------|---------|
| Upload failed | Unable to upload file. Please try again. |
| File too large | File exceeds maximum size of 10MB. |
| Invalid file type | File type not allowed. Supported: JPEG, PNG, WebP, GIF, PDF. |
| Delete failed | Unable to delete file. Please try again. |
| Network error | Storage service temporarily unavailable. Please try again. |

---

## S3 Bucket Structure

```
laravel-blueprint-assets/
├── temp/                          # Private - processing files
│   └── {uuid}/
│       ├── original.{ext}
│       ├── optimized.{ext}
│       └── variants/
│           ├── thumb.webp
│           └── medium.webp
│
└── permanent/                     # Public via CloudFront
    └── {year}/{month}/
        ├── {uuid}.{ext}           # Original (optimized)
        └── {uuid}-{variant}.webp  # Generated variants
```

---

## CloudFront Configuration

### Distribution Settings

| Setting | Value |
|---------|-------|
| Origin | `laravel-blueprint-assets.s3.us-west-1.amazonaws.com` |
| Origin Path | `/permanent` |
| Viewer Protocol | Redirect HTTP to HTTPS |
| Allowed Methods | GET, HEAD |
| Cache Policy | Managed-CachingOptimized |
| TTL | Default 86400 (1 day) |

### URL Format

```
https://dxrnpyjkukgbc.cloudfront.net/{path}
```

Where `{path}` is relative to `/permanent/` folder.

---

## Security Considerations

1. **Temp Folder Private**: Temp files not publicly accessible
2. **Pre-signed URLs**: Not used (CloudFront handles distribution)
3. **No Directory Listing**: S3 bucket blocks list operations
4. **File Validation**: Server-side validation required (don't trust client)
5. **Path Traversal**: Prevent `../` in file paths
6. **Content-Type Headers**: Set correctly to prevent XSS

---

## Testing Strategy

### Unit Tests
- Disk switching based on environment
- Path generation logic
- File validation rules

### Integration Tests (with mocked S3)
- Upload to temp storage
- Move from temp to permanent
- Delete operations
- URL generation

### Feature Tests
- File upload via admin panel
- Error handling display
- Retry mechanism
