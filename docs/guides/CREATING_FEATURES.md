# Creating Features - Step by Step

Guide to tạo API features một cách chuẩn.

## Feature: User Management API

Chúng ta sẽ tạo complete User API với CRUD operations.

### Step 1: Create Database Model

```bash
php artisan make:model User -m
```

**Migration:**
```php
// database/migrations/[date]_create_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->boolean('is_active')->default(true);
    $table->rememberToken();
    $table->timestamps();

    $table->index(['email', 'is_active']);
});
```

```bash
php artisan migrate
```

**Model:**
```php
// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
```

### Step 2: Create Repository

```bash
php artisan make:class Repositories/UserRepository
```

```php
// app/Repositories/UserRepository.php
<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new User();
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function active()
    {
        return $this->model->where('is_active', true);
    }

    public function paginateActive(int $perPage = 15)
    {
        return $this->active()->paginate($perPage);
    }
}
```

### Step 3: Create Service

```bash
php artisan make:class Services/UserService
```

```php
// app/Services/UserService.php
<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserService extends BaseService
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function paginate($perPage = 15)
    {
        return $this->repository->paginateActive($perPage);
    }

    public function find($id)
    {
        $user = $this->repository->find($id);
        
        if (!$user || !$user->is_active) {
            throw new Exception('User not found', 404);
        }

        return $user;
    }

    public function create(array $data)
    {
        // Hash password before saving
        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        // Only hash if password was provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $this->repository->update($id, $data);
        return $this->find($id);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function toggleActive($id)
    {
        $user = $this->repository->findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        return $user;
    }
}
```

### Step 4: Create Requests (Validation)

```bash
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest
```

**Store (Create):**
```php
// app/Http/Requests/StoreUserRequest.php
<?php

namespace App\Http\Requests;

class StoreUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email already exists',
            'password.min' => 'Password must be at least 8 characters',
        ];
    }
}
```

**Update:**
```php
// app/Http/Requests/UpdateUserRequest.php
<?php

namespace App\Http\Requests;

class UpdateUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:users,email,{$userId}",
            'password' => 'sometimes|string|min:8|confirmed',
        ];
    }
}
```

### Step 5: Create Resources (Transformers)

```bash
php artisan make:resource UserResource
php artisan make:resource UserCollection
```

**User Resource:**
```php
// app/Http/Resources/UserResource.php
<?php

namespace App\Http\Resources;

class UserResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
```

**Collection:**
```php
// app/Http/Resources/UserCollection.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
```

### Step 6: Create Controller

```bash
php artisan make:controller Api/V1/UserController
```

```php
// app/Http/Controllers/Api/V1/UserController.php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Exception;

class UserController extends ApiController
{
    public function __construct(
        private UserService $service
    ) {}

    /**
     * List all users
     */
    public function index(): JsonResponse
    {
        try {
            $users = $this->service->paginate();
            return $this->success(
                UserResource::collection($users),
                'Users retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Create new user
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->service->create($request->validated());
            return $this->created(
                new UserResource($user),
                'User created successfully'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get specific user
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->service->find($id);
            return $this->success(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }

    /**
     * Update user
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->service->update($id, $request->validated());
            return $this->success(
                new UserResource($user),
                'User updated successfully'
            );
        } catch (Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return $this->noContent();
        } catch (Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $user = $this->service->toggleActive($id);
            return $this->success(
                new UserResource($user),
                'User status updated'
            );
        } catch (Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }
}
```

### Step 7: Add Routes

```php
// routes/api/v1.php
<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Public routes
    
    // User management (protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive']);
    });
});
```

### Step 8: Testing

```php
// tests/Feature/UserControllerTest.php
<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function test_list_users()
    {
        User::factory(3)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.0.id', 1);
    }

    public function test_create_user()
    {
        $response = $this->postJson('/api/v1/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_get_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.email', $user->email);
    }

    public function test_update_user()
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Jane Doe',
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['name' => 'Jane Doe']);
    }

    public function test_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
```

Run tests:
```bash
php artisan test
```

### Step 9: Test API Manually

```bash
# List users
curl http://localhost:8000/api/v1/users

# Create user
curl -X POST http://localhost:8000/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Get user
curl http://localhost:8000/api/v1/users/1

# Update user
curl -X PUT http://localhost:8000/api/v1/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name": "Jane Doe"}'

# Delete user
curl -X DELETE http://localhost:8000/api/v1/users/1
```

## Checklist

Khi tạo feature mới, chắc chắn check:

- [ ] Model & Migration
- [ ] Repository (data access)
- [ ] Service (business logic)
- [ ] Controller (API endpoints)
- [ ] Form Requests (validation)
- [ ] Resources (response transformation)
- [ ] Routes (api/v1.php)
- [ ] Tests (feature tests)
- [ ] Documentation (README, API docs)

## Common Patterns

### Pagination

```php
$users = $this->service->paginate(request('per_page', 15));
```

### Filtering

```php
// In Repository
public function filterByStatus(string $status)
{
    return $this->model->where('status', $status);
}
```

### Search

```php
// In Repository
public function search(string $query)
{
    return $this->model
        ->where('name', 'like', "%{$query}%")
        ->orWhere('email', 'like', "%{$query}%");
}
```

### Relations

```php
// In Resource
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'posts' => PostResource::collection($this->whenLoaded('posts')),
    ];
}
```

See [BEST_PRACTICES.md](./BEST_PRACTICES.md) for more patterns.
