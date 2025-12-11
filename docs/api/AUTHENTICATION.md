# Authentication API

## Overview

Backend dùng **Laravel Sanctum** để quản lý token-based authentication. Mỗi user sau khi login nhận 1 `access_token` để sử dụng trong các request tiếp theo.

## Authentication Flow

```
1. User register → Tạo account
2. User login → Lấy access_token
3. Frontend lưu token (cookie hoặc localStorage)
4. Gửi request với Authorization header: Bearer {access_token}
5. Backend verify token → Cấp quyền truy cập
```

## Endpoints

### Public Routes

#### Register
```
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```

**Response Success (201)**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "is_admin": false,
    "created_at": "2025-12-03T10:00:00Z",
    "updated_at": "2025-12-03T10:00:00Z"
  }
}
```

#### Login
```
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "Password123"
}
```

**Response Success (200)**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "is_admin": false,
      "created_at": "2025-12-03T10:00:00Z",
      "updated_at": "2025-12-03T10:00:00Z"
    },
    "access_token": "3|sG2kqYZ9r0w7xL8m9n...",
    "token_type": "Bearer"
  }
}
```

**Response Error (401)**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {
    "email": ["The provided credentials are invalid."]
  }
}
```

### Protected Routes

#### Get Current User Profile
```
GET /api/v1/auth/me
Authorization: Bearer {access_token}
```

**Response (200)**
```json
{
  "success": true,
  "message": "User profile retrieved",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "is_admin": false,
    "created_at": "2025-12-03T10:00:00Z",
    "updated_at": "2025-12-03T10:00:00Z"
  }
}
```

#### Update Profile
```
PUT /api/v1/auth/profile
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": "jane@example.com"
}
```

**Response (200)**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "user",
    "is_admin": false,
    "created_at": "2025-12-03T10:00:00Z",
    "updated_at": "2025-12-03T10:00:01Z"
  }
}
```

#### Change Password
```
POST /api/v1/auth/change-password
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "current_password": "Password123",
  "new_password": "NewPassword456",
  "new_password_confirmation": "NewPassword456"
}
```

**Response (200)**
```json
{
  "success": true,
  "message": "Password changed successfully",
  "data": null
}
```

#### Logout
```
POST /api/v1/auth/logout
Authorization: Bearer {access_token}
```

**Response (200)**
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": null
}
```

## Password Requirements

- Minimum 8 characters
- Contains uppercase letter
- Contains lowercase letter
- Contains number

Example valid passwords:
- `Password123`
- `MySecure@Pass456`

## User Roles

### User (mặc định)
- Role: `user`
- is_admin: `false`
- Permissions: Xem thông tin cá nhân, cập nhật profile

### Admin
- Role: `admin`
- is_admin: `true`
- Permissions: Quản lý users, admin routes

## Headers Required

Mỗi request protected route cần gửi token:

```
Authorization: Bearer {access_token}
```

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized",
  "errors": null
}
```

### 403 Forbidden (Not Admin)
```json
{
  "success": false,
  "message": "Forbidden",
  "errors": null
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email already registered"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

## Frontend Integration

### Lưu Token

```javascript
// Sau login
const { access_token } = response.data.data;

// Option 1: Cookie (httpOnly)
// Server sẽ set automatically

// Option 2: localStorage
localStorage.setItem('auth_token', access_token);
```

### Gửi Request

```javascript
// Fetch API
fetch('/api/v1/auth/me', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

// Axios
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
axios.get('/api/v1/auth/me');
```

### Middleware (Next.js)

```typescript
// app/middleware.ts
export function middleware(request: NextRequest) {
  const token = request.cookies.get('auth_token')?.value;
  
  if (request.nextUrl.pathname.startsWith('/admin') && !token) {
    return NextResponse.redirect(new URL('/login', request.url));
  }
}
```

### Protected Layout (Next.js)

```typescript
// app/admin/layout.tsx
export default async function AdminLayout({ children }) {
  const token = cookies().get('auth_token')?.value;
  
  if (!token) {
    redirect('/login');
  }

  // Verify token with backend
  const res = await fetch('http://localhost:8000/api/v1/auth/me', {
    headers: { 'Authorization': `Bearer ${token}` }
  });

  if (!res.ok) {
    redirect('/login');
  }

  const { data: user } = await res.json();
  
  if (!user.is_admin) {
    redirect('/unauthorized');
  }

  return <>{children}</>;
}
```
