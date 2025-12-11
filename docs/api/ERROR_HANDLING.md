# Error Handling

## Exception Handler

Tất cả API exceptions cần được handled properly trong `app/Exceptions/Handler.php`.

### Setup

```php
// app/Exceptions/Handler.php

public function register(): void
{
    $this->reportable(function (Throwable $e) {
        // Log exceptions
    });

    $this->renderable(function (ApiException $e, Request $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getData(),
            ], $e->getCode() ?: 400);
        }
    });
}
```

## Custom ApiException

```php
// app/Exceptions/ApiException.php
class ApiException extends Exception
{
    public function __construct(
        string $message = 'API Error',
        int $code = 400,
        ?Exception $previous = null,
        protected mixed $data = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
```

## Usage Examples

### Basic Error

```php
throw new ApiException('User not found', 404);
```

### Error with Details

```php
throw new ApiException(
    'Validation failed',
    422,
    null,
    ['email' => 'Email already exists']
);
```

### In Controller

```php
public function show(User $user)
{
    if (!$user) {
        throw new ApiException('User not found', 404);
    }

    return $this->success(new UserResource($user));
}
```

## Standard Error Codes

| Status | Meaning | When to Use |
|--------|---------|-------------|
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing/invalid auth |
| 403 | Forbidden | Authenticated but no permission |
| 404 | Not Found | Resource doesn't exist |
| 409 | Conflict | Data conflict (duplicate unique field) |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Unexpected server error |
| 503 | Service Unavailable | Maintenance mode |

## Validation Errors

### Form Request Handling

```php
// app/Http/Requests/BaseFormRequest.php
protected function failedValidation(Validator $validator): void
{
    throw new HttpResponseException(
        response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422)
    );
}
```

### Response

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

## Model Not Found

### Automatic Handling

```php
// Laravel automatically throws ModelNotFoundException
public function show(User $user)
{
    // If $user doesn't exist, throws 404
    return $this->success(new UserResource($user));
}
```

### Register Handler

```php
// app/Exceptions/Handler.php
$this->renderable(function (ModelNotFoundException $e, Request $request) {
    if ($request->is('api/*')) {
        return response()->json([
            'success' => false,
            'message' => 'Resource not found',
            'errors' => null,
        ], 404);
    }
});
```

## Logging Errors

### Log in Handler

```php
$this->reportable(function (Throwable $e) {
    if ($this->shouldReport($e)) {
        Log::error('API Error', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
});
```

### Log in Service

```php
class UserService extends BaseService
{
    public function create(array $data)
    {
        try {
            return $this->repository->create($data);
        } catch (Exception $e) {
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw new ApiException('Failed to create user', 500);
        }
    }
}
```

## Development vs Production

### Development

```php
// config/app.php
'debug' => true, // Show full error details

// responses include full stack trace
```

### Production

```php
// config/app.php
'debug' => false, // Hide error details

// responses hide sensitive information
```

### Example Error in Production

```json
{
  "success": false,
  "message": "Server error",
  "errors": null
}
```

## Error Response Examples

### Validation Error (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

### Resource Not Found (404)

```json
{
  "success": false,
  "message": "Resource not found",
  "errors": null
}
```

### Unauthorized (401)

```json
{
  "success": false,
  "message": "Unauthorized",
  "errors": null
}
```

### Conflict (409)

```json
{
  "success": false,
  "message": "Email already exists",
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

## Best Practices

1. **Always catch exceptions** at service level
2. **Convert exceptions** to ApiException with proper codes
3. **Don't expose** database errors to clients
4. **Log all errors** for debugging
5. **Use meaningful messages** that help clients understand
6. **Return appropriate status codes**
7. **Include error details** only when necessary
8. **Test error scenarios** in unit tests
9. **Monitor errors** in production
10. **Set up alerts** for critical errors

## Testing Errors

```php
// tests/Feature/ErrorHandlingTest.php

public function test_validation_error()
{
    $response = $this->postJson('/api/v1/users', [
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('success', false);
    $response->assertJsonPath('message', 'Validation failed');
}

public function test_not_found_error()
{
    $response = $this->getJson('/api/v1/users/999');

    $response->assertStatus(404);
    $response->assertJsonPath('success', false);
}

public function test_unauthorized_error()
{
    $response = $this->getJson('/api/v1/user');

    $response->assertStatus(401);
}
```
