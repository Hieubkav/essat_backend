# Chính Sách Mật Khẩu (Password Policy)

## Yêu Cầu Hiện Tại

Mật khẩu người dùng phải đáp ứng các tiêu chuẩn sau:

- **Độ dài tối thiểu:** 8 ký tự
- **Không có yêu cầu về ký tự đặc biệt, chữ hoa, hoặc chữ số**

## Ví Dụ Mật Khẩu Hợp Lệ

- `password12`
- `abcdefgh`
- `12345678`
- `admin@123`
- `Welcome1`

## Cảnh Báo Cho Admin

⚠️ **Lưu ý quan trọng:** Quy tắc mật khẩu hiện tại rất đơn giản và không yêu cầu độ phức tạp cao.

**Khuyến nghị:**
- Thông báo cho người dùng về tầm quan trọng của mật khẩu mạnh
- Hướng dẫn người dùng tạo mật khẩu khó đoán
- Nên sử dụng các ký tự chữ hoa, thường, số, ký tự đặc biệt khi có thể
- Thường xuyên nhắc nhở người dùng thay đổi mật khẩu

## Nâng Cấp Trong Tương Lai

Nếu muốn yêu cầu mật khẩu mạnh hơn, hãy sửa các file:
1. `app/Http/Requests/Auth/RegisterRequest.php`
2. `app/Http/Controllers/Api/V1/UserController.php` (phương thức `store`)
3. Update frontend validation tương ứng

### Quy Tắc Được Đề Xuất
```php
'password' => [
    'required',
    'string',
    'min:8',
    'confirmed',
    'regex:/[A-Z]/',      // Ít nhất 1 chữ hoa
    'regex:/[a-z]/',      // Ít nhất 1 chữ thường
    'regex:/[0-9]/',      // Ít nhất 1 chữ số
    'regex:/[!@#$%^&*]/', // Ít nhất 1 ký tự đặc biệt (tuỳ chọn)
],
```
