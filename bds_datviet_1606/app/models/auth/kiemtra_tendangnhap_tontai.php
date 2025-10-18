<?php
header('Content-Type: application/json');

// 1. KẾT NỐI CSDL
require_once "../../../config/database.php"; // Đảm bảo đường dẫn này đúng

// Lấy tên đăng nhập từ query string
$tendangnhap = trim($_GET['tendangnhap'] ?? '');

// Nếu không có tên đăng nhập, không cần kiểm tra
if (empty($tendangnhap)) {
    // Trả về false để không báo lỗi không cần thiết trên giao diện
    echo json_encode(['exists' => false]);
    exit;
}

try {
    $pdo = ketnoicsdl();
    $sql = "SELECT COUNT(*) FROM nguoi_dung WHERE ten_dang_nhap = :tendangnhap";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tendangnhap' => $tendangnhap]);
    $count = $stmt->fetchColumn();

    // Trả về true nếu count > 0 (đã tồn tại), ngược lại trả về false
    echo json_encode(['exists' => $count > 0]);

} catch (PDOException $e) {
    // Trong trường hợp lỗi CSDL, không thể xác nhận.
    // Ghi log lỗi để admin xem, nhưng trả về false để không chặn người dùng.
    error_log("Lỗi kiểm tra tên đăng nhập: " . $e->getMessage());
    echo json_encode(['exists' => false, 'error' => 'Lỗi máy chủ khi kiểm tra tên đăng nhập.']);
}
?>
