# Contract: Admin Authentication

**Feature Branch**: `002-system-foundation`
**Date**: 2025-12-05
**Spec Reference**: FR-015 through FR-022

## Overview

This contract defines the authentication flows for the Filament admin panel at `/admin`.

---

## Endpoints

### GET /admin

**Description**: Admin panel entry point (protected)

**Authentication**: Required

**Response (Unauthenticated)**:
- Status: `302 Found`
- Redirect: `/admin/login`

**Response (Authenticated)**:
- Status: `200 OK`
- Body: Filament admin dashboard HTML

---

### GET /admin/login

**Description**: Login page display

**Authentication**: None (public)

**Response**:
- Status: `200 OK`
- Body: Filament login form HTML

**Form Fields**:
| Field | Type | Required | Validation |
|-------|------|----------|------------|
| email | email | yes | valid email format |
| password | password | yes | min:1 |
| remember | checkbox | no | boolean |

---

### POST /admin/login

**Description**: Process login attempt

**Authentication**: None (public)

**Request Body**:
```json
{
  "email": "info@proweb.ai",
  "password": "password123",
  "remember": true
}
```

**Response (Success)**:
- Status: `302 Found`
- Redirect: `/admin`
- Set-Cookie: Session cookie with 2-hour lifetime

**Response (Invalid Credentials)**:
- Status: `302 Found`
- Redirect: `/admin/login`
- Session Flash: `These credentials do not match our records.`

**Response (Validation Error)**:
- Status: `302 Found`
- Redirect: `/admin/login`
- Session Errors: Field validation messages

**Rate Limiting**: 5 attempts per minute per IP

---

### POST /admin/logout

**Description**: End authenticated session

**Authentication**: Required

**Request**: CSRF token required

**Response**:
- Status: `302 Found`
- Redirect: `/admin/login`
- Action: Invalidate session, regenerate CSRF token

---

### GET /admin/password-reset/request

**Description**: Password reset request page

**Authentication**: None (public)

**Response**:
- Status: `200 OK`
- Body: Password reset request form HTML

**Form Fields**:
| Field | Type | Required | Validation |
|-------|------|----------|------------|
| email | email | yes | valid email format, exists:users |

---

### POST /admin/password-reset/request

**Description**: Request password reset email

**Authentication**: None (public)

**Request Body**:
```json
{
  "email": "info@proweb.ai"
}
```

**Response (Success)**:
- Status: `302 Found`
- Redirect: `/admin/password-reset/request`
- Session Flash: `We have emailed your password reset link.`
- Action: Send password reset email

**Response (Email Not Found)**:
- Status: `302 Found`
- Redirect: `/admin/password-reset/request`
- Session Flash: `We have emailed your password reset link.` (same message for security)

**Rate Limiting**: 1 request per 60 seconds per email

---

### GET /admin/password-reset/reset

**Description**: Password reset form (with token)

**Authentication**: None (public)

**Query Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | yes | Password reset token |
| email | string | yes | User's email (URL encoded) |

**Response (Valid Token)**:
- Status: `200 OK`
- Body: Password reset form HTML

**Response (Invalid/Expired Token)**:
- Status: `302 Found`
- Redirect: `/admin/password-reset/request`
- Session Flash: `This password reset link is invalid or has expired.`

---

### POST /admin/password-reset/reset

**Description**: Process password reset

**Authentication**: None (public)

**Request Body**:
```json
{
  "token": "abc123...",
  "email": "info@proweb.ai",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (Success)**:
- Status: `302 Found`
- Redirect: `/admin/login`
- Session Flash: `Your password has been reset.`
- Action: Update password, invalidate all other sessions

**Response (Invalid Token)**:
- Status: `302 Found`
- Redirect: `/admin/password-reset/request`
- Session Flash: `This password reset link is invalid or has expired.`

**Response (Validation Error)**:
- Status: `302 Found`
- Redirect: `/admin/password-reset/reset`
- Session Errors: Field validation messages

---

## Password Reset Email

**Subject**: `Reset Your Password`

**Template Variables**:
| Variable | Description |
|----------|-------------|
| `user.name` | User's display name |
| `resetUrl` | Full URL with token and email |
| `expiresIn` | Token expiration time (60 minutes) |

**Reset URL Format**:
```
https://{APP_URL}/admin/password-reset/reset?token={token}&email={email}
```

---

## Session Configuration

| Setting | Value | Description |
|---------|-------|-------------|
| Lifetime | 120 minutes | 2-hour inactivity timeout |
| Driver | database | Database-backed sessions |
| Encrypt | false | Session data not encrypted |
| HTTP Only | true | Cookie not accessible via JavaScript |
| Same Site | lax | CSRF protection |
| Secure | production only | HTTPS-only in production |

---

## Security Requirements

1. **CSRF Protection**: All POST requests require valid CSRF token
2. **Rate Limiting**: Login attempts limited to prevent brute force
3. **Password Hashing**: bcrypt with cost factor 12
4. **Session Regeneration**: Session ID regenerated on login
5. **Token Expiration**: Password reset tokens expire after 60 minutes
6. **Throttling**: Password reset requests throttled to 1 per minute per email

---

## Error Messages

| Code | Message |
|------|---------|
| `auth.failed` | These credentials do not match our records. |
| `auth.throttle` | Too many login attempts. Please try again in :seconds seconds. |
| `passwords.sent` | We have emailed your password reset link. |
| `passwords.token` | This password reset link is invalid or has expired. |
| `passwords.reset` | Your password has been reset. |
| `validation.email` | The email field must be a valid email address. |
| `validation.password` | The password field must be at least 8 characters. |
