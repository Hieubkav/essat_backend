# Auth Backend Implementation Summary

## Các file đã tạo

### Services
- `app/Services/AuthService.php` - Logic xử lý auth (register, login, logout, etc)

### Controllers
- `app/Http/Controllers/Api/V1/AuthController.php` - API endpoints cho auth

### Requests (Validation)
- `app/Http/Requests/Auth/RegisterRequest.php` - Validate registration input
- `app/Http/Requests/Auth/LoginRequest.php` - Validate login input
- `app/Http/Requests/Auth/ChangePasswordRequest.php` - Validate password change

### Resources (Response Transformation)
- `app/Http/Resources/UserResource.php` - Format user data khi response

### Middleware
- `app/Http/Middleware/IsAdmin.php` - Check nếu user là admin

### Migrations
- `2025_12_03_000000_add_role_to_users_table.php` - Thêm role column
- `2025_12_03_000001_create_personal_access_tokens_table.php` - Sanctum tokens

### Routes
- `routes/api/v1.php` - Updated với auth routes

### Models
- `app/Models/User.php` - Updated với HasApiTokens, role field, helper methods

### Documentation
- `docs/api/AUTHENTICATION.md` - API documentation
- `docs/guides/FRONTEND_AUTH_SETUP.md` - Hướng dẫn setup frontend

## API Endpoints

### Public Routes
- `POST /api/v1/auth/register` - Đăng ký
- `POST /api/v1/auth/login` - Đăng nhập

### Protected Routes (require auth)
- `GET /api/v1/auth/me` - Lấy profile hiện tại
- `PUT /api/v1/auth/profile` - Cập nhật profile
- `POST /api/v1/auth/change-password` - Thay đổi password
- `POST /api/v1/auth/logout` - Đăng xuất

## Cách sử dụng

### 1. Database Migration
```bash
php artisan migrate
```

### 2. Test API
```bash
# Register
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "Password123"
  }'

# Protected route
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {token}"
```

### 3. Frontend Setup
Xem `docs/guides/FRONTEND_AUTH_SETUP.md`

## Key Features

✅ **JWT Token-based auth** dùng Laravel Sanctum
✅ **User roles** (user/admin)
✅ **Password hashing** (Laravel bcrypt)
✅ **Input validation** (FormRequest)
✅ **Admin middleware** để bảo vệ admin routes
✅ **Token revocation** khi logout
✅ **Consistent response format** 

## Next Steps

1. ✅ Run migration: `php artisan migrate`
2. ✅ Test endpoints với Postman/curl
3. ✅ Setup frontend auth (xem FRONTEND_AUTH_SETUP.md)
4. ✅ Tạo admin panel routes (dùng `IsAdmin` middleware)

## Middleware Usage Example

```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
});
```

## Config Changes Needed

Nếu Sanctum chưa được register, thêm vào `config/app.php`:

```php
'providers' => [
    // ...
    \Laravel\Sanctum\SanctumServiceProvider::class,
],
```

Hoặc chạy publish:
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```
