<?php

return [
    'exception_message' => 'Thông điệp ngoại lệ: :message',
    'exception_trace' => 'Stack trace: :trace',
    'exception_message_title' => 'Thông điệp ngoại lệ',
    'exception_trace_title' => 'Stack trace',

    'backup_failed_subject' => 'Sao lưu :application_name thất bại',
    'backup_failed_body' => 'Quan trọng: Đã xảy ra lỗi khi sao lưu :application_name',

    'backup_successful_subject' => 'Sao lưu mới của :application_name thành công',
    'backup_successful_subject_title' => 'Sao lưu thành công!',
    'backup_successful_body' => 'Tin vui: bản sao lưu mới của :application_name đã được tạo thành công trên ổ :disk_name.',

    'cleanup_failed_subject' => 'Dọn dẹp bản sao lưu của :application_name thất bại',
    'cleanup_failed_body' => 'Đã xảy ra lỗi khi dọn dẹp các bản sao lưu của :application_name',

    'cleanup_successful_subject' => 'Dọn dẹp bản sao lưu :application_name thành công',
    'cleanup_successful_subject_title' => 'Dọn dẹp thành công!',
    'cleanup_successful_body' => 'Các bản sao lưu của :application_name trên ổ :disk_name đã được dọn dẹp thành công.',

    'healthy_backup_found_subject' => 'Bản sao lưu của :application_name trên ổ :disk_name đang ổn định',
    'healthy_backup_found_subject_title' => 'Bản sao lưu của :application_name đang ổn định',
    'healthy_backup_found_body' => 'Các bản sao lưu của :application_name được đánh giá là khỏe mạnh. Làm tốt lắm!',

    'unhealthy_backup_found_subject' => 'Quan trọng: bản sao lưu của :application_name không ổn định',
    'unhealthy_backup_found_subject_title' => 'Quan trọng: bản sao lưu của :application_name không ổn định. :problem',
    'unhealthy_backup_found_body' => 'Các bản sao lưu của :application_name trên ổ :disk_name đang không ổn định.',
    'unhealthy_backup_found_not_reachable' => 'Không thể kết nối tới nơi lưu trữ sao lưu. :error',
    'unhealthy_backup_found_empty' => 'Chưa có bản sao lưu nào của ứng dụng này.',
    'unhealthy_backup_found_old' => 'Bản sao lưu mới nhất vào :date đã quá cũ.',
    'unhealthy_backup_found_unknown' => 'Rất tiếc, không xác định được nguyên nhân chính xác.',
    'unhealthy_backup_found_full' => 'Các bản sao lưu đang dùng quá nhiều dung lượng. Hiện dùng :disk_usage, vượt giới hạn cho phép :disk_limit.',

    'no_backups_info' => 'Chưa có bản sao lưu nào',
    'application_name' => 'Tên ứng dụng',
    'backup_name' => 'Tên bản sao lưu',
    'disk' => 'Ổ lưu trữ',
    'newest_backup_size' => 'Dung lượng bản sao lưu mới nhất',
    'number_of_backups' => 'Số lượng bản sao lưu',
    'total_storage_used' => 'Tổng dung lượng đã dùng',
    'newest_backup_date' => 'Ngày sao lưu mới nhất',
    'oldest_backup_date' => 'Ngày sao lưu cũ nhất',
];
