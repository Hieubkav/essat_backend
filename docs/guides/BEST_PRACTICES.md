# Best Practices

Các best practices khi code API Laravel.

## Code Quality

### 1. Type Hints Everything

```php
// ✅ Good
public function create(array $data): User
{
    return $this->repository->create($data);
}

// ❌ Bad
public function create($data)
{
    return $this->repository->create($data);
}
```

### 2. Use Dependency Injection

```php
// ✅ Good
public function __construct(
    private UserService $service,
    private UserRepository $repository
) {}

// ❌ Bad
public function store()
{
    $service = new UserService();
}
```

### 3. Single Responsibility Principle

```php
// ✅ Good - Repository chỉ handle database
class UserRepository extends BaseRepository
{
    public function find($id)
    {
        return $this->model->find($id);
    }
}

// ✅ Good - Service xử lý business logic
class UserService
{
    public function create(array $data)
    {
        // Validate, process, etc.
        return $this->repository->create($data);
    }
}

// ✅ Good - Controller chỉ handle request/response
class UserController
{
    public function store(StoreUserRequest $request)
    {
        $user = $this->service->create($request->validated());
        return $this->created(new UserResource($user));
    }
}

// ❌ Bad - Mixing concerns
class UserController
{
    public function store(Request $request)
    {
        $this->validate($request, [...]);
        $user = User::create($request->all());
        return response()->json($user);
    }
}
```

### 4. Avoid N+1 Queries

```php
// ✅ Good - Eager load
$users = User::with('posts', 'comments')->paginate();

// ❌ Bad - N+1 problem
$users = User::paginate();
foreach ($users as $user) {
    $posts = $user->posts; // Extra query for each user
}
```

### 5. Use Collections & Query Builder

```php
// ✅ Good
$users = User::where('is_active', true)
    ->orderBy('created_at', 'desc')
    ->paginate();

// ❌ Bad
$users = User::all();
$users = $users->filter(function ($u) { 
    return $u->is_active; 
});
```

## API Design

### 1. Consistent Response Format

```php
// ✅ Good - Always same format
return $this->success([
    'user' => new UserResource($user),
    'token' => $token,
], 'Login successful');

// ❌ Bad - Different formats
return response()->json([
    'user' => $user,
    'token' => $token,
]);
```

### 2. Proper HTTP Status Codes

```php
// ✅ Good
return $this->created($data);           // 201
return $this->success($data);           // 200
return $this->noContent();              // 204
return $this->error('Not found', 404);  // 404
return $this->error('Unauthorized', 401); // 401

// ❌ Bad
return response()->json($data, 200);    // Always 200
return response()->json(['error' => 'Not found'], 200);
```

### 3. Meaningful Error Messages

```php
// ✅ Good
return $this->error('Email already exists', 409);
return $this->error('User not found', 404);

// ❌ Bad
return $this->error('Error', 400);
return $this->error('Exception occurred', 500);
```

### 4. Pagination

```php
// ✅ Good - Always paginate lists
public function index()
{
    $users = $this->service->paginate(
        request('per_page', 15)
    );
    return $this->success(UserResource::collection($users));
}

// ❌ Bad - Return all records
public function index()
{
    $users = User::all();
    return $this->success(UserResource::collection($users));
}
```

## Validation

### 1. Use Form Requests

```php
// ✅ Good - Validation in Form Request
class StoreUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users',
        ];
    }
}

// ❌ Bad - Validation in Controller
public function store(Request $request)
{
    $this->validate($request, [
        'email' => 'required|email|unique:users',
    ]);
}
```

### 2. Custom Messages

```php
// ✅ Good
public function messages(): array
{
    return [
        'email.unique' => 'Email already exists',
        'password.min' => 'Password must be at least 8 characters',
    ];
}

// ❌ Bad - Generic messages
// No custom messages - users see "The email field is invalid"
```

### 3. Validate Early

```php
// ✅ Good - Validation in request, not service
class StoreUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return ['email' => 'required|email|unique:users'];
    }
}

// ❌ Bad - Validation in service
public function create(array $data)
{
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email');
    }
}
```

## Security

### 1. Hash Passwords

```php
// ✅ Good
$data['password'] = Hash::make($data['password']);
$user = User::create($data);

// ❌ Bad
$user = User::create([
    'password' => $request->password,
]);
```

### 2. Authorize Actions

```php
// ✅ Good
public function update(UpdateUserRequest $request, User $user)
{
    $this->authorize('update', $user);
    $user->update($request->validated());
    return $this->success(new UserResource($user));
}

// ❌ Bad - No authorization check
public function update(UpdateUserRequest $request, User $user)
{
    $user->update($request->validated());
    return $this->success(new UserResource($user));
}
```

### 3. Hide Sensitive Data

```php
// ✅ Good - Resource controls visible fields
class UserResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // Password hidden automatically
        ];
    }
}

// Or in Model
class User extends Model
{
    protected $hidden = ['password', 'remember_token'];
}

// ❌ Bad - Returns all fields including sensitive
return response()->json($user);
```

## Database

### 1. Use Migrations

```php
// ✅ Good - Migrations for schema changes
php artisan make:migration create_users_table

// ❌ Bad - Manual SQL
// ALTER TABLE users ADD COLUMN phone VARCHAR(20);
```

### 2. Use Factories & Seeders

```php
// ✅ Good - Seeding data
php artisan make:factory UserFactory
php artisan make:seeder UserSeeder

// ❌ Bad - Manual INSERT
// INSERT INTO users VALUES (...);
```

### 3. Transactions for Related Operations

```php
// ✅ Good - Atomic operations
DB::transaction(function () {
    $user = User::create($data);
    $user->roles()->attach($roleIds);
    event(new UserCreated($user));
});

// ❌ Bad - No transaction
$user = User::create($data);
$user->roles()->attach($roleIds);
```

## Relationships

### 1. Define Clear Relationships

```php
// ✅ Good
class User extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}

// ❌ Bad - No type hints
public function posts()
{
    return $this->hasMany('App\Models\Post');
}
```

### 2. Eager Load

```php
// ✅ Good
public function show(User $user)
{
    return $this->success(
        new UserResource($user->load('posts', 'roles'))
    );
}

// ❌ Bad - N+1 queries
public function show(User $user)
{
    return $this->success(new UserResource($user));
}
```

## Testing

### 1. Test All Endpoints

```php
// ✅ Good
class UserControllerTest extends TestCase
{
    public function test_list_users() { /* ... */ }
    public function test_create_user() { /* ... */ }
    public function test_get_user() { /* ... */ }
    public function test_update_user() { /* ... */ }
    public function test_delete_user() { /* ... */ }
}

// ❌ Bad - No tests
```

### 2. Test Error Cases

```php
// ✅ Good
public function test_create_user_with_invalid_email()
{
    $response = $this->postJson('/api/v1/users', [
        'email' => 'invalid',
    ]);
    $response->assertStatus(422);
}

public function test_create_user_with_duplicate_email()
{
    User::factory()->create(['email' => 'test@example.com']);
    
    $response = $this->postJson('/api/v1/users', [
        'email' => 'test@example.com',
    ]);
    $response->assertStatus(422);
}

// ❌ Bad - Only test happy path
public function test_create_user()
{
    $response = $this->postJson('/api/v1/users', [...]);
    $response->assertStatus(201);
}
```

### 3. Use Database Transactions in Tests

```php
// ✅ Good - Rollback after each test
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user() { /* ... */ }
}

// ❌ Bad - Data pollution between tests
public function test_create_user() { /* ... */ }
public function test_another_test() { /* ... */ }
```

## Documentation

### 1. Document APIs

```php
// ✅ Good
/**
 * List all users
 * 
 * @return JsonResponse
 */
public function index(): JsonResponse
{
    // ...
}

// ❌ Bad - No documentation
public function index()
{
    // ...
}
```

### 2. Keep README Updated

```markdown
# User API

## Endpoints

GET /api/v1/users - List all users
POST /api/v1/users - Create user
GET /api/v1/users/{id} - Get specific user
PUT /api/v1/users/{id} - Update user
DELETE /api/v1/users/{id} - Delete user
```

### 3. Add Comments for Complex Logic

```php
// ✅ Good - Explains why, not what
/**
 * We use transaction here because we need to atomically
 * create user, assign role, and log activity.
 * If any step fails, we rollback everything.
 */
DB::transaction(function () {
    $user = User::create($data);
    $user->roles()->attach($roleIds);
    Activity::log('user.created', $user);
});

// ❌ Bad - Comments state the obvious
// Create user
$user = User::create($data);
// Assign role
$user->roles()->attach($roleIds);
```

## Performance

### 1. Use Caching

```php
// ✅ Good
public function index()
{
    $users = Cache::remember('users', 3600, function () {
        return User::with('posts')->paginate();
    });
    return $this->success(UserResource::collection($users));
}

// ❌ Bad - No caching
public function index()
{
    return $this->success(
        UserResource::collection(User::with('posts')->paginate())
    );
}
```

### 2. Optimize Queries

```php
// ✅ Good - Select only needed fields
$users = User::select('id', 'name', 'email')->get();

// ❌ Bad - Load all fields
$users = User::all();
```

### 3. Use Indices

```php
// ✅ Good
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->timestamp('created_at')->nullable();
    
    $table->index(['email', 'created_at']);
});

// ❌ Bad - No indices
```

## Logging

### 1. Log Important Events

```php
// ✅ Good
public function create(array $data)
{
    $user = $this->repository->create($data);
    Log::info('User created', ['user_id' => $user->id, 'email' => $user->email]);
    return $user;
}

// ❌ Bad - No logging
```

### 2. Log Errors

```php
// ✅ Good
try {
    $user = $this->repository->create($data);
} catch (Exception $e) {
    Log::error('Failed to create user', ['error' => $e->getMessage()]);
    throw new ApiException('Failed to create user', 500);
}

// ❌ Bad - Silent failures
```

## Summary

Key principles:
- **Clean Code**: Type hints, clear names, single responsibility
- **API Design**: Consistent formats, proper status codes
- **Security**: Hash passwords, validate input, authorize actions
- **Performance**: Eager load, cache, optimize queries
- **Testing**: Test all cases, use transactions
- **Documentation**: Keep it updated and clear
- **Logging**: Log important events and errors

Follow these practices to build maintainable, secure, performant APIs.
