# Getting Started

## Prerequisites

- PHP 8.2+
- Composer
- Laravel 12.x
- MySQL/PostgreSQL (or SQLite for local dev)

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/Hieubkav/Backend_Core_Laravel_nextjs
cd Backend_Core_Laravel_nextjs
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup

```bash
# Create database
php artisan migrate

# (Optional) Seed with demo data
php artisan db:seed
```

### 5. Run Server

```bash
php artisan serve
```

Server sẽ chạy tại `http://localhost:8000`

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/      # API controllers
│   │   ├── Requests/             # Form requests / validation
│   │   └── Resources/            # Response transformers
│   ├── Services/                 # Business logic
│   ├── Repositories/             # Data access
│   ├── Models/                   # Eloquent models
│   ├── DTOs/                     # Data transfer objects
│   └── Exceptions/               # Custom exceptions
├── routes/
│   ├── api.php                   # Main API routes
│   └── api/
│       └── v1.php                # V1 API routes
├── config/
│   ├── api.php                   # API configuration
│   └── sanctum.php               # Authentication config
├── docs/                         # Documentation
│   ├── api/                      # API documentation
│   └── guides/                   # Developer guides
└── tests/                        # Tests
```

## Creating Your First API Endpoint

### 1. Create Model & Migration

```bash
php artisan make:model Product -m
```

### 2. Update Migration

```php
// database/migrations/[date]_create_products_table.php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->timestamps();
});
```

Run migration:
```bash
php artisan migrate
```

### 3. Create Repository

```bash
php artisan make:class Repositories/ProductRepository
```

```php
// app/Repositories/ProductRepository.php
<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Product();
    }
}
```

### 4. Create Service

```bash
php artisan make:class Services/ProductService
```

```php
// app/Services/ProductService.php
<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService extends BaseService
{
    public function __construct(
        private ProductRepository $repository
    ) {}

    public function paginate($perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function find($id)
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        $this->repository->update($id, $data);
        return $this->find($id);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
```

### 5. Create Controller

```bash
php artisan make:controller Api/V1/ProductController
```

```php
// app/Http/Controllers/Api/V1/ProductController.php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class ProductController extends ApiController
{
    public function __construct(
        private ProductService $service
    ) {}

    public function index(): JsonResponse
    {
        $products = $this->service->paginate();
        return $this->success(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->create($request->validated());
        return $this->created(
            new ProductResource($product),
            'Product created successfully'
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->service->find($id);
        return $this->success(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->service->update($id, $request->validated());
        return $this->success(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->noContent();
    }
}
```

### 6. Create Requests

```bash
php artisan make:request StoreProductRequest
php artisan make:request UpdateProductRequest
```

```php
// app/Http/Requests/StoreProductRequest.php
<?php

namespace App\Http\Requests;

class StoreProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ];
    }
}
```

### 7. Create Resources

```bash
php artisan make:resource ProductResource
```

```php
// app/Http/Resources/ProductResource.php
<?php

namespace App\Http\Resources;

class ProductResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
```

### 8. Add Routes

```php
// routes/api/v1.php
<?php

use App\Http\Controllers\Api\V1\ProductController;

Route::apiResource('products', ProductController::class);
```

### 9. Test API

```bash
# List products
curl http://localhost:8000/api/v1/products

# Create product
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Laptop","price":999.99}'

# Get specific product
curl http://localhost:8000/api/v1/products/1

# Update product
curl -X PUT http://localhost:8000/api/v1/products/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Laptop","price":1099.99}'

# Delete product
curl -X DELETE http://localhost:8000/api/v1/products/1
```

## Next Steps

1. Read [API Structure](../api/STRUCTURE.md) for architecture details
2. Check [Response Format](../api/RESPONSE_FORMAT.md) for response standards
3. Learn [Authentication](../api/AUTHENTICATION.md) to add auth
4. Review [Best Practices](./BEST_PRACTICES.md) for coding standards

## Useful Commands

```bash
# Generate code
php artisan make:model Post -mcr
php artisan make:request StorePostRequest
php artisan make:resource PostResource
php artisan make:controller Api/V1/PostController

# Database
php artisan migrate
php artisan migrate:rollback
php artisan db:seed

# Debugging
php artisan tinker
php artisan route:list

# Testing
php artisan test
php artisan test --filter=UserTest
```

## Troubleshooting

### Port 8000 already in use

```bash
php artisan serve --port=8001
```

### Database connection error

Check `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=root
DB_PASSWORD=
```

### CORS errors

Update `config/cors.php` or add to `.env`:
```
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

## Resources

- [Official Laravel Docs](https://laravel.com/docs/12.x)
- [API Documentation](../api/)
- [Development Guides](./BEST_PRACTICES.md)
