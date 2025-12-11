# API Structure

## Folder Organization

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── ApiController.php          # Base API controller with common methods
│   │       └── V1/                        # API version 1
│   │           ├── UserController.php
│   │           └── ProductController.php
│   ├── Requests/
│   │   ├── BaseFormRequest.php            # Base form request class
│   │   ├── V1/
│   │   │   ├── StoreUserRequest.php
│   │   │   └── UpdateUserRequest.php
│   │
│   └── Resources/
│       ├── BaseResource.php               # Base resource transformer
│       ├── UserResource.php
│       └── UserCollection.php
├── Services/
│   ├── BaseService.php                    # Base service class
│   └── UserService.php                    # Business logic for User
├── Repositories/
│   ├── BaseRepository.php                 # Base repository class
│   ├── BaseRepositoryInterface.php        # Repository interface
│   └── UserRepository.php                 # Data access for User
├── DTOs/
│   ├── BaseDTO.php                        # Base DTO class
│   └── UserDTO.php                        # Data transfer object
├── Exceptions/
│   └── ApiException.php                   # Custom API exception

routes/
├── api.php                                # Main API route file
└── api/
    ├── v1.php                             # API V1 routes
    └── v2.php                             # API V2 routes (future)
```

## Architecture Layers

### 1. Controller Layer (Http/Controllers/Api)
- **Responsibility**: Handle HTTP requests/responses
- **Base Class**: `ApiController`
- **Methods**: index, show, store, update, destroy
- **Return**: JSON responses via helper methods
- **Example**:
```php
class UserController extends ApiController
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index()
    {
        $users = $this->userService->paginate();
        return $this->success(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }
}
```

### 2. Service Layer (Services)
- **Responsibility**: Business logic and orchestration
- **Base Class**: `BaseService`
- **Methods**: paginate(), find(), create(), update(), delete()
- **Uses**: Repository for data access, DTOs for data transfer
- **Example**:
```php
class UserService extends BaseService
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function paginate($perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }
}
```

### 3. Repository Layer (Repositories)
- **Responsibility**: Database access abstraction
- **Base Class**: `BaseRepository`
- **Methods**: all(), paginate(), find(), findOrFail(), create(), update(), delete()
- **Uses**: Eloquent models
- **Example**:
```php
class UserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new User();
    }
}
```

### 4. Request Layer (Http/Requests)
- **Responsibility**: Input validation
- **Base Class**: `BaseFormRequest`
- **Methods**: rules(), messages(), authorize()
- **Example**:
```php
class StoreUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users',
            'name' => 'required|string|max:255',
        ];
    }
}
```

### 5. Resource Layer (Http/Resources)
- **Responsibility**: Data transformation for responses
- **Base Class**: `BaseResource`
- **Methods**: toArray()
- **Example**:
```php
class UserResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
```

## Request Flow

```
HTTP Request
    ↓
Route → ApiController (V1/UserController)
    ↓
Service (UserService)
    ↓
Repository (UserRepository)
    ↓
Model (User)
    ↓
Database
    ↓
Model → Repository → Service
    ↓
Resource (UserResource) - Transform
    ↓
ApiController - success() method
    ↓
JSON Response
```

## Best Practices

1. **Use Dependency Injection** in constructors
2. **Type Hint** all parameters and return types
3. **Validate Input** using Form Requests
4. **Transform Output** using Resources
5. **Separate Concerns**: Controllers → Services → Repositories
6. **Use DTOs** for complex data transfers
7. **Eager Load** relationships to avoid N+1 queries
8. **Cache** expensive operations
9. **Use Pagination** for large datasets
10. **Document APIs** with comments and type hints
