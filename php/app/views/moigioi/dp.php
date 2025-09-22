<?php
function ketnoicsdl() {
    $host = "localhost";   // XAMPP chạy MySQL trên localhost
    $dbname = "csdl_bds";  // Tên CSDL bạn đã tạo
    $user = "root";        // User mặc định của XAMPP
    $pass = "";            // Mặc định root không có mật khẩu

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Lỗi kết nối CSDL: " . $e->getMessage());
    }
}
?>
