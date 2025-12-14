# API Design Patterns - Đã Áp Dụng

Tài liệu này mô tả các REST API Design Patterns đã được áp dụng vào backend API của dự án ESSAT.

## Tổng Quan

API của dự án đã được cập nhật để tuân thủ **4 Pattern chính** từ API Design Principles:

### ✅ Pattern 1: Resource Collection Design
**Trạng thái**: ĐÃ TỐT từ trước

- Routes resource-oriented (`/api/v1/products`, `/api/v1/posts`, `/api/v1/users`)
- Sử dụng đúng HTTP methods (GET, POST, PUT, PATCH, DELETE)
- Không có action verbs trong URL
- Có versioning API (`/api/v1/`)

### ✅ Pattern 2: Pagination and Filtering
**Trạng thái**: ĐÃ CẢI THIỆN

**Trước đây:**
- Có pagination cơ bản với `per_page`
- Thiếu metadata đầy đủ

**Hiện tại:**
- ✅ Thêm `PaginatedResponse` helper class
- ✅ Metadata đầy đủ: `total`, `per_page`, `current_page`, `last_page`, `from`, `to`, `has_more_pages`
- ✅ Hỗ trợ HATEOAS links cho pagination: `first`, `last`, `prev`, `next`, `self`

**File tạo mới:**
- `backend/app/Http/Helpers/PaginatedResponse.php`

**Ví dụ response:**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "items": [...],
    "pagination": {
      "total": 100,
      "per_page": 15,
      "current_page": 1,
      "last_page": 7,
      "from": 1,
      "to": 15,
      "has_more_pages": true
    },
    "_links": {
      "first": "http://localhost/api/v1/products?page=1",
      "last": "http://localhost/api/v1/products?page=7",
      "prev": null,
      "next": "http://localhost/api/v1/products?page=2",
      "self": "http://localhost/api/v1/products?page=1"
    }
  }
}
```

### ✅ Pattern 3: Error Handling and Status Codes
**Trạng thái**: ĐÃ TỐT từ trước

- Exception Handler tập trung trong `app/Exceptions/Handler.php`
- Status codes chính xác:
  - 200: Success
  - 201: Created
  - 204: No Content
  - 400: Bad Request
  - 401: Unauthorized
  - 403: Forbidden
  - 404: Not Found
  - 422: Validation Error
  - 500: Internal Server Error
- Response format nhất quán với `success`, `message`, `errors`

### ✅ Pattern 4: HATEOAS (Hypermedia as the Engine of Application State)
**Trạng thái**: MỚI THÊM

**File tạo mới:**
- `backend/app/Http/Resources/Concerns/HasHypermediaLinks.php` - Trait cho HATEOAS

**Cập nhật:**
- `backend/app/Http/Resources/ProductResource.php` - Thêm `_links`
- `backend/app/Http/Resources/PostResource.php` - Thêm `_links`

**Ví dụ Product response với HATEOAS:**
```json
{
  "id": 1,
  "name": "Sản phẩm A",
  "slug": "san-pham-a",
  "price": 100000,
  "_links": {
    "self": {
      "href": "http://localhost/api/v1/products/san-pham-a"
    },
    "update": {
      "href": "http://localhost/api/v1/products/san-pham-a",
      "method": "PUT"
    },
    "delete": {
      "href": "http://localhost/api/v1/products/san-pham-a",
      "method": "DELETE"
    },
    "categories": {
      "href": "http://localhost/api/v1/product-categories?product_id=1"
    }
  }
}
```

**Lợi ích HATEOAS:**
- Client không cần hard-code URLs
- API tự mô tả (self-descriptive)
- Dễ dàng thêm/sửa endpoints mà không break client
- Links hiển thị các actions khả dụng dựa trên permissions

## Controllers Đã Cập Nhật

### ProductController
**File**: `backend/app/Http/Controllers/Api/V1/ProductController.php`

**Thay đổi:**
```php
// Trước
return $this->success(
    ProductResource::collection($products),
    'Products retrieved successfully'
);

// Sau
return $this->success(
    PaginatedResponse::makeWithLinks(ProductResource::collection($products)),
    'Products retrieved successfully'
);
```

### PostController
**File**: `backend/app/Http/Controllers/Api/V1/PostController.php`

**Thay đổi:** Tương tự ProductController

## Cách Sử Dụng

### 1. Sử dụng PaginatedResponse trong Controller

```php
use App\Http\Helpers\PaginatedResponse;

// Với HATEOAS links
return $this->success(
    PaginatedResponse::makeWithLinks(YourResource::collection($paginator)),
    'Data retrieved successfully'
);

// Không cần HATEOAS links
return $this->success(
    PaginatedResponse::make(YourResource::collection($paginator)),
    'Data retrieved successfully'
);
```

### 2. Thêm HATEOAS vào Resource mới

```php
use App\Http\Resources\Concerns\HasHypermediaLinks;

class YourResource extends BaseResource
{
    use HasHypermediaLinks;

    protected string $resourceType = 'your-resources';

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // ... các fields khác
            '_links' => $this->generateLinks(),
        ];
    }

    // Override nếu cần custom links
    protected function relatedLinks(): array
    {
        return [
            'related-resource' => [
                'href' => $this->baseUrl() . '/related/' . $this->id,
            ],
        ];
    }

    // Override nếu cần check permissions
    protected function canUpdate(): bool
    {
        return request()->user()?->isAdmin() ?? false;
    }
}
```

## Checklist cho API Endpoints Mới

Khi tạo API endpoint mới, đảm bảo:

- [ ] Routes tuân thủ resource-oriented design
- [ ] Sử dụng đúng HTTP methods (GET, POST, PUT, PATCH, DELETE)
- [ ] List endpoints trả về paginated response với metadata đầy đủ
- [ ] Resources có `_links` cho HATEOAS (nếu phù hợp)
- [ ] Error handling đúng với status codes chuẩn
- [ ] Có validation cho input
- [ ] Có authorization/permission checks

## Tài Liệu Tham Khảo

- Skill: `.claude/skills/api-design-principles/SKILL.md`
- REST API Best Practices: https://restfulapi.net/
- HATEOAS: https://en.wikipedia.org/wiki/HATEOAS
