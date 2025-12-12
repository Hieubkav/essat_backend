## Ngôn ngữ
Trả lời bằng tiếng Việt

## Nguyên tắc thiết kế
- **SOLID**: Thiết kế OOP rõ ràng, linh hoạt
- **KISS**: Giữ mọi thứ đơn giản, không over-engineering
- **YAGNI**: Không làm thứ chưa cần
- **DRY**: Không lặp lại logic
- **TDA**: Ra lệnh cho object, không lấy dữ liệu ra xử lý

## API Structure

Xem tài liệu chi tiết tại: `docs/`

### Kiến trúc tầng (Layered Architecture)
- **Controller** (`Http/Controllers/Api`) - Xử lý HTTP request/response, KHÔNG có try-catch
- **Service** (`Services`) - Logic nghiệp vụ, extends BaseService
- **Repository** (`Repositories`) - Truy cập dữ liệu (chỉ dùng khi có custom queries)
- **Model** (`Models`) - Eloquent models
- **Request** (`Http/Requests`) - Validation input, extends BaseFormRequest
- **Resource** (`Http/Resources`) - Transform output
- **Exception** (`Exceptions`) - Custom exceptions, xử lý global trong Handler.php

### Response format
```json
{
  "success": true|false,
  "message": "...",
  "data": {...}|[...],
  "errors": {...}
}
```

### Exception Handling (QUAN TRỌNG)
**KHÔNG dùng try-catch trong Controller!** Exceptions được xử lý global trong `Handler.php`:
- `ValidationException` → 422
- `ModelNotFoundException` → 404
- `ApiException` → custom status code
- `HttpException` → tương ứng HTTP status
- Các exception khác → 500

```php
// ✅ ĐÚNG - Controller clean, không try-catch
public function store(PostStoreRequest $request): JsonResponse
{
    $post = $this->postService->create($request->validated());
    return $this->created(new PostResource($post), 'Post created');
}

// ❌ SAI - Không cần try-catch
public function store(Request $request): JsonResponse
{
    try {
        $post = $this->postService->create($request->validated());
        return $this->created(new PostResource($post));
    } catch (\Exception $e) {
        return $this->error($e->getMessage(), 500);
    }
}
```

### Dùng ApiException cho business errors
```php
use App\Exceptions\ApiException;

// Trong Service hoặc Controller
if ($adminCount <= 1) {
    throw new ApiException('Cannot delete the last admin', 400);
}
```

### Khi tạo feature mới
1. Tạo Migration & Model
2. Tạo Service (extends BaseService)
3. Tạo Repository (CHỈ nếu cần custom queries phức tạp)
4. Tạo Controller (extends ApiController, KHÔNG try-catch)
5. Tạo Form Requests (StoreRequest, UpdateRequest) extends BaseFormRequest
6. Tạo Resources (Resource, Collection)
7. Thêm routes vào `routes/api/v1.php`
8. Test API

### Routes naming
- `GET /api/v1/users` - List
- `POST /api/v1/users` - Create
- `GET /api/v1/users/{id}` - Show
- `PUT/PATCH /api/v1/users/{id}` - Update
- `DELETE /api/v1/users/{id}` - Delete

## 📚 Documentation

Xem `docs/README.md` để tiếp cận tài liệu chi tiết:
- `docs/api/STRUCTURE.md` - Kiến trúc API
- `docs/api/RESPONSE_FORMAT.md` - Format response
- `docs/api/VERSIONING.md` - Versioning strategy
- `docs/api/AUTHENTICATION.md` - Auth & Authorization
- `docs/api/ERROR_HANDLING.md` - Error handling
- `docs/guides/GETTING_STARTED.md` - Hướng dẫn bắt đầu
- `docs/guides/CREATING_FEATURES.md` - Tạo feature
- `docs/guides/BEST_PRACTICES.md` - Best practices


## Filament
- EditRecord pages phải extend `App\Filament\Pages\BaseEditRecord` để giữ nguyên trang edit sau khi lưu

## File Upload
- **Chuyển đổi ảnh sang WebP**: Ảnh upload (JPEG, PNG, GIF) phải tự động chuyển sang WebP với quality 80 bằng `Spatie\Image\Image`
- **Observer xóa file**: Mọi Model có trường lưu đường dẫn file (ảnh, video, PDF...) phải có Observer để:
  - `updating`: Xóa file cũ khi đường dẫn thay đổi
  - `deleted`: Xóa tất cả file liên quan khi xóa record
- Sử dụng `Storage::disk('public')->delete($path)` để xóa file