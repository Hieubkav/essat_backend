<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    'column.name' => 'Tên',
    'column.guard_name' => 'Guard',
    'column.roles' => 'Vai trò',
    'column.permissions' => 'Quyền',
    'column.updated_at' => 'Cập nhật',
    'column.team' => 'Nhóm',

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    'field.name' => 'Tên vai trò',
    'field.guard_name' => 'Guard',
    'field.permissions' => 'Quyền',
    'field.select_all.name' => 'Chọn tất cả',
    'field.select_all.message' => 'Bật tất cả quyền cho vai trò này',
    'field.team' => 'Nhóm',
    'field.team.placeholder' => 'Chọn nhóm',

    /*
    |--------------------------------------------------------------------------
    | Navigation & Resource
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Hệ thống',
    'nav.role.label' => 'Vai trò',
    'nav.role.icon' => 'heroicon-o-shield-check',
    'resource.label.role' => 'Vai trò',
    'resource.label.roles' => 'Vai trò',

    /*
    |--------------------------------------------------------------------------
    | Section & Tabs
    |--------------------------------------------------------------------------
    */

    'section' => 'Thực thể',
    'resources' => 'Quản lý dữ liệu',
    'widgets' => 'Widget',
    'pages' => 'Trang',
    'custom' => 'Quyền tùy chỉnh',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'Bạn không có quyền truy cập.',

    /*
    |--------------------------------------------------------------------------
    | Resource Permissions' Labels
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'Xem chi tiết',
        'view_any' => 'Xem danh sách',
        'create' => 'Thêm mới',
        'update' => 'Chỉnh sửa',
        'delete' => 'Xóa',
        'delete_any' => 'Xóa nhiều',
        'force_delete' => 'Xóa vĩnh viễn',
        'force_delete_any' => 'Xóa vĩnh viễn nhiều',
        'restore' => 'Khôi phục',
        'restore_any' => 'Khôi phục nhiều',
        'reorder' => 'Sắp xếp',
        'replicate' => 'Nhân bản',
    ],
];
