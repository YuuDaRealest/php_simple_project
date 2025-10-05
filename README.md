## Cài đặt thêm
sudo apt install php-mysql php-pdo

### Thay đổi cấu hình database
Chỉnh sửa file `config/database.php`:
```php
private $host = 'your_host';
private $db_name = 'your_database_name';
private $username = 'your_username';
private $password = 'your_password';
```

### Lỗi kết nối database
- Kiểm tra thông tin kết nối trong `config/database.php`
- Đảm bảo MySQL service đang chạy
- Kiểm tra quyền truy cập database

### Lỗi PHP
- Kiểm tra phiên bản PHP (cần 7.4+)
- Đảm bảo PDO extension được cài đặt
- Kiểm tra error log của web server