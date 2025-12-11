# API Versioning Strategy

## Overview

API được versioning theo URL path: `/api/v1/`, `/api/v2/`, etc.

## Version Structure

### Current Version: V1

**Base URL:** `/api/v1`

**Endpoints:**
```
GET    /api/v1/users
POST   /api/v1/users
GET    /api/v1/users/{id}
PUT    /api/v1/users/{id}
PATCH  /api/v1/users/{id}
DELETE /api/v1/users/{id}
```

### Folder Structure per Version

```
app/Http/Controllers/Api/
├── ApiController.php
├── V1/
│   ├── UserController.php
│   ├── ProductController.php
│   └── ...
└── V2/        # Future version
    ├── UserController.php
    └── ...

app/Http/Requests/
├── BaseFormRequest.php
└── V1/
    ├── StoreUserRequest.php
    └── UpdateUserRequest.php

app/Http/Resources/
├── BaseResource.php
├── V1/
│   ├── UserResource.php
│   └── ...
└── V2/
    └── ...

routes/
├── api.php
├── api/v1.php
└── api/v2.php     # Future
```

## Adding a New Version

### Step 1: Create V2 Routes

```php
// routes/api/v2.php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // V2 endpoints
});
```

### Step 2: Update Main API Routes

```php
// routes/api.php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(base_path('routes/api/v1.php'));
Route::prefix('v2')->group(base_path('routes/api/v2.php'));
```

### Step 3: Create V2 Controllers

```bash
php artisan make:controller Api/V2/UserController
```

### Step 4: Create V2 Resources (if needed)

If response format changes:
```bash
php artisan make:resource V2/UserResource
```

### Step 5: Create V2 Requests (if needed)

```bash
php artisan make:request V1/StoreUserRequest
```

## Backward Compatibility

### Breaking Changes

Khi có breaking change:
1. Tạo version mới (v2)
2. Giữ v1 ít nhất 6-12 tháng
3. Gửi notification cho clients
4. Support deprecation header

### Non-Breaking Changes

Cho non-breaking changes (thêm field, thêm endpoint):
- Thêm vào current version
- Không cần tạo version mới

## Response Format per Version

### V1 Format
```json
{
  "success": true,
  "message": "...",
  "data": {...}
}
```

### V2 Format (Example)
```json
{
  "status": "success",
  "message": "...",
  "result": {...}
}
```

## Deprecation Strategy

Khi deprecate API endpoint:

1. **Add header:**
```php
return response()->json($data)
    ->header('Deprecation', 'true')
    ->header('Sunset', 'Wed, 21 Dec 2025 23:59:59 GMT')
    ->header('Link', '</api/v2/users>; rel="successor-version"');
```

2. **Document in changelog**
3. **Send deprecation notice to clients**
4. **Set removal date (6+ months)**

## Migration Path

### From V1 to V2

1. Deploy V2 endpoints
2. Test thoroughly
3. Announce deprecation
4. Give clients time to migrate (6 months)
5. Remove V1 (or keep minimal support)

## Versioning Best Practices

1. **URL versioning** is more discoverable than header versioning
2. **Keep versions independent** - each version has its own controllers/resources
3. **Don't version too early** - wait until you have actual breaking changes
4. **Maintain backward compatibility** as long as possible
5. **Clear communication** with API clients about version strategy
6. **Gradual deprecation** - give clients time to upgrade

## Example: V1 to V2 Migration

### V1 Endpoint
```
GET /api/v1/users/{id}

Response:
{
  "success": true,
  "message": "User retrieved",
  "data": { "id": 1, "name": "John" }
}
```

### V2 Endpoint (New structure)
```
GET /api/v2/users/{id}

Response:
{
  "status": "success",
  "message": "User retrieved",
  "result": { "id": 1, "name": "John" }
}
```

### Migration Steps:
1. Deploy both versions
2. Client updates to use `/api/v2`
3. After transition period, deprecate `/api/v1`
