<?php
// File này chỉ dùng cho kết nối database, không chứa session_start()

$host = 'localhost';         // Địa chỉ MySQL server (thường là localhost)
$db   = 'nhadep24h';         // Tên database của bạn
$user = 'root';              // Tài khoản MySQL
$pass = '';                  // Mật khẩu MySQL (nếu có thì điền vào)
$charset = 'utf8mb4';        // Charset nên để utf8mb4 cho Unicode

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Bắt lỗi dưới dạng Exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Trả dữ liệu dạng mảng kết hợp
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Tối ưu bảo mật
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);  // Biến $pdo này dùng ở các file khác
} catch (\PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}
?>