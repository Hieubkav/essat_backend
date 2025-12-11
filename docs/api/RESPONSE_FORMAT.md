# API Response Format

## Standard Response Structure

Tất cả API responses phải follow format sau:

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {}
}
```

**Status Codes:**
- `200 OK` - Lấy, cập nhật, xóa thành công
- `201 Created` - Tạo resource thành công
- `204 No Content` - Xóa thành công, không có body

### Error Response

```json
{
  "success": false,
  "message": "Error message",
  "errors": {}
}
```

**Status Codes:**
- `400 Bad Request` - Request không hợp lệ
- `401 Unauthorized` - Chưa authenticate
- `403 Forbidden` - Không có quyền truy cập
- `404 Not Found` - Resource không tồn tại
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

## Response Examples

### List Resource (Success)

```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 1,
      "email": "user@example.com",
      "name": "John Doe",
      "created_at": "2025-01-01T00:00:00Z"
    }
  ]
}
```

### Single Resource (Success)

```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "created_at": "2025-01-01T00:00:00Z"
  }
}
```

### Create Resource (201)

```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "created_at": "2025-01-01T00:00:00Z"
  }
}
```

### Validation Error (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "name": ["The name must be a string"]
  }
}
```

### Not Found (404)

```json
{
  "success": false,
  "message": "Resource not found",
  "errors": null
}
```

### Server Error (500)

```json
{
  "success": false,
  "message": "Server error",
  "errors": null
}
```

## Controller Helper Methods

Use ApiController helper methods để tạo responses:

```php
// Success
return $this->success($data, 'Message', 200);

// Created
return $this->created($data, 'Created message');

// No Content
return $this->noContent();

// Error
return $this->error('Message', 400, $errors);

// Not Found
return $this->notFound('User not found');

// Unauthorized
return $this->unauthorized();

// Forbidden
return $this->forbidden();

// Validation Error
return $this->validationError(['field' => 'error']);
```

## Data Transformation with Resources

Luôn sử dụng Resources để transform data:

```php
// Single resource
return $this->success(new UserResource($user));

// Collection
return $this->success(UserResource::collection($users));

// Paginated
return $this->success(UserResource::collection($users));
```

## Pagination Response

Khi paginate, Laravel tự động include pagination info:

```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 1,
      "email": "user@example.com",
      "name": "John Doe"
    }
  ],
  "links": {
    "first": "http://example.com/api/v1/users?page=1",
    "last": "http://example.com/api/v1/users?page=5",
    "prev": null,
    "next": "http://example.com/api/v1/users?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "http://example.com/api/v1/users",
    "per_page": 15,
    "to": 15,
    "total": 100
  }
}
```

## Consistent Error Messages

**Messages phải:**
- Rõ ràng, dễ hiểu
- Tiếng Việt hoặc Tiếng Anh (consistent)
- Không expose thông tin sensitive
- Gợi ý fix nếu có thể

**Examples:**
- ✅ "Email already exists"
- ✅ "User not found"
- ✅ "Validation failed"
- ❌ "SQL error: ..."
- ❌ "Server down"
